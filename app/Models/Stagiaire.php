<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Stagiaire extends Model
{
    use SoftDeletes;

    protected $table = 'stagiaires';

    protected $fillable = [
        'nom', 'prenoms', 'email', 'photo_path',
        'date_naissance', 'lieu_naissance',
        'sexe', 'telephone', 'situation_matrimoniale',
        'titre', 'niveau_etude', 'diplome', 'ecole_formation',
        'autorisation_clientele_privee',
        'service',
        'date_debut_stage', 'date_fin_stage', 'type_stage',
        'observations',
        'contact_urgence_nom', 'contact_urgence_telephone',
        'statut', 'created_by',
    ];

    protected $casts = [
        'date_naissance'              => 'date',
        'date_debut_stage'            => 'date',
        'date_fin_stage'              => 'date',
        'autorisation_clientele_privee' => 'boolean',
    ];

    // ── Niveaux d'étude ───────────────────────────────────────────────────────
    public static function niveauxEtude(): array
    {
        return [
            'CEP', 'BEPC', 'BAC',
            'Licence 1', 'Licence 2', 'Licence 3',
            'Master 1', 'Master 2',
            'Doctorat',
        ];
    }

    public static function situationsMatrimoniales(): array
    {
        return ['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'];
    }

    public static function typesStage(): array
    {
        return ['Académique de découverte','Académique', 'Professionnel', 'Autre'];
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . strtoupper($this->nom);
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenoms, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_cours'  => 'En cours',
            'termine'   => 'Terminé',
            'abandonne' => 'Abandonné',
            default     => $this->statut,
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match($this->statut) {
            'en_cours'  => 'approved',
            'termine'   => 'gray',
            'abandonne' => 'danger',
            default     => 'gray',
        };
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::url($this->photo_path);
        }
        return null;
    }

    public function getDureeStageAttribute(): ?string
    {
        if (!$this->date_debut_stage || !$this->date_fin_stage) return null;
        $jours = $this->date_debut_stage->diffInDays($this->date_fin_stage) + 1;
        if ($jours >= 30) {
            $mois = round($jours / 30);
            return $mois . ' mois';
        }
        return $jours . ' jours';
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
