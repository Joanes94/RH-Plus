<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();

            // Type de demande
            $table->string('type_demande', 80); // slug ex: attestation_travail

            // Infos spécifiques selon le type
            $table->date('date_debut')->nullable();      // congé maladie / maternité
            $table->date('date_fin')->nullable();
            $table->integer('nb_jours')->nullable();
            $table->string('motif', 500)->nullable();    // congé maladie : diagnostic...
            $table->text('observations')->nullable();

            // Congé maternité
            $table->date('date_accouchement_prevu')->nullable();

            // Stage
            $table->string('etablissement_stage', 200)->nullable();
            $table->string('niveau_etude', 100)->nullable();
            $table->string('specialite', 150)->nullable();

            // Demande d'explication
            $table->text('faits_reproches')->nullable();
            $table->date('date_faits')->nullable();

            // Workflow
            $table->enum('statut', ['brouillon', 'soumis', 'approuve', 'rejete'])->default('brouillon');
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuve_le')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->string('signature_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
