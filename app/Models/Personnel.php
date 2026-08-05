<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Personnel extends Model
{
    use SoftDeletes;

    protected $table = 'personnels';

    protected $fillable = [
        'nom', 'prenoms', 'email', 'photo_path',
        'date_naissance', 'lieu_naissance', 'nationalite', 'residence',
        'sexe', 'telephone', 'situation_matrimoniale', 'diplome',
        'autorisation_clientele_privee',
        'corporation', 'service', 'type_contrat', 'categorie_echelon',
        'date_embauche_centre', 'date_embauche_isd',
        'date_debauchage', 'motif_debauchage',
        'numero_cnss', 'date_depart_retraite', 'date_fin_contrat',
        'conge_annee', 'conge_jours',
        'contact_urgence_nom', 'contact_urgence_telephone',
        'affectation', 'date_affectation',
        'statut', 'motif_depart', 'date_depart', 'created_by', 'centre_id',
    ];

    protected $casts = [
        'date_naissance'         => 'date',
        'date_embauche_centre'   => 'date',
        'date_embauche_isd'      => 'date',
        'date_debauchage'        => 'date',
        'date_depart_retraite'   => 'date',
        'date_fin_contrat'       => 'date',
        'date_affectation'       => 'date',
        'date_depart'            => 'date',
        'autorisation_clientele_privee' => 'boolean',
    ];

    // ── Scope : exclure uniquement les Stagiaires de la liste principale ──────
    // Les Prestataires restent dans Personnel
    public function scopePersonnelPrincipal($query)
    {
        return $query->where(function($q) {
            $q->where('type_contrat', '!=', 'Stagiaire')
              ->orWhereNull('type_contrat');
        });
    }

    /** Anciens travailleurs : affectés ailleurs, contrat terminé, débauchés ou retraités. */
    public function scopeAnciensTravailleurs($query)
    {
        return $query->whereIn('statut', ['ancien', 'retraite']);
    }

    /** Personnel toujours en poste (exclut les anciens travailleurs). */
    public function scopeEnPoste($query)
    {
        return $query->whereNotIn('statut', ['ancien', 'retraite']);
    }

    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    public function scopeForUser($query, $user)
    {
        if ($user->isGlobal()) {
            return $query;
        }
        return $query->where('centre_id', $user->centre_id);
    }

    // ── Listes de référence ───────────────────────────────────────────────────

    public static function corporations(): array
    {
        return [
            'AGENT ADMINISTRATIF',
            'AGENT DE PHARMACIE',
            "AGENT D'ENTRETIEN",
            'AIDE-SOIGNANT',
            'AIDE SOIGNANT PROMU',
            'ANESTHESISTE',
            'ASSISTANT DENTAIRE',
            'ASSISTANT LABORATOIRE',
            'ASSISTANT RH',
            'BIOLOGISTE MEDICALE',
            'CAISSIER',
            'CARDIOLOGUE',
            'CHAUFFEUR',
            'CHEF SERVICE FINANCIER ET COMPTABLE',
            'CHIRURGIEN MAXILO FACIALE',
            'CHIRURGIEN',
            'COMPTABLE',
            'DENTISTE',
            'DERMATOLOGUE',
            'DIRECTEUR',
            'DIRECTEUR DES RESSOURCES HUMAINES',
            'ENDOCRINOLOGUE',
            'GASTROLOGUE',
            'INFIRMIER',
            'KINESITHERAPEUTE',
            'MAGASINIER',
            'MEDECIN ANESTHESISTE REANIMATEUR',
            'MEDECIN',
            'MEDECIN CHEF',
            'NEPHROLOGUE',
            'NEURO CHIRURGIEN',
            'NEUROLOGUE',
            'SURVEILLANT',
            'OPHTAMOLOGUE',
            'ORL',
            'PSYCHIATRE',
            'RADIOLOGUE',
            'SECRETAIRE ADMINISTRATIVE',
            'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE MEDICAL',
            'STATISTICIEN',
            'TECHNICIEN DE LABORATOIRE',
            'TECHNICIEN EN IMAGERIE MEDICALE',
            'TECHNICIEN SUPERIEUR EN ODONTO',
            'TRAUMATOLOGUE',
            'UROLOGUE',
        ];
    }

    public static function services(): array
    {
        return [
            'MEDECINE',
            'COMPTABILITE',
            'CAISSE',
            'PHARMACIE',
            'FACTURATION',
            'MAGASIN',
            'CHIRURGIE',
            'KINESITHERAPIE',
            'STOMATOLOGIE',
            'MATERNITE',
            'PEDIATRIE',
            'SERVICE PLURIDISCIPLINAIRE',
            'URGENCES',
            'RADIOLOGIE',
            'LABORATOIRE',
            'DIRECTION DES RESSOURCES HUMAINES',
            'DIRECTION',
            'SECRETARIAT',
            'GASTRO-ENTEROLOGIE',
            'SERVICE DES SOINS INFIRMIERS',
            'SERVICE GENERAL',
        ];
    }

    public static function typesContrat(): array
    {
        return ['CDI', 'CDD', 'Stagiaire', 'Prestataire', 'Vacataire', 'Autre'];
    }

    public static function situationsMatrimoniales(): array
    {
        return ['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'];
    }

    /** Motifs possibles de passage en "Ancien travailleur". */
    public static function motifsDepart(): array
    {
        return [
            'affectation_externe' => 'Affecté(e) dans un autre hôpital',
            'fin_contrat'         => 'Contrat terminé',
            'debauchage'          => 'Débauché(e)',
            'demission'           => 'Démission',
            'retraite'            => 'Retraite',
            'autre'               => 'Autre',
        ];
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
            'actif'    => 'Actif',
            'inactif'  => 'Inactif',
            'en_conge' => 'En congé',
            'retraite' => 'Retraité',
            'ancien'   => 'Ancien travailleur',
            default    => $this->statut,
        };
    }

    public function getMotifDepartLabelAttribute(): ?string
    {
        return $this->motif_depart ? (self::motifsDepart()[$this->motif_depart] ?? $this->motif_depart) : null;
    }

    public function getEstAncienTravailleurAttribute(): bool
    {
        return in_array($this->statut, ['ancien', 'retraite']);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $this->photo_path), '/');

        if (Storage::disk('public')->exists($cleanPath) || file_exists(storage_path('app/public/' . $cleanPath)) || file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        return null;
    }

    public function isActif(): bool { return $this->statut === 'actif'; }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function historiques()
    {
        return $this->hasMany(PersonnelHistorique::class)->orderByDesc('date_debut');
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class)->orderByDesc('date_debut');
    }

    public function avancements()
    {
        return $this->hasMany(Avancement::class)->orderByDesc('date_effet');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }

    /** A déjà bénéficié de la bonification des 58 ans (article 88). */
    public function getEstBonifieAttribute(): bool
    {
        return $this->relationLoaded('avancements')
            ? $this->avancements->contains('type', 'bonification')
            : $this->avancements()->where('type', 'bonification')->exists();
    }

    public function enfants()
    {
        return $this->hasMany(PersonnelEnfant::class)->orderBy('date_naissance');
    }

    public function conjoints()
    {
        return $this->hasMany(PersonnelConjoint::class);
    }

    // ── Ayants droits ────────────────────────────────────────────────────────

    /** Âge limite (en années) au-delà duquel un enfant n'est plus ayant droit. */
    public const AGE_LIMITE_ENFANT = 21;

    public function getEnfantsAyantsDroitAttribute()
    {
        return $this->enfants->filter(fn ($e) => $e->date_naissance && $e->date_naissance->age <= self::AGE_LIMITE_ENFANT);
    }

    // ── Contrats ─────────────────────────────────────────────────────────────

    /** Contrat en cours le plus récent (ou dernier contrat en date à défaut). */
    public function getContratActifAttribute(): ?Contrat
    {
        $contrats = $this->relationLoaded('contrats') ? $this->contrats : $this->contrats()->get();

        return $contrats->first(fn ($c) => $c->statut === 'actif' && (!$c->date_fin || !$c->date_fin->isPast()))
            ?? $contrats->first();
    }

    public function getTypeContratActuelAttribute(): ?string
    {
        return $this->contrat_actif?->type_contrat ?? $this->type_contrat;
    }
}
