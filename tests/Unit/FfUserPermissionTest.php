<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class FfUserPermissionTest extends TestCase
{
    public function test_has_ff_permission_returns_false_if_permissions_null()
    {
        $user = new User();
        $this->assertFalse($user->hasFfPermission('any'));
    }

    public function test_has_ff_permission_logic()
    {
        $user = new User();
        $user->ff_granular_permissions = ['admin.view'];
        
        $this->assertTrue($user->hasFfPermission('admin.view'));
        $this->assertFalse($user->hasFfPermission('admin.manage_clients'));
    }
}
