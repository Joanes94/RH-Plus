<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigRh extends Model
{
    protected $table    = 'config_rh';
    protected $fillable = ['cle', 'valeur'];

    public static function get(string $cle, $default = null)
    {
        $row = static::where('cle', $cle)->first();
        return $row ? $row->valeur : $default;
    }

    public static function set(string $cle, $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }
}
