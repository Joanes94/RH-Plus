<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pay_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pay_period_id')->constrained('pay_periods')->cascadeOnDelete();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('centre_id')->constrained('centres')->cascadeOnDelete();
            
            $table->string('matricule_cnss', 50)->nullable();
            $table->string('poste', 150)->nullable();
            $table->string('type_contrat', 100)->nullable();
            $table->string('categorie', 50)->nullable();
            $table->string('echelon', 10)->nullable();
            
            $table->string('banque', 100)->nullable();
            $table->string('mode_reglement', 50)->default('Virement');
            $table->string('numero_compte', 100)->nullable();

            $table->integer('jours_travailles')->default(30);
            $table->integer('jours_absence')->default(0);
            $table->decimal('heures_supplementaires', 8, 2)->default(0);
            $table->decimal('heures_astreinte', 8, 2)->default(0);

            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('indemnite_residence', 12, 2)->default(0);
            $table->decimal('indemnite_logement', 12, 2)->default(0);
            $table->decimal('indemnite_transport', 12, 2)->default(0);
            $table->decimal('autre_indemnite', 12, 2)->default(0);
            $table->decimal('ecart', 12, 2)->default(0);

            $table->decimal('prime_caisse', 12, 2)->default(0);
            $table->decimal('prime_risque', 12, 2)->default(0);
            $table->decimal('prime_responsabilite', 12, 2)->default(0);
            $table->decimal('prime_garde', 12, 2)->default(0);
            $table->decimal('autre_prime', 12, 2)->default(0);

            $table->decimal('trop_percu_brut', 12, 2)->default(0);
            $table->decimal('salaire_brut', 12, 2)->default(0);

            $table->decimal('cotisation_sociale_salarie', 12, 2)->default(0); // 3.6%
            $table->decimal('impot_its', 12, 2)->default(0);                  // progressive
            
            $table->decimal('cotisation_sociale_patronale', 12, 2)->default(0); // 6.4%
            $table->decimal('prestation_familiale_patronale', 12, 2)->default(0); // 9%
            $table->decimal('risque_professionnel_patronale', 12, 2)->default(0);  // 1%

            $table->decimal('taxe_radio', 12, 2)->default(0);  // March ORTB
            $table->decimal('taxe_tele', 12, 2)->default(0);   // June ORTB
            $table->decimal('frais_medicaux', 12, 2)->default(0);
            $table->decimal('avance_salaire', 12, 2)->default(0);
            $table->decimal('trop_percu_net', 12, 2)->default(0);
            $table->decimal('mise_a_pied', 12, 2)->default(0);

            $table->decimal('moins_percu_rembourse', 12, 2)->default(0);
            $table->decimal('salaire_net', 12, 2)->default(0);

            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_slips');
    }
};
