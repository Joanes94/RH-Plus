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
        Schema::table('pay_adjustments', function (Blueprint $table) {
            // JSON d'échéancier personnalisé : [{"mois":"2026-08","montant":3000}, ...]
            $table->json('echeances')->nullable()->after('mois_restants');
            // Mois de départ de l'échéancier (format YYYY-MM)
            $table->string('mois_debut', 7)->nullable()->after('echeances');
        });
    }

    public function down(): void
    {
        Schema::table('pay_adjustments', function (Blueprint $table) {
            $table->dropColumn(['echeances', 'mois_debut']);
        });
    }
};
