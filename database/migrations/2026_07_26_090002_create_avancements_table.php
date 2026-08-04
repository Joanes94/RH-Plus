<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table absente du zip fourni alors que le modèle/service/contrôleur
     * Avancement l'utilisent déjà — recréée ici à partir du modèle, en y
     * ajoutant le circuit de validation DDIS exigé pour les bonifications :
     *   - un avancement d'échelon est appliqué automatiquement (statut "valide")
     *   - une bonification (art. 88) reste "soumis" tant que la DDIS ne l'a
     *     pas validée ; le nouveau salaire n'est répercuté sur le contrat
     *     qu'au moment de la validation.
     */
    public function up(): void
    {
        Schema::create('avancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->nullOnDelete();

            $table->enum('type', ['echelon', 'bonification']);
            $table->date('date_effet');

            $table->string('ancienne_categorie', 100)->nullable();
            $table->string('ancien_echelon', 10)->nullable();
            $table->string('nouvelle_categorie', 100)->nullable();
            $table->string('nouvel_echelon', 10)->nullable();

            $table->integer('ancien_salaire')->nullable();
            $table->integer('nouveau_salaire')->nullable();
            $table->decimal('coefficient_applique', 8, 3)->nullable();

            $table->string('numero_reference', 150)->nullable();

            // ── Circuit de validation (DDIS pour les bonifications) ────────
            $table->enum('statut', ['valide', 'soumis', 'rejete'])->default('valide');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->string('motif_rejet', 500)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avancements');
    }
};
