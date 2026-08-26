<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\GrilleSalariale;
use App\Models\PayAdjustment;
use App\Models\PaySlip;
use Illuminate\Support\Carbon;

class PayrollService
{
    /**
     * Calcule l'Impôt sur le Traitement des Salaires (ITS) selon le barème progressif du Bénin.
     */
    public static function calculerITS(float $baseImposable): float
    {
        if ($baseImposable <= 60000) {
            return 0;
        }

        $tax = 0;

        // Tranche 1 : 60 000 — 150 000 (taux 10%)
        if ($baseImposable > 60000) {
            $taxableInBracket = min($baseImposable - 60000, 90000);
            $tax += $taxableInBracket * 0.10;
        }

        // Tranche 2 : 150 000 — 250 000 (taux 15%)
        if ($baseImposable > 150000) {
            $taxableInBracket = min($baseImposable - 150000, 100000);
            $tax += $taxableInBracket * 0.15;
        }

        // Tranche 3 : 250 000 — 500 000 (taux 19%)
        if ($baseImposable > 250000) {
            $taxableInBracket = min($baseImposable - 250000, 250000);
            $tax += $taxableInBracket * 0.19;
        }

        // Tranche 4 : 500 000 et plus (taux 30%)
        if ($baseImposable > 500000) {
            $taxableInBracket = $baseImposable - 500000;
            $tax += $taxableInBracket * 0.30;
        }

        return round($tax);
    }

