<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nom',
        'prenoms',
        'sexe',
        'email',
        'telephone',
        'role',
        'password',
        'centre_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . strtoupper($this->nom);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'crh'               => 'Conseiller RH',
            'ddis'              => 'Directeur DDIS',
            'ddrh'              => 'Directeur DRH',
            'drh_centre'        => 'DRH Centre',
            'assistant_rh'      => 'Assistant RH',
            'directeur_centre'  => 'Directeur',
            // Rétrocompatibilité
            'drh'               => 'DRH',
            default             => $this->role,
        };
    }

    // ── Vérification de rôles ─────────────────────────────────────────────────

    public function isCRH(): bool
    {
        return $this->role === 'crh';
    }

    public function isDDIS(): bool
    {
        return $this->role === 'ddis';
    }

    public function isDDRH(): bool
    {
        return $this->role === 'ddrh';
    }

    public function isDrhCentre(): bool
    {
        return $this->role === 'drh_centre';
    }

    public function isAssistantRH(): bool
    {
        return $this->role === 'assistant_rh';
    }

    public function isDirecteurCentre(): bool
    {
        return $this->role === 'directeur_centre';
    }

    /** Rétrocompatibilité : ancien rôle 'drh' ou nouveau 'drh_centre'. */
    public function isDRH(): bool
    {
        return in_array($this->role, ['drh', 'drh_centre']);
    }

    /** L'utilisateur a-t-il un rôle global (pas attaché à un centre) ? */
    public function isGlobal(): bool
    {
        return in_array($this->role, ['crh', 'ddis', 'ddrh']);
    }

    /** L'utilisateur est-il en mode lecture seule ? */
    public function isReadOnly(): bool
    {
        return in_array($this->role, ['ddrh', 'directeur_centre']);
    }

    /** L'utilisateur peut-il approuver des demandes (DRH, Directeur faisant office, ou CRH) ? */
    public function canApprove(): bool
    {
        return in_array($this->role, ['crh', 'drh_centre', 'drh', 'directeur_centre']);
    }

    /** L'utilisateur peut-il gérer un centre donné ? */
    public function canManageCentre(?int $centreId): bool
    {
        // Les rôles globaux peuvent gérer tous les centres
        if ($this->isGlobal()) {
            return true;
        }

        // Les rôles locaux ne peuvent gérer que leur propre centre
        return $this->centre_id && $this->centre_id === $centreId;
    }

    /** L'utilisateur peut-il voir les données d'un centre ? */
    public function canViewCentre(?int $centreId): bool
    {
        return $this->canManageCentre($centreId);
    }

    /** Retourne les IDs de centres accessibles (null = tous). */
    public function centresAccessibles()
    {
        if ($this->isGlobal()) {
            return null; // accès à tous les centres
        }
        return $this->centre_id ? [$this->centre_id] : [];
    }
}
