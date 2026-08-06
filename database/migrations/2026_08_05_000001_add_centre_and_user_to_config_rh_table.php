<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_rh', function (Blueprint $table) {
            if (!Schema::hasColumn('config_rh', 'centre_id')) {
                $table->foreignId('centre_id')->nullable()->after('id')->constrained('centres')->nullOnDelete();
            }
            if (!Schema::hasColumn('config_rh', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('centre_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('config_rh', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['centre_id', 'user_id']);
        });
    }
};
