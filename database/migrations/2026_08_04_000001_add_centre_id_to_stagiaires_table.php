<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stagiaires', 'centre_id')) {
            Schema::table('stagiaires', function (Blueprint $table) {
                $table->foreignId('centre_id')->nullable()->after('id')->constrained('centres')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stagiaires', 'centre_id')) {
            Schema::table('stagiaires', function (Blueprint $table) {
                $table->dropForeign(['centre_id']);
                $table->dropColumn('centre_id');
            });
        }
    }
};
