<?php

namespace Database\Seeders;

use App\Models\GrilleSalariale;
use Illuminate\Database\Seeder;

class GrilleSalarialeSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/grille_data.json');
        if (!file_exists($jsonPath)) {
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data) {
            return;
        }

        foreach ($data as $row) {
            GrilleSalariale::updateOrCreate(
                [
                    'categorie' => $row['categorie'],
                    'echelon'   => (int) $row['echelon'],
                ],
                [
                    'libelle'         => $row['libelle'],
                    'ordre'           => (int) $row['ordre'],
                    'anciennete_mois' => (int) $row['anciennete_mois'],
                    'coefficient'     => (float) $row['coefficient'],
                    'salaire'         => (int) $row['salaire'],
                ]
            );
        }
    }
}
