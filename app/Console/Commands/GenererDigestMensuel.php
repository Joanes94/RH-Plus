<?php

namespace App\Console\Commands;

use App\Services\AvancementService;
use Illuminate\Console\Command;

class GenererDigestMensuel extends Command
{
    protected $signature = 'notifications:digest';

    protected $description = "Génère (si ce n'est pas déjà fait ce mois-ci) le rappel groupé des avancements/bonifications à venir dans le mois";

    public function handle(AvancementService $service): int
    {
        $notification = $service->genererDigestMensuel();

        if (!$notification) {
            $this->info("Rien à signaler : soit le rappel de ce mois existe déjà, soit personne n'a d'avancement prévu d'ici la fin du mois.");
            return self::SUCCESS;
        }

        $this->info("Rappel mensuel créé : {$notification->titre}");
        return self::SUCCESS;
    }
}