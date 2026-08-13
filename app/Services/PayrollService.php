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

        // 2. Traitement des jours travaillés
        $joursAbsence = isset($variables['jours_absence']) ? (int) $variables['jours_absence'] : 0;
        $joursMiseAPied = isset($variables['jours_mise_a_pied']) ? (int) $variables['jours_mise_a_pied'] : 0;
        
        $joursTravailles = 30 - $joursAbsence - $joursMiseAPied;
        if ($joursTravailles < 0) {
            $joursTravailles = 0;
        }

        // Prorata du salaire de base
        $salaireBaseProrate = ($salaireBaseOriginal / 30) * $joursTravailles;

        // 3. Indemnités
        // Résidence = 10% du salaire de base proratisé
        $indemniteResidence = round($salaireBaseProrate * 0.10);
        $indemniteLogement = isset($variables['indemnite_logement']) ? (float) $variables['indemnite_logement'] : 0;
        $indemniteTransport = isset($variables['indemnite_transport']) ? (float) $variables['indemnite_transport'] : 0;
        $autreIndemnite = isset($variables['autre_indemnite']) ? (float) $variables['autre_indemnite'] : 0;
        $ecart = isset($variables['ecart']) ? (float) $variables['ecart'] : 0;

        // 4. Primes
        $primeCaisse = isset($variables['prime_caisse']) ? (float) $variables['prime_caisse'] : 0;
        $primeRisque = isset($variables['prime_risque']) ? (float) $variables['prime_risque'] : 0;
        $primeResponsabilite = isset($variables['prime_responsabilite']) ? (float) $variables['prime_responsabilite'] : 0;
        $primeGarde = isset($variables['prime_garde']) ? (float) $variables['prime_garde'] : 0;
        $autrePrime = isset($variables['autre_prime']) ? (float) $variables['autre_prime'] : 0;

        // 5. Retenues sur brut
        $tropPercuBrut = isset($variables['trop_percu_brut']) ? (float) $variables['trop_percu_brut'] : 0;

        // Calcul du salaire brut (Sb)
        $salaireBrut = $salaireBaseProrate + $indemniteResidence + $indemniteLogement + $indemniteTransport + $autreIndemnite + $ecart
                     + $primeCaisse + $primeRisque + $primeResponsabilite + $primeGarde + $autrePrime
                     - $tropPercuBrut;

        if ($salaireBrut < 0) {
            $salaireBrut = 0;
        }

        // 6. Cotisation Sociale (Employé 3.6%)
        $cotisationSalarie = round($salaireBrut * 0.036);

        // 7. Impôts (ITS sur base Salaire Brut - Cotisation Sociale)
        $baseImposable = $salaireBrut - $cotisationSalarie;
        $impotIts = $this->calculerITS($baseImposable);

        // 8. Charges Patronales (Part Patronale 16.4%)
        $cotisationPatronale = round($salaireBrut * 0.064);
        $prestationFamiliale = round($salaireBrut * 0.09);
        $risqueProfessionnel = round($salaireBrut * 0.01);

        // 9. Retenues sur Net
        // Redevances fiscales annuelles obligatoires (Radio en Mars, Télé en Juin)
        $month = substr($moisCode, 5, 2);
        $taxeRadio = ($month === '03') ? 1000.0 : 0.0;
        $taxeTele = ($month === '06') ? 3000.0 : 0.0;

        // Récupérer les échéances actives automatiques si non fournies
        $fraisMedicaux = isset($variables['frais_medicaux']) ? (float) $variables['frais_medicaux'] : $this->getAdjustmentAmount($personnel->id, 'frais_medicaux');
        $avanceSalaire = isset($variables['avance_salaire']) ? (float) $variables['avance_salaire'] : $this->getAdjustmentAmount($personnel->id, 'avance_salaire');
        $tropPercuNet = isset($variables['trop_percu_net']) ? (float) $variables['trop_percu_net'] : 0;

        // Mise à pied : retenue équivalente aux jours de mise à pied
        $miseAPied = isset($variables['mise_a_pied']) ? (float) $variables['mise_a_pied'] : (($salaireBaseOriginal / 30) * $joursMiseAPied);

        // 10. Remboursements (Moins-Perçus)
        $moinsPercuRembourse = isset($variables['moins_percu_rembourse']) ? (float) $variables['moins_percu_rembourse'] : $this->getAdjustmentAmount($personnel->id, 'moins_percu');

        // Calcul du salaire net (Sn)
        // Sn = Brut - CotisationSalarie - ITS - Taxes - Retenues - MiseAPied + Remboursements
        $salaireNet = $salaireBrut - $cotisationSalarie - $impotIts - $taxeRadio - $taxeTele - $fraisMedicaux - $avanceSalaire - $tropPercuNet - $miseAPied + $moinsPercuRembourse;

        if ($salaireNet < 0) {
            $salaireNet = 0;
        }

        // Règlement par défaut basé sur le contrat actif
        $banque = isset($variables['banque']) ? $variables['banque'] : ($contrat->banque ?? 'Archevêché');
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

            'moins_percu_rembourse'      => $moinsPercuRembourse,
            'salaire_net'                => $salaireNet,
        ];
    }

    /**
     * Récupère le montant mensuel actif pour un type d'ajustement.
     */
    private function getAdjustmentAmount(int $personnelId, string $type): float
    {
        $adj = PayAdjustment::where('personnel_id', $personnelId)
            ->where('type', $type)
            ->where('statut', 'actif')
            ->first();

        return $adj ? (float) $adj->montant_mensuel : 0.0;
    }

    /**
     * Génère/Calcule les bulletins de paie de brouillon pour tous les personnels actifs d'un centre de santé.
     */
    public function initialiserBulletinsPourPeriode(int $payPeriodId, string $moisCode, ?int $centreId = null)
    {
        // Récupérer tout le personnel actif de ce centre
        $personnels = Personnel::where('statut', 'actif')
            ->when($centreId, fn($q) => $q->where('centre_id', $centreId))
            ->get();

        foreach ($personnels as $p) {
            // Vérifier s'il n'y a pas déjà un bulletin
            $exists = PaySlip::where('pay_period_id', $payPeriodId)
                ->where('personnel_id', $p->id)
                ->exists();

            if (!$exists) {
                // Calculer les détails initiaux avec variables par défaut (vides)
                $details = $this->calculerDetailsFiche($p, $moisCode);
                
                $details['pay_period_id'] = $payPeriodId;
                $details['personnel_id'] = $p->id;
                $details['centre_id'] = $p->centre_id;
                
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
