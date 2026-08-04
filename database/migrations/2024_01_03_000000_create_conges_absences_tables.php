<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Configuration générale RH ──────────────────────────────────────────
        Schema::create('config_rh', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur')->nullable();
            $table->timestamps();
        });

        // ── Jours fériés par année ─────────────────────────────────────────────
        Schema::create('jours_feries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('libelle', 150);
            $table->enum('type', ['fixe', 'mobile'])->default('fixe');
            $table->year('annee');
            $table->timestamps();
        });

        // ── Demandes de congés ─────────────────────────────────────────────────
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->enum('type_conge', ['administratif', 'technique']);
            $table->date('date_debut');
            $table->integer('nb_jours_demandes');          // jours ouvrables demandés
            $table->date('date_fin');                       // calculée par le système
            $table->integer('nb_jours_acquis')->default(24); // base + majoration ancienneté
            $table->integer('nb_jours_deja_pris')->default(0);
            $table->integer('nb_jours_restants');          // calculé
            $table->text('observations')->nullable();
            $table->string('annee', 9);                    // ex: 2024-2025
            $table->enum('statut', ['brouillon', 'soumis', 'approuve', 'rejete'])->default('brouillon');
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuve_le')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->string('signature_path')->nullable(); // chemin image signature DRH
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Demandes d'absences ────────────────────────────────────────────────
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->enum('type_absence', [
                // Non déductibles
                'deces_conjoint',
                'deces_parent_enfant',
                'deces_frere_soeur_beau',
                'mariage_travailleur',
                'mariage_enfant_frere_soeur',
                'naissance',
                // Déductibles
                'deductible',
            ]);
            $table->boolean('deductible'); // calculé selon le type
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('nb_jours');  // jours calendaires (absences = jours calendaires)
            $table->text('motif')->nullable();
            $table->text('observations')->nullable();
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
        Schema::dropIfExists('absences');
        Schema::dropIfExists('conges');
        Schema::dropIfExists('jours_feries');
        Schema::dropIfExists('config_rh');
    }
};
