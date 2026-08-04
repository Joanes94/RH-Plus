<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $date_debut
 * @property Carbon|null $date_effet_echelon
 * @property Carbon|null $date_fin
 * @property Carbon|null $date_debauchage
 * @property Carbon|null $date_signature
 * @property Carbon|null $date_visa
 */
class Contrat extends Model
{
    use SoftDeletes;

    protected $table = 'contrats';

    protected $fillable = [
        'personnel_id', 'type_contrat', 'fonction', 'service', 'categorie_echelon',
        'categorie', 'echelon', 'date_effet_echelon',
        'centre', 'lieu_conge', 'salaire_base', 'honoraire_garde', 'honoraire_permanence',
        'date_debut', 'duree_mois', 'date_fin',
        'date_debauchage', 'motif_debauchage',
        'lieu_signature', 'date_signature', 'numero_visa', 'date_visa',
        'statut', 'created_by', 'centre_id',
    ];

    protected $casts = [
        'date_debut'       => 'date',
        'date_effet_echelon' => 'date',
        'date_fin'         => 'date',
        'date_debauchage'  => 'date',
        'date_signature'   => 'date',
        'date_visa'        => 'date',
        'salaire_base'     => 'decimal:2',
        'honoraire_garde'      => 'decimal:2',
        'honoraire_permanence' => 'decimal:2',
    ];

    public static function typesContrat(): array
    {
        return ['CDI', 'CDD', 'Stagiaire', 'Prestataire', 'Vacataire', 'Autre'];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function avancements()
    {
        return $this->hasMany(Avancement::class)->orderByDesc('date_effet');
    }

    public function caseGrilleActuelle(): ?GrilleSalariale
    {
        return GrilleSalariale::caseGrille($this->categorie, $this->echelon);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Règles métier ────────────────────────────────────────────────────────

    /**
     * Calcule automatiquement la date de fin du contrat :
     * - CDI : cesse de plein droit le jour du 60e anniversaire de l'agent.
     * - CDD : date_debut + duree_mois (si fournie et date_fin non déjà saisie).
     */
    public function calculerDateFin(): ?Carbon
    {
        if ($this->type_contrat === 'CDI') {
            $naissance = $this->personnel?->date_naissance;
            return $naissance ? Carbon::parse((string) $naissance)->addYears(60) : null;
        }

        if (in_array($this->type_contrat, ['CDD', 'Prestataire']) && $this->date_debut && $this->duree_mois) {
            return Carbon::parse((string) $this->date_debut)->addMonths((int) $this->duree_mois)->subDay();
        }

        return $this->date_fin;
    }

    /** "un (01) an neuf (09) mois" — pour les contrats à durée fixe (CDD / Prestation). */
    public function getDureeLibelleAttribute(): ?string
    {
        if (!$this->duree_mois) return null;

        $noms = [0=>'zéro',1=>'un',2=>'deux',3=>'trois',4=>'quatre',5=>'cinq',6=>'six',7=>'sept',
                 8=>'huit',9=>'neuf',10=>'dix',11=>'onze',12=>'douze',13=>'treize',14=>'quatorze',
                 15=>'quinze',16=>'seize',17=>'dix-sept',18=>'dix-huit',19=>'dix-neuf',20=>'vingt'];

        $mot = fn ($n) => $noms[$n] ?? (string) $n;

        $annees = intdiv((int) $this->duree_mois, 12);
        $mois   = (int) $this->duree_mois % 12;

        $parties = [];
        if ($annees > 0) $parties[] = $mot($annees) . ' (' . sprintf('%02d', $annees) . ') an' . ($annees > 1 ? 's' : '');
        if ($mois > 0)   $parties[] = $mot($mois) . ' (' . sprintf('%02d', $mois) . ') mois';

        return $parties ? implode(' ', $parties) : $mot(0) . ' (00) mois';
    }

    public function getEstEnCoursAttribute(): bool
    {
        if ($this->statut !== 'actif') return false;
        if ($this->date_fin && $this->date_fin->isPast()) return false;
        return true;
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'actif'   => $this->est_en_cours ? 'En cours' : 'Échu',
            'termine' => 'Terminé',
            'rompu'   => 'Rompu',
            default   => $this->statut,
        };
    }

    // ── Boot : calcul automatique de la date de fin ─────────────────────────

    protected static function booted()
    {
        static::saving(function (Contrat $contrat) {
            $calculee = $contrat->calculerDateFin();
            if ($calculee) {
                $contrat->date_fin = $calculee->toDateString();
            }

            if (!empty($contrat->categorie) && !empty($contrat->echelon)) {
                $contrat->categorie_echelon = $contrat->categorie . '-' . $contrat->echelon;
            }
        });
    }
}
