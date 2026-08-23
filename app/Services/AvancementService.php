<?php

namespace App\Services;

use App\Models\Avancement;
use App\Models\ConfigRh;
use App\Models\Contrat;
use App\Models\GrilleSalariale;
use App\Models\Personnel;
use Carbon\Carbon;

class AvancementService
{
    /** Âge à partir duquel la bonification (article 88) s'applique. */
    public const AGE_BONIFICATION = 58;

    /** Ancienneté (en mois) requise entre deux avancements d'échelon. */
    public const MOIS_ENTRE_ECHELONS = 24;

    /**
     * Parcourt tout le personnel actif et applique automatiquement les
     * avancements d'échelon et bonifications qui sont dus.
     *
     * @return array{echelons:int, bonifications:int, avancements: \Illuminate\Support\Collection}
     */
    public function traiterTout(): array
    {
        $resultats = collect();

        $personnels = Personnel::whereIn('statut', ['actif', 'en_conge'])
            ->with(['contrats' => fn ($q) => $q->orderByDesc('date_debut'), 'avancements'])
            ->get();

        foreach ($personnels as $personnel) {
            $contrat = $personnel->contrat_actif;
            if (!$contrat || !$contrat->categorie || !$contrat->echelon) {
                continue; // rien à évaluer sans catégorie/échelon renseignés
            }

            $bonification = $this->traiterBonification($personnel, $contrat);
            if ($bonification) {
                $resultats->push($bonification);
                continue; // une fois bonifié, on ne recalcule plus l'échelon ce passage-ci
            }

            $echelon = $this->traiterEchelon($personnel, $contrat);
            if ($echelon) {
                $resultats->push($echelon);
            }
        }

        return [
            'echelons'      => $resultats->where('type', 'echelon')->count(),
            'bonifications' => $resultats->where('type', 'bonification')->count(),
            'avancements'   => $resultats,
        ];
    }

