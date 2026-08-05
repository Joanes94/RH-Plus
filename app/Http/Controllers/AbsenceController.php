<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Personnel;
use App\Models\Conge;
use App\Services\CalendrierService;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    public function __construct(
        private CalendrierService $cal,
        private DocumentService   $doc,
    ) {}

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Absence::with('personnel')->latest();

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type'))   $query->where('type_absence', $request->type);
        if ($request->filled('search')) {
            $mots = preg_split('/\s+/', trim($request->search), -1, PREG_SPLIT_NO_EMPTY);
            $query->whereHas('personnel', function ($q) use ($mots) {
                foreach ($mots as $mot) {
                    $q->where(fn ($qq) => $qq->where('nom', 'like', "%$mot%")
                                              ->orWhere('prenoms', 'like', "%$mot%"));
                }
            });
        }

        // Filtrage par centre
        $user = auth()->user();
        if ($request->filled('centre_id')) {
            $query->whereHas('personnel', fn($q) => $q->where('centre_id', $request->centre_id));
        } elseif (!$user->isGlobal() && $user->centre_id) {
            $query->whereHas('personnel', fn($q) => $q->where('centre_id', $user->centre_id));
        }

        $absences = $query->paginate(20)->withQueryString();
        $types    = Absence::typesDisponibles();
        $centres  = \App\Models\Centre::actifs()->orderBy('nom')->get();
        $selectedCentreId = $request->get('centre_id');

        return view('absences.index', compact('absences', 'types', 'centres', 'selectedCentreId'));
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        $personnels = Personnel::where('statut', 'actif')->orderBy('nom')->get();
        $types      = Absence::typesDisponibles();
        return view('absences.create', compact('personnels', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'personnel_id'  => 'required|exists:personnels,id',
            'type_absence'  => 'required|in:' . implode(',', array_keys(Absence::typesDisponibles())),
            'date_debut'    => 'required|date',
            'date_fin'      => 'required|date|after_or_equal:date_debut',
            'motif'         => 'nullable|string|max:255',
            'observations'  => 'nullable|string',
            'action'        => 'required|in:brouillon,soumettre',
        ]);

        $types       = Absence::typesDisponibles();
        $meta        = $types[$data['type_absence']];
        $deductible  = $meta['deductible'];
        $dateDebut   = Carbon::parse($data['date_debut']);
        $dateFin     = Carbon::parse($data['date_fin']);
        $nbJours     = $dateDebut->diffInDays($dateFin) + 1;

        // Pour les types non déductibles, les jours sont fixés
        if (!$deductible && $meta['jours']) {
            $nbJours = $meta['jours'];
            $dateFin = $dateDebut->copy()->addDays($nbJours - 1);
        }

        $absence = Absence::create([
            'personnel_id'  => $data['personnel_id'],
            'type_absence'  => $data['type_absence'],
            'deductible'    => $deductible,
            'date_debut'    => $dateDebut,
            'date_fin'      => $dateFin,
            'nb_jours'      => $nbJours,
            'motif'         => $data['motif'] ?? null,
            'observations'  => $data['observations'] ?? null,
            'statut'        => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
            'cree_par'      => Auth::id(),
        ]);

        $msg = $data['action'] === 'soumettre'
            ? 'Autorisation d\'absence soumise au DRH.'
            : 'Brouillon enregistré.';

        return redirect()->route('absences.show', $absence)->with('success', $msg);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Absence $absence)
    {
        $absence->load('personnel', 'creePar', 'approuvePar');
        return view('absences.show', compact('absence'));
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Absence $absence)
    {
        abort_if(!$absence->isEditable(), 403);
        $personnels = Personnel::where('statut', 'actif')->orderBy('nom')->get();
        $types      = Absence::typesDisponibles();
        return view('absences.edit', compact('absence', 'personnels', 'types'));
    }

    public function update(Request $request, Absence $absence)
    {
        abort_if(!$absence->isEditable(), 403);

        $data = $request->validate([
            'type_absence'  => 'required|in:' . implode(',', array_keys(Absence::typesDisponibles())),
            'date_debut'    => 'required|date',
            'date_fin'      => 'required|date|after_or_equal:date_debut',
            'motif'         => 'nullable|string|max:255',
            'observations'  => 'nullable|string',
            'action'        => 'required|in:brouillon,soumettre',
        ]);

        $types      = Absence::typesDisponibles();
        $meta       = $types[$data['type_absence']];
        $deductible = $meta['deductible'];
        $dateDebut  = Carbon::parse($data['date_debut']);
        $dateFin    = Carbon::parse($data['date_fin']);
        $nbJours    = $dateDebut->diffInDays($dateFin) + 1;

        if (!$deductible && $meta['jours']) {
            $nbJours = $meta['jours'];
            $dateFin = $dateDebut->copy()->addDays($nbJours - 1);
        }

        $absence->update([
            'type_absence'  => $data['type_absence'],
            'deductible'    => $deductible,
            'date_debut'    => $dateDebut,
            'date_fin'      => $dateFin,
            'nb_jours'      => $nbJours,
            'motif'         => $data['motif'] ?? null,
            'observations'  => $data['observations'] ?? null,
            'statut'        => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
        ]);

        return redirect()->route('absences.show', $absence)->with('success', 'Demande mise à jour.');
    }

    public function destroy(Absence $absence)
    {
        abort_if(!$absence->isEditable(), 403);
        $absence->delete();
        return redirect()->route('absences.index')->with('success', 'Demande supprimée.');
    }

    // ── Approbation / Rejet (DRH) ─────────────────────────────────────────────
    public function approuver(Request $request, Absence $absence)
    {
        abort_if(!Auth::user()->canApprove(), 403);
        abort_if($absence->statut !== 'soumis', 422);

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $this->doc->sauvegarderSignature($request->file('signature'));
        } else {
            $signPath = \App\Models\ConfigRh::get('drh_signature_path');
        }

        $absence->update([
            'statut'         => 'approuve',
            'approuve_par'   => Auth::id(),
            'approuve_le'    => now(),
            'signature_path' => $signPath,
            'reference'      => $this->doc->resolveReference($request->reference, now()),
        ]);

        return redirect()->route('absences.show', $absence)
            ->with('success', 'Absence approuvée. Document disponible.');
    }

    public function rejeter(Request $request, Absence $absence)
    {
        abort_if(!Auth::user()->canApprove(), 403);
        $request->validate(['motif_rejet' => 'required|string|max:500']);

        $absence->update([
            'statut'       => 'rejete',
            'motif_rejet'  => $request->motif_rejet,
            'approuve_par' => Auth::id(),
            'approuve_le'  => now(),
        ]);

        return redirect()->route('absences.show', $absence)->with('success', 'Demande rejetée.');
    }

    // ── Document officiel ──────────────────────────────────────────────────────
    public function document(Absence $absence)
    {
        abort_if($absence->statut !== 'approuve', 403);
        $absence->load('personnel');
        $data = $this->doc->buildAbsenceData($absence);
        return view('absences.document', $data);
    }
}
