<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avancement extends Model
{
    protected $table = 'avancements';

    protected $fillable = [
        'personnel_id', 'contrat_id', 'type', 'statut', 'date_effet',
        'ancienne_categorie', 'ancien_echelon', 'nouvelle_categorie', 'nouvel_echelon',
        'ancien_salaire', 'nouveau_salaire', 'coefficient_applique',
        'numero_reference', 'created_by', 'valide_par', 'valide_le',
    ];

    protected $casts = [
        'date_effet'   => 'date',
        'ancien_salaire'   => 'integer',
        'nouveau_salaire'  => 'integer',
        'coefficient_applique' => 'decimal:3',
        'valide_le' => 'datetime',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'bonification' ? 'Bonification (58 ans)' : 'Avancement d\'échelon';
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'soumis'     => 'En attente de validation',
            'valide_crh' => 'Pré-validé par CRH (Attente DDIS)',
            'valide'     => 'Validé',
            'rejete'     => 'Rejeté',
            default      => $this->statut,
        };
    }

    public function isSoumis(): bool
    {
        return in_array($this->statut, ['soumis', 'valide_crh']);
    }

    public function isApprouve(): bool
    {
        return $this->statut === 'valide';
    }

}
