<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Contrat;
use App\Models\ConfigRh;
use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    /**
     * Page de sélection des filtres pour le rapport.
     */
    public function personnel(Request $request)
    {
        $user = auth()->user();
        $centreId = $request->filled('centre_id') && $user?->isGlobal() ? (int)$request->centre_id : (!$user?->isGlobal() ? $user?->centre_id : null);

        // Stats de prévisualisation selon les filtres
        $query = $this->buildQuery($request, $centreId);

        // "En congé" n'est pas une valeur stockée dans personnels.statut : c'est
        // un état calculé à partir des congés approuvés couvrant la date du jour.
        $idsEnConge = \App\Models\Conge::where('statut', 'approuve')
            ->whereDate('date_debut', '<=', now())
            ->whereDate('date_fin', '>=', now())
            ->pluck('personnel_id');

        $stats = [
            'total'     => (clone $query)->count(),
            'hommes'    => (clone $query)->where('sexe', 'M')->count(),
            'femmes'    => (clone $query)->where('sexe', 'F')->count(),
            'cdi'       => (clone $query)->whereHas('contrats', fn ($q) => $q->where('type_contrat', 'CDI'))->count(),
            'cdd'       => (clone $query)->whereHas('contrats', fn ($q) => $q->where('type_contrat', 'CDD'))->count(),
            'actifs'    => (clone $query)->where('statut', 'actif')->count(),
            'inactifs'  => (clone $query)->where('statut', 'inactif')->count(),
            'en_conge'  => (clone $query)->whereIn('id', $idsEnConge)->count(),
            'retraites' => (clone $query)->where('statut', 'retraite')->count(),
            'stagiaires' => (clone $query)->whereHas('contrats', fn ($q) => $q->where('type_contrat', 'Stagiaire'))->count(),
            'prestataires' => (clone $query)->whereHas('contrats', fn ($q) => $q->where('type_contrat', 'Prestataire'))->count(),
        ];

        // Aperçu des 5 premiers résultats
        $apercu = (clone $query)->with('contrats')->orderBy('nom')->take(5)->get();

        return view('rapports.personnel', [
            'services'     => Personnel::services(),
            'corporations' => Personnel::corporations(),
            'contrats'     => Personnel::typesContrat(),
            'centres'      => \App\Models\Centre::actifs()->orderBy('nom')->get(),
            'filters'      => $request->only('service', 'sexe', 'statut', 'type_contrat', 'corporation', 'centre_id'),
            'stats'        => $stats,
            'apercu'       => $apercu,
        ]);
    }

    // ── Rapport "Absents du jour" ───────────────────────────────────────────────
    // Agrège Congés, Absences et Demandes de congé maladie approuvés dont la
    // période couvre la date sélectionnée.

    public function absents(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);
        $date = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : now()->startOfDay();

        $user = auth()->user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

        // Déterminer le centre : global = filtre optionnel, local = forcé
        if ($isGlobal) {
            $centreId = $request->filled('centre_id') ? (int) $request->centre_id : null;
        } else {
            $centreId = $user?->centre_id;
        }

        $absents = $this->buildAbsentsList($date, $centreId);

        if ($request->filled('service')) {
            $absents = $absents->filter(fn ($a) => $a['personnel']->service === $request->service);
        }
        if ($request->filled('sexe')) {
            $absents = $absents->filter(fn ($a) => $a['personnel']->sexe === $request->sexe);
        }

        $absents = $absents->sortBy(fn ($a) => $a['personnel']->nom_complet)->values();

        $stats = [
            'total'  => $absents->count(),
            'hommes' => $absents->where('personnel.sexe', 'M')->count(),
            'femmes' => $absents->where('personnel.sexe', 'F')->count(),
            'conge'         => $absents->where('categorie', 'Congé')->count(),
            'absence'       => $absents->where('categorie', 'Absence')->count(),
            'conge_maladie' => $absents->where('categorie', 'Congé maladie')->count(),
        ];

        return view('rapports.absents', [
            'date'     => $date,
            'absents'  => $absents,
            'stats'    => $stats,
            'services' => Personnel::services(),
            'centres'  => \App\Models\Centre::actifs()->orderBy('nom')->get(),
            'filters'  => $request->only('service', 'sexe', 'centre_id'),
            'isGlobal' => $isGlobal,
        ]);
    }

    /**
     * Construit la liste des agents absents à une date donnée, toutes sources
     * confondues (congés, absences exceptionnelles, congés maladie).
     */
    private function buildAbsentsList(Carbon $date, ?int $centreId = null)
    {
        $absents = collect();

        Conge::with('personnel')
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($q2) => $q2->where('centre_id', $centreId)))
            ->where('statut', 'approuve')
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>', $date) // date_fin is the return date, so they are absent if return date is strictly after $date
            ->get()
            ->each(function (Conge $c) use ($absents) {
                if (!$c->personnel) return;
                $absents->push([
                    'personnel'  => $c->personnel,
                    'categorie'  => 'Congé',
                    'type_label' => $c->getTypeCongeLabel(),
                    'date_debut' => $c->date_debut,
                    'date_fin'   => $c->date_fin,
                    'date_reprise'  => $c->date_fin, // In Conge, date_fin is already the return date
                    'route_show' => route('conges.show', $c),
                ]);
            });

        Absence::with('personnel')
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($q2) => $q2->where('centre_id', $centreId)))
            ->where('statut', 'approuve')
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date)
            ->get()
            ->each(function (Absence $a) use ($absents) {
                if (!$a->personnel) return;
                $absents->push([
                    'personnel'  => $a->personnel,
                    'categorie'  => 'Absence',
                    'type_label' => $a->getTypeLabel(),
                    'date_debut' => $a->date_debut,
                    'date_fin'   => $a->date_fin,
                    'date_reprise'  => $a->date_reprise, // Uses holiday-aware accessor
                    'route_show' => route('absences.show', $a),
                ]);
            });

        Demande::with('personnel')
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($q2) => $q2->where('centre_id', $centreId)))
            ->where('statut', 'approuve')
            ->where('type_demande', 'conge_maladie')
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date)
            ->get()
            ->each(function (Demande $d) use ($absents) {
                if (!$d->personnel) return;
                $absents->push([
                    'personnel'  => $d->personnel,
                    'categorie'  => 'Congé maladie',
                    'type_label' => $d->type_label,
                    'date_debut' => $d->date_debut,
                    'date_fin'   => $d->date_fin,
                    'date_reprise'  => $d->date_reprise, // Uses holiday-aware accessor
                    'route_show' => route('demandes.show', $d),
                ]);
            });

        return $absents;
    }

    /**
     * Génération du PDF / page imprimable.
     */
    public function personnelPdf(Request $request)
    {
        $user = auth()->user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();
        $query      = $this->buildQuery($request, $isGlobal ? null : $user?->centre_id);
        $personnels = $query->with('contrats')->orderBy('nom')->orderBy('prenoms')->get();

        // Même correction que pour personnel() : "en congé" est calculé, pas stocké.
        $idsEnConge = \App\Models\Conge::where('statut', 'approuve')
            ->whereDate('date_debut', '<=', now())
            ->whereDate('date_fin', '>=', now())
            ->pluck('personnel_id');

        // Stats globales filtrées
        $stats = [
            'total'        => $personnels->count(),
            'hommes'       => $personnels->where('sexe', 'M')->count(),
            'femmes'       => $personnels->where('sexe', 'F')->count(),
            'cdi'          => $personnels->filter(fn ($p) => $p->type_contrat_actuel === 'CDI')->count(),
            'cdd'          => $personnels->filter(fn ($p) => $p->type_contrat_actuel === 'CDD')->count(),
            'stagiaires'   => $personnels->filter(fn ($p) => $p->type_contrat_actuel === 'Stagiaire')->count(),
            'prestataires' => $personnels->filter(fn ($p) => $p->type_contrat_actuel === 'Prestataire')->count(),
            'actifs'       => $personnels->where('statut', 'actif')->count(),
            'inactifs'     => $personnels->where('statut', 'inactif')->count(),
            'en_conge'     => $personnels->whereIn('id', $idsEnConge)->count(),
            'retraites'    => $personnels->where('statut', 'retraite')->count(),
        ];

        // Répartition par service (filtré)
        $parService = $personnels->groupBy('service')->map(fn($g) => [
            'total'  => $g->count(),
            'hommes' => $g->where('sexe', 'M')->count(),
            'femmes' => $g->where('sexe', 'F')->count(),
        ])->sortByDesc('total');

        // Répartition par corporation (filtré)
        $parCorp = $personnels->groupBy('corporation')->map(fn($g) => [
            'total'  => $g->count(),
            'hommes' => $g->where('sexe', 'M')->count(),
            'femmes' => $g->where('sexe', 'F')->count(),
        ])->sortByDesc('total');

        // Titre du rapport selon les filtres
        $titreFiltre = $this->buildTitreFiltre($request);

        // Logos en base64
        $logoPath  = public_path('images/letterhead/logo_archidiocese.jpeg');
        $evePath   = public_path('images/letterhead/photo_eveque.jpeg');
        $logoB64   = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $eveB64    = file_exists($evePath)  ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evePath))  : null;

        return view('rapports.personnel_pdf', [
            'personnels'   => $personnels,
            'stats'        => $stats,
            'parService'   => $parService,
            'parCorp'      => $parCorp,
            'filters'      => $request->only('service', 'sexe', 'statut', 'type_contrat', 'corporation'),
            'titreFiltre'  => $titreFiltre,
            'date_rapport' => now()->isoFormat('D MMMM YYYY'),
            'organisation' => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'        => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom'      => ConfigRh::get('drh_nom', ''),
            'logo_b64'     => $logoB64,
            'eveque_b64'   => $eveB64,
        ]);
    }

    // ── Rapport historique (Année / Mois) ──────────────────────────────────────
    // Répond à : "en [mois] [année], qui travaillait ici, dans quel service, en
    // quelle qualité ?" — basé sur les périodes réelles des contrats, pas sur
    // la situation actuelle du personnel.

    public function historique(Request $request)
    {
        $request->validate([
            'annee' => 'nullable|integer|min:2000|max:2100',
            'mois'  => 'nullable|integer|min:1|max:12',
        ]);
        $request->merge(['annee' => $request->input('annee', now()->year)]);

        $user = auth()->user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

        // Déterminer le centre : global = filtre optionnel, local = forcé
        if ($isGlobal) {
            $centreId = $request->filled('centre_id') ? (int) $request->centre_id : null;
        } else {
            $centreId = $user?->centre_id;
        }

        [$debut, $fin] = $this->periode($request);
        $contrats = $this->buildHistoriqueQuery($request, $debut, $fin, $centreId)->get();

        return view('rapports.historique', [
            'services'      => Personnel::services(),
            'contratsTypes' => Personnel::typesContrat(),
            'centres'       => \App\Models\Centre::actifs()->orderBy('nom')->get(),
            'filters'       => $request->only('annee', 'mois', 'service', 'type_contrat', 'centre_id'),
            'annee'         => (int) $request->annee,
            'mois'          => $request->filled('mois') ? (int) $request->mois : null,
            'periode_label' => $this->periodeLabel($request, $debut, $fin),
            'contrats'      => $contrats,
            'stats'         => $this->statsHistorique($contrats),
            'isGlobal'      => $isGlobal,
        ]);
    }

    public function historiquePdf(Request $request)
    {
        $request->validate([
            'annee' => 'nullable|integer|min:2000|max:2100',
            'mois'  => 'nullable|integer|min:1|max:12',
        ]);
        $request->merge(['annee' => $request->input('annee', now()->year)]);

        $user = auth()->user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

        if ($isGlobal) {
            $centreId = $request->filled('centre_id') ? (int) $request->centre_id : null;
        } else {
            $centreId = $user?->centre_id;
        }

        [$debut, $fin] = $this->periode($request);
        $contrats = $this->buildHistoriqueQuery($request, $debut, $fin, $centreId)->get();

        $logoPath  = public_path('images/letterhead/logo_archidiocese.jpeg');
        $evePath   = public_path('images/letterhead/photo_eveque.jpeg');
        $logoB64   = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $eveB64    = file_exists($evePath)  ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evePath))  : null;

        return view('rapports.historique_pdf', [
            'contrats'      => $contrats,
            'stats'         => $this->statsHistorique($contrats),
            'periode_label' => $this->periodeLabel($request, $debut, $fin),
            'filters'       => $request->only('annee', 'mois', 'service', 'type_contrat', 'centre_id'),
            'date_rapport'  => now()->isoFormat('D MMMM YYYY'),
            'organisation'  => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'         => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom'       => ConfigRh::get('drh_nom', ''),
            'logo_b64'      => $logoB64,
            'eveque_b64'    => $eveB64,
        ]);
    }

    /** Bornes [début, fin] de la période sélectionnée (mois précis ou année entière). */
    private function periode(Request $request): array
    {
        $annee = (int) $request->annee;

        if ($request->filled('mois')) {
            $debut = Carbon::create($annee, (int) $request->mois, 1)->startOfMonth();
            $fin   = $debut->copy()->endOfMonth();
        } else {
            $debut = Carbon::create($annee, 1, 1)->startOfYear();
            $fin   = Carbon::create($annee, 12, 31)->endOfYear();
        }

        return [$debut, $fin];
    }

    private function periodeLabel(Request $request, Carbon $debut, Carbon $fin): string
    {
        return $request->filled('mois')
            ? ucfirst($debut->locale('fr')->isoFormat('MMMM YYYY'))
            : 'Année ' . $debut->year;
    }

    /**
     * Contrats dont la période chevauche [début, fin] : ils représentent le
     * personnel réellement en poste durant la période demandée.
     */
    private function buildHistoriqueQuery(Request $request, Carbon $debut, Carbon $fin, ?int $centreId = null)
    {
        $query = Contrat::query()
            ->with('personnel')
            ->whereHas('personnel')
            ->where('date_debut', '<=', $fin)
            ->where(function ($q) use ($debut) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $debut);
            });

        if ($request->filled('service'))      $query->where('service', $request->service);
        if ($request->filled('type_contrat')) $query->where('type_contrat', $request->type_contrat);
        if ($centreId !== null) {
            $query->whereHas('personnel', fn ($q) => $q->where('centre_id', $centreId));
        }

        return $query->orderBy('date_debut');
    }

    private function statsHistorique($contrats): array
    {
        $personnesUniques = $contrats->pluck('personnel_id')->unique();

        return [
            'total_contrats'   => $contrats->count(),
            'total_personnes'  => $personnesUniques->count(),
            'hommes'           => $contrats->unique('personnel_id')->filter(fn ($c) => $c->personnel?->sexe === 'M')->count(),
            'femmes'           => $contrats->unique('personnel_id')->filter(fn ($c) => $c->personnel?->sexe === 'F')->count(),
            'par_service'      => $contrats->groupBy(fn ($c) => $c->service ?: 'Non renseigné')->map->count()->sortDesc(),
        ];
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function buildQuery(Request $request, ?int $centreId = null)
    {
        // Exclure les stagiaires, inclure les prestataires
        $query = Personnel::personnelPrincipal()->whereNull('deleted_at');

        // Application des filtres
        if ($request->filled('service'))      $query->where('service', $request->service);
        if ($request->filled('corporation'))  $query->where('corporation', $request->corporation);
        if ($request->filled('sexe'))         $query->where('sexe', $request->sexe);
        if ($request->filled('statut')) {
            if ($request->statut === 'en_conge') {
                $idsEnConge = \App\Models\Conge::where('statut', 'approuve')
                    ->whereDate('date_debut', '<=', now())
                    ->whereDate('date_fin', '>=', now())
                    ->pluck('personnel_id');
                $query->whereIn('id', $idsEnConge);
            } else {
                $query->where('statut', $request->statut);
            }
        }
        if ($request->filled('type_contrat')) {
            $t = $request->type_contrat;
            $query->where(function ($q) use ($t) {
                $q->whereHas('contrats', fn ($cq) => $cq->where('type_contrat', $t))
                  ->orWhere('type_contrat', $t);
            });
        }

        // Filtrage par centre (multi-centres)
        $user = auth()->user();
        if ($centreId !== null) {
            $query->where('centre_id', $centreId);
        } elseif ($request->filled('centre_id') && $user?->isGlobal()) {
            $query->where('centre_id', $request->centre_id);
        } elseif ($user?->isGlobal() === false && $user?->centre_id) {
            $query->where('centre_id', $user->centre_id);
        }

        return $query;
    }

    private function buildTitreFiltre(Request $request): string
    {
        $parts = [];
        if ($request->filled('service'))      $parts[] = 'Service : ' . $request->service;
        if ($request->filled('corporation'))  $parts[] = 'Corporation : ' . $request->corporation;
        if ($request->filled('sexe'))         $parts[] = ($request->sexe === 'F' ? '👩 Femmes' : '👨 Hommes');
        if ($request->filled('statut')) {
            $labels = ['actif' => 'Actifs', 'inactif' => 'Inactifs', 'en_conge' => 'En congé', 'retraite' => 'Retraités'];
            $parts[] = $labels[$request->statut] ?? $request->statut;
        }
        if ($request->filled('type_contrat')) $parts[] = 'Contrat : ' . $request->type_contrat;

        return $parts ? implode(' · ', $parts) : 'Tout le personnel';
    }
}