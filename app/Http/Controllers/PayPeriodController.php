<?php

namespace App\Http\Controllers;

use App\Models\PayPeriod;
use App\Models\PaySlip;
use App\Models\Centre;
use App\Models\Personnel;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayPeriodController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Liste toutes les périodes de paie.
     */
    public function index()
    {
        $periods = PayPeriod::orderByDesc('code')->get();
        return view('pay_periods.index', compact('periods'));
    }

    /**
     * Ouvre un nouveau mois de paie.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        // Vérifier s'il y a déjà une période ouverte
        $hasOpen = PayPeriod::where('statut', 'ouvert')->exists();
        if ($hasOpen) {
            return back()->with('error', 'Impossible d\'ouvrir un nouveau mois. Veuillez d\'abord clôturer le mois en cours.');
        }

        $request->validate([
            'code'  => 'required|string|regex:/^\d{4}-\d{2}$/|unique:pay_periods,code',
            'label' => 'required|string|max:100',
        ], [
            'code.unique' => 'Ce mois de paie a déjà été initialisé.',
            'code.regex'  => 'Le format du code doit être AAAA-MM (ex: 2026-08).',
        ]);

        $period = PayPeriod::create([
            'code'       => $request->code,
            'label'      => $request->label,
            'statut'     => 'ouvert',
            'created_by' => Auth::id(),
        ]);

        // Initialiser automatiquement les fiches de paie pour les agents actifs
        $this->payrollService->initialiserBulletinsPourPeriode($period->id, $period->code);

        return redirect()->route('pay-periods.show', $period->id)->with('success', 'Mois de paie ouvert avec succès.');
    }

    /**
     * Affiche les détails du mois de paie et la liste des fiches de paie.
     */
    public function show(PayPeriod $payPeriod, Request $request)
    {
        $user = Auth::user();
        $centres = Centre::where('actif', true)->orderBy('ordre')->get();

        // Résolution du centre actif
        if ($user->isGlobal()) {
            $centreId = $request->get('centre_id') ?: ($centres->first()?->id ?? 0);
        } else {
            $centreId = $user->centre_id;
        }

        $selectedCentre = Centre::find($centreId);
        if (!$selectedCentre) {
            abort(404, 'Centre non trouvé.');
        }

        // Si la période est ouverte, s'assurer que les bulletins pour ce centre sont bien initialisés
        if ($payPeriod->statut === 'ouvert') {
            $this->payrollService->initialiserBulletinsPourPeriode($payPeriod->id, $payPeriod->code, $selectedCentre->id);
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $selectedCentre->id)
            ->get();

        return view('pay_periods.show', compact('payPeriod', 'selectedCentre', 'centres', 'slips', 'user'));
    }

    /**
     * Clôture le mois de paie en cours.
     */
    public function cloture(PayPeriod $payPeriod)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        if ($payPeriod->statut !== 'ouvert') {
            return back()->with('error', 'Cette période est déjà clôturée.');
        }

        DB::transaction(function () use ($payPeriod, $user) {
            // Passer le statut de la période à cloture
            $payPeriod->update([
                'statut' => 'cloture',
            ]);

            // Mettre à jour l'utilisateur qui valide tous les bulletins de la période
            PaySlip::where('pay_period_id', $payPeriod->id)
                ->update(['validated_by' => $user->id]);

            // Traiter la décrémentation des échéanciers (avances, frais médicaux)
            $this->payrollService->traiterClotureAjustementsPourPeriode($payPeriod->id);
        });

        return redirect()->route('pay-periods.index')->with('success', 'Mois de paie clôturé avec succès. Les informations sont maintenant verrouillées en lecture seule.');
    }

    /**
     * Génère l'état du Livre de paie pour un centre (PDF/Impression).
     */
    public function livreDePaie(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé aux données de ce centre.');
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $centre->id)
            ->get();

        return view('pay_periods.exports.livre_paie', compact('payPeriod', 'centre', 'slips'));
    }

    /**
     * Génère le Registre de paie.
     */
    public function registreDePaie(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé.');
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $centre->id)
            ->get();

        return view('pay_periods.exports.registre_paie', compact('payPeriod', 'centre', 'slips'));
    }

    /**
     * Génère l'état des virements par Banque (BOA, Ecobank, Archevêché).
     */
    public function virements(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé.');
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $centre->id)
            ->get();

        // Grouper par Banque
        $groupedSlips = $slips->groupBy('banque');

        return view('pay_periods.exports.virements', compact('payPeriod', 'centre', 'groupedSlips', 'slips'));
    }

    /**
     * Génère la déclaration CNSS.
     */
    public function declarationCnss(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé.');
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $centre->id)
            ->get();

        return view('pay_periods.exports.cnss', compact('payPeriod', 'centre', 'slips'));
    }

    /**
     * Génère la déclaration ITS (Impôt).
     */
    public function declarationIts(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé.');
        }

        $slips = PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriod->id)
            ->where('centre_id', $centre->id)
            ->get();

        return view('pay_periods.exports.its', compact('payPeriod', 'centre', 'slips'));
    }
}
