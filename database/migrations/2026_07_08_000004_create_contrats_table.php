<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();

            // ── Nature du contrat ────────────────────────────────────────
            $table->string('type_contrat', 100);            // CDI, CDD, Stagiaire, Prestataire, Vacataire, Autre
            $table->string('fonction', 150)->nullable();     // Fonction/poste occupé pour CE contrat
            $table->string('service', 150)->nullable();
            $table->string('categorie_echelon', 100)->nullable();
            $table->string('categorie', 50)->nullable();
            $table->string('echelon', 10)->nullable();
            $table->date('date_effet_echelon')->nullable();
            $table->string('centre', 200)->nullable();       // Nom du centre / institution (ex: CSVH Saint Luc)
            $table->string('lieu_conge', 150)->nullable();
            $table->decimal('salaire_base', 12, 2)->nullable();
            $table->decimal('honoraire_garde', 12, 2)->nullable();
            $table->decimal('honoraire_permanence', 12, 2)->nullable();

            // ── Dates ─────────────────────────────────────────────────────
            $table->date('date_debut');
            $table->unsignedSmallInteger('duree_mois')->nullable(); // pour CDD
            $table->date('date_fin')->nullable();                   // CDD : calculée ; CDI : 60e anniversaire (interne)
            $table->date('date_debauchage')->nullable();
            $table->string('motif_debauchage')->nullable();

            // ── Signature / enregistrement administratif ────────────────
            $table->string('lieu_signature', 150)->nullable();
            $table->date('date_signature')->nullable();
            $table->string('numero_visa', 100)->nullable();
            $table->date('date_visa')->nullable();

            // ── Statut ────────────────────────────────────────────────────
            $table->enum('statut', ['actif', 'termine', 'rompu'])->default('actif');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
