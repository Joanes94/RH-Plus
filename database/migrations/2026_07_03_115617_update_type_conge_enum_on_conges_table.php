<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE conges
            MODIFY COLUMN type_conge ENUM(
                'administratif',
                'technique',
                'maternite'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE conges
            MODIFY COLUMN type_conge ENUM(
                'administratif',
                'technique'
            ) NOT NULL
        ");
    }
};