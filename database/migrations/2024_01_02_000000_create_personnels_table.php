<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();

            // ── Identité ─────────────────────────────────────────────
            $table->string('nom');
            $table->string('prenoms');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 150)->nullable();
            $table->enum('sexe', ['M', 'F']);
            $table->string('telephone', 20)->nullable();
            $table->enum('situation_matrimoniale', [
                'Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'
            ])->nullable();
            $table->string('diplome', 200)->nullable();
            $table->boolean('autorisation_clientele_privee')->default(false);

            // ── Poste ─────────────────────────────────────────────────
            $table->string('corporation', 150)->nullable();   // Titre/Fonction
            $table->string('service', 150)->nullable();
            $table->string('type_contrat', 100)->nullable();
            $table->string('categorie_echelon', 100)->nullable();

            // ── Dates contrat ─────────────────────────────────────────
            $table->date('date_embauche_centre')->nullable();
            $table->date('date_embauche_isd')->nullable();
            $table->date('date_debauchage')->nullable();
            $table->string('motif_debauchage')->nullable();
            $table->string('numero_cnss', 50)->nullable();
            $table->date('date_depart_retraite')->nullable();
            $table->date('date_fin_contrat')->nullable();

            // ── Congé ─────────────────────────────────────────────────
            $table->string('conge_annee', 10)->nullable();
            $table->integer('conge_jours')->nullable();

            // ── Contact urgence ───────────────────────────────────────
            $table->string('contact_urgence_nom', 150)->nullable();
            $table->string('contact_urgence_telephone', 20)->nullable();

            // ── Affectation ───────────────────────────────────────────
            $table->string('affectation', 200)->nullable();
            $table->date('date_affectation')->nullable();

            // ── Statut ────────────────────────────────────────────────
            $table->enum('statut', ['actif', 'inactif', 'en_conge', 'retraite'])
                  ->default('actif');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
