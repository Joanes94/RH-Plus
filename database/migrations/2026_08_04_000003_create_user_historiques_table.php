<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ancien_centre_id')->nullable()->constrained('centres')->nullOnDelete();
            $table->foreignId('nouveau_centre_id')->nullable()->constrained('centres')->nullOnDelete();
            $table->string('ancien_centre_nom')->nullable();
            $table->string('nouveau_centre_nom')->nullable();
            $table->string('role')->nullable();
            $table->date('date_transfert');
            $table->text('motif')->nullable();
            $table->foreignId('transfere_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_historiques');
    }
};