    /**
     * Calcule tous les détails financiers du bulletin de paie d'un personnel pour une période donnée.
     */
    public function calculerDetailsFiche(Personnel $personnel, string $moisCode, array $variables = []): array
    {
        // 1. Déterminer le salaire de base à partir du contrat actif
        $contrat = $personnel->contrat_actif;
        $salaireBaseOriginal = 0;

        if ($contrat) {
            if ($contrat->salaire_base && $contrat->salaire_base > 0) {
                $salaireBaseOriginal = (float) $contrat->salaire_base;
            } else {
                $case = GrilleSalariale::caseGrille($contrat->categorie, $contrat->echelon);
                $salaireBaseOriginal = $case ? (float) $case->salaire : 0;
            }
        }

        // 2. Traitement des jours travaillés (Base statutaire de 24 jours par mois)
        $joursAbsence = isset($variables['jours_absence']) ? (int) $variables['jours_absence'] : 0;
        $joursMiseAPied = isset($variables['jours_mise_a_pied']) ? (int) $variables['jours_mise_a_pied'] : 0;
        
        $joursTravailles = 24 - $joursAbsence - $joursMiseAPied;
        if ($joursTravailles < 0) {
            $joursTravailles = 0;
        }

        // Prorata du salaire de base sur 24 jours
        $salaireBaseProrate = ($salaireBaseOriginal / 24) * $joursTravailles;

        // 3. Indemnités & Primes Fixes
        $indemniteResidence = isset($variables['indemnite_residence']) ? (float) $variables['indemnite_residence'] : 0;
        $indemniteLogement = isset($variables['indemnite_logement']) ? (float) $variables['indemnite_logement'] : 0;
        // Prime de transport = 10% du salaire de base par défaut si non spécifié
        $indemniteTransport = isset($variables['indemnite_transport']) ? (float) $variables['indemnite_transport'] : round($salaireBaseOriginal * 0.10);
        $autreIndemnite = isset($variables['autre_indemnite']) ? (float) $variables['autre_indemnite'] : 0;
        $ecart = isset($variables['ecart']) ? (float) $variables['ecart'] : 0;

        // 4. Primes
        $primeCaisse = isset($variables['prime_caisse']) ? (float) $variables['prime_caisse'] : 0;
        $primeRisque = isset($variables['prime_risque']) ? (float) $variables['prime_risque'] : 0;
        $primeResponsabilite = isset($variables['prime_responsabilite']) ? (float) $variables['prime_responsabilite'] : 0;
        $primeGarde = isset($variables['prime_garde']) ? (float) $variables['prime_garde'] : 0;
        $primeSpecialite = isset($variables['prime_specialite']) ? (float) $variables['prime_specialite'] : 0;
        $autrePrime = isset($variables['autre_prime']) ? (float) $variables['autre_prime'] : 0;

        // Heures Supplémentaires
        $heuresSup12  = isset($variables['heures_sup_12']) ? (float) $variables['heures_sup_12'] : 0;
        $heuresSup35  = isset($variables['heures_sup_35']) ? (float) $variables['heures_sup_35'] : 0;
        $heuresSup50  = isset($variables['heures_sup_50']) ? (float) $variables['heures_sup_50'] : 0;
        $heuresSup100 = isset($variables['heures_sup_100']) ? (float) $variables['heures_sup_100'] : 0;
        $montantHeuresSup = $heuresSup12 + $heuresSup35 + $heuresSup50 + $heuresSup100;

        // 5. Retenues sur brut
        $tropPercuBrut = isset($variables['trop_percu_brut']) ? (float) $variables['trop_percu_brut'] : 0;

        // Calcul du salaire brut (Sb)
        $salaireBrut = $salaireBaseProrate + $indemniteResidence + $indemniteLogement + $indemniteTransport + $autreIndemnite + $ecart
                     + $primeCaisse + $primeRisque + $primeResponsabilite + $primeGarde + $primeSpecialite + $autrePrime
                     + $montantHeuresSup - $tropPercuBrut;

        if ($salaireBrut < 0) {
            $salaireBrut = 0;
        }

        // 6. Cotisation Sociale (Part Salariale CNSS 3.6%)
        $cotisationSalarie = round($salaireBrut * 0.036);

        // 7. Impôts (ITS sur base imposable)
        $baseImposable = $salaireBrut - $cotisationSalarie;
        $impotIts = $this->calculerITS($baseImposable);

        // 8. Charges Patronales (Part Patronale 16.4%)
        $cotisationPatronale = round($salaireBrut * 0.064);
        $prestationFamiliale = round($salaireBrut * 0.09);
        $risqueProfessionnel = round($salaireBrut * 0.01);

        // 9. Retenues sur Net & Déductions
        $month = substr($moisCode, 5, 2);
        $taxeRadio = ($month === '03') ? 1000.0 : 0.0;
        $taxeTele = ($month === '06') ? 3000.0 : 0.0;

        $fraisMedicaux = isset($variables['frais_medicaux']) ? (float) $variables['frais_medicaux'] : $this->getAdjustmentAmount($personnel->id, 'frais_medicaux', $moisCode);
        $avanceSalaire = isset($variables['avance_salaire']) ? (float) $variables['avance_salaire'] : $this->getAdjustmentAmount($personnel->id, 'avance_salaire', $moisCode);
        $tropPercuNet = isset($variables['trop_percu_net']) ? (float) $variables['trop_percu_net'] : 0;

        $delegationSaisie   = isset($variables['delegation_saisie']) ? (float) $variables['delegation_saisie'] : 0;
        $pretLongTerme      = isset($variables['pret_long_terme']) ? (float) $variables['pret_long_terme'] : 0;
        $retenueCompteTiers = isset($variables['retenue_compte_tiers']) ? (float) $variables['retenue_compte_tiers'] : 0;
        $pretEcobank        = isset($variables['pret_ecobank']) ? (float) $variables['pret_ecobank'] : 0;
        $assuranceAscoma    = isset($variables['assurance_ascoma']) ? (float) $variables['assurance_ascoma'] : 0;

        // Mise à pied : retenue équivalente aux jours de mise à pied sur base 24 jours
        $miseAPied = isset($variables['mise_a_pied']) ? (float) $variables['mise_a_pied'] : (($salaireBaseOriginal / 24) * $joursMiseAPied);

        // 10. Remboursements (Moins-Perçus)
        $moinsPercuRembourse = isset($variables['moins_percu_rembourse']) ? (float) $variables['moins_percu_rembourse'] : $this->getAdjustmentAmount($personnel->id, 'moins_percu', $moisCode);


        // Calcul du salaire net (Sn)
        $totalDeductionsNet = $cotisationSalarie + $impotIts + $taxeRadio + $taxeTele + $fraisMedicaux + $avanceSalaire + $tropPercuNet + $miseAPied 
                            + $delegationSaisie + $pretLongTerme + $retenueCompteTiers + $pretEcobank + $assuranceAscoma;

        $salaireNet = $salaireBrut - $totalDeductionsNet + $moinsPercuRembourse;

        if ($salaireNet < 0) {
            $salaireNet = 0;
        }

        // Règlement par défaut basé sur le contrat actif
        $banque = isset($variables['banque']) ? $variables['banque'] : ($contrat->banque ?? 'BOA');
        $modeReglement = isset($variables['mode_reglement']) ? $variables['mode_reglement'] : 'Virement';
        $numeroCompte = isset($variables['numero_compte']) ? $variables['numero_compte'] : ($contrat->numero_compte ?? null);

        return [
            'matricule_cnss'             => $personnel->numero_cnss,
            'poste'                      => $personnel->corporation,
            'type_contrat'               => $contrat->type_contrat ?? null,
            'categorie'                  => $contrat->categorie ?? null,
            'echelon'                    => $contrat->echelon ?? null,
            
            'banque'                     => $banque,
            'mode_reglement'             => $modeReglement,
            'numero_compte'              => $numeroCompte,

            'jours_travailles'           => $joursTravailles,
            'jours_absence'              => $joursAbsence,
            'heures_supplementaires'     => isset($variables['heures_supplementaires']) ? (float) $variables['heures_supplementaires'] : 0,
            'heures_astreinte'           => isset($variables['heures_astreinte']) ? (float) $variables['heures_astreinte'] : 0,

            'salaire_base'               => $salaireBaseOriginal,
            'indemnite_residence'        => $indemniteResidence,
            'indemnite_logement'         => $indemniteLogement,
            'indemnite_transport'        => $indemniteTransport,
            'autre_indemnite'            => $autreIndemnite,
            'ecart'                      => $ecart,

            'prime_caisse'               => $primeCaisse,
            'prime_risque'               => $primeRisque,
            'prime_responsabilite'       => $primeResponsabilite,
            'prime_garde'                => $primeGarde,
            'prime_specialite'           => $primeSpecialite,
            'autre_prime'                => $autrePrime,

            'trop_percu_brut'            => $tropPercuBrut,
            'salaire_brut'               => $salaireBrut,

            'cotisation_sociale_salarie' => $cotisationSalarie,
            'impot_its'                  => $impotIts,

            'cotisation_sociale_patronale'   => $cotisationPatronale,
            'prestation_familiale_patronale' => $prestationFamiliale,
            'risque_professionnel_patronale' => $risqueProfessionnel,

            'taxe_radio'                 => $taxeRadio,
            'taxe_tele'                  => $taxeTele,
            'frais_medicaux'             => $fraisMedicaux,
            'avance_salaire'             => $avanceSalaire,
            'trop_percu_net'             => $tropPercuNet,
            'mise_a_pied'                => $miseAPied,

            'delegation_saisie'          => $delegationSaisie,
            'pret_long_terme'            => $pretLongTerme,
            'retenue_compte_tiers'       => $retenueCompteTiers,
            'pret_ecobank'               => $pretEcobank,
            'assurance_ascoma'           => $assuranceAscoma,

            'moins_percu_rembourse'      => $moinsPercuRembourse,
            'salaire_net'                => $salaireNet,
        ];
    }

