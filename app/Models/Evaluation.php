<?php
// app/Models/Evaluation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $table = 'evaluations';

    protected $fillable = [
        'stagiaire_id',
        'qualites',
        'defauts',
        'maitrise_pratique',
        'appreciation_personnelle',
        'statut',
        'reference',
        'cree_par',
        'approuve_par',
        'approuve_le',
        'motif_rejet',
        'signature_path',
    ];

    protected $casts = [
        'approuve_le' => 'datetime',
    ];

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis'    => 'En attente',
            'approuve'  => 'Approuvée',
            'rejete'    => 'Rejetée',
            default     => $this->statut,
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'gray',
            'soumis'    => 'warn',
            'approuve'  => 'approved',
            'rejete'    => 'danger',
            default     => 'gray',
        };
    }

    public function isEditable(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function isSubmittable(): bool
    {
        return $this->statut === 'brouillon';
    }

    // ── Relations ─────────────────────────────────────────────────────────────

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
}