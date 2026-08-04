<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grille_salariale', function (Blueprint $table) {
            $table->id();
            $table->string('categorie', 50);
            $table->string('libelle', 200)->nullable();
            $table->integer('ordre')->default(0);
            $table->integer('echelon');
            $table->integer('anciennete_mois')->default(0);
            $table->decimal('coefficient', 8, 3);
            $table->integer('salaire');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grille_salariale');
    }
};
