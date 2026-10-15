<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->string('mode_selection', 20)->default('plage')->after('type_absence');
            $table->json('jours_specifiques')->nullable()->after('nb_jours');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn(['jours_specifiques', 'mode_selection']);
        });
    }
};
