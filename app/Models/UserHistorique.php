<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistorique extends Model
{
    protected $table = 'user_historiques';

    protected $fillable = [
        'user_id',
        'ancien_centre_id',
        'nouveau_centre_id',
        'ancien_centre_nom',
        'nouveau_centre_nom',
        'role',
        'date_transfert',
        'motif',
        'transfere_par',
    ];

    protected $casts = [
        'date_transfert' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ancienCentre()
    {
        return $this->belongsTo(Centre::class, 'ancien_centre_id');
    }

    public function nouveauCentre()
    {
        return $this->belongsTo(Centre::class, 'nouveau_centre_id');
    }

    public function transferePar()
    {
        return $this->belongsTo(User::class, 'transfere_par');
    }
}
