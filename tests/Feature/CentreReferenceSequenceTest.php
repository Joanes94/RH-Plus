<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Centre;
use App\Models\Conge;
use App\Models\Personnel;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentreReferenceSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_centre_has_its_own_reference_sequence(): void
    {
        $centreA = Centre::create([
            'nom' => 'Centre A',
            'code' => 'CENTRE_A',
            'a_drh_dedie' => true,
            'actif' => true,
        ]);

        $centreB = Centre::create([
            'nom' => 'Centre B',
            'code' => 'CENTRE_B',
            'a_drh_dedie' => false,
            'actif' => true,
        ]);

        $personnelA = Personnel::create([
            'nom' => 'A',
            'prenoms' => 'Alpha',
            'sexe' => 'M',
            'statut' => 'actif',
            'centre_id' => $centreA->id,
        ]);

        $personnelB = Personnel::create([
            'nom' => 'B',
            'prenoms' => 'Beta',
            'sexe' => 'F',
            'statut' => 'actif',
            'centre_id' => $centreB->id,
        ]);

        $service = app(DocumentService::class);
        $period = now()->format('m-y');

        $refA1 = $service->generateMonthlyReference(null, null, $centreA->id);
        $this->assertSame('O001/' . $period . '/AC/DDIS/CSVHHSL/DIR/DRH/ARH', $refA1);

        Conge::create([
            'personnel_id' => $personnelA->id,
            'type_conge' => 'administratif',
            'date_debut' => now()->startOfMonth()->toDateString(),
            'nb_jours_demandes' => 2,
            'date_fin' => now()->startOfMonth()->addDay()->toDateString(),
            'nb_jours_acquis' => 24,
            'nb_jours_deja_pris' => 0,
            'nb_jours_restants' => 22,
            'annee' => now()->year . '-' . (now()->year + 1),
            'statut' => 'approuve',
            'reference' => $refA1,
        ]);

        $refA2 = $service->generateMonthlyReference(null, null, $centreA->id);
        $this->assertSame('O002/' . $period . '/AC/DDIS/CSVHHSL/DIR/DRH/ARH', $refA2);

        Absence::create([
            'personnel_id' => $personnelA->id,
            'type_absence' => 'deductible',
            'deductible' => true,
            'date_debut' => now()->startOfMonth()->addDays(2)->toDateString(),
            'date_fin' => now()->startOfMonth()->addDays(2)->toDateString(),
            'nb_jours' => 1,
            'statut' => 'approuve',
            'reference' => $refA2,
        ]);

        $refA3 = $service->generateMonthlyReference(null, null, $centreA->id);
        $this->assertSame('O003/' . $period . '/AC/DDIS/CSVHHSL/DIR/DRH/ARH', $refA3);

        $refB1 = $service->generateMonthlyReference(null, null, $centreB->id);
        $this->assertSame('O001/' . $period . '/AC/DDIS/CSVHHSL/DIR/DRH/ARH', $refB1);
    }
}
