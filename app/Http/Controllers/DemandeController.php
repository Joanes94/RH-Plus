<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Personnel;
use App\Models\ConfigRh;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DemandeController extends Controller
{
    public function __construct(private DocumentService $doc) {}

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Demande::with('personnel')->latest();

        if ($request->filled('statut'))       $query->where('statut', $request->statut);
        if ($request->filled('type_demande')) $query->where('type_demande', $request->type_demande);
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

        $demandes  = $query->paginate(20)->withQueryString();
        $catalogue = Demande::catalogue();
        $centres   = \App\Models\Centre::actifs()->orderBy('nom')->get();
        $selectedCentreId = $request->get('centre_id');

        return view('demandes.index', compact('demandes', 'catalogue', 'centres', 'selectedCentreId'));
    }

    // ── Formulaire de création ────────────────────────────────────────────────
    public function create(Request $request)
    {
        $user = auth()->user();
        $personnelsQuery = Personnel::where('statut', 'actif')->orderBy('nom');

        // Filtrer par centre si l'utilisateur n'est pas global
        if (!$user->isGlobal() && $user->centre_id) {
            $personnelsQuery->where('centre_id', $user->centre_id);
        }

        $personnels    = $personnelsQuery->get();
        $catalogue     = Demande::catalogue();
        $typePreselect = $request->get('type');

        return view('demandes.create', compact('personnels', 'catalogue', 'typePreselect'));
    }

    // ── Enregistrement ────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $this->validerDemande($request);
        $data['cree_par'] = Auth::id();
        $data['statut']   = $request->input('action') === 'soumettre' ? 'soumis' : 'brouillon';

        $demande = Demande::create($data);

        $msg = $data['statut'] === 'soumis'
            ? 'Demande soumise au DRH avec succès.'
            : 'Brouillon enregistré.';

        return redirect()->route('demandes.show', $demande)->with('success', $msg);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Demande $demande)
    {
        $demande->load('personnel', 'creePar', 'approuvePar');
        return view('demandes.show', compact('demande'));
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Demande $demande)
    {
        abort_if(!$demande->isEditable(), 403, 'Cette demande ne peut plus être modifiée.');
        $personnels = Personnel::where('statut', 'actif')->orderBy('nom')->get();
        $catalogue  = Demande::catalogue();

        return view('demandes.edit', compact('demande', 'personnels', 'catalogue'));
    }

    public function update(Request $request, Demande $demande)
    {
        abort_if(!$demande->isEditable(), 403);

        $data = $this->validerDemande($request, $demande);
        $data['statut'] = $request->input('action') === 'soumettre' ? 'soumis' : 'brouillon';

        $demande->update($data);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande mise à jour.');
    }

    public function destroy(Demande $demande)
    {
        abort_if(!$demande->isEditable(), 403);
        $demande->delete();
        return redirect()->route('demandes.index')->with('success', 'Demande supprimée.');
    }

    // ── Approbation (DRH) ─────────────────────────────────────────────────────
    public function approuver(Request $request, Demande $demande)
    {
        abort_if(!Auth::user()->canApprove(), 403);
        abort_if($demande->statut !== 'soumis', 422, 'Statut invalide.');

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $this->doc->sauvegarderSignature($request->file('signature'));
        } else {
            $signPath = ConfigRh::get('drh_signature_path');
        }

        $demande->update([
            'statut'         => 'approuve',
            'approuve_par'   => Auth::id(),
            'approuve_le'    => now(),
            'signature_path' => $signPath,
            'reference'      => $this->doc->resolveReference($request->reference, now()),
        ]);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande approuvée. Document disponible.');
    }

    public function rejeter(Request $request, Demande $demande)
    {
        $request->validate(['motif_rejet' => 'required|string|max:500']);

        $demande->update([
            'statut'       => 'rejete',
            'motif_rejet'  => $request->motif_rejet,
            'approuve_par' => Auth::id(),
            'approuve_le'  => now(),
        ]);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande rejetée.');
    }

    // ── Document officiel ──────────────────────────────────────────────────────
    public function document(Demande $demande)
    {
        abort_if($demande->statut !== 'approuve', 403, 'Document disponible uniquement après approbation.');
        $demande->load('personnel');

        $p        = $demande->personnel;
        $estFemme = $p->sexe === 'F';

        // Signature en base64 pour garantir l'affichage à l'impression
        $signPath = $demande->signature_path ?: ConfigRh::get('drh_signature_path');
        $signUrl  = null;
        if ($signPath) {
            $fullPath = Storage::disk('public')->path($signPath);
            if (file_exists($fullPath)) {
                $mime    = mime_content_type($fullPath) ?: 'image/png';
                $signUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $data = [
            'demande'       => $demande,
            'personnel'     => $p,
            'est_femme'     => $estFemme,
            'civilite'      => $estFemme ? 'Madame' : 'Monsieur',
            'le_la'         => $estFemme ? 'la' : 'le',
            'du_de_la'      => $estFemme ? 'de la' : 'du',
            'nomme_e'       => $estFemme ? 'nommée' : 'nommé',
            'employe_e'     => $estFemme ? 'employée' : 'employé',
            'drh_nom'       => ConfigRh::get('drh_nom',   'Nom du DRH'),
            'drh_titre'     => ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
            'organisation'  => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'         => ConfigRh::get('ville', 'Cotonou'),
            'signature_url' => $signUrl,
            'date_doc'      => $demande->approuve_le
                ? $demande->approuve_le->isoFormat('D MMMM YYYY')
                : now()->isoFormat('D MMMM YYYY'),
        ];

        // Vue spécifique par type
        $view = 'demandes.documents.' . $demande->type_demande;
        if (!view()->exists($view)) {
            $view = 'demandes.documents.generique';
        }

        return view($view, $data);
    }

    // ── Validation privée ─────────────────────────────────────────────────────
    private function validerDemande(Request $request, ?Demande $demande = null): array
    {
        $type   = $request->input('type_demande');
        $champs = Demande::typeChamps($type);

        $rules = [
            'personnel_id'  => 'required|exists:personnels,id',
            'type_demande'  => 'required|in:' . implode(',', array_keys(Demande::tousLesTypes())),
            'observations'  => 'nullable|string|max:1000',
        ];

        if (in_array('dates', $champs)) {
            $rules['date_debut'] = 'nullable|date';
            $rules['date_fin']   = 'nullable|date|after_or_equal:date_debut';
            $rules['nb_jours']   = 'nullable|integer|min:1';
        }
        if (in_array('motif', $champs)) {
            $rules['motif'] = 'nullable|string|max:500';
        }
        if (in_array('date_accouchement', $champs)) {
            $rules['date_accouchement_prevu'] = 'nullable|date';
        }
        if (in_array('etablissement', $champs)) {
            $rules['etablissement_stage'] = 'nullable|string|max:200';
            $rules['niveau_etude']        = 'nullable|string|max:100';
        }
        if (in_array('specialite', $champs)) {
            $rules['specialite'] = 'nullable|string|max:150';
        }
        if (in_array('faits', $champs)) {
            $rules['faits_reproches'] = 'nullable|string|max:2000';
        }
        if (in_array('date_faits', $champs)) {
            $rules['date_faits'] = 'nullable|date';
        }

        return $request->validate($rules);
    }
}
