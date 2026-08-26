<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayAdjustment extends Model
{
    protected $table = 'pay_adjustments';

    protected $fillable = [
        'personnel_id', 'type', 'libelle', 'montant_total', 
        'montant_mensuel', 'mois_restants', 'echeances', 'mois_debut', 'statut', 'created_by'
    ];

    protected $casts = [
        'montant_total'   => 'decimal:2',
        'montant_mensuel' => 'decimal:2',
        'mois_restants'   => 'integer',
        'echeances'       => 'array',
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

    /**
     * Retourne le montant dû pour un mois donné (format "YYYY-MM").
     * Si un écheancier JSON existe, cherche le montant du mois dans le JSON.
     * Sinon, retourne montant_mensuel fixe.
     */
    public function getMontantPourMois(string $moisCode): float
    {
        if (!empty($this->echeances)) {
            foreach ($this->echeances as $ligne) {
                if (($ligne['mois'] ?? '') === $moisCode) {
                    return (float) ($ligne['montant'] ?? 0);
                }
            }
            return 0.0; // mois non trouvé = rien à prélever (plan terminé pour ce mois)
        }
        return (float) $this->montant_mensuel;
    }
}
