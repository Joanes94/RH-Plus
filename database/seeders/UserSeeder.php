<?php

namespace Database\Seeders;

use App\Models\Centre;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rôles Globaux (DDIS)
        User::firstOrCreate(
            ['email' => 'crh@rhplus.bj'],
            [
                'nom' => 'RH',
                'prenoms' => 'Conseiller',
                'sexe' => 'M',
                'telephone' => '+229 01000001',
                'role' => 'crh',
                'password' => 'rhplus2026', // Haché automatiquement par le cast du modèle User
            ]
        );

        User::firstOrCreate(
            ['email' => 'ddis@rhplus.bj'],
            [
                'nom' => 'DDIS',
                'prenoms' => 'Directeur',
                'sexe' => 'M',
                'telephone' => '+229 01000002',
                'role' => 'ddis',
                'password' => 'rhplus2026',
            ]
        );

        User::firstOrCreate(
            ['email' => 'ddrh@rhplus.bj'],
            [
                'nom' => 'DDRH',
                'prenoms' => 'Directeur',
                'sexe' => 'M',
                'telephone' => '+229 01000003',
                'role' => 'ddrh',
                'password' => 'rhplus2026',
            ]
        );

        // 2. Rôles Locaux par Centre (exclut le centre DDIS lui-même)
        $centres = Centre::where('code', '!=', 'DDIS')->get();

        foreach ($centres as $centre) {
            $codeLower = strtolower($centre->code);

            // Créer le Directeur de Centre (tous les centres)
            User::firstOrCreate(
                ['email' => "directeur.{$codeLower}@rhplus.bj"],
                [
                    'nom' => 'Directeur',
                    'prenoms' => $centre->nom,
                    'sexe' => 'M',
                    'telephone' => '+229 99000000',
                    'role' => 'directeur_centre',
                    'centre_id' => $centre->id,
                    'password' => 'rhplus2026',
                ]
            );

            // Créer l'Assistant RH (tous les centres)
            User::firstOrCreate(
                ['email' => "assistant.{$codeLower}@rhplus.bj"],
                [
                    'nom' => 'Assistant',
                    'prenoms' => $centre->nom,
                    'sexe' => 'F',
                    'telephone' => '+229 98000000',
                    'role' => 'assistant_rh',
                    'centre_id' => $centre->id,
                    'password' => 'rhplus2026',
                ]
            );

            // Créer le DRH Centre si le centre dispose d'un DRH dédié (St Luc, St Jean)
            if ($centre->a_drh_dedie) {
                User::firstOrCreate(
                    ['email' => "drh.{$codeLower}@rhplus.bj"],
                    [
                        'nom' => 'DRH',
                        'prenoms' => $centre->nom,
                        'sexe' => 'M',
                        'telephone' => '+229 97000000',
                        'role' => 'drh_centre',
                        'centre_id' => $centre->id,
                        'password' => 'rhplus2026',
                    ]
                );
            }
        }
    }
}
