<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\ConfigRh;
use App\Models\GrilleSalariale;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ContratController extends Controller
{
    // ── Création ──────────────────────────────────────────────────────────────
    public function create(Personnel $personnel)
    {
        return view('contrats.create', [
            'personnel'  => $personnel,
            'types'      => Contrat::typesContrat(),
            'categories' => GrilleSalariale::categories(),
            'grille'     => $this->grillePourJs(),
        ]);
    }

    public function store(Request $request, Personnel $personnel)
    {
        $data = $this->validateContrat($request);
        $data['personnel_id'] = $personnel->id;
        $data['created_by']   = Auth::id();

        $data = $this->filterContratColumns($data);

        Contrat::create($data);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat ajouté avec succès.');
    }

    // ── Import Excel / CSV en masse ──────────────────────────────────────────
    // Chaque ligne est rattachée à un personnel déjà existant, identifié par
    // son N° CNSS (prioritaire) ou, à défaut, par nom + prénoms.

    public function importForm()
    {
        return view('contrats.import');
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
                if (class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
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

                $cnss    = trim($row['numero_cnss'] ?? '');
                $nom     = trim($row['nom'] ?? '');
                $prenoms = trim($row['prenoms'] ?? '');
                if ($cnss === '' && $nom === '' && $prenoms === '') continue;

                // ── Retrouver le personnel concerné ──
                $personnel = null;
                if ($cnss !== '') {
                    $personnel = Personnel::where('numero_cnss', $cnss)->first();
                }
                if (!$personnel && $nom !== '' && $prenoms !== '') {
                    $personnel = Personnel::whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])
                        ->whereRaw('LOWER(prenoms) = ?', [mb_strtolower($prenoms)])
                        ->first();
                }

                if (!$personnel) {
                    $qui = $cnss !== '' ? "CNSS $cnss" : "$nom $prenoms";
                    $errors[] = "Ligne $line : aucun agent trouvé pour \"$qui\".";
                    continue;
                }

                $validator = Validator::make($row, [
                    'type_contrat' => 'required|string|max:100',
                    'date_debut'   => 'required|date',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Ligne $line ({$personnel->nom_complet}) : " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Contrat::create(array_merge(
                    $this->filterContratColumns($this->sanitizeContratRow($row)),
                    [
                        'personnel_id' => $personnel->id,
                        'created_by'   => Auth::id(),
                    ]
                ));
                $imported++;
            }

            $msg = "$imported contrat(s) importé(s).";
            if ($errors) {
                session()->flash('import_errors', $errors);
                $msg .= ' ' . count($errors) . ' ligne(s) ignorée(s) — voir le détail ci-dessous.';
            }

            return redirect()->route('personnel.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'numero_cnss', 'nom', 'prenoms', 'type_contrat', 'fonction', 'service',
            'categorie', 'echelon', 'date_effet_echelon', 'centre',
            'salaire_base', 'honoraire_garde', 'honoraire_permanence',
            'date_debut', 'duree_mois', 'date_fin',
            'lieu_signature', 'date_signature', 'numero_visa', 'date_visa', 'statut',
        ];

        $csv = implode(';', $headers) . "\n";
        $csv .= implode(';', [
            'CN123456', 'GBÈDJI', 'Rodrigue', 'CDI', 'Infirmier', 'MEDECINE',
            'C2', '1', '2020-01-15', 'CSVH Saint Luc',
            '75000', '5000', '3000',
            '2020-01-15', '', '',
            'Cotonou', '2020-01-10', '', '', 'actif',
        ]) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_contrats.csv"',
        ]);
    }

    private function sanitizeContratRow(array $row): array
    {
        $dateFields = ['date_debut', 'date_fin', 'date_effet_echelon', 'date_signature', 'date_visa'];
        foreach ($dateFields as $field) {
            if (!empty($row[$field])) {
                try { $row[$field] = \Carbon\Carbon::parse($row[$field])->format('Y-m-d'); }
                catch (\Exception $e) { $row[$field] = null; }
            } else { $row[$field] = null; }
        }

        $row['echelon']    = !empty($row['echelon']) ? (int) $row['echelon'] : null;
        $row['duree_mois'] = !empty($row['duree_mois']) ? (int) $row['duree_mois'] : null;
        $row['salaire_base']         = !empty($row['salaire_base'])         ? (float) str_replace(',', '.', $row['salaire_base'])         : null;
        $row['honoraire_garde']      = !empty($row['honoraire_garde'])      ? (float) str_replace(',', '.', $row['honoraire_garde'])      : null;
        $row['honoraire_permanence'] = !empty($row['honoraire_permanence']) ? (float) str_replace(',', '.', $row['honoraire_permanence']) : null;

        $row['statut'] = in_array($row['statut'] ?? '', ['actif', 'termine', 'rompu']) ? $row['statut'] : 'actif';

        return array_intersect_key($row, array_flip((new Contrat)->getFillable()));
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

    private function filterContratColumns(array $data): array
    {
        $columns = Schema::getColumnListing('contrats');

        if (!in_array('honoraire_garde', $columns, true)) {
            unset($data['honoraire_garde']);
        }

        if (!in_array('honoraire_permanence', $columns, true)) {
            unset($data['honoraire_permanence']);
        }

        if (!in_array('centre_id', $columns, true)) {
            unset($data['centre_id']);
        }

        return $data;
    }

    // ── Modification ─────────────────────────────────────────────────────────
    public function edit(Personnel $personnel, Contrat $contrat)
    {
        return view('contrats.edit', [
            'personnel'  => $personnel,
            'contrat'    => $contrat,
            'types'      => Contrat::typesContrat(),
            'categories' => GrilleSalariale::categories(),
            'grille'     => $this->grillePourJs(),
        ]);
    }

    public function update(Request $request, Personnel $personnel, Contrat $contrat)
    {
        $data = $this->validateContrat($request);
        $data = $this->filterContratColumns($data);
        $contrat->update($data);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat mis à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Personnel $personnel, Contrat $contrat)
    {
        $contrat->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat supprimé.');
    }

    // ── Document imprimable (CDD / CDI / Prestation) ─────────────────────────
    public function document(Personnel $personnel, Contrat $contrat)
    {
        abort_unless(in_array($contrat->type_contrat, ['CDD', 'CDI', 'Prestataire']), 422,
            "La génération du document n'est disponible que pour les contrats CDD, CDI ou Prestation.");

        $logoPath = public_path('images/letterhead/logo_archidiocese.jpeg');
        $evePath  = public_path('images/letterhead/photo_eveque.jpeg');

        // Branding dynamique du centre du personnel
        $centre = $personnel->centre;
        $centreLogo = null;
        if ($centre && $centre->logo_path) {
            $lPath = \Illuminate\Support\Facades\Storage::disk('public')->path($centre->logo_path);
            if (file_exists($lPath)) {
                $ext  = strtolower(pathinfo($lPath, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $centreLogo = "data:{$mime};base64," . base64_encode(file_get_contents($lPath));
            }
        }

        $data = [
            'personnel'       => $personnel,
            'contrat'         => $contrat,
            'centre'          => $centre,
            'entete_texte'    => $centre?->entete_texte,
            'centre_logo'     => $centreLogo,
            'pied_page_texte' => $centre?->pied_page_texte,
            'organisation'    => ConfigRh::get('organisation', "L'Archidiocèse de Cotonou"),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'employeur_nom'       => ConfigRh::get('contrat_employeur_nom', "L'ARCHIDIOCÈSE DE COTONOU"),
            'employeur_adresse'   => ConfigRh::get('contrat_employeur_adresse',
                "sis au lot 624-B, lieudit Awhouanlèko, (Cadjèhoun), 12ème Arrondissement, Commune de Cotonou, IFU: 6201000203005, 01 BP: 491; Tél: 21 30 01 45"),
            'representant_nom'    => ConfigRh::get('contrat_representant_nom', 'Roger HOUNGBEDJI'),
            'representant_titre'  => ConfigRh::get('contrat_representant_titre', 'Archevêque, op.'),
            'delegataire_nom'     => ConfigRh::get('contrat_delegataire_nom', 'Abbé Théophile AKOHA'),
            'delegataire_titre'   => ConfigRh::get('contrat_delegataire_titre', 'Vicaire Général'),
            'directeur_travail_titre' => ConfigRh::get('directeur_travail_titre', 'La Directrice Départementale du Travail et de la Fonction Publique du Littoral'),
            'directeur_travail_nom'   => ConfigRh::get('directeur_travail_nom', 'Mireille C. LEGBA ADANKON'),
            'directrice_travail'      => ConfigRh::get('directeur_travail_nom', 'Mireille C. LEGBA ADANKON'),
            'logo_b64'      => file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null,
            'eveque_b64'    => file_exists($evePath)  ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evePath))  : null,
        ];

        if ($contrat->type_contrat === 'Prestataire') {
            return view('contrats.document_prestation', $data);
        }

        return view('contrats.document', $data);
    }

    // ── Validation ────────────────────────────────────────────────────────────
    private function validateContrat(Request $request): array
    {
        $data = $request->validate([
            'type_contrat'       => 'required|string|max:100',
            'fonction'           => 'nullable|string|max:150',
            'service'            => 'nullable|string|max:150',
            'categorie'          => 'nullable|string|max:10',
            'echelon'            => 'nullable|integer|min:1|max:11',
            'date_effet_echelon' => 'nullable|date',
            'centre'             => 'nullable|string|max:200',
            'lieu_conge'         => 'nullable|string|max:150',
            'salaire_base'       => 'nullable|numeric|min:0',
            'honoraire_garde'       => 'nullable|numeric|min:0',
            'honoraire_permanence'  => 'nullable|numeric|min:0',
            'date_debut'         => 'required|date',
            'duree_mois'         => 'nullable|integer|min:1|max:60',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'date_debauchage'    => 'nullable|date',
            'motif_debauchage'   => 'nullable|string|max:255',
            'lieu_signature'     => 'nullable|string|max:150',
            'date_signature'     => 'nullable|date',
            'numero_visa'        => 'nullable|string|max:100',
            'date_visa'          => 'nullable|date',
            'statut'             => 'nullable|in:actif,termine,rompu',
        ]);

        // Un CDD doit avoir une durée pour pouvoir calculer sa date de fin.
        if (in_array($data['type_contrat'], ['CDD', 'Prestataire']) && empty($data['duree_mois']) && empty($data['date_fin'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'duree_mois' => "Merci d'indiquer la durée (en mois) ou une date de fin pour ce contrat à durée déterminée.",
            ]);
        }

        return $data;
    }

    /** Grille salariale formatée pour être injectée en JS côté formulaire. */
    private function grillePourJs(): array
    {
        $grille = [];
        foreach (GrilleSalariale::all() as $case) {
            $grille[$case->categorie][$case->echelon] = [
                'salaire'     => $case->salaire,
                'coefficient' => (float) $case->coefficient,
            ];
        }
        return $grille;
    }
}