<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            if (!Schema::hasColumn('centres', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('email');
            }
            if (!Schema::hasColumn('centres', 'entete_texte')) {
                $table->text('entete_texte')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('centres', 'pied_page_texte')) {
                $table->text('pied_page_texte')->nullable()->after('entete_texte');
            }
            if (!Schema::hasColumn('centres', 'reference_suffix')) {
                $table->string('reference_suffix')->nullable()->after('pied_page_texte');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'entete_texte', 'pied_page_texte', 'reference_suffix']);
        });
    }
};
