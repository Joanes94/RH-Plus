<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelEnfant extends Model
{
    protected $table = 'personnel_enfants';

    protected $fillable = ['personnel_id', 'nom', 'prenom', 'sexe', 'date_naissance'];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }
}
