<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelConjoint extends Model
{
    protected $table = 'personnel_conjoints';

    protected $fillable = ['personnel_id', 'nom', 'prenom'];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }
}
