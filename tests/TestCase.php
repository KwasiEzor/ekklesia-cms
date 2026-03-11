<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Globally bypass authorization for all tests to isolate functionality vs auth logic
        // Skip bypassing for RbacTest because it explicitly asserts role permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (str_contains(static::class, 'RbacTest')) {
                return null; // Let real Spatie checks run
            }

            return true;
        });
    }

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
