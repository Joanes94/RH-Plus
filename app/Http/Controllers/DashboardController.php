<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use App\Models\Personnel;
use App\Models\Contrat;
use App\Models\Stagiaire;
use App\Models\StagiaireDocument;
use App\Models\Evaluation;
use App\Models\Avancement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Les rôles globaux voient le dashboard global par défaut
        if ($user->isGlobal() || $user->isCRH()) {
            return $this->dashboardGlobal($request);
        }

        // DRH centre ou Directeur (avec ou sans DRH dédié) → dashboard.drh (vue enrichie)
        if ($user->isDRH() || $user->isDirecteurCentre()) {
            return $this->dashboardDrhCentre($request, $user->centre_id);
        }

        // Assistant RH → dashboard.assistant_rh (vue simplifiée)
        return $this->dashboardAssistant($request, $user->centre_id);
    }

    /**
     * Tableau de bord global — CRH, DDIS, DDRH
     */
    public function dashboardGlobal(Request $request)
    {
        $selectedCentreId = $request->get('centre_id');
        $centres = Centre::actifs()->orderBy('nom')->get();

        $stats = [];
        foreach ($centres as $centre) {
            $personnelsCentre = Personnel::where('centre_id', $centre->id)->enPoste();

            $stats[$centre->id] = [
                'centre'       => $centre,
                'total'        => (clone $personnelsCentre)->count(),
                'hommes'       => (clone $personnelsCentre)->where('sexe', 'M')->count(),
                'femmes'       => (clone $personnelsCentre)->where('sexe', 'F')->count(),
                'cdd'          => (clone $personnelsCentre)->whereHas('contrats', fn($q) => $q->where('statut', 'actif')->where('type_contrat', 'CDD'))->count(),
                'cdi'          => (clone $personnelsCentre)->whereHas('contrats', fn($q) => $q->where('statut', 'actif')->where('type_contrat', 'CDI'))->count(),
                'prestataires' => (clone $personnelsCentre)->whereHas('contrats', fn($q) => $q->where('statut', 'actif')->where('type_contrat', 'Prestataire'))->count(),
                'stagiaires'   => Stagiaire::where('centre_id', $centre->id)->where('statut', 'en_cours')->count(),
            ];
        }

        // Totaux globaux (ou filtrés si un centre est sélectionné)
        if ($selectedCentreId && isset($stats[$selectedCentreId])) {
            $totaux = $stats[$selectedCentreId];
            $totaux['stagiaires'] = $stats[$selectedCentreId]['stagiaires'];
        } else {
            $totaux = [
                'total'        => array_sum(array_column($stats, 'total')),
                'hommes'       => array_sum(array_column($stats, 'hommes')),
                'femmes'       => array_sum(array_column($stats, 'femmes')),
                'cdd'          => array_sum(array_column($stats, 'cdd')),
                'cdi'          => array_sum(array_column($stats, 'cdi')),
                'prestataires' => array_sum(array_column($stats, 'prestataires')),
                'stagiaires'   => array_sum(array_column($stats, 'stagiaires')),
            ];
        }

        // Stagiaires globaux sans centre attribué si vue globale
        if (!$selectedCentreId) {
            $stagiairesNonAffectes = Stagiaire::whereNull('centre_id')->where('statut', 'en_cours')->count();
            $totaux['stagiaires'] += $stagiairesNonAffectes;
        }

        // Alertes globales
        $contratsExpirantBientot = Contrat::where('statut', 'actif')
            ->whereIn('type_contrat', ['CDD', 'Prestataire'])
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [Carbon::today(), Carbon::today()->addDays(30)])
            ->when($selectedCentreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $selectedCentreId)))
            ->with('personnel.centre')
            ->orderBy('date_fin')
            ->get();

        $retraitesImminentes = Personnel::enPoste()
            ->whereNotNull('date_naissance')
            ->whereHas('contrats', fn($q) => $q->where('statut', 'actif')->where('type_contrat', 'CDI'))
            ->when($selectedCentreId, fn($q) => $q->where('centre_id', $selectedCentreId))
            ->get()
            ->filter(fn($p) => $p->date_naissance && $p->date_naissance->copy()->addYears(60)->between(Carbon::today(), Carbon::today()->addMonths(3)))
            ->values();

        // Compteurs de demandes en attente
        $congesQuery = Conge::where('statut', 'soumis');
        $absencesQuery = Absence::where('statut', 'soumis');
        $demandesQuery = Demande::where('statut', 'soumis');
        $stagiaireDocsQuery = StagiaireDocument::where('statut', 'soumis');
        $evaluationsQuery = Evaluation::where('statut', 'soumis');
        $avancementsQuery = Avancement::where('statut', 'soumis');

        if ($selectedCentreId) {
            $congesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $selectedCentreId));
            $absencesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $selectedCentreId));
            $demandesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $selectedCentreId));
            $stagiaireDocsQuery->whereHas('stagiaire', fn($q) => $q->where('centre_id', $selectedCentreId));
            $evaluationsQuery->whereHas('stagiaire', fn($q) => $q->where('centre_id', $selectedCentreId));
            $avancementsQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $selectedCentreId));
        }

        $nbCongesSoumis      = $congesQuery->count();
        $nbAbsencesSoumis    = $absencesQuery->count();
        $nbDemandesSoumis    = $demandesQuery->count();
        $nbStagiaireDocs     = $stagiaireDocsQuery->count();
        $nbEvaluationsSoumis = $evaluationsQuery->count();
        $nbAvancementsSoumis = $avancementsQuery->count();

        $nbDemandesTotal = $nbCongesSoumis + $nbAbsencesSoumis + $nbDemandesSoumis + $nbStagiaireDocs + $nbEvaluationsSoumis;

        return view('dashboard.global', compact(
            'centres', 'stats', 'totaux', 'selectedCentreId',
            'contratsExpirantBientot', 'retraitesImminentes',
            'nbCongesSoumis', 'nbAbsencesSoumis', 'nbDemandesSoumis',
            'nbStagiaireDocs', 'nbEvaluationsSoumis', 'nbAvancementsSoumis',
            'nbDemandesTotal'
        ));
    }

    /**
     * Tableau de bord par centre — DRH centre, Directeur, ou rôle global filtrant
     */
    public function dashboardParCentre(Request $request)
    {
        $user = auth()->user();
        $centreId = $request->get('centre_id', $user->centre_id);

        // Vérification d'accès
        if (!$user->canViewCentre($centreId)) {
            abort(403);
        }

        return $this->dashboardDrhCentre($request, $centreId);
    }

    private function dashboardDrhCentre(Request $request, ?int $centreId)
    {
        $centre = $centreId ? Centre::find($centreId) : null;
        $centres = Centre::actifs()->orderBy('nom')->get();

        $query = Personnel::enPoste()->personnelPrincipal();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $personnels = $query->get();
        $effectifTotal = $personnels->count();
        $hommes = $personnels->where('sexe', 'M')->count();
        $femmes = $personnels->where('sexe', 'F')->count();

        // Anciens travailleurs
        $anciensQuery = Personnel::anciensTravailleurs()->personnelPrincipal();
        if ($centreId) {
            $anciensQuery->where('centre_id', $centreId);
        }
        $nbAnciens = $anciensQuery->count();

        // Demandes en attente
        $congesQuery = Conge::where('statut', 'soumis');
        $absencesQuery = Absence::where('statut', 'soumis');
        $demandesQuery = Demande::where('statut', 'soumis');

        if ($centreId) {
            $congesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $centreId));
            $absencesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $centreId));
            $demandesQuery->whereHas('personnel', fn($q) => $q->where('centre_id', $centreId));
        }

        $nbCongesSoumis   = $congesQuery->count();
        $nbAbsencesSoumis = $absencesQuery->count();
        $nbDemandesSoumis = $demandesQuery->count();

        $nbStagiairesEnCours = Stagiaire::query()
            ->when($centreId, fn($q) => $q->where('centre_id', $centreId))
            ->where('statut', 'en_cours')
            ->count();

        // Personnel en congé
        $enConge = Conge::where('statut', 'approuve')
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->with('personnel.centre')
            ->get();

        // Effectifs par service
        $parService = $personnels->groupBy('service')->map->count()->sortDesc();

        // Contrats expirant bientôt
        $contratsExpirantBientot = Contrat::where('statut', 'actif')
            ->whereIn('type_contrat', ['CDD', 'Prestataire'])
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays(30)])
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->with('personnel.centre')
            ->orderBy('date_fin')
            ->get();

        return view('dashboard.drh', compact(
            'centre', 'centres', 'effectifTotal', 'hommes', 'femmes', 'nbAnciens', 'nbStagiairesEnCours',
            'nbCongesSoumis', 'nbAbsencesSoumis', 'nbDemandesSoumis',
            'enConge', 'parService', 'contratsExpirantBientot'
        ));
    }

    private function dashboardAssistant(Request $request, ?int $centreId)
    {
        $centre = $centreId ? Centre::find($centreId) : null;

        $query = Personnel::enPoste()->personnelPrincipal();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $personnels = $query->get();
        $effectifTotal = $personnels->count();
        $hommes = $personnels->where('sexe', 'M')->count();
        $femmes = $personnels->where('sexe', 'F')->count();

        // Anciens
        $anciensQuery = Personnel::anciensTravailleurs()->personnelPrincipal();
        if ($centreId) {
            $anciensQuery->where('centre_id', $centreId);
        }
        $anciens = $anciensQuery->latest()->take(5)->get();
        $nbAnciens = $anciensQuery->count();

        // Demandes soumises en attente de réponse DRH
        $nbCongesSoumis   = Conge::where('statut', 'soumis')
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->count();
        $nbAbsencesSoumis = Absence::where('statut', 'soumis')
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->count();
        $nbDemandesSoumis = Demande::where('statut', 'soumis')
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->count();

        $enConge = Conge::where('statut', 'approuve')
            ->where('date_debut', '<=', now())->where('date_fin', '>=', now())
            ->when($centreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $centreId)))
            ->count();

        return view('dashboard.assistant_rh', compact(
            'centre', 'effectifTotal', 'hommes', 'femmes', 'nbAnciens', 'anciens',
            'nbCongesSoumis', 'nbAbsencesSoumis', 'nbDemandesSoumis', 'enConge'
        ));
    }
}