<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PersonnelController extends Controller
{
    // ── Liste (exclut stagiaires et prestataires) ──────────────────────────────
    public function index(Request $request)
    {
        $query = Personnel::personnelPrincipal()->with('contrats');

        if (!$request->filled('statut')) {
            $query->enPoste();
        }

        if ($request->filled('search')) {
            $mots = preg_split('/\s+/', trim($request->search), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($mots as $mot) {
                $query->where(function ($q) use ($mot) {
                    $q->where('nom', 'like', "%$mot%")
                      ->orWhere('prenoms', 'like', "%$mot%")
                      ->orWhere('email', 'like', "%$mot%")
                      ->orWhere('numero_cnss', 'like', "%$mot%")
                      ->orWhere('telephone', 'like', "%$mot%");
                });
            }
        }

        if ($request->filled('service'))     $query->where('service', $request->service);
        if ($request->filled('corporation')) $query->where('corporation', $request->corporation);
        if ($request->filled('statut'))      $query->where('statut', $request->statut);
        if ($request->filled('contrat')) {
            $c = $request->contrat;
            $query->where(function ($q) use ($c) {
                $q->whereHas('contrats', fn ($cq) => $cq->where('type_contrat', $c))
                  ->orWhere('type_contrat', $c);
            });
        }

        // Filtrage par centre
        $user = auth()->user();
        if ($request->filled('centre_id') && $user->isGlobal()) {
            $query->where('centre_id', $request->centre_id);
        } elseif (!$user->isGlobal() && $user->centre_id) {
            $query->where('centre_id', $user->centre_id);
        }

        $personnels = $query->orderBy('nom')->paginate(20)->withQueryString();

        // Stats sur le personnel principal uniquement
        $base = Personnel::personnelPrincipal();
        return view('personnel.index', [
            'personnels'   => $personnels,
            'services'     => Personnel::services(),
            'corporations' => Personnel::corporations(),
            'filters'      => $request->only('search', 'service', 'corporation', 'statut', 'contrat'),
            'stats' => [
                'total'    => (clone $base)->count(),
                'actifs'   => (clone $base)->where('statut', 'actif')->count(),
                'conges'   => \App\Models\Conge::where('statut', 'approuve')
                                    ->whereDate('date_debut', '<=', now())
                                    ->whereDate('date_fin', '>=', now())
                                    ->distinct('personnel_id')
                                    ->count('personnel_id'),
                'inactifs' => (clone $base)->where('statut', 'inactif')->count(),
                'hommes'   => (clone $base)->where('sexe', 'M')->count(),
                'femmes'   => (clone $base)->where('sexe', 'F')->count(),
            ],
        ]);
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        return view('personnel.create', [
            'corporations' => Personnel::corporations(),
            'services'     => Personnel::services(),
            'contrats'     => Personnel::typesContrat(),
            'situations'   => Personnel::situationsMatrimoniales(),
            'centres'      => Centre::actifs()->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePersonnel($request);
        $data['created_by'] = Auth::id();
        $this->syncCentreSelection($data);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('photos/personnel', 'public');
        }

        $personnel = Personnel::create($data);
        $this->syncAyantsDroit($request, $personnel);

        return redirect()->route('personnel.index')
            ->with('success', 'Personnel ajouté avec succès.');
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Personnel $personnel)
    {
        $personnel->load(['contrats', 'enfants', 'conjoints', 'avancements']);
        return view('personnel.show', [
            'personnel' => $personnel,
            'centres'   => Centre::actifs()->orderBy('nom')->get(),
        ]);
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Personnel $personnel)
    {
        $personnel->load(['enfants', 'conjoints']);

        return view('personnel.edit', [
            'personnel'    => $personnel,
            'corporations' => Personnel::corporations(),
            'services'     => Personnel::services(),
            'contrats'     => Personnel::typesContrat(),
            'situations'   => Personnel::situationsMatrimoniales(),
            'centres'      => Centre::actifs()->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Personnel $personnel)
    {
        $data = $this->validatePersonnel($request, $personnel->id);
        $this->syncCentreSelection($data, $personnel);

        if ($request->hasFile('photo')) {
            if ($personnel->photo_path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $data['photo_path'] = $request->file('photo')
                ->store('photos/personnel', 'public');
        }

        $personnel->update($data);
        $this->syncAyantsDroit($request, $personnel);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Fiche mise à jour.');
    }

    // ── Ancien travailleur (archivage / restauration) ───────────────────────────
    public function archiver(Request $request, Personnel $personnel)
    {
        $data = $request->validate([
            'motif_depart' => 'required|in:' . implode(',', array_keys(Personnel::motifsDepart())),
            'date_depart'  => 'required|date',
        ]);

        $personnel->update([
            'statut'       => $data['motif_depart'] === 'retraite' ? 'retraite' : 'ancien',
            'motif_depart' => $data['motif_depart'],
            'date_depart'  => $data['date_depart'],
        ]);

        return redirect()->route('personnel.anciens')
            ->with('success', $personnel->nom_complet . ' a été déplacé(e) vers les anciens travailleurs.');
    }

    public function restaurer(Personnel $personnel)
    {
        $personnel->update([
            'statut'       => 'actif',
            'motif_depart' => null,
            'date_depart'  => null,
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', $personnel->nom_complet . ' a été restauré(e) et est de nouveau actif(ve).');
    }

    /** Liste des anciens travailleurs (affectés ailleurs, contrat terminé, débauchés, retraités). */
    public function anciens(Request $request)
    {
        $query = Personnel::anciensTravailleurs()->with('contrats');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('prenoms', 'like', "%$s%");
            });
        }

        if ($request->filled('motif')) $query->where('motif_depart', $request->motif);
        if ($request->filled('service')) $query->where('service', $request->service);

        $personnels = $query->orderByDesc('date_depart')->paginate(20)->withQueryString();

        return view('personnel.anciens', [
            'personnels' => $personnels,
            'services'   => Personnel::services(),
            'motifs'     => Personnel::motifsDepart(),
            'filters'    => $request->only('search', 'motif', 'service'),
            'total'      => Personnel::anciensTravailleurs()->count(),
        ]);
    }

    // ── Affectation ───────────────────────────────────────────────────────────
    public function affecter(Request $request, Personnel $personnel)
    {
        $request->validate([
            'centre_id'        => 'required|exists:centres,id',
            'service'          => 'nullable|string',
            'date_affectation' => 'nullable|date',
        ]);

        $centre = Centre::findOrFail($request->centre_id);

        $personnel->update([
            'centre_id'        => $centre->id,
            'affectation'      => $centre->nom,
            'service'          => $request->service ?: $personnel->service,
            'date_affectation' => $request->date_affectation,
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Affectation enregistrée.');
    }

    // ── Import Excel ──────────────────────────────────────────────────────────
    public function importForm()
    {
        return view('personnel.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('fichier');
        $ext  = strtolower($file->getClientOriginalExtension());

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsv($file->getRealPath());
            } else {
                if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $rows = $this->parseXlsx($file->getRealPath());
                } else {
                    return back()->with('error',
                        'PhpSpreadsheet non installé. Utilisez le format CSV.');
                }
            }

            $imported = 0;
            $errors   = [];

            foreach ($rows as $i => $row) {
                $line = $i + 2;
                if (empty(trim($row['nom'] ?? '')) && empty(trim($row['prenoms'] ?? ''))) continue;

                $validator = Validator::make($row, [
                    'nom'     => 'required|string',
                    'prenoms' => 'required|string',
                    'sexe'    => 'required|in:M,F',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Ligne $line : " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Personnel::create(array_merge(
                    $this->sanitizeImportRow($row),
                    ['created_by' => Auth::id()]
                ));
                $imported++;
            }

            $msg = "$imported personnel(s) importé(s).";
            if ($errors) $msg .= ' ' . count($errors) . ' ligne(s) ignorée(s).';

            return redirect()->route('personnel.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'nom', 'prenoms', 'email', 'date_naissance', 'lieu_naissance', 'sexe',
            'telephone', 'situation_matrimoniale', 'diplome',
            'autorisation_clientele_privee', 'corporation', 'service',
            'type_contrat', 'categorie_echelon', 'date_embauche_centre',
            'date_embauche_isd', 'numero_cnss', 'date_fin_contrat',
            'contact_urgence_nom', 'contact_urgence_telephone',
        ];

        $csv = implode(';', $headers) . "\n";
        $csv .= implode(';', [
            'GBÈDJI', 'Rodrigue', 'rodrigue@exemple.bj', '1990-05-15', 'Cotonou', 'M',
            '+229 97000000', 'Célibataire', 'BTS Informatique',
            '0', 'INFIRMIER', 'MEDECINE', 'CDI', 'C2-E1', '2020-01-15',
            '2019-06-01', 'CN123456', '', 'Akossiwa Gbèdji', '+229 96000000',
        ]) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_personnel.csv"',
        ]);
    }

    // ── Ayants droits (enfants / conjoints) ─────────────────────────────────────

    /**
     * Remplace la liste des enfants et conjoints d'un personnel à partir des
     * tableaux `enfants[]` et `conjoints[]` du formulaire. Les lignes vides
     * (nom et prénom tous les deux vides) sont ignorées.
     */
    private function syncAyantsDroit(Request $request, Personnel $personnel): void
    {
        $request->validate([
            'enfants.*.nom'            => 'nullable|string|max:100',
            'enfants.*.prenom'         => 'nullable|string|max:150',
            'enfants.*.sexe'           => 'nullable|in:M,F',
            'enfants.*.date_naissance' => 'nullable|date|before_or_equal:today',
            'conjoints.*.nom'          => 'nullable|string|max:100',
            'conjoints.*.prenom'       => 'nullable|string|max:150',
        ]);

        $enfants = collect($request->input('enfants', []))
            ->filter(fn ($e) => trim($e['nom'] ?? '') !== '' || trim($e['prenom'] ?? '') !== '')
            ->filter(function ($e) {
                if (empty($e['date_naissance'])) return true;
                return \Carbon\Carbon::parse($e['date_naissance'])->age <= Personnel::AGE_LIMITE_ENFANT;
            })
            ->map(fn ($e) => [
                'nom'            => $e['nom'] ?? '',
                'prenom'         => $e['prenom'] ?? '',
                'sexe'           => $e['sexe'] ?? 'M',
                'date_naissance' => $e['date_naissance'] ?? null,
            ]);

        $conjoints = collect($request->input('conjoints', []))
            ->filter(fn ($c) => trim($c['nom'] ?? '') !== '' || trim($c['prenom'] ?? '') !== '')
            ->map(fn ($c) => [
                'nom'    => $c['nom'] ?? '',
                'prenom' => $c['prenom'] ?? '',
            ]);

        $personnel->enfants()->delete();
        foreach ($enfants as $e) {
            $personnel->enfants()->create($e);
        }

        $personnel->conjoints()->delete();
        foreach ($conjoints as $c) {
            $personnel->conjoints()->create($c);
        }
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function validatePersonnel(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nom'                          => 'required|string|max:100',
            'prenoms'                      => 'required|string|max:150',
            'email'                        => 'nullable|email|unique:personnels,email,' . $ignoreId,
            'date_naissance'               => 'nullable|date',
            'lieu_naissance'               => 'nullable|string|max:150',
            'nationalite'                  => 'nullable|string|max:100',
            'residence'                    => 'nullable|string|max:200',
            'sexe'                         => 'required|in:M,F',
            'telephone'                    => 'nullable|string|max:20',
            'situation_matrimoniale'       => 'nullable|in:Célibataire,Marié(e),Divorcé(e),Veuf/Veuve',
            'diplome'                      => 'nullable|string|max:200',
            'autorisation_clientele_privee'=> 'nullable|boolean',
            'corporation'                  => 'nullable|string|max:150',
            'service'                      => 'nullable|string|max:150',
            'centre_id'                    => 'required|exists:centres,id',
            'type_contrat'                 => 'nullable|string|max:100',
            'categorie_echelon'            => 'nullable|string|max:100',
            'date_embauche_centre'         => 'nullable|date',
            'date_embauche_isd'            => 'nullable|date',
            'date_debauchage'              => 'nullable|date',
            'motif_debauchage'             => 'nullable|string|max:255',
            'numero_cnss'                  => 'nullable|string|max:50',
            'date_depart_retraite'         => 'nullable|date',
            'date_fin_contrat'             => 'nullable|date',
            'conge_annee'                  => 'nullable|string|max:10',
            'conge_jours'                  => 'nullable|integer|min:0',
            'contact_urgence_nom'          => 'nullable|string|max:150',
            'contact_urgence_telephone'    => 'nullable|string|max:20',
            'date_affectation'             => 'nullable|date',
            'statut'                       => 'nullable|in:actif,inactif,en_conge,retraite',
            'photo'                        => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);
    }

    private function syncCentreSelection(array &$data, ?Personnel $personnel = null): void
    {
        if (empty($data['centre_id'])) {
            return;
        }

        $centre = Centre::find($data['centre_id']);
        if (!$centre) {
            return;
        }

        $data['affectation'] = $centre->nom;

        if ($personnel && empty($data['date_affectation']) && $personnel->date_affectation) {
            $data['date_affectation'] = Carbon::parse((string) $personnel->date_affectation)->format('Y-m-d');
        }
    }

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $handle  = fopen($path, 'r');
        $headers = null;
        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            if (!$headers) { $headers = array_map('trim', $line); continue; }
            if (count($line) < 2) continue;
            $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
        }
        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';
        $spreadsheet = $ioFactoryClass::load($path);
        $sheet       = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $headers     = array_map('trim', array_shift($sheet));
        $rows        = [];
        foreach ($sheet as $line) {
            $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
        }
        return $rows;
    }

    private function sanitizeImportRow(array $row): array
    {
        $dateFields = [
            'date_naissance', 'date_embauche_centre', 'date_embauche_isd',
            'date_debauchage', 'date_depart_retraite', 'date_fin_contrat', 'date_affectation',
        ];
        foreach ($dateFields as $field) {
            if (!empty($row[$field])) {
                try { $row[$field] = \Carbon\Carbon::parse($row[$field])->format('Y-m-d'); }
                catch (\Exception $e) { $row[$field] = null; }
            } else { $row[$field] = null; }
        }
        $row['autorisation_clientele_privee'] = in_array(
            strtolower(trim($row['autorisation_clientele_privee'] ?? '')),
            ['1', 'oui', 'yes', 'true']
        ) ? 1 : 0;
        $row['sexe'] = strtoupper(trim($row['sexe'] ?? 'M'));
        if (!in_array($row['sexe'], ['M', 'F'])) $row['sexe'] = 'M';
        return array_intersect_key($row, array_flip((new Personnel)->getFillable()));
    }

    // ── Actions CRH exclusives ───────────────────────────────────────────────

    /** Afficher le formulaire de désactivation. */
    public function showDesactiver(Personnel $personnel)
    {
        return view('personnel.desactiver', compact('personnel'));
    }

    /** Afficher le formulaire de transfert. */
    public function showTransferer(Personnel $personnel)
    {
        $centres = \App\Models\Centre::actifs()->orderBy('nom')->get();
        return view('personnel.transferer', compact('personnel', 'centres'));
    }

    /** Désactiver un personnel (CRH uniquement). */
    public function desactiver(Request $request, Personnel $personnel)
    {
        $request->validate([
            'motif_depart' => 'required|string',
            'commentaire'  => 'nullable|string',
        ]);

        // Archiver le contrat actif
        $contrat = $personnel->contrat_actif;
        if ($contrat) {
            $contrat->update(['statut' => 'termine']);
        }

        // Désactiver le personnel
        $statut = $request->motif_depart === 'retraite' ? 'retraite' : 'ancien';
        $personnel->update([
            'statut'       => $statut,
            'motif_depart' => $request->motif_depart,
            'date_depart'  => now(),
        ]);

        // Créer l'historique
        \App\Models\PersonnelHistorique::create([
            'personnel_id'      => $personnel->id,
            'contrat_id'        => $contrat?->id,
            'centre_id'         => $personnel->centre_id,
            'date_debut'        => $contrat?->date_debut ?? $personnel->date_embauche_centre,
            'date_fin'          => now(),
            'type_contrat'      => $contrat?->type_contrat ?? $personnel->type_contrat,
            'categorie_echelon' => $contrat?->categorie_echelon ?? $personnel->categorie_echelon,
            'salaire_base'      => $contrat?->salaire_base,
            'corporation'       => $contrat?->fonction ?? $personnel->corporation,
            'service'           => $contrat?->service ?? $personnel->service,
            'centre_nom'        => $personnel->centre?->nom,
            'type_evenement'    => 'desactivation',
            'commentaire'       => $request->commentaire ?? ('Désactivation : ' . $request->motif_depart),
            'created_by'        => Auth::id(),
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', $personnel->nom_complet . ' a été désactivé(e).');
    }

    /** Réactiver un personnel (CRH uniquement, pour renouveler un contrat). */
    public function reactiver(Request $request, Personnel $personnel)
    {
        $personnel->update([
            'statut'       => 'actif',
            'motif_depart' => null,
            'date_depart'  => null,
        ]);

        \App\Models\PersonnelHistorique::create([
            'personnel_id'   => $personnel->id,
            'centre_id'      => $personnel->centre_id,
            'date_debut'     => now(),
            'type_evenement' => 'reactivation',
            'centre_nom'     => $personnel->centre?->nom,
            'commentaire'    => 'Réactivation du personnel pour renouvellement de contrat.',
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', $personnel->nom_complet . ' a été réactivé(e). Vous pouvez maintenant créer un nouveau contrat.');
    }

    /** Transférer un personnel vers un autre centre (CRH uniquement). */
    public function transferer(Request $request, Personnel $personnel)
    {
        $request->validate([
            'centre_id'       => 'required|exists:centres,id',
            'date_transfert'  => 'required|date',
            'commentaire'     => 'nullable|string',
        ]);

        $ancienCentre = $personnel->centre;
        $nouveauCentre = \App\Models\Centre::find($request->centre_id);

        // Fermer la période dans l'ancien centre
        \App\Models\PersonnelHistorique::create([
            'personnel_id'      => $personnel->id,
            'contrat_id'        => $personnel->contrat_actif?->id,
            'centre_id'         => $personnel->centre_id,
            'date_debut'        => $personnel->date_affectation ?? $personnel->date_embauche_centre,
            'date_fin'          => $request->date_transfert,
            'type_contrat'      => $personnel->type_contrat_actuel,
            'categorie_echelon' => $personnel->contrat_actif?->categorie_echelon ?? $personnel->categorie_echelon,
            'salaire_base'      => $personnel->contrat_actif?->salaire_base,
            'corporation'       => $personnel->corporation,
            'service'           => $personnel->service,
            'centre_nom'        => $ancienCentre?->nom,
            'type_evenement'    => 'transfert',
            'commentaire'       => $request->commentaire ?? ('Transfert de ' . ($ancienCentre?->nom ?? 'N/A') . ' vers ' . $nouveauCentre->nom),
            'created_by'        => Auth::id(),
        ]);

        // Mettre à jour le centre
        $personnel->update([
            'centre_id'       => $request->centre_id,
            'date_affectation' => $request->date_transfert,
            'affectation'     => $nouveauCentre->nom,
        ]);

        // Mettre à jour le contrat actif
        if ($personnel->contrat_actif) {
            $personnel->contrat_actif->update(['centre_id' => $request->centre_id, 'centre' => $nouveauCentre->nom]);
        }

        return redirect()->route('personnel.show', $personnel)
            ->with('success', $personnel->nom_complet . ' a été transféré(e) vers ' . $nouveauCentre->nom . '.');
    }

    /** Passage CDD → CDI (CRH uniquement). */
    public function passerCDI(Request $request, Personnel $personnel)
    {
        $contrat = $personnel->contrat_actif;
        if (!$contrat || $contrat->type_contrat !== 'CDD') {
            return back()->with('error', 'Ce personnel n\'a pas de contrat CDD actif.');
        }

        // Fermer le CDD
        $contrat->update(['statut' => 'termine']);

        // Créer un nouveau CDI
        $nouveauContrat = \App\Models\Contrat::create([
            'personnel_id'     => $personnel->id,
            'type_contrat'     => 'CDI',
            'fonction'         => $contrat->fonction,
            'service'          => $contrat->service,
            'categorie_echelon'=> $contrat->categorie_echelon,
            'centre'           => $contrat->centre,
            'centre_id'        => $contrat->centre_id ?? $personnel->centre_id,
            'salaire_base'     => $contrat->salaire_base,
            'date_debut'       => now(),
            'statut'           => 'actif',
            'created_by'       => Auth::id(),
        ]);

        // Mettre à jour le type sur personnel
        $personnel->update(['type_contrat' => 'CDI']);

        // Historique
        \App\Models\PersonnelHistorique::create([
            'personnel_id'      => $personnel->id,
            'contrat_id'        => $nouveauContrat->id,
            'centre_id'         => $personnel->centre_id,
            'date_debut'        => now(),
            'type_contrat'      => 'CDI',
            'categorie_echelon' => $contrat->categorie_echelon,
            'salaire_base'      => $contrat->salaire_base,
            'corporation'       => $contrat->fonction ?? $personnel->corporation,
            'service'           => $contrat->service ?? $personnel->service,
            'centre_nom'        => $personnel->centre?->nom,
            'type_evenement'    => 'passage_cdi',
            'commentaire'       => 'Passage CDD → CDI.',
            'created_by'        => Auth::id(),
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', $personnel->nom_complet . ' est passé(e) en CDI.');
    }

    /** Historique complet d'un personnel. */
    public function historique(Personnel $personnel)
    {
        $historiques = $personnel->historiques()->orderByDesc('date_debut')->get();

        return view('personnel.historique', compact('personnel', 'historiques'));
    }
}