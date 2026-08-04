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
        $query = Stagiaire::query();
        $user = auth()->user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

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

        $stagiaires = $query->orderBy('nom')->paginate(20)->withQueryString();

        $stats = [
            'total'     => Stagiaire::count(),
            'en_cours'  => Stagiaire::where('statut', 'en_cours')->count(),
            'termines'  => Stagiaire::where('statut', 'termine')->count(),
            'hommes'    => Stagiaire::count() > 0 ? Stagiaire::where('sexe', 'M')->count() : 0,
            'femmes'    => Stagiaire::count() > 0 ? Stagiaire::where('sexe', 'F')->count() : 0,
        ];

        return view('stagiaires.index', [
            'stagiaires' => $stagiaires,
            'stats'      => $stats,
            'services'   => \App\Models\Personnel::services(),
            'filters'    => $request->only('search', 'service', 'statut', 'sexe'),
        ]);
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        return view('stagiaires.create', [
            'niveaux'    => Stagiaire::niveauxEtude(),
            'situations' => Stagiaire::situationsMatrimoniales(),
            'services'   => \App\Models\Personnel::services(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->valider($request);
        $data['created_by'] = Auth::id();

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
