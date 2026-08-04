<?php

namespace App\Console\Commands;

use App\Services\AvancementService;
use Illuminate\Console\Command;

class TraiterAvancements extends Command
{
    protected $signature = 'avancements:traiter';

    protected $description = "Détecte et applique automatiquement les avancements d'échelon (tous les 2 ans) et les bonifications (58 ans, article 88)";

    public function handle(AvancementService $service): int
    {
        $this->info('Traitement des avancements en cours…');

        $resultats = $service->traiterTout();

        $this->info("Terminé : {$resultats['echelons']} avancement(s) d'échelon, {$resultats['bonifications']} bonification(s) appliquée(s).");

        foreach ($resultats['avancements'] as $a) {
            $personnel = $a->personnel ?? \App\Models\Personnel::find($a->personnel_id);
            $this->line(" - {$personnel?->nom_complet} : {$a->type_label} → {$a->nouveau_salaire} FCFA (réf. {$a->numero_reference})");
        }

        return self::SUCCESS;
    }
}
