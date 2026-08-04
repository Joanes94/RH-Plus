<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE personnels p
             INNER JOIN centres c ON p.affectation = c.nom
             SET p.centre_id = c.id
             WHERE p.centre_id IS NULL'
        );

        DB::statement(
            'UPDATE contrats ct
             INNER JOIN personnels p ON ct.personnel_id = p.id
             SET ct.centre_id = p.centre_id
             WHERE ct.centre_id IS NULL AND p.centre_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        // On ne retire pas les correspondances historiques.
    }
};