<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_enfants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->string('prenom', 150);
            $table->enum('sexe', ['M', 'F']);
            $table->date('date_naissance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_enfants');
    }
};
