<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('assistant_rh', 'drh', 'crh', 'ddis', 'ddrh', 'drh_centre', 'directeur_centre') DEFAULT 'assistant_rh'");
        DB::statement("UPDATE users SET role = 'drh_centre' WHERE role = 'drh'");

        Schema::table('personnels', function (Blueprint $table) {
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropColumn('centre_id');
        });

        Schema::table('personnels', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropColumn('centre_id');
        });

        DB::statement("UPDATE users SET role = 'drh' WHERE role = 'drh_centre'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('assistant_rh', 'drh') DEFAULT 'assistant_rh'");

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropColumn('centre_id');
        });
    }
};
