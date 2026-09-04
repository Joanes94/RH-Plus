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
use ZipArchive;
use App\Http\Controllers\PaySlipController;
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
    public function index(Request $request)
    {
        $user = Auth::user();
        $centres = Centre::where('actif', true)->orderBy('ordre')->get();

        if ($user->isGlobal()) {
            // Rôles globaux : CRH, DDIS, DDRH voient toutes les périodes de tous les centres (avec filtre optionnel)
            $query = PayPeriod::with('centre', 'createdBy')->orderByDesc('code')->orderBy('centre_id');
            if ($request->filled('centre_id')) {
                $query->where('centre_id', $request->centre_id);
            }
            $periods = $query->get();
        } else {
            // Rôles de centre : Assistant RH, DRH Centre, Directeur Centre ne voient QUE leur centre
            $periods = PayPeriod::with('centre', 'createdBy')
                ->where('centre_id', $user->centre_id)
                ->orderByDesc('code')
                ->get();
        }

        return view('pay_periods.index', compact('periods', 'centres', 'user'));
    }

    /**
     * Ouvre un nouveau mois de paie pour un centre.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        // Déterminer le centre cible
        if ($user->isGlobal()) {
            $request->validate([
                'centre_id' => 'required|exists:centres,id',
            ], [
                'centre_id.required' => 'Veuillez sélectionner le centre pour lequel ouvrir ce mois de paie.',
            ]);
            $targetCentreId = (int) $request->centre_id;
        } else {
            $targetCentreId = (int) $user->centre_id;
        }

        if (!$targetCentreId) {
            return back()->with('error', 'Aucun centre valide n\'est associé à cette action.');
        }

        // Vérifier s'il y a déjà une période ouverte pour CE centre
        $hasOpen = PayPeriod::where('centre_id', $targetCentreId)->where('statut', 'ouvert')->exists();
        if ($hasOpen) {
            $centre = Centre::find($targetCentreId);
            return back()->with('error', "Impossible d'ouvrir un nouveau mois pour {$centre?->nom}. Veuillez d'abord clôturer le mois en cours.");
        }

        $request->validate([
            'code'  => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}$/',
                \Illuminate\Validation\Rule::unique('pay_periods')->where(fn($q) => $q->where('centre_id', $targetCentreId)),
            ],
            'label' => 'required|string|max:100',
        ], [
            'code.unique' => 'Ce mois de paie a déjà été initialisé pour ce centre.',
            'code.regex'  => 'Le format du code doit être AAAA-MM (ex: 2026-08).',
        ]);

        $period = PayPeriod::create([
            'centre_id'  => $targetCentreId,
            'code'       => $request->code,
            'label'      => $request->label,
            'statut'     => 'ouvert',
            'created_by' => $user->id,
        ]);

        // Initialiser automatiquement les fiches de paie pour les agents actifs de CE centre
        $this->payrollService->initialiserBulletinsPourPeriode($period->id, $period->code, $targetCentreId);

        return redirect()->route('pay-periods.show', $period->id)->with('success', 'Mois de paie ouvert avec succès pour ce centre.');
    }

    /**
     * Affiche les détails du mois de paie et la liste des fiches de paie du centre concerné.
     */
    public function show(PayPeriod $payPeriod, Request $request)
    {
        $user = Auth::user();

        if (!$user->isGlobal() && $user->centre_id !== $payPeriod->centre_id) {
            abort(403, 'Accès non autorisé aux périodes de paie de ce centre.');
        }

        $selectedCentre = $payPeriod->centre ?: ($user->centre ?: Centre::first());
        if (!$selectedCentre) {
            abort(404, 'Centre non trouvé.');
        }

        $slips = $this->getPayableSlips($payPeriod->id, $selectedCentre->id);

        return view('pay_periods.show', compact('payPeriod', 'selectedCentre', 'slips', 'user'));
    }

    /**
     * Clôture le mois de paie en cours pour le centre concerné.
     */
    public function cloture(PayPeriod $payPeriod)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        if (!$user->isGlobal() && $user->centre_id !== $payPeriod->centre_id) {
            abort(403, 'Accès non autorisé.');
        }

        if ($payPeriod->statut !== 'ouvert') {
            return back()->with('error', 'Cette période est déjà clôturée.');
        }

        DB::transaction(function () use ($payPeriod, $user) {
            $payPeriod->update(['statut' => 'cloture']);

            PaySlip::where('pay_period_id', $payPeriod->id)
                ->update(['validated_by' => $user->id]);

            $this->payrollService->traiterClotureAjustementsPourPeriode($payPeriod->id);
        });

        return redirect()->route('pay-periods.index')->with('success', 'Mois de paie clôturé avec succès pour ce centre. Les informations sont maintenant verrouillées en lecture seule.');
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

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

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

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

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

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);
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
            abort(403, 'Accès non autorisé aux données de ce centre.');
        }

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

        return view('pay_periods.exports.cnss', compact('payPeriod', 'centre', 'slips'));
    }

    /**
     * Génère la déclaration ITS (Impôt).
     */
    public function declarationIts(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé aux données de ce centre.');
        }

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

        return view('pay_periods.exports.its', compact('payPeriod', 'centre', 'slips'));
    }

    /**
     * Envoie par email les bulletins de paie de tout le personnel actif du centre.
     */
    public function envoyerBulletinsEmail(PayPeriod $payPeriod, Request $request)
    {
        $user = Auth::user();
        $centreId = $request->get('centre_id') ?: ($user->centre_id ?: 0);
        $centre = Centre::find($centreId);

        if (!$centre) {
            return back()->with('error', 'Centre introuvable.');
        }

        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            abort(403, 'Accès non autorisé aux données de ce centre.');
        }

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

        if ($slips->isEmpty()) {
            return back()->with('error', 'Aucun bulletin de paie actif trouvé pour ce centre.');
        }

        $sentCount = 0;
        $missingEmailCount = 0;
        $errors = [];
        $docService = new \App\Services\DocumentService();

        foreach ($slips as $slip) {
            $personnel = $slip->personnel;
            if (!$personnel || empty($personnel->email)) {
                $missingEmailCount++;
                continue;
            }

            try {
                $recipientEmail = trim($personnel->email);
                $subject = "Votre bulletin de paie - " . $payPeriod->label . " (" . $centre->nom . ")";
                $drhInfo = $docService->resolveDrhCentre($personnel);

                \Illuminate\Support\Facades\Mail::mailer('payroll')->send('pay_slips.bulletin_email', [
                    'paySlip'   => $slip,
                    'centre'    => $centre,
                    'personnel' => $personnel,
                    'drhInfo'   => $drhInfo,
                ], function ($message) use ($recipientEmail, $subject) {
                    $message->from(
                            config('mail.mailers.payroll.from.address', 'ddis@tekton.com'),
                            config('mail.mailers.payroll.from.name', 'DDIS')
                        )
                        ->to($recipientEmail)
                        ->subject($subject);
                });

                $sentCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur d'envoi bulletin email à {$personnel->email}: " . $e->getMessage());
                $errors[] = $e->getMessage();
            }
        }

        if ($sentCount === 0 && !empty($errors)) {
            $firstError = $errors[0];
            if (str_contains($firstError, '535') || str_contains($firstError, 'BadCredentials') || str_contains($firstError, 'authenticate')) {
                return back()->with('error', "Échec de l'envoi des bulletins par email : les identifiants SMTP DDIS ne sont pas acceptés. Vérifie PAYROLL_MAIL_USERNAME / PAYROLL_MAIL_PASSWORD dans le fichier .env.");
            }
            return back()->with('error', "Échec d'envoi des bulletins par email : " . substr($firstError, 0, 250));
        }

        $msg = "{$sentCount} bulletin(s) de paie ont été transmis par email aux salariés du centre {$centre->nom}.";
        if ($missingEmailCount > 0) {
            $msg .= " ({$missingEmailCount} salarié(s) n'ont pas d'adresse email renseignée).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Retourne la liste des bulletins actifs d'un centre pour génération ZIP.
     */
    public function bulletinsList(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            return response()->json(['error' => 'Accès non autorisé aux données de ce centre.'], 403);
        }

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);

        $items = $slips->map(function ($slip) use ($payPeriod, $centre) {
            $personnel = $slip->personnel;
            $safeNom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->nom ?? 'NOM');
            $safePrenom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->prenoms ?? 'PRENOM');
            $matricule = $personnel->matricule ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->matricule) : $slip->id;

            $filename = "Bulletin_Paie_{$safeNom}_{$safePrenom}_{$matricule}_{$payPeriod->code}.pdf";

            return [
                'id'          => $slip->id,
                'nom_complet' => $personnel->nom_complet ?? 'Salarié',
                'matricule'   => $personnel->matricule ?? '-',
                'filename'    => $filename,
                'url'         => route('pay-slips.pdf', $slip->id),
            ];
        })->values();

        $safeCentre = preg_replace('/[^A-Za-z0-9_\-]/', '_', $centre->reference_suffix ?: $centre->code ?: $centre->nom);
        $zipFilename = "Bulletins_Paie_{$safeCentre}_{$payPeriod->code}.zip";

        return response()->json([
            'centre_nom'   => $centre->nom,
            'periode_code' => $payPeriod->code,
            'zip_filename' => $zipFilename,
            'total'        => $items->count(),
            'bulletins'    => $items,
        ]);
    }

    /**
     * Génère un fichier ZIP contenant tous les bulletins PDF d'un centre pour une période.
     */
    public function bulletinsZip(PayPeriod $payPeriod, Centre $centre)
    {
        $user = Auth::user();
        if (!$user->isGlobal() && $user->centre_id !== $centre->id) {
            return response()->json(['error' => 'Accès non autorisé aux données de ce centre.'], 403);
        }

        $slips = $this->getPayableSlips($payPeriod->id, $centre->id);
        if ($slips->isEmpty()) {
            return response()->json(['error' => 'Aucun bulletin trouvé pour ce centre.'], 404);
        }

        // Garde-fous : la génération de nombreux PDF (gros centres) peut être longue / gourmande en mémoire.
        @set_time_limit(300);
        if (function_exists('ini_get') && (int) ini_get('memory_limit') > 0 && (int) ini_get('memory_limit') < 512) {
            @ini_set('memory_limit', '512M');
        }

        $safeCentre = preg_replace('/[^A-Za-z0-9_\-]/', '_', $centre->reference_suffix ?: $centre->code ?: $centre->nom);
        $zipFilename = "Bulletins_Paie_{$safeCentre}_{$payPeriod->code}.zip";
        $tempPath = storage_path('app/temp/' . $zipFilename);
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['error' => 'Impossible de créer le fichier ZIP.'], 500);
        }

        try {
            // Images communes au centre : calculées UNE SEULE FOIS (et non à chaque bulletin).
            $logoBase64 = $enteteBase64 = null;
            if ($centre->logo_path) {
                $logoBase64 = $this->imageToBase64(public_path('storage/' . $centre->logo_path));
            }
            if ($centre->entete_image_path) {
                $enteteBase64 = $this->imageToBase64(public_path('storage/' . $centre->entete_image_path));
            }
            $dioceseLogoBase64 = $this->imageToBase64(public_path('images/diocese-logo.png'))
                ?: $this->imageToBase64(public_path('images/logo.png'));
            $evequePhotoBase64 = $this->imageToBase64(public_path('images/letterhead/photo_eveque.jpeg'));
            $stJeanPhotoBase64 = $this->imageToBase64(public_path('images/letterhead/photo_st_jean.png'))
                ?: $this->imageToBase64(public_path('storage/letterhead/logo_st_jean_maria_gleta.png'));

            $viewName = ($centre->code === 'ST_JEAN') ? 'pay_slips.bulletin_st_jean' : 'pay_slips.bulletin';

            foreach ($slips as $slip) {
                $slip->load(['personnel.centre', 'payPeriod']);
                $personnel = $slip->personnel;

                $safeNom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->nom ?? 'NOM');
                $safePrenom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->prenoms ?? 'PRENOM');
                $matricule = $personnel->matricule ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $personnel->matricule) : $slip->id;
                $filename = "Bulletin_Paie_{$safeNom}_{$safePrenom}_{$matricule}_{$payPeriod->code}.pdf";

                // Seule la photo de l'employé change d'un bulletin à l'autre.
                $personnelPhotoBase64 = null;
                if ($personnel && $personnel->photo_path) {
                    $personnelPhotoBase64 = $this->imageToBase64(public_path('storage/' . $personnel->photo_path));
                }

                $paySlip = $slip;
                $html = view($viewName, compact('paySlip', 'centre', 'logoBase64', 'enteteBase64', 'dioceseLogoBase64', 'evequePhotoBase64', 'stJeanPhotoBase64', 'personnelPhotoBase64'))
                    ->render();
                $pdf = \PDF::loadHTML($html);
                $zip->addFromString($filename, $pdf->output());

                // Libère la mémoire utilisée par Dompdf avant de passer au bulletin suivant.
                unset($pdf, $html);
            }

            $zip->close();
        } catch (\Throwable $e) {
            $zip->close();
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            \Illuminate\Support\Facades\Log::error('Erreur génération ZIP bulletins pour le centre ' . $centre->id . ' / période ' . $payPeriod->id . ' : ' . $e->getMessage());
            return response()->json(['error' => "Erreur lors de la génération de l'archive ZIP : " . $e->getMessage()], 500);
        }

        return response()->download($tempPath, $zipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Convertit une image locale en chaîne base64.
     * (Nécessaire pour bulletinsZip : méthode dupliquée depuis PaySlipController
     * car celle-ci est privée et n'est pas héritée entre les deux contrôleurs.)
     */
    private function imageToBase64(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Récupère les bulletins de paie valides (non fictifs et CDI/CDD) d'un centre pour une période.
     */
    private function getPayableSlips(int $payPeriodId, int $centreId)
    {
        return PaySlip::with('personnel')
            ->where('pay_period_id', $payPeriodId)
            ->where('centre_id', $centreId)
            ->where('is_fictif', false)
            ->get()
            ->filter(function ($slip) {
                $type = strtoupper($slip->personnel?->type_contrat_actuel ?? '');
                return in_array($type, ['CDI', 'CDD']);
            });
    }
}