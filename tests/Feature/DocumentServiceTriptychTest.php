<?php

namespace Tests\Feature;

use App\Models\Centre;
use App\Models\ConfigRh;
use App\Models\Personnel;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentServiceTriptychTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_the_signatory_from_the_personnels_centre_not_the_authenticated_user(): void
    {
        Storage::fake('public');

        $centre = Centre::create([
            'nom' => 'Centre X',
            'code' => 'CENTRE_X',
            'email' => 'centre-x@example.test',
            'a_drh_dedie' => true,
            'actif' => true,
        ]);

        $personnel = Personnel::create([
            'nom' => 'DOE',
            'prenoms' => 'John',
            'sexe' => 'M',
            'statut' => 'actif',
            'centre_id' => $centre->id,
        ]);

        $signaturePath = 'signatures/centre-x-triptych.png';
        Storage::disk('public')->put($signaturePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2qfKQAAAAASUVORK5CYII='));

        $authUser = User::create([
            'nom' => 'GLOBAL',
            'prenoms' => 'Admin',
            'sexe' => 'M',
            'email' => 'global@example.test',
            'telephone' => '90000000',
            'role' => 'assistant_rh',
            'password' => 'password',
        ]);

        ConfigRh::set('drh_nom', 'Abbé Pierre BOULE', $authUser);
        ConfigRh::set('drh_titre', 'Titre global qui ne doit pas sortir', $authUser);
        ConfigRh::set('drh_signature_path', 'signatures/global-signature.png', $authUser);

        ConfigRh::create([
            'cle' => 'drh_nom',
            'valeur' => 'Abbé Pierre BOULE',
            'centre_id' => $centre->id,
            'user_id' => null,
        ]);

        ConfigRh::create([
            'cle' => 'drh_titre',
            'valeur' => 'Directeur des Ressources Humaines',
            'centre_id' => $centre->id,
            'user_id' => null,
        ]);

        ConfigRh::create([
            'cle' => 'drh_signature_path',
            'valeur' => $signaturePath,
            'centre_id' => $centre->id,
            'user_id' => null,
        ]);

        $this->actingAs($authUser);

        $service = new DocumentService();
        $info = $service->resolveDrhCentre($personnel);

        $this->assertSame('Abbé Pierre BOULE', $info['nom']);
        $this->assertSame('Directeur des Ressources Humaines', $info['titre']);
        $this->assertIsString($info['signature_url']);
        $this->assertStringStartsWith('data:image/png;base64,', $info['signature_url']);
        $this->assertNotSame('Titre global qui ne doit pas sortir', $info['titre']);
    }
}
