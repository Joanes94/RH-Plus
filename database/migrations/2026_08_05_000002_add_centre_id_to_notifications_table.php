<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'centre_id')) {
                $table->foreignId('centre_id')->nullable()->after('id')->constrained('centres')->nullOnDelete();
            }
        });

        // Modifier la colonne type pour accepter tous les types (affectation, transfert, echelon, bonification, etc.)
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropColumn(['centre_id']);
        });
    }
};
