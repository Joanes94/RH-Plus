<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 200);
            $table->string('code', 30)->unique();
            $table->string('email', 150)->nullable();
            $table->string('adresse', 300)->nullable();
            $table->string('telephone', 30)->nullable();
            $table->boolean('a_drh_dedie')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centres');
    }
};
