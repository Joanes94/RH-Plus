<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayAdjustment extends Model
{
    protected $table = 'pay_adjustments';

    protected $fillable = [
        'personnel_id', 'type', 'libelle', 'montant_total', 
        'montant_mensuel', 'mois_restants', 'statut', 'created_by'
    ];

    protected $casts = [
        'montant_total'   => 'decimal:2',
        'montant_mensuel' => 'decimal:2',
        'mois_restants'   => 'integer',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatutLabelAttribute(): string
    {
        return $this->statut === 'actif' ? 'Actif' : 'Terminé';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'avance_salaire' => 'Avance sur salaire',
            'frais_medicaux' => 'Frais médicaux (Soins)',
            'moins_percu'    => 'Moins-perçu (Remboursement)',
            default          => $this->type,
        };
    }
}
