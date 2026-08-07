<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_rh', function (Blueprint $table) {
            // Supprimer l'ancien index unique sur 'cle' seule
            $table->dropUnique('config_rh_cle_unique');

            // Créer un index unique composite : même clé autorisée pour différents users
            $table->unique(['cle', 'user_id'], 'config_rh_cle_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('config_rh', function (Blueprint $table) {
            $table->dropUnique('config_rh_cle_user_unique');
            $table->unique('cle', 'config_rh_cle_unique');
        });
    }
};
