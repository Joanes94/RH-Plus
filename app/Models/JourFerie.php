<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JourFerie extends Model
{
    protected $table    = 'jours_feries';
    protected $fillable = ['date', 'libelle', 'type', 'annee'];
    protected $casts    = ['date' => 'date'];

    /**
     * Retourne un tableau de dates (Carbon) fériées pour une année donnée.
     */
    public static function pourAnnee(int $annee): array
    {
        return static::where('annee', $annee)
            ->get()
            ->map(fn($j) => $j->date->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Jours fériés fixes au Bénin — insérés automatiquement si absents.
     */
    public static function joursFixesBenin(int $annee): array
    {
        return [
            ['date' => "$annee-01-01", 'libelle' => 'Jour de l\'An',                 'type' => 'fixe'],
            ['date' => "$annee-01-10", 'libelle' => 'Fête du Vodoun',                'type' => 'fixe'],
            ['date' => "$annee-04-01", 'libelle' => 'Commémoration marxiste-léniniste','type' => 'fixe'],
            ['date' => "$annee-05-01", 'libelle' => 'Fête du Travail',               'type' => 'fixe'],
            ['date' => "$annee-08-01", 'libelle' => 'Fête de l\'Indépendance',       'type' => 'fixe'],
            ['date' => "$annee-08-15", 'libelle' => 'Assomption',                    'type' => 'fixe'],
            ['date' => "$annee-10-26", 'libelle' => 'Jour des Forces Armées',        'type' => 'fixe'],
            ['date' => "$annee-11-01", 'libelle' => 'Toussaint',                     'type' => 'fixe'],
            ['date' => "$annee-11-30", 'libelle' => 'Fête Nationale',                'type' => 'fixe'],
            ['date' => "$annee-12-25", 'libelle' => 'Noël',                          'type' => 'fixe'],
            ['date' => "$annee-12-31", 'libelle' => 'Saint-Sylvestre',               'type' => 'fixe'],
        ];
    }
}
