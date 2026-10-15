<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Personnel;

return new class extends Migration
{
    /**
     * Nettoie et normalise les libellés de corporation existants en base de données
     * pour éliminer les doublons orthographiques et typographiques.
     */
    public function up(): void
    {
        $personnels = DB::table('personnels')->whereNotNull('corporation')->get();
        foreach ($personnels as $p) {
            $normalized = Personnel::normaliserCorporation($p->corporation);
            if ($normalized && $normalized !== $p->corporation) {
                DB::table('personnels')->where('id', $p->id)->update([
                    'corporation' => $normalized,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Pas de retour en arrière nécessaire pour une normalisation
    }
};
