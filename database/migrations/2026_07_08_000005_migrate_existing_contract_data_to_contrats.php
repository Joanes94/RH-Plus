<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Convertit les colonnes de contrat historiques présentes sur `personnels`
     * (type_contrat, categorie_echelon, dates, débauchage...) en une première
     * ligne dans `contrats`, pour chaque personnel qui a au moins un type de
     * contrat renseigné. Les colonnes d'origine sur `personnels` ne sont pas
     * supprimées (conservées pour historique / compatibilité).
     */
    public function up(): void
    {
        $personnels = DB::table('personnels')->whereNull('deleted_at')->get();

        foreach ($personnels as $p) {
            // On ne crée un contrat que s'il y a un minimum d'info exploitable
            if (empty($p->type_contrat) && empty($p->date_embauche_centre)) {
                continue;
            }

            $dateDebut = $p->date_embauche_centre ?? $p->date_embauche_isd ?? now()->toDateString();

            $statut = 'actif';
            if (!empty($p->date_debauchage) || $p->statut === 'inactif') {
                $statut = 'rompu';
            } elseif (!empty($p->date_fin_contrat) && Carbon::parse($p->date_fin_contrat)->isPast()) {
                $statut = 'termine';
            }

            DB::table('contrats')->insert([
                'personnel_id'       => $p->id,
                'type_contrat'       => $p->type_contrat ?: 'Autre',
                'fonction'           => $p->corporation,
                'service'            => $p->service,
                'categorie_echelon'  => $p->categorie_echelon,
                'centre'             => $p->affectation,
                'lieu_conge'         => null,
                'salaire_base'       => null,
                'date_debut'         => $dateDebut,
                'duree_mois'         => null,
                'date_fin'           => $p->date_fin_contrat,
                'date_debauchage'    => $p->date_debauchage,
                'motif_debauchage'   => $p->motif_debauchage,
                'lieu_signature'     => null,
                'date_signature'     => null,
                'numero_visa'        => null,
                'date_visa'          => null,
                'statut'             => $statut,
                'created_by'         => $p->created_by,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Rien à faire : on ne supprime pas des données saisies après coup.
    }
};
