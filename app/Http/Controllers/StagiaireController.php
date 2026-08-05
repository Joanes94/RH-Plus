<?php

namespace App\Http\Controllers;

use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StagiaireController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Stagiaire::query()->with('centre');
        $user = auth()->user();

        if ($request->filled('search')) {
            $mots = preg_split('/\s+/', trim($request->search), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($mots as $mot) {
                $query->where(function ($q) use ($mot) {
                    $q->where('nom', 'like', "%$mot%")
                      ->orWhere('prenoms', 'like', "%$mot%")
                      ->orWhere('email', 'like', "%$mot%")
                      ->orWhere('ecole_formation', 'like', "%$mot%");
                });
            }
        }
        if ($request->filled('service')) $query->where('service', $request->service);
        if ($request->filled('statut'))  $query->where('statut',  $request->statut);
        if ($request->filled('sexe'))    $query->where('sexe',    $request->sexe);

        // Filtrage par centre
        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        } elseif (!$user->isGlobal() && $user->centre_id) {
            $query->where('centre_id', $user->centre_id);
        }

        $stagiaires = $query->orderBy('nom')->paginate(20)->withQueryString();

        $statsQuery = Stagiaire::query();
        if ($request->filled('centre_id')) {
            $statsQuery->where('centre_id', $request->centre_id);
        } elseif (!$user->isGlobal() && $user->centre_id) {
            $statsQuery->where('centre_id', $user->centre_id);
        }

        $stats = [
            'total'     => (clone $statsQuery)->count(),
            'en_cours'  => (clone $statsQuery)->where('statut', 'en_cours')->count(),
            'termines'  => (clone $statsQuery)->where('statut', 'termine')->count(),
            'hommes'    => (clone $statsQuery)->where('sexe', 'M')->count(),
            'femmes'    => (clone $statsQuery)->where('sexe', 'F')->count(),
        ];

        $centres = \App\Models\Centre::actifs()->orderBy('nom')->get();

        return view('stagiaires.index', [
            'stagiaires' => $stagiaires,
            'stats'      => $stats,
            'services'   => \App\Models\Personnel::services(),
            'centres'    => $centres,
            'filters'    => $request->only('search', 'service', 'statut', 'sexe', 'centre_id'),
        ]);
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        return view('stagiaires.create', [
            'niveaux'    => Stagiaire::niveauxEtude(),
            'situations' => Stagiaire::situationsMatrimoniales(),
            'services'   => \App\Models\Personnel::services(),
            'centres'    => \App\Models\Centre::actifs()->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->valider($request);
        $data['created_by'] = Auth::id();

        // Par défaut le centre de l'utilisateur connecté si non renseigné
        if (empty($data['centre_id']) && auth()->user()->centre_id) {
            $data['centre_id'] = auth()->user()->centre_id;
        }

        // Photo
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('photos/stagiaires', 'public');
        }

        Stagiaire::create($data);

        return redirect()->route('stagiaires.index')
            ->with('success', 'Stagiaire ajouté avec succès.');
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Stagiaire $stagiaire)
    {
        $stagiaire->load('centre');
        return view('stagiaires.show', compact('stagiaire'));
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Stagiaire $stagiaire)
    {
        return view('stagiaires.edit', [
            'stagiaire'  => $stagiaire,
            'niveaux'    => Stagiaire::niveauxEtude(),
            'situations' => Stagiaire::situationsMatrimoniales(),
            'services'   => \App\Models\Personnel::services(),
            'centres'    => \App\Models\Centre::actifs()->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Stagiaire $stagiaire)
    {
        $data = $this->valider($request, $stagiaire->id);

        // Photo
        if ($request->hasFile('photo')) {
            if ($stagiaire->photo_path) {
                Storage::disk('public')->delete($stagiaire->photo_path);
            }
            $data['photo_path'] = $request->file('photo')
                ->store('photos/stagiaires', 'public');
        }

        $stagiaire->update($data);

        return redirect()->route('stagiaires.show', $stagiaire)
            ->with('success', 'Fiche mise à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Stagiaire $stagiaire)
    {
        $stagiaire->delete();
        return redirect()->route('stagiaires.index')
            ->with('success', 'Stagiaire archivé.');
    }

    // ── Validation privée ─────────────────────────────────────────────────────
    private function valider(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nom'                           => 'required|string|max:100',
            'prenoms'                       => 'required|string|max:150',
            'email'                         => 'nullable|email|unique:stagiaires,email,' . $ignoreId,
            'centre_id'                     => 'nullable|exists:centres,id',
            'date_naissance'                => 'nullable|date',
            'lieu_naissance'                => 'nullable|string|max:150',
            'sexe'                          => 'required|in:M,F',
            'telephone'                     => 'nullable|string|max:20',
            'situation_matrimoniale'        => 'nullable|in:Célibataire,Marié(e),Divorcé(e),Veuf/Veuve',
            'titre'                         => 'nullable|string|max:150',
            'niveau_etude'                  => 'nullable|string|max:50',
            'diplome'                       => 'nullable|string|max:200',
            'ecole_formation'               => 'nullable|string|max:200',
            'autorisation_clientele_privee' => 'nullable|boolean',
            'service'                       => 'nullable|string|max:150',
            'date_debut_stage'              => 'nullable|date',
            'date_fin_stage'                => 'nullable|date|after_or_equal:date_debut_stage',
            'type_stage'                    => 'nullable|in:Académique de découverte,Académique,Professionnel,Autre',
            'observations'                  => 'nullable|string',
            'contact_urgence_nom'           => 'nullable|string|max:150',
            'contact_urgence_telephone'     => 'nullable|string|max:20',
            'statut'                        => 'nullable|in:en_cours,termine,abandonne',
            'photo'                         => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);
    }
}
