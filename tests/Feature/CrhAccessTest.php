<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrhAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_crh_can_access_drh_dashboard_and_approve_permissions(): void
    {
        $user = User::factory()->create([
            'role' => 'crh',
            'centre_id' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('drh.dashboard'));

        $response->assertStatus(200);
        $this->assertTrue($user->canApprove());
        $this->assertTrue($user->isCRH());
    }
}
