<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centre extends Model
{
    protected $fillable = [
        'nom', 'code', 'email', 'adresse', 'telephone', 'a_drh_dedie', 'actif',
        'logo_path', 'entete_image_path', 'entete_texte', 'pied_page_texte', 'reference_suffix',
    ];

    protected $casts = [
        'a_drh_dedie' => 'boolean',
        'actif'       => 'boolean',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->logo_path)) {
            return \Illuminate\Support\Facades\Storage::url($this->logo_path);
        }
        return null;
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function personnels()
    {
        return $this->hasMany(Personnel::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    public function historiques()
    {
        return $this->hasMany(PersonnelHistorique::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /** Nombre d'agents actifs dans ce centre. */
    public function getEffectifActifAttribute(): int
    {
        return $this->personnels()->enPoste()->count();
    }

    /** Le centre dispose-t-il d'un DRH dédié ? Sinon le Directeur fait office de DRH. */
    public function getDrhOuDirecteurAttribute(): ?User
    {
        if ($this->a_drh_dedie) {
            return $this->users()->where('role', 'drh_centre')->first();
        }
        return $this->users()->where('role', 'directeur_centre')->first();
    }

    /** Liste des centres prédéfinis. */
    public static function centresPredefinis(): array
    {
        return [
            ['code' => 'ST_LUC',        'nom' => 'CSVH St Luc',                         'email' => 'hopitalsaintluc@gmail.com',     'a_drh_dedie' => true],
            ['code' => 'ST_JEAN',       'nom' => 'CSVH St Jean',                        'email' => 'cm_stjean@yahoo.fr',            'a_drh_dedie' => true],
            ['code' => 'GLO',           'nom' => 'CSVH Padre Pio de Glo',               'email' => 'csvhpadrepioglo@gmail.com',     'a_drh_dedie' => false],
            ['code' => 'MARIA_GLETA',   'nom' => 'CSVH St Jean de Maria Gleta',         'email' => 'cmmariagleta@gmail.com',        'a_drh_dedie' => false],
            ['code' => 'SO_TCHANHOUE',  'nom' => 'CSVH St Joseph de So-tchanhoue',      'email' => 'csvhsotchanhoue@gmail.com',     'a_drh_dedie' => false],
            ['code' => 'SEYON',         'nom' => 'Centre Sêyon',                        'email' => 'centreseyon@yahoo.com',         'a_drh_dedie' => false],
            ['code' => 'CAFSC',         'nom' => 'CAFSC',                               'email' => 'phciediocesaine@gmail.com',     'a_drh_dedie' => false],
            ['code' => 'DDIS',          'nom' => 'DDIS',                                'email' => 'contact@ddiscotonou.org',       'a_drh_dedie' => false],
        ];
    }
}
