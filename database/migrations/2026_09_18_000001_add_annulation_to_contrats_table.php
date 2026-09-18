<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nouveau statut "annule" : contrat gardé en base (traçabilité), mais
        // exclu de tous les calculs (contrat_actif, paie, avancement...) au même
        // titre que 'termine'/'rompu'. Utilisé notamment pour résoudre un doublon
        // de contrat actif créé par l'import Excel.
        DB::statement("ALTER TABLE contrats MODIFY COLUMN statut ENUM('actif','termine','rompu','annule') NOT NULL DEFAULT 'actif'");

        Schema::table('contrats', function (Blueprint $table) {
            $table->string('motif_annulation')->nullable()->after('statut');
            $table->timestamp('annule_le')->nullable()->after('motif_annulation');
            $table->foreignId('annule_par_id')->nullable()->after('annule_le')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['annule_par_id']);
            $table->dropColumn(['motif_annulation', 'annule_le', 'annule_par_id']);
        });

        DB::statement("ALTER TABLE contrats MODIFY COLUMN statut ENUM('actif','termine','rompu') NOT NULL DEFAULT 'actif'");
    }
};