<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_ddis_and_ddrh_are_read_only(): void
    {
        $ddis = new User(['role' => 'ddis']);
        $ddrh = new User(['role' => 'ddrh']);

        $this->assertTrue($ddis->isReadOnly());
        $this->assertFalse($ddis->canApprove());

        $this->assertTrue($ddrh->isReadOnly());
        $this->assertFalse($ddrh->canApprove());
    }

    public function test_crh_is_global_and_can_approve(): void
    {
        $crh = new User(['role' => 'crh']);

        $this->assertTrue($crh->isGlobal());
        $this->assertTrue($crh->canApprove());
    }
}
