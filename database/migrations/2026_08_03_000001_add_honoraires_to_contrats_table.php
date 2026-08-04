<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            if (!Schema::hasColumn('contrats', 'honoraire_garde')) {
                $table->decimal('honoraire_garde', 12, 2)->nullable()->after('salaire_base');
            }

            if (!Schema::hasColumn('contrats', 'honoraire_permanence')) {
                $table->decimal('honoraire_permanence', 12, 2)->nullable()->after('honoraire_garde');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            if (Schema::hasColumn('contrats', 'honoraire_permanence')) {
                $table->dropColumn('honoraire_permanence');
            }

            if (Schema::hasColumn('contrats', 'honoraire_garde')) {
                $table->dropColumn('honoraire_garde');
            }
        });
    }
};