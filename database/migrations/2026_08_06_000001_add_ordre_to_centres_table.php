<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            if (!Schema::hasColumn('centres', 'ordre')) {
                $table->unsignedInteger('ordre')->nullable()->after('actif');
            }
        });

        // Initialiser l'ordre selon l'id courant (ordre naturel par défaut)
        DB::statement('UPDATE centres SET ordre = id WHERE ordre IS NULL');
    }

    public function down(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            $table->dropColumn('ordre');
        });
    }
};
