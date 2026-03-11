<?php

use App\Models\Member;
use App\Models\PrayerRequest;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to prayer requests returns 401', function () {
    $this->getJson('/api/v1/prayer-requests')
        ->assertUnauthorized();
});

test('authenticated user can list prayer requests', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    PrayerRequest::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/prayer-requests')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'title', 'content', 'status', 'prayer_count'],
            ],
            'links',
            'meta',
        ]);
});

test('prayer requests response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/prayer-requests')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('anonymous prayer request hides member info', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    PrayerRequest::factory()->anonymous()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/prayer-requests')
        ->assertOk();

    expect($response->json('data.0.is_anonymous'))->toBeTrue()
        ->and($response->json('data.0'))->not->toHaveKey('member');
});

test('authenticated user can create a prayer request', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/prayer-requests', [
            'member_id' => $member->id,
            'type' => 'prayer',
            'visibility' => 'public',
            'title' => 'Prière pour ma guérison',
            'content' => 'Je demande la prière pour ma santé',
            'category' => 'guérison',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Prière pour ma guérison')
        ->assertJsonPath('data.type', 'prayer');
});

test('authenticated user can commit to pray', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/prayer-requests/{$prayer->id}/commit", [
            'member_id' => $member->id,
        ])
        ->assertOk()
        ->assertJsonPath('prayer_count', 1);
});

test('prayer requests can be filtered by type', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'type' => 'prayer',
    ]);
    PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'type' => 'praise',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/prayer-requests?type=prayer')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('authenticated user can mark prayer as answered', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/prayer-requests/{$prayer->id}", [
            'status' => 'answered',
            'testimony' => 'Gloire à Dieu, je suis guéri !',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'answered')
        ->assertJsonPath('data.testimony', 'Gloire à Dieu, je suis guéri !');
});

test('authenticated user can delete prayer request', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/prayer-requests/{$prayer->id}")
        ->assertNoContent();

    expect(PrayerRequest::find($prayer->id))->toBeNull();
});
