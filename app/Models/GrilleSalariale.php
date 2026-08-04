<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrilleSalariale extends Model
{
    protected $table = 'grille_salariale';

    protected $fillable = ['categorie', 'libelle', 'ordre', 'echelon', 'anciennete_mois', 'coefficient', 'salaire'];

    protected $casts = [
        'coefficient' => 'decimal:3',
        'salaire'     => 'integer',
    ];

    /** Liste des catégories distinctes, triées par ordre (pour les <select>). */
    public static function categories()
    {
        return static::query()
            ->select('categorie', 'libelle', 'ordre')
            ->distinct()
            ->orderBy('ordre')
            ->get();
    }

    /** Case précise de la grille pour une catégorie + échelon donnés. */
    public static function caseGrille(?string $categorie, ?int $echelon): ?self
    {
        if (!$categorie || !$echelon) return null;

        return static::where('categorie', $categorie)->where('echelon', $echelon)->first();
    }

    public const ECHELON_MAX = 11;
}
