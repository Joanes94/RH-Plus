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
        Schema::create('pay_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->enum('type', ['avance_salaire', 'frais_medicaux', 'moins_percu']);
            $table->string('libelle', 150);
            $table->decimal('montant_total', 12, 2)->nullable();
            $table->decimal('montant_mensuel', 12, 2);
            $table->integer('mois_restants')->nullable(); // null means unlimited (e.g. moins_percu)
            $table->enum('statut', ['actif', 'termine'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_adjustments');
    }
};
