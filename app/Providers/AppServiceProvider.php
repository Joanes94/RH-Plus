<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Locale française pour les dates générées par Carbon (isoFormat),
        // utilisée dans tous les documents officiels (congés, absences, attestations...).
        Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        // Cloche de notifications (avancements/bonifications), disponible sur
        // toutes les pages via le layout principal.
        \Illuminate\Support\Facades\View::composer('partials.notifications_bell', \App\View\Composers\NotificationComposer::class);

        // Liste des centres ordonnée pour la barre latérale (CRH / Global)
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $sidebarCentres = \App\Models\Centre::withCount([
                    'personnels as effectif_actif' => fn($q) => $q->whereNotIn('statut', ['ancien', 'retraite']),
                ])->orderByRaw('ordre IS NULL, ordre ASC, nom ASC')->get();
                $view->with('sidebarCentres', $sidebarCentres);
            }
        });
    }
}