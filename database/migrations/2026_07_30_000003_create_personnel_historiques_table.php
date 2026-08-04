<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->nullOnDelete();
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('type_contrat', 100)->nullable();
            $table->string('categorie_echelon', 100)->nullable();
            $table->decimal('salaire_base', 12, 2)->nullable();
            $table->string('corporation', 150)->nullable();
            $table->string('service', 150)->nullable();
            $table->string('centre_nom', 200)->nullable();
            $table->enum('type_evenement', [
                'embauche', 'renouvellement', 'transfert', 'avancement',
                'passage_cdi', 'fin_contrat', 'desactivation', 'reactivation'
            ]);
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_historiques');
    }
};
