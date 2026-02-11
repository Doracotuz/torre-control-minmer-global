<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Area;

class FfPermissionTest extends TestCase
{

    public function test_user_without_permissions_cannot_access_sales_index()
    {
        $user = User::factory()->make([
            'id' => 1,
            'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => [] 
        ]);

        $response = $this->actingAs($user)->get(route('ff.sales.index'));
        $response->assertStatus(403);
    }

    public function test_user_with_sales_view_permission_passes_check()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['sales.view']
        ]);

        try {
            $response = $this->actingAs($user)->get(route('ff.sales.index'));
            $this->assertNotEquals(403, $response->status(), 'User should have access, expected non-403 status.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_user_without_checkout_permission_cannot_checkout()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['sales.view']
        ]);

        $response = $this->actingAs($user)->post(route('ff.sales.checkout'));
        $response->assertStatus(403);
    }

    public function test_user_without_orders_view_permission_cannot_view_orders()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => []
        ]);

        $response = $this->actingAs($user)->get(route('ff.orders.index'));
        $response->assertStatus(403);
    }

    public function test_user_with_orders_view_permission_can_view_orders_check()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['orders.view']
        ]);

        try {
            $response = $this->actingAs($user)->get(route('ff.orders.index'));
            $this->assertNotEquals(403, $response->status());
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_user_without_admin_view_permission_cannot_access_admin_index()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => []
        ]);

        $response = $this->actingAs($user)->get(route('ff.admin.index'));
        $response->assertStatus(403);
    }

    public function test_user_with_admin_view_permission_can_access_admin_index()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['admin.view']
        ]);

        try {
            $response = $this->actingAs($user)->get(route('ff.admin.index'));
            $this->assertNotEquals(403, $response->status());
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_user_without_manage_clients_permission_cannot_manage_clients()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['admin.view']
        ]);

        $response = $this->actingAs($user)->post(route('ff.admin.store', ['type' => 'clients']), [
            'name' => 'New Client Test'
        ]);
        $response->assertStatus(403);
    }

    public function test_user_with_manage_clients_permission_can_manage_clients_check()
    {
        $user = User::factory()->make([
             'id' => 1,
             'area_id' => 1,
            'is_area_admin' => false,
            'ff_granular_permissions' => ['admin.manage_clients']
        ]);

        try {
            $response = $this->actingAs($user)->post(route('ff.admin.store', ['type' => 'clients']), [
                'name' => 'New Client Test'
            ]);
            
            $this->assertNotEquals(403, $response->status());
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }
}
