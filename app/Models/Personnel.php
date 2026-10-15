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
        'nom', 'prenoms', 'email', 'photo_path', 'matricule',
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

    /**
     * Supprime les accents et convertit en majuscules pour normalisation.
     */
    public static function stripAccentsMaj(string $str): string
    {
        $accents = [
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Á' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Î' => 'I', 'Ï' => 'I', 'Ì' => 'I', 'Í' => 'I',
            'Ô' => 'O', 'Ö' => 'O', 'Ò' => 'O', 'Ó' => 'O', 'Õ' => 'O',
            'Û' => 'U', 'Ü' => 'U', 'Ù' => 'U', 'Ú' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
            'à' => 'A', 'â' => 'A', 'ä' => 'A', 'ã' => 'A', 'å' => 'A', 'á' => 'A',
            'é' => 'E', 'è' => 'E', 'ê' => 'E', 'ë' => 'E',
            'î' => 'I', 'ï' => 'I', 'ì' => 'I', 'í' => 'I',
            'ô' => 'O', 'ö' => 'O', 'ò' => 'O', 'ó' => 'O', 'õ' => 'O',
            'û' => 'U', 'ü' => 'U', 'ù' => 'U', 'ú' => 'U',
            'ç' => 'C', 'ñ' => 'N',
        ];
        $str = strtr($str, $accents);
        $str = mb_strtoupper($str, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $str));
    }

    /**
     * Normalise un libellé de corporation : supprime les accents, les espaces superflus,
     * convertit en majuscules et résout les doublons/variantes de genre (ex: CAISSIER / CAISSIERE).
     */
    public static function normaliserCorporation(?string $corp): ?string
    {
        if ($corp === null || trim($corp) === '') {
            return null;
        }

        $upper = self::stripAccentsMaj($corp);

        // Mappage exhaustif des féminins, abréviations, variantes orthographiques et doublons
        $alias = [
            'CAISSIERE'                        => 'CAISSIER',
            'CAISSIER'                         => 'CAISSIER',
            'INFIRMIERE'                       => 'INFIRMIER',
            'INFIRMIER'                        => 'INFIRMIER',
            'AIDE SOIGNANT'                    => 'AIDE-SOIGNANT',
            'AIDE-SOIGNANT'                    => 'AIDE-SOIGNANT',
            'AIDE SOIGNANTE'                   => 'AIDE-SOIGNANT',
            'AIDE-SOIGNANTE'                   => 'AIDE-SOIGNANT',
            'AIDE SOIGNANT PROMU'              => 'AIDE-SOIGNANT PROMU',
            'AIDE-SOIGNANT PROMU'              => 'AIDE-SOIGNANT PROMU',
            'AIDE SOIGNANT PROMUE'             => 'AIDE-SOIGNANT PROMU',
            'AIDE-SOIGNANT PROMUE'             => 'AIDE-SOIGNANT PROMU',
            'AIDE SOIGNANTE PROMUE'            => 'AIDE-SOIGNANT PROMU',
            'AIDE-SOIGNANTE PROMUE'            => 'AIDE-SOIGNANT PROMU',
            'SECRETAIRE ADMINISTRATIVE'        => 'SECRETAIRE ADMINISTRATIF',
            'SECRETAIRE ADMINISTRATIF'         => 'SECRETAIRE ADMINISTRATIF',
            'SECRETAIRE MEDICALE'              => 'SECRETAIRE MEDICAL',
            'SECRETAIRE MEDICAL'               => 'SECRETAIRE MEDICAL',
            'SECRETAIRE DE LABORATOIRE'        => 'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE LABO'                  => 'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE DE LABO'               => 'SECRETAIRE DE LABORATOIRE',
            'TECHNICIEN EN ODONTO'             => 'TECHNICIEN SUPERIEUR EN ODONTO',
            'TECHNICIEN EN ODONTOLOGIE'        => 'TECHNICIEN SUPERIEUR EN ODONTO',
            'TECHNICIEN SUPERIEUR EN ODONTO'   => 'TECHNICIEN SUPERIEUR EN ODONTO',
            'TECHNICIEN SUPERIEUR EN ODONTOLOGIE' => 'TECHNICIEN SUPERIEUR EN ODONTO',
            'BIOLOGISTE MEDICALE'              => 'BIOLOGISTE MEDICAL',
            'BIOLOGISTE MEDICAL'               => 'BIOLOGISTE MEDICAL',
            'CHIRURGIEN MAXILO FACIALE'        => 'CHIRURGIEN MAXILLO-FACIAL',
            'CHIRURGIEN MAXILLO FACIAL'        => 'CHIRURGIEN MAXILLO-FACIAL',
            'CHIRURGIEN MAXILLO-FACIAL'        => 'CHIRURGIEN MAXILLO-FACIAL',
            'CHIRURGIENNE'                     => 'CHIRURGIEN',
            'CHIRURGIEN'                       => 'CHIRURGIEN',
            'CHIRUGIEN'                        => 'CHIRURGIEN',
            'NEURO CHIRURGIEN'                 => 'NEUROCHIRURGIEN',
            'NEUROCHIRURGIEN'                  => 'NEUROCHIRURGIEN',
            'OPHTAMOLOGUE'                     => 'OPHTALMOLOGUE',
            'OPHTALMOLOGUE'                    => 'OPHTALMOLOGUE',
            'CUISINIERE'                       => 'CUISINIER',
            'CUISINIER'                        => 'CUISINIER',
            'SURVEILLANTE'                     => 'SURVEILLANT',
            'SURVEILLANT'                      => 'SURVEILLANT',
            'MAGASINIERE'                      => 'MAGASINIER',
            'MAGASINIER'                       => 'MAGASINIER',
            'DIRECTRICE'                       => 'DIRECTEUR',
            'DIRECTEUR'                        => 'DIRECTEUR',
            'DIRECTRICE DES RESSOURCES HUMAINES' => 'DIRECTEUR DES RESSOURCES HUMAINES',
            'DIRECTEUR DES RESSOURCES HUMAINES'  => 'DIRECTEUR DES RESSOURCES HUMAINES',
            'ASSISTANTE RH'                    => 'ASSISTANT RH',
            'ASSISTANT RH'                     => 'ASSISTANT RH',
            'ASSISTANT RESSOURCES HUMAINES'    => 'ASSISTANT RH',
            'ASSISTANTE RESSOURCES HUMAINES'   => 'ASSISTANT RH',
            'ASSISTANTE DENTAIRE'              => 'ASSISTANT DENTAIRE',
            'ASSISTANT DENTAIRE'               => 'ASSISTANT DENTAIRE',
            'ASSISTANTE LABORATOIRE'           => 'ASSISTANT LABORATOIRE',
            'ASSISTANT LABORATOIRE'            => 'ASSISTANT LABORATOIRE',
            'ASSISTANTE DE LABORATOIRE'        => 'ASSISTANT LABORATOIRE',
            'ASSISTANT DE LABORATOIRE'         => 'ASSISTANT LABORATOIRE',
            'STATISTICIENNE'                   => 'STATISTICIEN',
            'STATISTICIEN'                     => 'STATISTICIEN',
            'INFIRMIER/SURVEILLANT'            => 'INFIRMIER SURVEILLANT',
            'INFIRMIERE SURVEILLANTE'          => 'INFIRMIER SURVEILLANT',
            'INFIRMIERE (IDE)'                 => 'INFIRMIER',
            'INFIRMIER (IDE)'                  => 'INFIRMIER',
            'INFIRMIERE BREVETE'               => 'INFIRMIER BREVETE',
            'INFIRMIERE BREVETTE'              => 'INFIRMIER BREVETE',
            'INFIRMIER BREVETTE'               => 'INFIRMIER BREVETE',
            'TECHNICIENNE DE LABORATOIRE'      => 'TECHNICIEN DE LABORATOIRE',
            'TECHNICIENNE DU LABORATOIRE'      => 'TECHNICIEN DE LABORATOIRE',
            'TECHNICIEN DU LABORATOIRE'        => 'TECHNICIEN DE LABORATOIRE',
            'SAGE FEMME'                       => 'SAGE-FEMME',
            'SAGE-FEMME'                       => 'SAGE-FEMME',
            "SAGE-FEMME D'ETAT"                => 'SAGE-FEMME',
            "SAGE FEMME D'ETAT"                => 'SAGE-FEMME',
            'AGENT D\'ENTRETIEN'               => "AGENT D'ENTRETIEN",
            'AGENT D’ENTRETIEN'                => "AGENT D'ENTRETIEN",
        ];

        return $alias[$upper] ?? $upper;
    }

    /**
     * Retourne la liste des alias d'une corporation pour les filtres SQL.
     */
    public static function aliasesCorporation(string $corp): array
    {
        $canon = self::normaliserCorporation($corp);
        $map = [
            'CAISSIER'                       => ['CAISSIER', 'CAISSIERE', 'CAISSIÈRE', 'Caissier', 'Caissière', 'Caissiere'],
            'INFIRMIER'                      => ['INFIRMIER', 'INFIRMIERE', 'INFIRMIÈRE', 'Infirmier', 'Infirmière', 'Infirmiere'],
            'AIDE-SOIGNANT'                  => ['AIDE-SOIGNANT', 'AIDE SOIGNANT', 'AIDE-SOIGNANTE', 'AIDE SOIGNANTE', 'Aide-soignant', 'Aide soignant', 'Aide-soignante'],
            'AIDE-SOIGNANT PROMU'            => ['AIDE-SOIGNANT PROMU', 'AIDE SOIGNANT PROMU', 'AIDE-SOIGNANTE PROMUE', 'AIDE SOIGNANTE PROMUE'],
            'SECRETAIRE ADMINISTRATIF'       => ['SECRETAIRE ADMINISTRATIF', 'SECRETAIRE ADMINISTRATIVE', 'Secrétaire administrative', 'Secrétaire administratif', 'Secretaire administratif', 'Secretaire administrative'],
            'SECRETAIRE MEDICAL'             => ['SECRETAIRE MEDICAL', 'SECRETAIRE MEDICALE', 'Secrétaire médicale', 'Secrétaire médical'],
            'TECHNICIEN SUPERIEUR EN ODONTO' => ['TECHNICIEN SUPERIEUR EN ODONTO', 'TECHNICIEN EN ODONTO', 'Technicien en odonto', 'Technicien supérieur en odonto'],
            'BIOLOGISTE MEDICAL'             => ['BIOLOGISTE MEDICAL', 'BIOLOGISTE MEDICALE', 'Biologiste médicale', 'Biologiste médical'],
            'CHIRURGIEN MAXILLO-FACIAL'      => ['CHIRURGIEN MAXILLO-FACIAL', 'CHIRURGIEN MAXILLO FACIAL', 'CHIRURGIEN MAXILO FACIALE'],
            'CHIRURGIEN'                     => ['CHIRURGIEN', 'CHIRURGIENNE', 'Chirurgien', 'Chirurgienne'],
            'NEUROCHIRURGIEN'                => ['NEUROCHIRURGIEN', 'NEURO CHIRURGIEN'],
            'OPHTALMOLOGUE'                  => ['OPHTALMOLOGUE', 'OPHTAMOLOGUE'],
            'CUISINIER'                      => ['CUISINIER', 'CUISINIERE', 'CUISINIÈRE', 'Cuisinier', 'Cuisinière'],
            'SURVEILLANT'                    => ['SURVEILLANT', 'SURVEILLANTE', 'Surveillant', 'Surveillante'],
            'MAGASINIER'                     => ['MAGASINIER', 'MAGASINIERE', 'Magasinier', 'Magasinière'],
            'ASSISTANT RH'                   => ['ASSISTANT RH', 'ASSISTANTE RH', 'Assistant RH', 'Assistante RH'],
            'ASSISTANT DENTAIRE'             => ['ASSISTANT DENTAIRE', 'ASSISTANTE DENTAIRE', 'Assistant dentaire', 'Assistante dentaire'],
            'ASSISTANT LABORATOIRE'          => ['ASSISTANT LABORATOIRE', 'ASSISTANTE LABORATOIRE', 'Assistant laboratoire'],
            'DENTISTE'                       => ['DENTISTE', 'Dentiste'],
            'AGENT DE PHARMACIE'             => ['AGENT DE PHARMACIE', 'Agent de pharmacie', 'Agent de Pharmacie'],
            'AGENT ADMINISTRATIF'            => ['AGENT ADMINISTRATIF', 'Agent administratif', 'Agent Administratif'],
            'SECRETAIRE DE LABORATOIRE'      => ['SECRETAIRE DE LABORATOIRE', 'Secretaire de Laboratoire', 'Secrétaire de laboratoire'],
        ];

        return array_values(array_unique(array_merge([$corp, $canon], $map[$canon] ?? [])));
    }

    /**
     * Liste des corporations disponibles pour un centre donné (ou pour l'utilisateur connecté).
     * Isole les corporations créées par centre (ex. Saint-Luc ne voit pas les corporations de Cotonou).
     */
    public static function corporations(?int $centreId = null): array
    {
        $user = auth()->user();
        if ($centreId === null && $user && !$user->isGlobal()) {
            $centreId = $user->centre_id;
        }

        try {
            $corps = \App\Models\Corporation::pourCentre($centreId)
                ->pluck('nom')
                ->map(fn($c) => self::normaliserCorporation($c))
                ->filter()
                ->unique()
                ->toArray();

            if (empty($corps)) {
                $corps = self::baseCorporations();
            }

            natcasesort($corps);
            return array_values($corps);
        } catch (\Throwable $e) {
            $base = self::baseCorporations();
            natcasesort($base);
            return array_values($base);
        }
    }

    public static function baseCorporations(): array
    {
        return [
            'AGENT ADMINISTRATIF',
            'AGENT DE PHARMACIE',
            "AGENT D'ENTRETIEN",
            'AIDE-SOIGNANT',
            'AIDE-SOIGNANT PROMU',
            'ANESTHESISTE',
            'ASSISTANT DENTAIRE',
            'ASSISTANT LABORATOIRE',
            'ASSISTANT RH',
            'BIOLOGISTE MEDICAL',
            'CAISSIER',
            'CARDIOLOGUE',
            'CHAUFFEUR',
            'CHEF SERVICE FINANCIER ET COMPTABLE',
            'CHIRURGIEN',
            'CHIRURGIEN MAXILLO-FACIAL',
            'COMPTABLE',
            'CUISINIER',
            'DENTISTE',
            'DERMATOLOGUE',
            'DIRECTEUR',
            'DIRECTEUR DES RESSOURCES HUMAINES',
            'ENDOCRINOLOGUE',
            'GASTROLOGUE',
            'INFIRMIER',
            'KINESITHERAPEUTE',
            'MAGASINIER',
            'MEDECIN',
            'MEDECIN ANESTHESISTE REANIMATEUR',
            'MEDECIN CHEF',
            'NEPHROLOGUE',
            'NEUROCHIRURGIEN',
            'NEUROLOGUE',
            'OPHTALMOLOGUE',
            'ORL',
            'PSYCHIATRE',
            'RADIOLOGUE',
            'SAGE-FEMME',
            'SECRETAIRE ADMINISTRATIF',
            'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE MEDICAL',
            'STATISTICIEN',
            'SURVEILLANT',
            'TECHNICIEN DE LABORATOIRE',
            'TECHNICIEN EN IMAGERIE MEDICALE',
            'TECHNICIEN SUPERIEUR EN ODONTO',
            'TRAUMATOLOGUE',
            'UROLOGUE',
        ];
    }

    public static function services(): array
    {
        $base = [
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

        try {
            $fromDb = self::whereNotNull('service')
                ->where('service', '!=', '')
                ->pluck('service')
                ->map(fn($s) => trim(mb_strtoupper($s, 'UTF-8')))
                ->filter()
                ->unique()
                ->toArray();
            $merged = array_unique(array_merge($base, $fromDb));
            natcasesort($merged);
            return array_values($merged);
        } catch (\Throwable $e) {
            natcasesort($base);
            return array_values($base);
        }
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

    public function getCorporationAttribute($value): ?string
    {
        return self::normaliserCorporation($value) ?? $value;
    }

    public function setCorporationAttribute($value): void
    {
        $this->attributes['corporation'] = self::normaliserCorporation($value);
    }

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

        $cleanPath = ltrim(str_replace(['public/', 'storage/', 'public\\', 'storage\\'], '', $this->photo_path), '/\\');
        $cleanPath = str_replace('\\', '/', $cleanPath);

        if (file_exists(public_path('storage/' . $cleanPath)) || file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        return null;
    }

    /**
     * Avatar de substitution : génère une image avec les initiales si pas de photo.
     * Utilisable comme fallback universel dans les vues : $personnel->photo_url ?? $personnel->avatar_url
     */
    public function getAvatarUrlAttribute(): string
    {
        $initiales = urlencode($this->initiales);
        $colors = ['1a5c45', '3b82f6', '8b5cf6', 'f59e0b', 'ec4899', '06b6d4', '10b981', '6366f1'];
        $bg = $colors[$this->id % count($colors)];
        return "https://ui-avatars.com/api/?name={$initiales}&background={$bg}&color=fff&size=128&font-size=0.4&bold=true&format=svg";
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

    public function paySlips()
    {
        return $this->hasMany(PaySlip::class, 'personnel_id')->orderByDesc('created_at');
    }

    public function payAdjustments()
    {
        return $this->hasMany(PayAdjustment::class, 'personnel_id')->orderByDesc('created_at');
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
