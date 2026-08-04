<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vérifie chaque jour à 6h du matin les avancements d'échelon (24 mois) et
// les bonifications (58 ans, article 88). Nécessite que le scheduler Laravel
// soit lancé côté serveur (cron : `* * * * * php artisan schedule:run`).
Schedule::command('avancements:traiter')->dailyAt('06:00');

// Rappel groupé du 1er lundi de chaque mois : avancements/bonifications à
// venir dans le mois. `weeklyOn` cible tous les lundis, le `when` restreint
// au premier (jour du mois <= 7).
Schedule::command('notifications:digest')->weeklyOn(1, '06:30')->when(fn () => now()->day <= 7);

Schedule::command('personnel:auto-desactiver')->dailyAt('00:05');