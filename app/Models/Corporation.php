<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Corporation extends Model
{
    protected $table = 'corporations';

    protected $fillable = [
        'nom',
        'centre_id',
        'created_by',
    ];

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope : Corporations accessibles pour un centre donné.
     * Inclut les corporations globales (centre_id NULL) + celles spécifiques au centre demandé.
     * Si aucun centre n'est précisé et que l'utilisateur est global, retourne tout.
     */
    public function scopePourCentre($query, ?int $centreId = null)
    {
        if ($centreId) {
            return $query->where(function ($q) use ($centreId) {
                $q->whereNull('centre_id')
                  ->orWhere('centre_id', $centreId);
            });
        }
        return $query;
    }

    public function isGlobal(): bool
    {
        return is_null($this->centre_id);
    }
}
