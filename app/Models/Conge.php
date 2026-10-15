<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conge extends Model
{
    use SoftDeletes;

    protected $table = 'conges';

    protected $fillable = [
        'reference',
        'personnel_id', 'type_conge', 'date_debut', 'nb_jours_demandes',
        'date_fin', 'nb_jours_acquis', 'nb_jours_deja_pris', 'nb_jours_restants',
        'observations', 'annee', 'statut',
        'cree_par', 'approuve_par', 'approuve_le', 'motif_rejet', 'signature_path',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'approuve_le'  => 'datetime',
    ];

    public function personnel()   { return $this->belongsTo(Personnel::class); }
    public function creePar()     { return $this->belongsTo(User::class, 'cree_par'); }
    public function approuvePar() { return $this->belongsTo(User::class, 'approuve_par'); }

    public function getTypeCongeLabel(): string
    {
        return match($this->type_conge) {
            'administratif' => 'Congé administratif',
            'technique'     => 'Congé technique',
            'maternite'     => 'Congé de maternité',
            default         => $this->type_conge,
        };
    }

    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis'    => 'En attente',
            'approuve'  => 'Approuvé',
            'rejete'    => 'Rejeté',
            default     => $this->statut,
        };
    }

    public function getStatutColor(): string
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
        return in_array($this->statut, ['brouillon', 'rejete']);
    }

    /**
     * Droit acquis recalculé en tenant compte de conge_jours si l'enregistrement antérieur avait stocké une valeur brute erronée.
     */
    public function getNbJoursAcquisCalculeAttribute(): int
    {
        if ($this->personnel && $this->personnel->conge_jours > 0 && ($this->nb_jours_acquis === null || $this->nb_jours_acquis < $this->personnel->conge_jours)) {
            return (int) $this->personnel->conge_jours;
        }
        return (int) ($this->nb_jours_acquis ?? 24);
    }

    /**
     * Solde restant recalculé si le solde stocké était négatif suite à l'ancien calcul tronqué à 24 jours.
     */
    public function getSoldeRestantCalculeAttribute(): int
    {
        if ($this->personnel && $this->personnel->conge_jours > 0 && $this->nb_jours_restants !== null && $this->nb_jours_restants < 0) {
            $base = (int) $this->personnel->conge_jours;
            $pris = (int) ($this->nb_jours_deja_pris ?? 0);
            $demande = (int) ($this->nb_jours_demandes ?? 0);
            return $base - $pris - $demande;
        }
        return (int) ($this->nb_jours_restants ?? 0);
    }
}
