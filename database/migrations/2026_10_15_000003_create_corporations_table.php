<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Personnel;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('corporations')) {
            Schema::create('corporations', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 191);
                $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['centre_id', 'nom']);
            });
        }

        // ── 1. Insertion de la liste de base commune à tous les centres (centre_id = null)
        $base = [
            'AGENT ADMINISTRATIF',
            'AGENT DE PHARMACIE',
            "AGENT D'ENTRETIEN",
            'AIDE-SOIGNANT',
            'AIDE-SOIGNANT PROMU',
            'ANESTHESISTE',
            'ASSISTANT DENTAIRE',
            'ASSISTANT LABORATOIRE',
            'ASSISTANT RH',
            'BIOLOGISTE MEDICAL',
            'CAISSIER',
            'CARDIOLOGUE',
            'CHAUFFEUR',
            'CHEF SERVICE FINANCIER ET COMPTABLE',
            'CHIRURGIEN',
            'CHIRURGIEN MAXILLO-FACIAL',
            'COMPTABLE',
            'CUISINIER',
            'DENTISTE',
            'DERMATOLOGUE',
            'DIRECTEUR',
            'DIRECTEUR DES RESSOURCES HUMAINES',
            'ENDOCRINOLOGUE',
            'GASTROLOGUE',
            'INFIRMIER',
            'KINESITHERAPEUTE',
            'MAGASINIER',
            'MEDECIN',
            'MEDECIN ANESTHESISTE REANIMATEUR',
            'MEDECIN CHEF',
            'NEPHROLOGUE',
            'NEUROCHIRURGIEN',
            'NEUROLOGUE',
            'OPHTALMOLOGUE',
            'ORL',
            'PSYCHIATRE',
            'RADIOLOGUE',
            'SAGE-FEMME',
            'SECRETAIRE ADMINISTRATIF',
            'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE MEDICAL',
            'STATISTICIEN',
            'SURVEILLANT',
            'TECHNICIEN DE LABORATOIRE',
            'TECHNICIEN EN IMAGERIE MEDICALE',
            'TECHNICIEN SUPERIEUR EN ODONTO',
            'TRAUMATOLOGUE',
            'UROLOGUE',
        ];

        $now = now();
        foreach ($base as $nom) {
            $exists = DB::table('corporations')->whereNull('centre_id')->where('nom', $nom)->exists();
            if (!$exists) {
                DB::table('corporations')->insert([
                    'nom'        => $nom,
                    'centre_id'  => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── 2. Nettoyage des doublons dans personnels et rattachement des corporations spécifiques à leur centre
        $personnels = DB::table('personnels')->whereNotNull('corporation')->get();
        foreach ($personnels as $p) {
            $normalized = Personnel::normaliserCorporation($p->corporation);
            if ($normalized && $normalized !== $p->corporation) {
                DB::table('personnels')->where('id', $p->id)->update(['corporation' => $normalized]);
            }

            // Si cette corporation n'est pas dans la liste de base commune, on l'associe spécifiquement à son centre
            if ($normalized && !in_array($normalized, $base, true) && $p->centre_id) {
                $existsForCentre = DB::table('corporations')
                    ->where('centre_id', $p->centre_id)
                    ->where('nom', $normalized)
                    ->exists();

                if (!$existsForCentre) {
                    DB::table('corporations')->insert([
                        'nom'        => $normalized,
                        'centre_id'  => $p->centre_id,
                        'created_by' => $p->created_by ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('corporations');
    }
};