    /**
     * Récupère le montant mensuel actif pour un type d'ajustement.
     */
    private function getAdjustmentAmount(int $personnelId, string $type, string $moisCode = ''): float
    {
        $adj = PayAdjustment::where('personnel_id', $personnelId)
            ->where('type', $type)
            ->where('statut', 'actif')
            ->first();

        if (!$adj) {
            return 0.0;
        }

        // Si un écheancier personnalisé existe, utiliser le montant du mois donné
        $targetMonth = !empty($moisCode) ? $moisCode : date('Y-m');
        return $adj->getMontantPourMois($targetMonth);
    }

    /**
     * Génère/Calcule les bulletins de paie de brouillon pour tous les personnels actifs d'un centre de santé.
     * Seuls les personnels en CDI et CDD sont considérés (les prestataires sont exclus de la paie).
     */
    public function initialiserBulletinsPourPeriode(int $payPeriodId, string $moisCode, ?int $centreId = null)
    {
        // Récupérer le personnel actif du centre ayant un contrat CDI ou CDD
        $personnels = Personnel::where('statut', 'actif')
            ->when($centreId, fn($q) => $q->where('centre_id', $centreId))
            ->get()
            ->filter(function ($p) {
                $type = strtoupper($p->type_contrat_actuel ?? '');
                return in_array($type, ['CDI', 'CDD']);
            });

        foreach ($personnels as $p) {
            // Vérifier s'il n'y a pas déjà un bulletin actif (non-fictif) dans ce centre
            $exists = PaySlip::where('pay_period_id', $payPeriodId)
                ->where('personnel_id', $p->id)
                ->where('centre_id', $p->centre_id)
                ->where('is_fictif', false)
                ->exists();

            if (!$exists) {
                // Calculer les détails initiaux avec variables par défaut (vides)
                $details = $this->calculerDetailsFiche($p, $moisCode);
                
                $details['pay_period_id'] = $payPeriodId;
                $details['personnel_id']  = $p->id;
                $details['centre_id']     = $p->centre_id;
                $details['is_fictif']     = false;
                
                PaySlip::create($details);
            }
        }
    }


    /**
     * Applique la décrémentation des échéances actives des agents lors de la clôture d'un mois de paie.
     */
    public function traiterClotureAjustementsPourPeriode(int $payPeriodId)
    {
        $paySlips = PaySlip::where('pay_period_id', $payPeriodId)->get();

        foreach ($paySlips as $slip) {
            // Rechercher les échéanciers actifs correspondants
            $adjustments = PayAdjustment::where('personnel_id', $slip->personnel_id)
                ->where('statut', 'actif')
                ->get();

            foreach ($adjustments as $adj) {
                // Décrémenter s'il y a un nombre de mois défini
                if ($adj->mois_restants !== null) {
                    $newMoisRestants = $adj->mois_restants - 1;
                    if ($newMoisRestants <= 0) {
                        $adj->update([
                            'mois_restants' => 0,
                            'statut'        => 'termine'
                        ]);
                    } else {
                        $adj->update([
                            'mois_restants' => $newMoisRestants
                        ]);
                    }
                }
            }
        }
    }
}
