<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $table = 'pay_slips';

    protected $fillable = [
        'pay_period_id', 'personnel_id', 'centre_id',
        'matricule_cnss', 'poste', 'type_contrat', 'categorie', 'echelon',
        'banque', 'mode_reglement', 'numero_compte',
        'jours_travailles', 'jours_absence', 'heures_supplementaires', 'heures_astreinte',
        'salaire_base', 'indemnite_residence', 'indemnite_logement', 'indemnite_transport', 'autre_indemnite', 'ecart',
        'prime_caisse', 'prime_risque', 'prime_responsabilite', 'prime_garde', 'autre_prime',
        'trop_percu_brut', 'salaire_brut',
        'cotisation_sociale_salarie', 'impot_its',
        'cotisation_sociale_patronale', 'prestation_familiale_patronale', 'risque_professionnel_patronale',
        'taxe_radio', 'taxe_tele', 'frais_medicaux', 'avance_salaire', 'trop_percu_net', 'mise_a_pied',
        'moins_percu_rembourse', 'salaire_net', 'validated_by', 'is_fictif'
    ];

    protected $casts = [
        'is_fictif'        => 'boolean',
        'jours_travailles' => 'integer',
        'jours_absence'    => 'integer',
        'heures_supplementaires' => 'decimal:2',
        'heures_astreinte'       => 'decimal:2',
        'salaire_base'           => 'decimal:2',
        'indemnite_residence'    => 'decimal:2',
        'indemnite_logement'     => 'decimal:2',
        'indemnite_transport'    => 'decimal:2',
        'autre_indemnite'        => 'decimal:2',
        'ecart'                  => 'decimal:2',
        'prime_caisse'           => 'decimal:2',
        'prime_risque'           => 'decimal:2',
        'prime_responsabilite'   => 'decimal:2',
        'prime_garde'            => 'decimal:2',
        'autre_prime'            => 'decimal:2',
        'trop_percu_brut'        => 'decimal:2',
        'salaire_brut'           => 'decimal:2',
        'cotisation_sociale_salarie' => 'decimal:2',
        'impot_its'                  => 'decimal:2',
        'cotisation_sociale_patronale'   => 'decimal:2',
        'prestation_familiale_patronale' => 'decimal:2',
        'risque_professionnel_patronale' => 'decimal:2',
        'taxe_radio'      => 'decimal:2',
        'taxe_tele'       => 'decimal:2',
        'frais_medicaux'  => 'decimal:2',
        'avance_salaire'  => 'decimal:2',
        'trop_percu_net'  => 'decimal:2',
        'mise_a_pied'     => 'decimal:2',
        'moins_percu_rembourse' => 'decimal:2',
        'salaire_net'     => 'decimal:2',
    ];


    public function payPeriod()
    {
        return $this->belongsTo(PayPeriod::class, 'pay_period_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /** Calcule les charges patronales totales. */
    public function getChargesPatronalesTotalesAttribute(): float
    {
        return (float) $this->cotisation_sociale_patronale +
               (float) $this->prestation_familiale_patronale +
               (float) $this->risque_professionnel_patronale;
    }

    /** Calcule le coût global de l'employé pour le centre. */
    public function getCoutTotalEmployeurAttribute(): float
    {
        return (float) $this->salaire_brut + $this->charges_patronales_totales;
    }
}
