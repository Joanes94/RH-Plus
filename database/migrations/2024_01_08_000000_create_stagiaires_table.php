<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();

            // ── Identité ─────────────────────────────────────────────
            $table->string('nom');
            $table->string('prenoms');
            $table->string('email')->nullable()->unique();
            $table->string('photo_path')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 150)->nullable();
            $table->enum('sexe', ['M', 'F']);
            $table->string('telephone', 20)->nullable();
            $table->enum('situation_matrimoniale', [
                'Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'
            ])->nullable();

            // ── Formation ─────────────────────────────────────────────
            $table->string('titre', 150)->nullable();          // Titre / Fonction
            $table->string('niveau_etude', 50)->nullable();    // CEP, BEPC, BAC, Licence 1...
            $table->string('diplome', 200)->nullable();
            $table->string('ecole_formation', 200)->nullable();
            $table->boolean('autorisation_clientele_privee')->default(false);

            // ── Période de stage ──────────────────────────────────────
            $table->string('service', 150)->nullable();
            $table->date('date_debut_stage')->nullable();
            $table->date('date_fin_stage')->nullable();
            $table->text('observations')->nullable();

            // ── Contact urgence ───────────────────────────────────────
            $table->string('contact_urgence_nom', 150)->nullable();
            $table->string('contact_urgence_telephone', 20)->nullable();

            // ── Statut ────────────────────────────────────────────────
            $table->enum('statut', ['en_cours', 'termine', 'abandonne'])->default('en_cours');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};
