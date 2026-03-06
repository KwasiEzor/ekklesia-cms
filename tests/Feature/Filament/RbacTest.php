<?php

namespace Tests\Feature\Filament;

use App\Models\GivingRecord;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_volunteer_cannot_access_giving_records_via_api(): void
    {
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
        setPermissionsTeamId($tenant->id);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('volunteer');

        GivingRecord::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/giving-records')
            ->assertForbidden();
    }

    public function test_treasurer_can_access_giving_records_via_api(): void
    {
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
        setPermissionsTeamId($tenant->id);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('treasurer');

        GivingRecord::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/giving-records')
            ->assertOk();
    }

    public function test_volunteer_can_view_sermons_via_api(): void
    {
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
        setPermissionsTeamId($tenant->id);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('volunteer');

        \App\Models\Sermon::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/sermons')
            ->assertOk();
    }

    public function test_volunteer_cannot_create_sermons_via_api(): void
    {
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
        setPermissionsTeamId($tenant->id);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('volunteer');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/sermons', [
                'title' => 'New Sermon',
                'slug' => 'new-sermon',
                'speaker' => 'Pastor John',
                'date' => '2026-03-01',
            ])
            ->assertForbidden();
    }
}