    /**
     * Applique la bonification des 58 ans (article 88) si l'agent vient
     * d'atteindre l'âge requis et n'en a pas déjà bénéficié.
     */
    public function traiterBonification(Personnel $personnel, Contrat $contrat): ?Avancement
    {
        if (!$personnel->date_naissance || $personnel->age < self::AGE_BONIFICATION) {
            return null;
        }

        $dejaBonifie = $personnel->relationLoaded('avancements')
            ? $personnel->avancements->contains('type', 'bonification')
            : $personnel->avancements()->where('type', 'bonification')->exists();

        if ($dejaBonifie) {
            return null;
        }

        $caseBase = GrilleSalariale::caseGrille($contrat->categorie, 1);
        if (!$caseBase) return null;

        $coefficient   = (float) ConfigRh::get('coefficient_bonification', 2.3);
        $ancienSalaire = (int) $contrat->salaire_base;
        $nouveauSalaire = (int) round($caseBase->salaire * $coefficient);

        $avancement = Avancement::create([
            'personnel_id'          => $personnel->id,
            'contrat_id'            => $contrat->id,
            'type'                  => 'bonification',
            'statut'                => 'soumis',
            'date_effet'            => now()->toDateString(),
            'ancienne_categorie'    => $contrat->categorie,
            'ancien_echelon'        => $contrat->echelon,
            'nouvelle_categorie'    => $contrat->categorie,
            'nouvel_echelon'        => $contrat->echelon,
            'ancien_salaire'        => $ancienSalaire,
            'nouveau_salaire'       => $nouveauSalaire,
            'coefficient_applique'  => $coefficient,
            'numero_reference'      => $this->genererReference('bonification'),
        ]);

        // On ne met pas à jour le contrat ici, on attend l'approbation du DDIS.

        \App\Models\Notification::create([
            'centre_id'         => $personnel->centre_id,
            'type'              => 'bonification',
            'titre'             => 'Bonification (58 ans) soumise — ' . $personnel->nom_complet,
            'message'           => "{$personnel->nom_complet} a atteint 58 ans. Une bonification de l'article 88 (coefficient {$coefficient}) est soumise pour validation par la DDIS.",
            'personnel_id'      => $personnel->id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);

        return $avancement;
    }

    /**
     * Approuver une bonification (DDIS uniquement).
     */
    public function approuverBonification(Avancement $avancement, \App\Models\User $user): void
    {
        if ($avancement->type !== 'bonification' || !$avancement->isSoumis()) {
            return;
        }

        $contrat = $avancement->contrat ?? $avancement->personnel->contrat_actif;
        if ($contrat) {
            $contrat->update(['salaire_base' => $avancement->nouveau_salaire]);
        }

        $avancement->update([
            'statut'     => 'valide',
            'valide_par' => $user->id,
            'valide_le'  => now(),
        ]);

        // Historique
        \App\Models\PersonnelHistorique::create([
            'personnel_id'      => $avancement->personnel_id,
            'contrat_id'        => $contrat?->id,
            'centre_id'         => $avancement->personnel->centre_id,
            'date_debut'        => now()->toDateString(),
            'type_contrat'      => $contrat?->type_contrat ?? $avancement->personnel->type_contrat,
            'categorie_echelon' => $contrat?->categorie_echelon ?? $avancement->personnel->categorie_echelon,
            'salaire_base'      => $avancement->nouveau_salaire,
            'corporation'       => $contrat?->fonction ?? $avancement->personnel->corporation,
            'service'           => $contrat?->service ?? $avancement->personnel->service,
            'centre_nom'        => $avancement->personnel->centre?->nom,
            'type_evenement'    => 'avancement',
            'commentaire'       => "Bonification de l'article 88 validée par DDIS (coeff {$avancement->coefficient_applique}).",
            'created_by'        => $user->id,
        ]);

        \App\Models\Notification::create([
            'centre_id'         => $avancement->personnel?->centre_id,
            'type'              => 'bonification',
            'titre'             => 'Bonification validée — ' . $avancement->personnel->nom_complet,
            'message'           => "La bonification de l'article 88 pour {$avancement->personnel->nom_complet} a été validée par la DDIS. Nouveau salaire : " . number_format($avancement->nouveau_salaire, 0, ',', ' ') . ' FCFA.',
            'personnel_id'      => $avancement->personnel_id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);
    }

    /**
     * Rejeter une bonification (DDIS uniquement).
     */
    public function rejeterBonification(Avancement $avancement, \App\Models\User $user): void
    {
        if ($avancement->type !== 'bonification' || !$avancement->isSoumis()) {
            return;
        }

        $avancement->update([
            'statut'     => 'rejete',
            'valide_par' => $user->id,
            'valide_le'  => now(),
        ]);

        \App\Models\Notification::create([
            'centre_id'         => $avancement->personnel?->centre_id,
            'type'              => 'bonification',
            'titre'             => 'Bonification rejetée — ' . $avancement->personnel->nom_complet,
            'message'           => "La bonification de l'article 88 pour {$avancement->personnel->nom_complet} a été rejetée par la DDIS.",
            'personnel_id'      => $avancement->personnel_id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);
    }

    /**
     * Fait avancer l'agent d'un échelon si l'ancienneté depuis le dernier
     * avancement (ou depuis l'embauche) a atteint 24 mois, et qu'il n'est
     * pas déjà au dernier échelon de la grille.
     */
    /**
     * Propose un avancement d'échelon si l'ancienneté depuis l'embauche dans l'ISD
     * (ou depuis le dernier avancement validé) a atteint 24 mois (2 ans),
     * et que l'agent n'est pas déjà au dernier échelon de sa grille.
     */
    public function traiterEchelon(Personnel $personnel, Contrat $contrat): ?Avancement
    {
        if ((int) $contrat->echelon >= GrilleSalariale::ECHELON_MAX) {
            return null;
        }

        // Vérifier s'il y a déjà une demande d'échelon en attente de validation
        $demandeEnAttente = $personnel->relationLoaded('avancements')
            ? $personnel->avancements->where('type', 'echelon')->where('statut', 'soumis')->first()
            : $personnel->avancements()->where('type', 'echelon')->where('statut', 'soumis')->first();

        if ($demandeEnAttente) {
            return null;
        }

        // Date de référence : dernier avancement validé OU date d'embauche ISD / Centre
        $dateReference = $this->dateDernierAvancementEchelon($personnel)
            ?? $personnel->date_embauche_isd
            ?? $personnel->date_embauche_centre
            ?? $contrat->date_effet_echelon;

        if (!$dateReference) {
            return null; // pas de point de départ fiable
        }

        if ($dateReference->diffInMonths(now()) < self::MOIS_ENTRE_ECHELONS) {
            return null;
        }

        $nouvelEchelon = $contrat->echelon + 1;
        $caseGrille    = GrilleSalariale::caseGrille($contrat->categorie, $nouvelEchelon);
        if (!$caseGrille) return null;

        $ancienSalaire = (int) $contrat->salaire_base;

        $avancement = Avancement::create([
            'personnel_id'          => $personnel->id,
            'contrat_id'            => $contrat->id,
            'type'                  => 'echelon',
            'statut'                => 'soumis', // En attente de validation par la DDIS
            'date_effet'            => now()->toDateString(),
            'ancienne_categorie'    => $contrat->categorie,
            'ancien_echelon'        => $contrat->echelon,
            'nouvelle_categorie'    => $contrat->categorie,
            'nouvel_echelon'        => $nouvelEchelon,
            'ancien_salaire'        => $ancienSalaire,
            'nouveau_salaire'       => $caseGrille->salaire,
            'coefficient_applique'  => $caseGrille->coefficient,
            'numero_reference'      => $this->genererReference('echelon'),
        ]);

        // On ne met PAS à jour le contrat immédiatement. Il faut la validation par la DDIS.

        \App\Models\Notification::create([
            'centre_id'         => $personnel->centre_id,
            'type'              => 'echelon',
            'titre'             => "Avancement d'échelon soumis — " . $personnel->nom_complet,
            'message'           => "{$personnel->nom_complet} est éligible à un avancement d'échelon ({$contrat->categorie}-{$contrat->echelon} → {$contrat->categorie}-{$nouvelEchelon}). En attente de validation par la DDIS.",
            'personnel_id'      => $personnel->id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);

        return $avancement;
    }

    /**
     * Approuver un avancement d'échelon (DDIS uniquement).
     */
    public function approuverEchelon(Avancement $avancement, \App\Models\User $user): void
    {
        if ($avancement->type !== 'echelon' || !$avancement->isSoumis()) {
            return;
        }

        $contrat = $avancement->contrat ?? $avancement->personnel->contrat_actif;
        if ($contrat) {
            $contrat->update([
                'echelon'      => $avancement->nouvel_echelon,
                'salaire_base' => $avancement->nouveau_salaire,
            ]);
        }

        $avancement->update([
            'statut'     => 'valide',
            'valide_par' => $user->id,
            'valide_le'  => now(),
        ]);

        // Historique
        \App\Models\PersonnelHistorique::create([
            'personnel_id'      => $avancement->personnel_id,
            'contrat_id'        => $contrat?->id,
            'centre_id'         => $avancement->personnel->centre_id,
            'date_debut'        => now()->toDateString(),
            'type_contrat'      => $contrat?->type_contrat ?? $avancement->personnel->type_contrat,
            'categorie_echelon' => ($avancement->nouvelle_categorie ?? '') . '-' . ($avancement->nouvel_echelon ?? ''),
            'salaire_base'      => $avancement->nouveau_salaire,
            'corporation'       => $contrat?->fonction ?? $avancement->personnel->corporation,
            'service'           => $contrat?->service ?? $avancement->personnel->service,
            'centre_nom'        => $avancement->personnel->centre?->nom,
            'type_evenement'    => 'avancement',
            'commentaire'       => "Avancement d'échelon validé par la DDIS (Échelon {$avancement->nouvel_echelon}).",
            'created_by'        => $user->id,
        ]);

        // Notification de retour au centre du personnel
        \App\Models\Notification::create([
            'centre_id'         => $avancement->personnel?->centre_id,
            'type'              => 'echelon',
            'titre'             => "Avancement d'échelon validé par la DDIS — " . $avancement->personnel->nom_complet,
            'message'           => "L'avancement d'échelon de {$avancement->personnel->nom_complet} a été officiellement validé par la DDIS. Le document signé est maintenant disponible et l'avancement est effectif.",
            'personnel_id'      => $avancement->personnel_id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);
    }

    /**
     * Rejeter un avancement d'échelon (DDIS uniquement).
     */
    public function rejeterEchelon(Avancement $avancement, \App\Models\User $user): void
    {
        if ($avancement->type !== 'echelon' || !$avancement->isSoumis()) {
            return;
        }

        $avancement->update([
            'statut'     => 'rejete',
            'valide_par' => $user->id,
            'valide_le'  => now(),
        ]);

        \App\Models\Notification::create([
            'centre_id'         => $avancement->personnel?->centre_id,
            'type'              => 'echelon',
            'titre'             => "Avancement d'échelon rejeté par la DDIS — " . $avancement->personnel->nom_complet,
            'message'           => "L'avancement d'échelon pour {$avancement->personnel->nom_complet} a été rejeté par la DDIS.",
            'personnel_id'      => $avancement->personnel_id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);
    }

    /**
     * Génère (si besoin) le rappel mensuel du 1er lundi du mois : liste de
     * tout le personnel dont l'avancement d'échelon ou la bonification
     * tombera entre aujourd'hui et la fin du mois en cours.
     */
    public function genererDigestMensuel(): ?\App\Models\Notification
    {
        $debut = now()->startOfDay();
        $fin   = now()->copy()->endOfMonth();

        // Évite les doublons si la commande tourne plusieurs fois le même mois.
        $existe = \App\Models\Notification::where('type', 'digest_mensuel')
            ->whereYear('date_notification', now()->year)
            ->whereMonth('date_notification', now()->month)
            ->exists();
        if ($existe) return null;

        $aVenir = collect();

        $personnels = Personnel::whereIn('statut', ['actif', 'en_conge'])
            ->with(['contrats' => fn ($q) => $q->orderByDesc('date_debut'), 'avancements'])
            ->get();

        foreach ($personnels as $personnel) {
            $contrat = $personnel->contrat_actif;
            if (!$contrat || !$contrat->categorie || !$contrat->echelon) continue;

            $dejaBonifie = $personnel->avancements->contains('type', 'bonification');

            if (!$dejaBonifie && $personnel->date_naissance) {
                $dateBonif = $personnel->date_naissance->copy()->addYears(self::AGE_BONIFICATION);
                if ($dateBonif->between($debut, $fin)) {
                    $aVenir->push([
                        'personnel_id' => $personnel->id,
                        'nom'          => $personnel->nom_complet,
                        'type'         => 'bonification',
                        'date_prevue'  => $dateBonif->toDateString(),
                    ]);
                    continue; // priorité à la bonification, comme dans traiterTout()
                }
            }

            if ((int) $contrat->echelon < GrilleSalariale::ECHELON_MAX) {
                $dateReference = $this->dateDernierAvancementEchelon($personnel)
                    ?? $contrat->date_effet_echelon
                    ?? $personnel->date_embauche_centre;

                if ($dateReference) {
                    $dateEchelon = $dateReference->copy()->addMonths(self::MOIS_ENTRE_ECHELONS);
                    if ($dateEchelon->between($debut, $fin)) {
                        $aVenir->push([
                            'personnel_id' => $personnel->id,
                            'nom'          => $personnel->nom_complet,
                            'type'         => 'echelon',
                            'date_prevue'  => $dateEchelon->toDateString(),
                        ]);
                    }
                }
            }
        }

        if ($aVenir->isEmpty()) return null;

        $aVenir = $aVenir->sortBy('date_prevue')->values();

        return \App\Models\Notification::create([
            'type'              => 'digest_mensuel',
            'titre'             => $aVenir->count() . ' avancement(s) à venir ce mois-ci',
            'message'           => $aVenir->map(fn ($a) => $a['nom'] . ' — ' . ($a['type'] === 'bonification' ? 'bonification' : 'échelon') . ' le ' . \Carbon\Carbon::parse($a['date_prevue'])->format('d/m'))->implode(' · '),
            'payload'           => $aVenir->toArray(),
            'date_notification' => now()->toDateString(),
        ]);
    }

    private function dateDernierAvancementEchelon(Personnel $personnel): ?Carbon
    {
        $dernier = $personnel->relationLoaded('avancements')
            ? $personnel->avancements->where('type', 'echelon')->where('statut', 'valide')->sortByDesc('date_effet')->first()
            : $personnel->avancements()->where('type', 'echelon')->where('statut', 'valide')->orderByDesc('date_effet')->first();

        return $dernier?->date_effet;
    }

    /**
     * N° de référence auto-incrémenté par type et par année, au format
     * personnalisable (config_rh: reference_echelon / reference_bonification).
     */
    public function genererReference(string $type): string
    {
        $annee = now()->year;
        $sequence = Avancement::where('type', $type)->whereYear('date_effet', $annee)->count() + 1;
        $sequenceFormatee = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        $modele = $type === 'bonification'
            ? ConfigRh::get('reference_bonification', '{seq}.{mois}.{annee}/AC/VECI/DDIS/CRH-DDIS/SA')
            : ConfigRh::get('reference_echelon', '{seq}/{mois}-{anneeCourte}/AC/DDIS/CRH-DDIS/DIR/DRH/ARH');

        return str_replace(
            ['{seq}', '{mois}', '{annee}', '{anneeCourte}'],
            [$sequenceFormatee, now()->format('m'), $annee, now()->format('y')],
            $modele
        );
    }
}