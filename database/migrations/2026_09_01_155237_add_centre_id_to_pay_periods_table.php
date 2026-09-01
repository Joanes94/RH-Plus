<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->foreignId('centre_id')->nullable()->after('id')->constrained('centres')->cascadeOnDelete();
        });

        // Tenter d'attribuer le centre_id aux périodes existantes
        $periods = DB::table('pay_periods')->get();
        foreach ($periods as $p) {
            $centreId = null;
            if ($p->created_by) {
                $user = DB::table('users')->where('id', $p->created_by)->first();
                $centreId = $user?->centre_id;
            }
            if (!$centreId) {
                // Chercher dans pay_slips
                $slip = DB::table('pay_slips')->where('pay_period_id', $p->id)->first();
                $centreId = $slip?->centre_id;
            }
            if (!$centreId) {
                $firstCentre = DB::table('centres')->first();
                $centreId = $firstCentre?->id;
            }

            if ($centreId) {
                DB::table('pay_periods')->where('id', $p->id)->update(['centre_id' => $centreId]);
            }
        }

        // Supprimer la contrainte unique sur code seul et ajouter l'unicité par (code, centre_id)
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropUnique('pay_periods_code_unique');
            $table->unique(['code', 'centre_id'], 'pay_periods_code_centre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropUnique('pay_periods_code_centre_unique');
            $table->unique('code', 'pay_periods_code_unique');
            $table->dropForeign(['centre_id']);
            $table->dropColumn('centre_id');
        });
    }
};

