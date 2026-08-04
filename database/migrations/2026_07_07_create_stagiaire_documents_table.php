<?php
// database/migrations/2026_07_07_create_stagiaire_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stagiaire_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained()->cascadeOnDelete();
            $table->string('type_document'); // autorisation, attestation, evaluation
            $table->string('reference')->nullable();
            $table->string('type_stage')->nullable(); // professionnel, academique, decouverte
            $table->json('services')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('motif')->nullable();
            $table->text('observations')->nullable();
            $table->string('statut')->default('brouillon');
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuve_le')->nullable();
            $table->string('motif_rejet')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stagiaire_documents');
    }
};