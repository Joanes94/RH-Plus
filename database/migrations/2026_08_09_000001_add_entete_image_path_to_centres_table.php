<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            if (!Schema::hasColumn('centres', 'entete_image_path')) {
                $table->string('entete_image_path')->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            if (Schema::hasColumn('centres', 'entete_image_path')) {
                $table->dropColumn('entete_image_path');
            }
        });
    }
};
