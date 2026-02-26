<?php

use App\Events\ContentChanged;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// Fake ContentChanged to prevent the observer/listener from resetting tenant context
beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('unauthenticated request to giving-records returns 401', function () {
    $this->getJson('/api/v1/giving-records')
        ->assertUnauthorized();
});

test('authenticated user can list giving records', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'member_id', 'amount', 'currency', 'formatted_amount', 'date', 'method', 'reference', 'is_anonymous'],
            ],
            'links',
            'meta',
        ]);
});

test('giving records response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a giving record', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/giving-records', [
            'member_id' => $member->id,
            'amount' => 25000,
            'currency' => 'XOF',
            'date' => '2026-03-01',
            'method' => 'mobile_money',
            'reference' => 'MTN123456',
        ])
        ->assertCreated()
        ->assertJsonPath('data.amount', '25000.00')
        ->assertJsonPath('data.currency', 'XOF')
        ->assertJsonPath('data.method', 'mobile_money')
        ->assertJsonPath('data.is_anonymous', false);
});

test('authenticated user can create an anonymous giving record', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/giving-records', [
            'amount' => 10000,
            'currency' => 'XOF',
            'date' => '2026-03-01',
            'method' => 'cash',
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_anonymous', true)
        ->assertJsonPath('data.member_id', null);
});

test('authenticated user can view a giving record', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $record = GivingRecord::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/giving-records/{$record->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $record->id);
});

test('authenticated user can update a giving record', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'amount' => 10000,
        'currency' => 'XOF',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/giving-records/{$record->id}", [
            'amount' => 50000,
        ])
        ->assertOk()
        ->assertJsonPath('data.amount', '50000.00');
});

test('authenticated user can delete a giving record', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $record = GivingRecord::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/giving-records/{$record->id}")
        ->assertNoContent();

    expect(GivingRecord::find($record->id))->toBeNull();
});

test('giving records list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('giving records can be filtered by method', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->mobileMoney()->count(2)->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->cash()->count(3)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records?method=mobile_money')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('giving records can be filtered by currency', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->inCurrency('XOF')->count(3)->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->inCurrency('EUR')->count(2)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records?currency=XOF')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});

test('giving records can be filtered by date range', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-01-15']);
    GivingRecord::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-02-15']);
    GivingRecord::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-03-15']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records?from=2026-02-01&to=2026-02-28')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('giving record method must be valid', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/giving-records', [
            'amount' => 5000,
            'currency' => 'XOF',
            'date' => '2026-03-01',
            'method' => 'bitcoin',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('method');
});

test('giving records can be filtered by anonymous', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->anonymous()->count(2)->create(['tenant_id' => $tenant->id]);
    GivingRecord::factory()->create(['tenant_id' => $tenant->id, 'member_id' => $member->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/giving-records?anonymous=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});
