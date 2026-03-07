<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsSuperAdmin($user = null, $tenant = null)
    {
        if (! $tenant) {
            $tenant = \App\Models\Tenant::factory()->create();
        }

        if (! $user) {
            $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        }

        tenancy()->initialize($tenant);
        
        // Ensure super_admin role exists globally
        setPermissionsTeamId(null);
        \App\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web', 'team_id' => null]);
        
        // Assign to user for this tenant
        setPermissionsTeamId($tenant->id);
        $user->assignRole('super_admin');

        return $this->actingAs($user, 'sanctum');
    }
}
