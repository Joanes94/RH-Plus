<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demande extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'personnel_id',
        'type_demande',
        'statut',
        'motif',
        'date_debut',
        'date_fin',
        'date_faits',
        'faits_reproches',
        'date_accouchement_prevu',
        'etablissement_stage',
        'niveau_etude',
        'specialite',
        'observations',
        'approuve_par',
        'approuve_le',
        'rejete_par',
        'rejete_le',
        'motif_rejet',
        'signature_path',
    ];

    protected $casts = [
        'date_debut'              => 'date',
        'date_fin'                => 'date',
        'date_faits'              => 'date',
        'date_accouchement_prevu' => 'date',
        'approuve_le'             => 'datetime',
        'rejete_le'               => 'datetime',
    ];

    // ── Catalogue des types de demandes ──────────────────────────────────────
    // Congés Admin, Maternité et Absence sont gérés dans leurs modules dédiés.
    // Demande de stage stagiaire est dans le module Stagiaires.
    public static function catalogue(): array
    {
        return [
            [
                'label' => 'Personnel',
                'types' => [
                    'conge_maladie'        => ['label' => 'Congé maladie',                 'genre' => false],
                    'demande_explication'  => ['label' => "Demande d'explication",          'genre' => false],
                    'attestation_travail'  => ['label' => 'Attestation de travail',         'genre' => true],
                    'attestation_presence' => ['label' => 'Attestation de présence',        'genre' => true],
                    'attestation_validite' => ['label' => 'Attestation de validité',        'genre' => true],
                    'recu_solde_tout_compte' => ['label' => 'Reçu pour solde de tout compte', 'genre' => false],
                ],
            ],
            [
                'label' => 'Prestataires',
                'types' => [
                    'attestation_prestation' => ['label' => 'Attestation de prestation',   'genre' => true],
                ],
            ],
        ];
    }

    // Retourner tous les types à plat
    public static function tousLesTypes(): array
    {
        $result = [];
        foreach (self::catalogue() as $groupe) {
            foreach ($groupe['types'] as $slug => $meta) {
                $result[$slug] = $meta;
            }
        }
        return $result;
    }

    // Champs requis selon le type
    public static function typeChamps(string $type): array
    {
        return match($type) {
            'conge_maladie'        => ['dates', 'motif'],
            'demande_explication'  => ['faits', 'date_faits'],
            'recu_solde_tout_compte' => ['dates', 'motif'],
            'attestation_travail',
            'attestation_presence',
            'attestation_validite',
            'attestation_prestation' => [],
            default                => ['dates'],
        };
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::tousLesTypes()[$this->type_demande]['label'] ?? $this->type_demande;
    }

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
        return in_array($this->statut, ['brouillon', 'soumis']);
    }

    public function getDateRepriseAttribute(): ?\Carbon\Carbon
    {
        if (!$this->date_fin) {
            return null;
        }

        $feries = array_merge(
            JourFerie::pourAnnee($this->date_fin->year),
            JourFerie::pourAnnee($this->date_fin->year + 1)
        );

        $reprise = $this->date_fin->copy()->addDay();
        while ($reprise->isWeekend() || in_array($reprise->format('Y-m-d'), $feries)) {
            $reprise->addDay();
        }

        return $reprise;
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
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
