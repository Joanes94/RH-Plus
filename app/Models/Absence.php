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
        'personnel_id', 'type_absence', 'mode_selection', 'deductible',
        'date_debut', 'date_fin', 'nb_jours', 'jours_specifiques',
        'motif', 'observations', 'statut',
        'cree_par', 'approuve_par', 'approuve_le', 'motif_rejet', 'signature_path',
    ];

    protected $casts = [
        'date_debut'        => 'date',
        'date_fin'          => 'date',
        'deductible'        => 'boolean',
        'approuve_le'       => 'datetime',
        'jours_specifiques' => 'array',
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

    public function isModeSpecifique(): bool
    {
        return $this->mode_selection === 'specifique' && !empty($this->jours_specifiques);
    }

    /**
     * Calcule le nombre de jours calendaires réels (incluant week-ends et jours fériés)
     */
    public static function compterJoursCalendaires($dateDebut, $dateFin): int
    {
        $d1 = Carbon::parse($dateDebut)->startOfDay();
        $d2 = Carbon::parse($dateFin)->startOfDay();
        if ($d2->lt($d1)) {
            return 0;
        }
        return (int) $d1->diffInDays($d2) + 1;
    }

    /**
     * Formate la liste des dates pour affichage et documents officiels
     */
    public function getJoursFormatteAttribute(): string
    {
        if ($this->isModeSpecifique()) {
            $dates = collect($this->jours_specifiques)->sort()->map(function ($d) {
                return Carbon::parse($d)->isoFormat('dddd D MMMM YYYY');
            })->values();

            if ($dates->count() === 1) {
                return 'le ' . $dates->first();
            }
            $last = $dates->pop();
            return 'les ' . $dates->implode(', ') . ' et ' . $last;
        }

        if ($this->date_debut && $this->date_fin) {
            if ($this->date_debut->isSameDay($this->date_fin)) {
                return 'le ' . $this->date_debut->isoFormat('dddd D MMMM YYYY');
            }
            return 'du ' . $this->date_debut->isoFormat('D MMMM YYYY') . ' au ' . $this->date_fin->isoFormat('D MMMM YYYY');
        }

        return '';
    }

    /**
     * Accesseur : date de reprise du service
     */
    public function getDateRepriseAttribute(): ?Carbon
    {
        if (!$this->date_fin) {
            return null;
        }

        return $this->date_fin->copy()->addDay();
    }

    public function personnel()   { return $this->belongsTo(Personnel::class); }
    public function creePar()     { return $this->belongsTo(User::class, 'cree_par'); }
    public function approuvePar() { return $this->belongsTo(User::class, 'approuve_par'); }
}