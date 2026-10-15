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
        $user = auth()->user();
        $personnelsQuery = Personnel::where('statut', 'actif')->orderBy('nom')->orderBy('prenoms');

        // Filtrer par centre si l'utilisateur n'est pas global
        if (!$user->isGlobal() && $user->centre_id) {
            $personnelsQuery->where('centre_id', $user->centre_id);
        }

        $personnels = $personnelsQuery->get();
        $types      = Absence::typesDisponibles();
        return view('absences.create', compact('personnels', 'types'));
    }

    public function store(Request $request)
    {
        $modeSelection = $request->input('mode_selection', 'plage');
        $rules = [
            'personnel_id'   => 'required|exists:personnels,id',
            'type_absence'   => 'required|in:' . implode(',', array_keys(Absence::typesDisponibles())),
            'mode_selection' => 'required|in:plage,specifique',
            'motif'          => 'nullable|string|max:255',
            'observations'   => 'nullable|string',
            'action'         => 'required|in:brouillon,soumettre',
        ];

        if ($modeSelection === 'specifique') {
            $rules['jours_specifiques'] = 'required';
        } else {
            $rules['date_debut'] = 'required|date';
            $rules['date_fin']   = 'required|date|after_or_equal:date_debut';
        }

        $data = $request->validate($rules);

        $types      = Absence::typesDisponibles();
        $meta       = $types[$data['type_absence']];
        $deductible = $meta['deductible'];

        $joursSpecifiques = null;
        if ($modeSelection === 'specifique') {
            $rawDates = is_array($request->jours_specifiques)
                ? $request->jours_specifiques
                : (json_decode($request->jours_specifiques, true) ?: explode(',', $request->jours_specifiques));

            $dates = collect($rawDates)
                ->filter()
                ->map(fn($d) => Carbon::parse(trim($d))->format('Y-m-d'))
                ->unique()
                ->sort()
                ->values()
                ->all();

            if (empty($dates)) {
                return back()->withInput()->withErrors(['jours_specifiques' => 'Veuillez sélectionner au moins une date d\'absence.']);
            }

            $dateDebut = Carbon::parse($dates[0]);
            $dateFin   = Carbon::parse(end($dates));
            $nbJours   = count($dates);
            $joursSpecifiques = $dates;
        } else {
            $dateDebut = Carbon::parse($data['date_debut']);
            $dateFin   = Carbon::parse($data['date_fin']);

            if (!$deductible && $meta['jours']) {
                $nbJours = (int) $meta['jours'];
                // Décompte de tous les jours (weekends et fériés inclus)
                $dateFin = $dateDebut->copy()->addDays($nbJours - 1);
            } else {
                // Décompte calendaire réel (tous les jours inclus)
                $nbJours = Absence::compterJoursCalendaires($dateDebut, $dateFin);
            }
        }

        $absence = Absence::create([
            'personnel_id'      => $data['personnel_id'],
            'type_absence'      => $data['type_absence'],
            'mode_selection'    => $modeSelection,
            'deductible'        => $deductible,
            'date_debut'        => $dateDebut,
            'date_fin'          => $dateFin,
            'nb_jours'          => $nbJours,
            'jours_specifiques' => $joursSpecifiques,
            'motif'             => $data['motif'] ?? null,
            'observations'      => $data['observations'] ?? null,
            'statut'            => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
            'cree_par'          => Auth::id(),
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
        $user = auth()->user();
        $personnelsQuery = Personnel::where('statut', 'actif')->orderBy('nom')->orderBy('prenoms');
        if (!$user->isGlobal() && $user->centre_id) {
            $personnelsQuery->where('centre_id', $user->centre_id);
        }
        $personnels = $personnelsQuery->get();
        $types      = Absence::typesDisponibles();
        return view('absences.edit', compact('absence', 'personnels', 'types'));
    }

    public function update(Request $request, Absence $absence)
    {
        abort_if(!$absence->isEditable(), 403);

        $modeSelection = $request->input('mode_selection', $absence->mode_selection ?: 'plage');
        $rules = [
            'type_absence'   => 'required|in:' . implode(',', array_keys(Absence::typesDisponibles())),
            'mode_selection' => 'required|in:plage,specifique',
            'motif'          => 'nullable|string|max:255',
            'observations'   => 'nullable|string',
            'action'         => 'required|in:brouillon,soumettre',
        ];

        if ($modeSelection === 'specifique') {
            $rules['jours_specifiques'] = 'required';
        } else {
            $rules['date_debut'] = 'required|date';
            $rules['date_fin']   = 'required|date|after_or_equal:date_debut';
        }

        $data = $request->validate($rules);

        $types      = Absence::typesDisponibles();
        $meta       = $types[$data['type_absence']];
        $deductible = $meta['deductible'];

        $joursSpecifiques = null;
        if ($modeSelection === 'specifique') {
            $rawDates = is_array($request->jours_specifiques)
                ? $request->jours_specifiques
                : (json_decode($request->jours_specifiques, true) ?: explode(',', $request->jours_specifiques));

            $dates = collect($rawDates)
                ->filter()
                ->map(fn($d) => Carbon::parse(trim($d))->format('Y-m-d'))
                ->unique()
                ->sort()
                ->values()
                ->all();

            if (empty($dates)) {
                return back()->withInput()->withErrors(['jours_specifiques' => 'Veuillez sélectionner au moins une date d\'absence.']);
            }

            $dateDebut = Carbon::parse($dates[0]);
            $dateFin   = Carbon::parse(end($dates));
            $nbJours   = count($dates);
            $joursSpecifiques = $dates;
        } else {
            $dateDebut = Carbon::parse($data['date_debut']);
            $dateFin   = Carbon::parse($data['date_fin']);

            if (!$deductible && $meta['jours']) {
                $nbJours = (int) $meta['jours'];
                $dateFin = $dateDebut->copy()->addDays($nbJours - 1);
            } else {
                $nbJours = Absence::compterJoursCalendaires($dateDebut, $dateFin);
            }
        }

        $absence->update([
            'type_absence'      => $data['type_absence'],
            'mode_selection'    => $modeSelection,
            'deductible'        => $deductible,
            'date_debut'        => $dateDebut,
            'date_fin'          => $dateFin,
            'nb_jours'          => $nbJours,
            'jours_specifiques' => $joursSpecifiques,
            'motif'             => $data['motif'] ?? null,
            'observations'      => $data['observations'] ?? null,
            'statut'            => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
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
            $signPath = Auth::user()->signature_path ?: \App\Models\ConfigRh::get('drh_signature_path', null, Auth::user());
        }

        $absence->update([
            'statut'         => 'approuve',
            'approuve_par'   => Auth::id(),
            'approuve_le'    => now(),
            'signature_path' => $signPath,
            'reference'      => $this->doc->resolveReference($request->reference, now(), null, $absence->personnel?->centre_id),
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

    // ── Calcul dynamique (AJAX) ────────────────────────────────────────────────
    public function calculDetails(Request $request)
    {
        $types = Absence::typesDisponibles();
        $meta  = $types[$request->type_absence] ?? null;

        if (!$meta) {
            return response()->json(['error' => 'Type d\'absence invalide'], 422);
        }

        $mode = $request->input('mode_selection', 'plage');
        $deductible = $meta['deductible'];

        if ($mode === 'specifique') {
            $rawDates = $request->input('jours_specifiques');
            if (is_string($rawDates)) {
                $rawDates = json_decode($rawDates, true) ?: array_filter(array_map('trim', explode(',', $rawDates)));
            }

            $dates = collect($rawDates)
                ->filter()
                ->map(fn($d) => Carbon::parse(trim($d))->format('Y-m-d'))
                ->unique()
                ->sort()
                ->values()
                ->all();

            $nbJours   = count($dates);
            $dateDebut = !empty($dates) ? Carbon::parse($dates[0]) : null;
            $dateFin   = !empty($dates) ? Carbon::parse(end($dates)) : null;
            $dateReprise = $dateFin ? $dateFin->copy()->addDay() : null;

            return response()->json([
                'nb_jours'        => $nbJours,
                'date_debut'      => $dateDebut ? $dateDebut->format('Y-m-d') : null,
                'date_fin'        => $dateFin ? $dateFin->format('Y-m-d') : null,
                'date_fin_fr'     => $dateFin ? $dateFin->isoFormat('dddd D MMMM YYYY') : null,
                'date_reprise'    => $dateReprise ? $dateReprise->format('Y-m-d') : null,
                'date_reprise_fr' => $dateReprise ? $dateReprise->isoFormat('dddd D MMMM YYYY') : null,
            ]);
        }

        // Mode Plage
        $request->validate([
            'date_debut'   => 'required|date',
            'date_fin'     => 'nullable|date',
            'type_absence' => 'required|string',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);

        if (!$deductible && $meta['jours']) {
            $nbJours = (int) $meta['jours'];
            $dateFin = $dateDebut->copy()->addDays($nbJours - 1);
        } else {
            $dateFin = $request->filled('date_fin') ? Carbon::parse($request->date_fin) : null;
            if ($dateFin) {
                if ($dateFin->lt($dateDebut)) {
                    return response()->json(['error' => 'La date de fin doit être après la date de début'], 422);
                }
                $nbJours = Absence::compterJoursCalendaires($dateDebut, $dateFin);
            } else {
                $nbJours = 0;
            }
        }

        $dateReprise = $dateFin ? $dateFin->copy()->addDay() : null;

        return response()->json([
            'nb_jours'        => $nbJours,
            'date_fin'        => $dateFin ? $dateFin->format('Y-m-d') : null,
            'date_fin_fr'     => $dateFin ? $dateFin->isoFormat('dddd D MMMM YYYY') : null,
            'date_reprise'    => $dateReprise ? $dateReprise->format('Y-m-d') : null,
            'date_reprise_fr' => $dateReprise ? $dateReprise->isoFormat('dddd D MMMM YYYY') : null,
        ]);
    }
}
