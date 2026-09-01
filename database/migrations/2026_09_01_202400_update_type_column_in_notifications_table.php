<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN `type` VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN `type` ENUM('echelon', 'bonification', 'digest_mensuel', 'affectation', 'transfert', 'fin_stage') NOT NULL");
    }
};