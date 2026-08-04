<?php
// app/Models/StagiaireDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StagiaireDocument extends Model
{
    use SoftDeletes;

    protected $table = 'stagiaire_documents';

    protected $fillable = [
        'stagiaire_id',
        'type_document', // autorisation, attestation, evaluation
        'reference',
        'type_stage', // professionnel, academique, decouverte
        'services', // JSON des services
        'date_debut',
        'date_fin',
        'motif',
        'observations',
        'statut', // brouillon, soumis, approuve, rejete
        'cree_par',
        'approuve_par',
        'approuve_le',
        'motif_rejet',
        'signature_path',
    ];

    protected $casts = [
        'services' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'approuve_le' => 'datetime',
    ];

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function approuvePar()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis' => 'En attente du DRH',
            'approuve' => 'Approuvé',
            'rejete' => 'Rejeté',
            default => $this->statut,
        };
    }

    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'brouillon' => 'gray',
            'soumis' => 'warn',
            'approuve' => 'approved',
            'rejete' => 'danger',
            default => 'gray',
        };
    }

    public function isEditable()
    {
        return in_array($this->statut, ['brouillon', 'rejete']);
    }
}