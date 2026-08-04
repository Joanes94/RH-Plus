<?php

namespace App\Http\Controllers;

use App\Models\Stagiaire;
use App\Models\StagiaireDocument;
use App\Models\Evaluation;
use App\Models\ConfigRh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class StagiaireDocumentController extends Controller
{
    /**
     * Données communes à tous les documents stagiaires.
     */
    private function baseData(Stagiaire $stagiaire): array
    {
        $signPath = ConfigRh::get('drh_signature_path');
        $signUrl  = null;
        if ($signPath) {
            $full = Storage::disk('public')->path($signPath);
            if (file_exists($full)) {
                $mime    = mime_content_type($full) ?: 'image/png';
                $signUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
            }
        }

        return [
            'stagiaire'    => $stagiaire,
            'drh_nom'      => ConfigRh::get('drh_nom', 'Le Directeur des Ressources Humaines'),
            'drh_titre'    => ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
            'organisation' => ConfigRh::get('organisation', 'CSVH Saint Luc'),
            'ville'        => ConfigRh::get('ville', 'Cotonou'),
            'signature_url'=> $signUrl,
            'date_doc'     => now()->isoFormat('D MMMM YYYY'),
        ];
    }

    private function normalizeServices(array $services, Stagiaire $stagiaire): array
    {
        $normalized = [];

        foreach ($services as $service) {
            $name = is_array($service)
                ? trim((string) ($service['nom'] ?? ''))
                : trim((string) $service);

            if ($name !== '') {
                $normalized[] = $name;
            }
        }

        if (!$normalized) {
            $normalized[] = trim((string) ($stagiaire->service ?: '—'));
        }

        return array_values(array_unique($normalized));
    }

    private function serviceLabel(string $service): string
    {
        $normalized = \Illuminate\Support\Str::of($service)->ascii()->upper()->squish()->toString();

        $map = [
            'MEDECINE' => 'Médecine',
            'CHIRURGIE' => 'Chirurgie',
            'PHARMACIE' => 'Pharmacie',
            'FACTURATION' => 'Facturation',
            'MAGASIN' => 'Magasin',
            'KINESITHERAPIE' => 'Kinésithérapie',
            'STOMATOLOGIE' => 'Stomatologie',
            'MATERNITE' => 'Maternité',
            'PEDIATRIE' => 'Pédiatrie',
            'SERVICE PLURIDISCIPLINAIRE' => 'Service pluridisciplinaire',
            'URGENCES' => 'Urgences',
            'RADIOLOGIE' => 'Radiologie',
            'LABORATOIRE' => 'Laboratoire',
            'DIRECTION DES RESSOURCES HUMAINES' => 'Direction des ressources humaines',
            'DIRECTION' => 'Direction',
            'SECRETARIAT' => 'Secrétariat',
            'GASTRO-ENTEROLOGIE' => 'Gastro-entérologie',
            'SERVICE DES SOINS INFIRMIERS' => 'Service des soins infirmiers',
            'SERVICE GENERAL' => 'Service général',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $label = mb_convert_case(trim($service), MB_CASE_TITLE, 'UTF-8');
        return $label !== '' ? $label : $service;
    }

    private function serviceArticle(string $service): string
    {
        $value = \Illuminate\Support\Str::of($service)->ascii()->lower()->squish()->toString();

        foreach (['urgences', 'soins infirmiers'] as $needle) {
            if (str_contains($value, $needle)) {
                return 'des';
            }
        }

        foreach (['medecine', 'chirurgie', 'pharmacie', 'maternite', 'pediatrie', 'radiologie', 'kinesitherapie', 'gastro-enterologie', 'gastroenterologie', 'comptabilite', 'caisse', 'facturation', 'direction'] as $needle) {
            if (str_contains($value, $needle)) {
                return 'de la';
            }
        }

        foreach (['laboratoire', 'secretariat', 'magasin', 'service general', 'direction des ressources humaines'] as $needle) {
            if (str_contains($value, $needle)) {
                return 'du';
            }
        }

        return preg_match('/^[aeiouyh]/i', $value) ? "de l'" : 'du';
    }

    private function buildServiceSegments(Stagiaire $stagiaire, array $services): array
    {
        $services = $this->normalizeServices($services, $stagiaire);
        $start = $stagiaire->date_debut_stage ? Carbon::parse($stagiaire->date_debut_stage) : null;
        $end   = $stagiaire->date_fin_stage ? Carbon::parse($stagiaire->date_fin_stage) : null;
        $count = count($services);

        if (!$start || !$end) {
            return array_map(fn (string $service) => [
                'nom'     => $service,
                'label'   => $this->serviceLabel($service),
                'article' => $this->serviceArticle($service),
                'debut'   => null,
                'fin'     => null,
            ], $services);
        }

        $segments = [];
        $current  = $start->copy();

        foreach ($services as $index => $service) {
            if ($current->gt($end)) {
                $current = $end->copy();
            }

            if ($index === $count - 1) {
                $segmentEnd = $end->copy();
            } else {
                $segmentEnd = $current->copy()->addMonthNoOverflow()->subDay();
                if ($segmentEnd->gt($end)) {
                    $segmentEnd = $end->copy();
                }
                if ($segmentEnd->lt($current)) {
                    $segmentEnd = $current->copy();
                }
            }

            $segments[] = [
                'nom'     => $service,
                'label'   => $this->serviceLabel($service),
                'article' => $this->serviceArticle($service),
                'debut'   => $current->copy(),
                'fin'     => $segmentEnd->copy(),
            ];

            $current = $segmentEnd->copy()->addDay();
        }

        return $segments;
    }

    private function buildServicePhrase(array $segments): string
    {
        if (count($segments) === 1) {
            $s = $segments[0];
            return 'au service ' . $s['article'] . ' ' . $s['label'];
        }

        $parts = array_map(fn ($s) => trim($s['article'] . ' ' . $s['label']), $segments);
        $list = implode(', ', $parts);

        return 'aux services ' . preg_replace('/, ([^,]+)$/', ' et $1', $list);
    }

    /**
     * Génère une référence mensuelle incrémentée pour les documents stagiaires.
     */
    private function generateMonthlyReference(?Carbon $date = null, ?string $suffix = null): string
    {
        $date ??= now();
        $suffix ??= 'AC/DDIS/CSVHHSL/DIR/DRH/ARH';

        $period = $date->format('m-y');
        $key    = 'stagiaire_doc_ref_counter:' . $period;
        $ttl    = $date->copy()->endOfMonth()->endOfDay();

        if (!Cache::has($key)) {
            Cache::put($key, 0, $ttl);
        }

        $count = Cache::increment($key);

        return 'O' . $count . '/' . $period . '/' . $suffix;
    }

    /**
     * Liste unifiée des demandes/documents de stagiaires (autorisations, attestations,
     * fiches d'évaluation) avec leurs statuts. Consultable par l'Assistant RH et le DRH.
     * Seul le DRH peut approuver/rejeter (via les pages de détail respectives).
     */
    public function demandes(Request $request)
    {
        $search = $request->get('search');
        $statut = $request->get('statut');
        $type   = $request->get('type'); // autorisation, attestation, evaluation

        $items = collect();

        StagiaireDocument::with('stagiaire', 'creePar', 'approuvePar')
            ->when($type && in_array($type, ['autorisation', 'attestation']), fn($q) => $q->where('type_document', $type))
            ->when(!$type, fn($q) => $q)
            ->get()
            ->each(function (StagiaireDocument $d) use ($items) {
                if (!$d->stagiaire) return;
                $items->push([
                    'type_slug'    => $d->type_document,
                    'type_label'   => $d->type_document === 'autorisation' ? 'Autorisation de stage' : 'Attestation de stage',
                    'stagiaire'    => $d->stagiaire,
                    'statut'       => $d->statut,
                    'statut_label' => $d->statut_label,
                    'statut_color' => $d->statut_color,
                    'cree_par'     => $d->creePar,
                    'date'         => $d->created_at,
                    'route_show'   => route('stagiaires.documents.show', [$d->stagiaire_id, $d->id]),
                    'route_pdf'    => $d->statut === 'approuve' ? route('stagiaires.documents.pdf', [$d->stagiaire_id, $d->id]) : null,
                ]);
            });

        Evaluation::with('stagiaire', 'creePar', 'approuvePar')
            ->get()
            ->each(function (Evaluation $e) use ($items) {
                if (!$e->stagiaire) return;
                $items->push([
                    'type_slug'    => 'evaluation',
                    'type_label'   => "Fiche d'évaluation",
                    'stagiaire'    => $e->stagiaire,
                    'statut'       => $e->statut,
                    'statut_label' => $e->statut_label,
                    'statut_color' => $e->statut_color,
                    'cree_par'     => $e->creePar,
                    'date'         => $e->created_at,
                    'route_show'   => route('evaluations.show', $e),
                    'route_pdf'    => $e->statut === 'approuve' ? route('evaluations.document', $e) : null,
                ]);
            });

        if ($type === 'evaluation') {
            $items = $items->where('type_slug', 'evaluation');
        }

        if ($statut) {
            $items = $items->where('statut', $statut);
        }

        if ($search) {
            $s = \Illuminate\Support\Str::of($search)->lower()->toString();
            $items = $items->filter(function ($i) use ($s) {
                $nom = \Illuminate\Support\Str::of($i['stagiaire']->nom_complet ?? '')->lower()->toString();
                return str_contains($nom, $s);
            });
        }

        $items = $items->sortByDesc('date')->values();

        $page    = (int) $request->get('page', 1);
        $perPage = 20;

        $demandes = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('stagiaires.demandes.index', compact('demandes'));
    }

    /**
     * Page de sélection du document à générer.
     */
    public function choisir(Stagiaire $stagiaire)
    {
        $services = \App\Models\Personnel::services();
        return view('stagiaires.documents.choisir', array_merge(
            $this->baseData($stagiaire),
            compact('services')
        ));
    }

    /**
     * Autorisation de stage.
     */
    public function autorisation(Request $request, Stagiaire $stagiaire)
    {
        $request->validate([
            'type' => 'required|in:professionnel,academique,decouverte',
            'services' => 'required|array|min:1',
            'reference' => 'nullable|string',
        ]);

        $type_stage = $request->get('type', 'professionnel');
        $services = $request->get('services');
        $reference = $request->filled('reference') ? $request->get('reference') : $this->generateMonthlyReference();

        // Créer le document en base
        $document = StagiaireDocument::create([
            'stagiaire_id' => $stagiaire->id,
            'type_document' => 'autorisation',
            'type_stage' => $type_stage,
            'services' => $services,
            'reference' => $reference,
            'statut' => 'soumis',
            'cree_par' => Auth::id(),
        ]);

        return redirect()->route('stagiaires.documents.attente', [$stagiaire, $document])
            ->with('success', 'Autorisation soumise au DRH pour validation.');
    }

    /**
     * Attestation de stage.
     */
    public function attestation(Request $request, Stagiaire $stagiaire)
    {
        $request->validate([
            'type' => 'required|in:professionnel,academique,decouverte',
            'services' => 'required|array|min:1',
            'reference' => 'nullable|string',
        ]);

        $type_stage = $request->get('type', 'professionnel');
        $services = $request->get('services');
        $reference = $request->filled('reference') ? $request->get('reference') : $this->generateMonthlyReference();

        // Créer le document en base
        $document = StagiaireDocument::create([
            'stagiaire_id' => $stagiaire->id,
            'type_document' => 'attestation',
            'type_stage' => $type_stage,
            'services' => $services,
            'reference' => $reference,
            'statut' => 'soumis',
            'cree_par' => Auth::id(),
        ]);

        return redirect()->route('stagiaires.documents.attente', [$stagiaire, $document])
            ->with('success', 'Attestation soumise au DRH pour validation.');
    }

    /**
     * Page d'attente après soumission.
     */
    public function attente(Stagiaire $stagiaire, StagiaireDocument $document)
    {
        abort_if($document->stagiaire_id !== $stagiaire->id, 404);
        $document->load('stagiaire', 'creePar');

        return view('stagiaires.documents.attente', compact('stagiaire', 'document'));
    }

    /**
     * Page de visualisation du document.
     * Consultable par l'Assistant RH (lecture seule) et le DRH (peut approuver/rejeter).
     */
    public function show(Stagiaire $stagiaire, StagiaireDocument $document)
    {
        abort_if($document->stagiaire_id !== $stagiaire->id, 404);

        $document->load('stagiaire', 'creePar', 'approuvePar');

        return view('stagiaires.documents.show', compact('stagiaire', 'document'));
    }

    /**
     * Approuver un document (DRH uniquement).
     */
    public function approuver(Request $request, Stagiaire $stagiaire, StagiaireDocument $document)
    {
        abort_if($document->stagiaire_id !== $stagiaire->id, 404);
        abort_if(!Auth::user()->canApprove(), 403);
        abort_if($document->statut !== 'soumis', 422);

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $request->file('signature')->store('signatures/stagiaires', 'public');
        } else {
            $signPath = ConfigRh::get('drh_signature_path');
        }

        $document->update([
            'statut' => 'approuve',
            'approuve_par' => Auth::id(),
            'approuve_le' => now(),
            'signature_path' => $signPath,
            'reference' => $request->reference ?? $document->reference,
        ]);

        return redirect()->route('stagiaires.documents.pdf', [$stagiaire, $document])
            ->with('success', 'Document approuvé.');
    }

    /**
     * Rejeter un document (DRH uniquement).
     */
    public function rejeter(Request $request, Stagiaire $stagiaire, StagiaireDocument $document)
    {
        abort_if($document->stagiaire_id !== $stagiaire->id, 404);
        abort_if(!Auth::user()->isDRH(), 403);

        $request->validate(['motif_rejet' => 'required|string|max:500']);

        $document->update([
            'statut' => 'rejete',
            'motif_rejet' => $request->motif_rejet,
            'approuve_par' => Auth::id(),
            'approuve_le' => now(),
        ]);

        return redirect()->route('drh.dashboard')->with('success', 'Document rejeté.');
    }

    /**
     * Générer le PDF (DRH uniquement, après approbation).
     */
    public function pdf(Stagiaire $stagiaire, StagiaireDocument $document)
    {
        abort_if($document->stagiaire_id !== $stagiaire->id, 404);
        abort_if($document->statut !== 'approuve', 403, 'Le document doit être approuvé.');

        $document->load('stagiaire');

        // Données de base
        $base = $this->baseData($stagiaire);
        
        // Construire les segments de service
        $services_list = $document->services ?? [$stagiaire->service];
        $service_segments = $this->buildServiceSegments($stagiaire, $services_list);
        $multi_service = count($service_segments) > 1;
        $service_phrase = $this->buildServicePhrase($service_segments);

        // Signature du DRH
        $signatureUrl = null;
        if ($document->signature_path) {
            if (Storage::disk('public')->exists($document->signature_path)) {
                $signatureUrl = Storage::url($document->signature_path);
            } elseif (filter_var($document->signature_path, FILTER_VALIDATE_URL)) {
                $signatureUrl = $document->signature_path;
            }
        }

        // Choisir la vue selon le type de document
        $view = $document->type_document === 'autorisation'
            ? 'stagiaires.documents.autorisation_stage'
            : 'stagiaires.documents.attestation_stage';

        return view($view, array_merge(
            $base,
            compact('document', 'stagiaire', 'service_segments', 'multi_service', 'service_phrase', 'signatureUrl'),
            ['reference' => $document->reference]
        ));
    }
}