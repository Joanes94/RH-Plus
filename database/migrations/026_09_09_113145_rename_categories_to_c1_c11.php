<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renomme les catégories E1..E6 / M1..M3 / C1..C2 en une numérotation
 * unique et continue C1..C11, en conservant le même ordre hiérarchique
 * (celui déjà présent dans la colonne "ordre" de grille_salariale) :
 *
 *   E1→C1  E2→C2  E3→C3  E4→C4  E5→C5  E6→C6
 *   M1→C7  M2→C8  M3→C9
 *   C1→C10 C2→C11   (les anciennes catégories C1/C2 deviennent C10/C11)
 *
 * Le renommage se fait en 2 phases (vers des codes temporaires "_TMP_xx"
 * puis vers les codes finaux) pour éviter toute collision : sans ça,
 * renommer l'ancien "C1" en "C10" APRÈS avoir renommé "E1" en "C1"
 * écraserait par erreur les lignes qui viennent d'être renommées.
 *
 * Both grille_salariale.categorie ET contrats.categorie sont mis à jour,
 * pour que les contrats déjà existants restent alignés sur la grille.
 */
return new class extends Migration
{
    private array $mapping = [
        'E1' => 'C1',
        'E2' => 'C2',
        'E3' => 'C3',
        'E4' => 'C4',
        'E5' => 'C5',
        'E6' => 'C6',
        'M1' => 'C7',
        'M2' => 'C8',
        'M3' => 'C9',
        'C1' => 'C10',
        'C2' => 'C11',
    ];

    public function up(): void
    {
        foreach (['grille_salariale', 'contrats'] as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }

            // Phase 1 : vers des codes temporaires uniques (aucun risque de collision)
            foreach ($this->mapping as $ancien => $nouveau) {
                DB::table($table)
                    ->where('categorie', $ancien)
                    ->update(['categorie' => '_TMP_' . $ancien]);
            }

            // Phase 2 : des codes temporaires vers les codes finaux
            foreach ($this->mapping as $ancien => $nouveau) {
                DB::table($table)
                    ->where('categorie', '_TMP_' . $ancien)
                    ->update(['categorie' => $nouveau]);
            }
        }
    }

    public function down(): void
    {
        $inverse = array_flip($this->mapping);

        foreach (['grille_salariale', 'contrats'] as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }

            foreach ($inverse as $nouveau => $ancien) {
                DB::table($table)
                    ->where('categorie', $nouveau)
                    ->update(['categorie' => '_TMP_' . $nouveau]);
            }
            foreach ($inverse as $nouveau => $ancien) {
                DB::table($table)
                    ->where('categorie', '_TMP_' . $nouveau)
                    ->update(['categorie' => $ancien]);
            }
        }
    }
};