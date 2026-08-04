<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_conjoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->string('prenom', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_conjoints');
    }
};
