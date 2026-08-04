<?php

namespace App\Services;

use App\Models\JourFerie;
use App\Models\Personnel;
use Carbon\Carbon;

class CalendrierService
{
    /**
     * Calcule la date de REPRISE après $nbJours ouvrables de congé.
     * dateDebut incluse dans le décompte.
     * Ex : début=15/06, 3j → jours de congé = 15, 16, 17 → reprise le 18/06
     */
    public function calculerDateFin(Carbon $dateDebut, int $nbJours): Carbon
    {
        $feries  = array_merge(
            $this->feriesHash($dateDebut->year),
            $this->feriesHash($dateDebut->year + 1)
        );
        $current = $dateDebut->copy();
        $comptes = 0;

        while ($comptes < $nbJours) {
            if ($this->estOuvrable($current, $feries)) {
                $comptes++;
            }
            if ($comptes < $nbJours) {
                $current->addDay();
            }
        }

        // $current = dernier jour de congé → reprise = prochain jour ouvrable
        $reprise = $current->copy()->addDay();
        while (!$this->estOuvrable($reprise, $feries)) {
            $reprise->addDay();
        }

        return $reprise;
    }

    /**
     * Retourne le dernier jour de congé (pour affichage "du X au Y" dans les docs).
     */
    public function calculerDernierJourConge(Carbon $dateDebut, int $nbJours): Carbon
    {
        $feries  = array_merge(
            $this->feriesHash($dateDebut->year),
            $this->feriesHash($dateDebut->year + 1)
        );
        $current = $dateDebut->copy();
        $comptes = 0;

        while ($comptes < $nbJours) {
            if ($this->estOuvrable($current, $feries)) {
                $comptes++;
            }
            if ($comptes < $nbJours) {
                $current->addDay();
            }
        }

        return $current;
    }

    /**
     * Compte le nombre de jours ouvrables entre deux dates (incluses).
     */
    public function compterJoursOuvrables(Carbon $debut, Carbon $fin): int
    {
        $feries  = array_merge(
            $this->feriesHash($debut->year),
            $this->feriesHash($fin->year)
        );
        $current = $debut->copy();
        $count   = 0;

        while ($current->lte($fin)) {
            if ($this->estOuvrable($current, $feries)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /** Nombre de jours calendaires du congé de maternité (droit béninois : 14 semaines). */
    public const JOURS_CONGE_MATERNITE = 98;

    /**
     * Congé de maternité : 98 jours CALENDAIRES (14 semaines) à compter de la
     * date de début (incluse). La date de reprise est le premier jour
     * ouvrable suivant le dernier jour du congé.
     */
    public function calculerMaternite(Carbon $dateDebut): array
    {
        $dernierJour = $dateDebut->copy()->addDays(self::JOURS_CONGE_MATERNITE - 1);

        $feries = array_merge(
            $this->feriesHash($dernierJour->year),
            $this->feriesHash($dernierJour->year + 1)
        );

        $reprise = $dernierJour->copy()->addDay();
        while (!$this->estOuvrable($reprise, $feries)) {
            $reprise->addDay();
        }

        return [
            'dernier_jour' => $dernierJour,
            'date_reprise' => $reprise,
        ];
    }

    /**
     * Calcule le nombre de jours de congés acquis selon ancienneté ISD.
     * Base : 24 jours ouvrables.
     * +2j après 20 ans, +4j après 25 ans, +6j après 30 ans (max 30j).
     */
    public function joursAcquisParAnciennete(?Carbon $dateEntreeISD): int
    {
        if (!$dateEntreeISD) return 24;

        $annees = $dateEntreeISD->diffInYears(now());

        $maj = 0;
        if ($annees >= 30)      $maj = 6;
        elseif ($annees >= 25)  $maj = 4;
        elseif ($annees >= 20)  $maj = 2;

        return min(24 + $maj, 30);
    }

    /**
     * Calcule le solde de congés restants pour un personnel sur une année donnée.
     *
     * IMPORTANT : tout est en JOURS OUVRABLES.
     * - Congés pris : nb_jours_demandes (déjà stocké en ouvrables)
     * - Absences déductibles : on convertit en ouvrables via compterJoursOuvrables()
     */
    public function soldeConges(Personnel $personnel, string $annee): array
    {
        $entreeISD = $personnel->date_embauche_isd
            ? Carbon::parse($personnel->date_embauche_isd)
            : ($personnel->date_embauche_centre ? Carbon::parse($personnel->date_embauche_centre) : null);

        $acquis = $this->joursAcquisParAnciennete($entreeISD);

        // ── Congés approuvés cette année (déjà en jours ouvrables) ────────────
        // La maternité est un droit à part : elle ne doit pas être déduite du
        // solde de congés administratifs/techniques.
        $congesPris = (int) \App\Models\Conge::where('personnel_id', $personnel->id)
            ->where('annee', $annee)
            ->where('statut', 'approuve')
            ->where('type_conge', '!=', 'maternite')
            ->sum('nb_jours_demandes');

        // ── Absences déductibles approuvées cette année ───────────────────────
        // Convertir chaque absence en jours ouvrables (lun-ven, hors fériés)
        [$debutAnnee, $finAnnee] = $this->bornesAnneeRH($annee);

        $absencesDeductibles = \App\Models\Absence::where('personnel_id', $personnel->id)
            ->where('deductible', true)
            ->where('statut', 'approuve')
            ->whereBetween('date_debut', [$debutAnnee, $finAnnee])
            ->get();

        $absencesDedOuvrables = 0;
        foreach ($absencesDeductibles as $abs) {
            $absencesDedOuvrables += $this->compterJoursOuvrables(
                Carbon::parse($abs->date_debut),
                Carbon::parse($abs->date_fin)
            );
        }

        $totalPris = $congesPris + $absencesDedOuvrables;
        $restants  = $acquis - $totalPris;

        return [
            'acquis'              => $acquis,
            'conges_pris'         => $congesPris,
            'absences_ded'        => $absencesDedOuvrables,
            'total_pris'          => $totalPris,
            'restants'            => $restants,
            'annees_service'      => $entreeISD ? $entreeISD->diffInYears(now()) : 0,
        ];
    }

    public function bornesAnneeRH(string $annee): array
    {
        if (str_contains($annee, '-')) {
            [$a1, $a2] = explode('-', $annee);
            return [Carbon::create((int)$a1, 1, 1), Carbon::create((int)$a2, 12, 31)];
        }
        return [Carbon::create((int)$annee, 1, 1), Carbon::create((int)$annee, 12, 31)];
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function estOuvrable(Carbon $date, array $feries): bool
    {
        return !$date->isWeekend()
            && !in_array($date->format('Y-m-d'), $feries);
    }

    private function feriesHash(int $annee): array
    {
        return JourFerie::pourAnnee($annee);
    }
}