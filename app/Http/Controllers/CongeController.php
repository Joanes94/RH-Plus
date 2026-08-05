<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Personnel;
use App\Models\ConfigRh;
use App\Services\CalendrierService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CongeController extends Controller
{
    public function __construct(
        private CalendrierService $cal,
        private DocumentService   $doc,
    ) {}

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Conge::with('personnel');

        if ($request->filled('search')) {
            $mots = preg_split('/\s+/', trim($request->search), -1, PREG_SPLIT_NO_EMPTY);
            $query->whereHas('personnel', function ($q) use ($mots) {
                foreach ($mots as $mot) {
                    $q->where(fn ($qq) => $qq->where('nom', 'like', "%$mot%")
                                              ->orWhere('prenoms', 'like', "%$mot%"));
                }
            });
        }
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('annee'))  $query->where('annee',  $request->annee);

        // Filtrage par centre
        $user = auth()->user();
        if ($request->filled('centre_id')) {
            $query->whereHas('personnel', fn($q) => $q->where('centre_id', $request->centre_id));
        } elseif (!$user->isGlobal() && $user->centre_id) {
            $query->whereHas('personnel', fn($q) => $q->where('centre_id', $user->centre_id));
        }

        $conges = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $annees = Conge::distinct()->orderByDesc('annee')->pluck('annee')->map(fn($a) => (string)$a)->toArray();
        if (empty($annees)) $annees = [(string)date('Y')];

        $centres = \App\Models\Centre::actifs()->orderBy('nom')->get();
        $selectedCentreId = $request->get('centre_id');

        return view('conges.index', compact('conges', 'annees', 'centres', 'selectedCentreId'));
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        $personnels = Personnel::personnelPrincipal()->orderBy('nom')->get();

        $typesConge = [
            'administratif' => 'Congé Administratif',
            'technique'     => 'Congé Technique',
            'maternite'     => 'Congé de Maternité',
        ];

        return view('conges.create', compact('personnels', 'typesConge'));
    }

    public function store(Request $request)
    {
        $rules = [
            'personnel_id' => 'required|exists:personnels,id',
            'type_conge'   => 'required|in:administratif,technique,maternite',
            'date_debut'   => 'required|date',
            'annee'        => 'required|string|max:10',
        ];

        if ($request->type_conge !== 'maternite') {
            $rules['nb_jours_demandes'] = 'required|integer|min:1|max:365';
        }

        $data = $request->validate($rules);

        $personnel = Personnel::findOrFail($request->personnel_id);

        if ($request->type_conge === 'maternite' && $personnel->sexe !== 'F') {
            return back()->withInput()
                ->withErrors(['type_conge' => 'Le congé de maternité est réservé au personnel féminin.']);
        }

        // Calcul solde
        $solde = $this->cal->soldeConges($personnel, $request->annee);

        if ($request->type_conge === 'maternite') {
            // Maternité : 98 jours calendaires (14 semaines), calcul automatique
            $dateDebut  = \Carbon\Carbon::parse($request->date_debut);
            $resultat   = $this->cal->calculerMaternite($dateDebut);
            $dateFin    = $resultat['date_reprise'];
            $nbJours    = CalendrierService::JOURS_CONGE_MATERNITE;
            $nbRestants = $solde['restants'];
        } else {
            $nbJours   = (int)$request->nb_jours_demandes;
            $dateDebut = \Carbon\Carbon::parse($request->date_debut);
            $dateFin   = $this->cal->calculerDateFin($dateDebut, $nbJours);
            $nbRestants = $solde['restants'] - $nbJours;
        }

        Conge::create([
            'reference'          => null,
            'personnel_id'       => $request->personnel_id,
            'type_conge'         => $request->type_conge,
            'date_debut'         => $dateDebut,
            'date_fin'           => $dateFin,
            'nb_jours_demandes'  => $nbJours,
            'nb_jours_acquis'    => $solde['acquis'],
            'nb_jours_deja_pris' => $solde['total_pris'],
            'nb_jours_restants'  => $nbRestants,
            'annee'              => $request->annee,
            'observations'       => $request->observations,
            'statut'             => $request->action === 'soumettre' ? 'soumis' : 'brouillon',
            'cree_par'           => Auth::id(),
        ]);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé enregistrée.');
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Conge $conge)
{
    $conge->load('personnel');

    $solde = $this->cal->soldeConges(
        $conge->personnel,
        $conge->annee
    );

    return view('conges.show', compact('conge', 'solde'));
}

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);
        $personnels = Personnel::personnelPrincipal()->orderBy('nom')->get();
        $typesConge = [
            'administratif' => 'Congé Administratif',
            'technique'     => 'Congé Technique',
            'maternite'     => 'Congé de Maternité',
        ];
        return view('conges.edit', compact('conge', 'personnels', 'typesConge'));
    }

    public function update(Request $request, Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);

        $type = $request->type_conge ?? $conge->type_conge;
        $dateDebut = $request->date_debut ? \Carbon\Carbon::parse($request->date_debut) : $conge->date_debut;

        if ($type === 'maternite') {
            $personnel = $conge->personnel;
            if ($personnel->sexe !== 'F') {
                return back()->withInput()
                    ->withErrors(['type_conge' => 'Le congé de maternité est réservé au personnel féminin.']);
            }
            $resultat = $this->cal->calculerMaternite($dateDebut);
            $dateFin  = $resultat['date_reprise'];
            $nbJours  = CalendrierService::JOURS_CONGE_MATERNITE;
        } else {
            $nbJours = $request->nb_jours_demandes ?? $conge->nb_jours_demandes;
            $dateFin = $this->cal->calculerDateFin($dateDebut, (int) $nbJours);
        }

        $conge->update([
            'type_conge'        => $type,
            'date_debut'        => $dateDebut,
            'date_fin'          => $dateFin,
            'nb_jours_demandes' => $nbJours,
            'annee'             => $request->annee ?? $conge->annee,
            'observations'      => $request->observations,
            'statut'            => $request->action === 'soumettre' ? 'soumis' : $conge->statut,
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé mis à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);
        $conge->delete();
        return redirect()->route('conges.index')->with('success', 'Congé supprimé.');
    }

    // ── Approbation (DRH) ─────────────────────────────────────────────────────
    public function approuver(Request $request, Conge $conge)
    {
        abort_if(!Auth::user()->canApprove(), 403);
        abort_if($conge->statut !== 'soumis', 422);

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $this->doc->sauvegarderSignature($request->file('signature'));
        } else {
            $signPath = ConfigRh::get('drh_signature_path');
        }

        $conge->update([
            'statut'         => 'approuve',
            'approuve_par'   => Auth::id(),
            'approuve_le'    => now(),
            'signature_path' => $signPath,
            'reference'      => $this->doc->resolveReference($request->reference, now()),
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé approuvé. Document disponible.');
    }

    public function rejeter(Request $request, Conge $conge)
    {
        abort_if(!Auth::user()->canApprove(), 403);
        abort_if($conge->statut !== 'soumis', 422);

        $conge->update([
            'statut'      => 'rejete',
            'rejete_par'  => Auth::id(),
            'rejete_le'   => now(),
            'motif_rejet' => $request->motif_rejet,
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé rejeté.');
    }

    // ── Document officiel ──────────────────────────────────────────────────────
    public function document(Conge $conge)
    {
        abort_if($conge->statut !== 'approuve', 403);
        $conge->load('personnel');

        $data = $this->doc->buildCongeData($conge);

        // Surcharger la signature_url en base64
        $signPath = $conge->signature_path ?: ConfigRh::get('drh_signature_path');
        $signUrl  = null;
        if ($signPath) {
            $full = Storage::disk('public')->path($signPath);
            if (file_exists($full)) {
                $mime    = mime_content_type($full) ?: 'image/png';
                $signUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
            }
        }

        return view('conges.document', array_merge($data, [
            'document'      => $conge,
            'personnel'     => $conge->personnel,
            'signature_url' => $signUrl,
        ]));
    }

    // ── Calcul date de fin (AJAX) ──────────────────────────────────────────────
    public function calculerDateFin(Request $request)
    {
        $request->validate([
            'date_debut'        => 'required|date',
            'type_conge'        => 'nullable|in:administratif,technique,maternite',
            'nb_jours_demandes' => 'required_unless:type_conge,maternite|integer|min:1',
        ]);

        $dateDebut = \Carbon\Carbon::parse($request->date_debut);

        if ($request->type_conge === 'maternite') {
            $resultat = $this->cal->calculerMaternite($dateDebut);
            $dernierJour = $resultat['dernier_jour'];
            $dateFin     = $resultat['date_reprise'];
        } else {
            $dernierJour = null;
            $dateFin     = $this->cal->calculerDateFin($dateDebut, (int) $request->nb_jours_demandes);
        }

        return response()->json([
            'date_fin'          => $dateFin->format('Y-m-d'),
            'date_fin_fr'       => $dateFin->isoFormat('dddd D MMMM YYYY'),
            'date_fin_format'   => $dateFin->isoFormat('dddd D MMMM YYYY'),
            'dernier_jour'      => $dernierJour?->format('Y-m-d'),
            'dernier_jour_fr'   => $dernierJour?->isoFormat('dddd D MMMM YYYY'),
        ]);
    }
}