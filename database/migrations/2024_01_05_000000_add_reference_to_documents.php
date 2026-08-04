<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conges', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->after('id');
        });
        Schema::table('absences', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->after('id');
        });
        Schema::table('demandes', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('conges', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
