<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigRh extends Model
{
    protected $table    = 'config_rh';
    protected $fillable = ['cle', 'valeur', 'centre_id', 'user_id'];

    public static function get(string $cle, $default = null, ?User $user = null)
    {
        $user ??= auth()->user();

        if ($user) {
            // 1. Chercher la config spécifique à cet utilisateur
            $rowUser = static::where('cle', $cle)->where('user_id', $user->id)->first();
            if ($rowUser && $rowUser->valeur !== null && $rowUser->valeur !== '') {
                return $rowUser->valeur;
            }

            // 2. Chercher la config spécifique au centre de l'utilisateur
            if ($user->centre_id) {
                $rowCentre = static::where('cle', $cle)->where('centre_id', $user->centre_id)->whereNull('user_id')->first();
                if ($rowCentre && $rowCentre->valeur !== null && $rowCentre->valeur !== '') {
                    return $rowCentre->valeur;
                }
            }
        }

        // 3. Fallback sur la config globale
        $rowGlobal = static::where('cle', $cle)->whereNull('user_id')->whereNull('centre_id')->first();
        return $rowGlobal ? $rowGlobal->valeur : $default;
    }

    public static function set(string $cle, $valeur, ?User $user = null): void
    {
        $user ??= auth()->user();

        if ($user) {
            static::updateOrCreate(
                ['cle' => $cle, 'user_id' => $user->id],
                ['valeur' => $valeur, 'centre_id' => $user->centre_id]
            );
        } else {
            static::updateOrCreate(
                ['cle' => $cle, 'user_id' => null, 'centre_id' => null],
                ['valeur' => $valeur]
            );
        }
    }
}
