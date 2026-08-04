<?php
// database/migrations/2026_07_06_create_evaluations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained()->cascadeOnDelete();
            $table->text('qualites')->nullable();
            $table->text('defauts')->nullable();
            $table->text('maitrise_pratique')->nullable();
            $table->text('appreciation_personnelle')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, soumis, approuve, rejete
            $table->string('reference')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuve_le')->nullable();
            $table->string('motif_rejet')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};