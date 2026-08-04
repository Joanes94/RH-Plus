<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Absence extends Model
{
    use SoftDeletes;

    protected $table = 'absences';

    protected $fillable = [
        'reference',
        'personnel_id', 'type_absence', 'deductible',
        'date_debut', 'date_fin', 'nb_jours',
        'motif', 'observations', 'statut',
        'cree_par', 'approuve_par', 'approuve_le', 'motif_rejet', 'signature_path',
    ];

    protected $casts = [
        'date_debut'  => 'date',
        'date_fin'    => 'date',
        'deductible'  => 'boolean',
        'approuve_le' => 'datetime',
    ];

    // ── Types avec leurs métadonnées ──────────────────────────────────────────
    public static function typesDisponibles(): array
    {
        return [
            // Non déductibles
            'deces_conjoint'           => ['label' => 'Décès du conjoint',                     'jours' => 3, 'deductible' => false],
            'deces_parent_enfant'      => ['label' => 'Décès père, mère ou enfant',            'jours' => 3, 'deductible' => false],
            'deces_frere_soeur_beau'   => ['label' => 'Décès frère, sœur, beau-père, belle-mère', 'jours' => 2, 'deductible' => false],
            'mariage_travailleur'      => ['label' => 'Mariage du travailleur',                 'jours' => 3, 'deductible' => false],
            'mariage_enfant_frere_soeur'=> ['label' => 'Mariage enfant / frère / sœur',        'jours' => 1, 'deductible' => false],
            'naissance'                => ['label' => 'Naissance au foyer',                     'jours' => 3, 'deductible' => false],
            // Déductibles
            'deductible'               => ['label' => 'Autre absence (déductible des congés)',  'jours' => null, 'deductible' => true],
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typesDisponibles()[$this->type_absence]['label'] ?? $this->type_absence;
    }

    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis'    => 'En attente',
            'approuve'  => 'Approuvée',
            'rejete'    => 'Rejetée',
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
     * Calcule le nombre de jours ouvrables entre deux dates
     * (du lundi au vendredi, excluant samedi et dimanche)
     */
    public function calculerJoursOuvrables($dateDebut, $dateFin): int
    {
        $jours = 0;
        $current = clone $dateDebut;
        while ($current <= $dateFin) {
            // 0 = dimanche, 6 = samedi
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $jours++;
            }
            $current->addDay();
        }
        return $jours;
    }

    /**
     * Calcule la date de reprise (premier jour ouvrable après les jours d'absence)
     */
    public function calculerDateReprise($dateDebut, $nbJoursOuvrables): Carbon
    {
        $current = clone $dateDebut;
        $joursComptes = 0;
        
        // On ajoute d'abord les jours d'absence (ouvrables)
        while ($joursComptes < $nbJoursOuvrables) {
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $joursComptes++;
            }
            $current->addDay();
        }
        
        // On avance jusqu'au prochain jour ouvrable (lundi)
        while ($current->dayOfWeek === 0 || $current->dayOfWeek === 6) {
            $current->addDay();
        }
        
        return $current;
    }

    /**
     * Accesseur : nombre de jours ouvrables
     */
    public function getNbJoursOuvrablesAttribute(): int
    {
        if (!$this->date_debut || !$this->date_fin) {
            return 0;
        }
        return $this->calculerJoursOuvrables($this->date_debut, $this->date_fin);
    }

    /**
     * Accesseur : date de reprise (premier jour ouvrable après l'absence)
     */
    public function getDateRepriseAttribute(): ?Carbon
    {
        if (!$this->date_debut || !$this->nb_jours) {
            return null;
        }
        return $this->calculerDateReprise($this->date_debut, $this->nb_jours);
    }

    public function personnel()   { return $this->belongsTo(Personnel::class); }
    public function creePar()     { return $this->belongsTo(User::class, 'cree_par'); }
    public function approuvePar() { return $this->belongsTo(User::class, 'approuve_par'); }
}