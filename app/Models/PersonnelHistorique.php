<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelHistorique extends Model
{
    protected $table = 'personnel_historiques';

    protected $fillable = [
        'personnel_id', 'contrat_id', 'centre_id',
        'date_debut', 'date_fin',
        'type_contrat', 'categorie_echelon', 'salaire_base',
        'corporation', 'service', 'centre_nom',
        'type_evenement', 'commentaire', 'created_by',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'salaire_base' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type_evenement) {
            'embauche'       => 'Embauche',
            'renouvellement' => 'Renouvellement de contrat',
            'transfert'      => 'Transfert',
            'avancement'     => 'Avancement',
            'passage_cdi'    => 'Passage en CDI',
            'fin_contrat'    => 'Fin de contrat',
            'desactivation'  => 'Désactivation',
            'reactivation'   => 'Réactivation',
            default          => $this->type_evenement,
        };
    }

    public function getPeriodeAttribute(): string
    {
        $debut = $this->date_debut->format('d/m/Y');
        $fin = $this->date_fin ? $this->date_fin->format('d/m/Y') : 'en cours';
        return "du {$debut} au {$fin}";
    }
}
