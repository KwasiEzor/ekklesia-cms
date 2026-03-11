<?php

use App\Jobs\SendBulkMessageJob;
use App\Models\BulkMessage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('unauthenticated request to bulk-messages returns 401', function () {
    $this->getJson('/api/v1/bulk-messages')
        ->assertUnauthorized();
});

test('authenticated user can list bulk messages', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    BulkMessage::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/bulk-messages')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'body', 'channel', 'target_type', 'status'],
            ],
            'links',
            'meta',
        ]);
});

test('bulk messages response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/bulk-messages')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a bulk message as draft', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/bulk-messages', [
            'title' => 'Rappel culte',
            'body' => 'Rappel : culte du dimanche à 9h. Venez nombreux !',
            'channel' => 'sms',
            'target_type' => 'all',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Rappel culte')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.channel', 'sms');
});

test('authenticated user can create a scheduled bulk message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $scheduledAt = now()->addHours(3)->toISOString();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/bulk-messages', [
            'title' => 'Message programmé',
            'body' => 'Ce message sera envoyé plus tard.',
            'channel' => 'email',
            'target_type' => 'all',
            'scheduled_at' => $scheduledAt,
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'scheduled');
});

test('authenticated user can view a bulk message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/bulk-messages/{$message->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $message->id);
});

test('authenticated user can delete a draft bulk message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/bulk-messages/{$message->id}")
        ->assertNoContent();

    expect(BulkMessage::find($message->id))->toBeNull();
});

test('cannot delete a sent bulk message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/bulk-messages/{$message->id}")
        ->assertUnprocessable();

    expect(BulkMessage::find($message->id))->not->toBeNull();
});

test('cannot delete a sending bulk message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->sending()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/bulk-messages/{$message->id}")
        ->assertUnprocessable();
});

test('send action dispatches job for draft message', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/bulk-messages/{$message->id}/send")
        ->assertSuccessful();

    Queue::assertPushed(SendBulkMessageJob::class, fn ($job) => $job->bulkMessage->id === $message->id);
});

test('send action dispatches job for scheduled message', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/bulk-messages/{$message->id}/send")
        ->assertSuccessful();

    Queue::assertPushed(SendBulkMessageJob::class);
});

test('send action rejects already sent message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/bulk-messages/{$message->id}/send")
        ->assertUnprocessable();
});

test('send action rejects sending message', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->sending()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/bulk-messages/{$message->id}/send")
        ->assertUnprocessable();
});

test('bulk messages can be filtered by status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/bulk-messages?status=draft')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('bulk messages can be filtered by channel', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'channel' => 'sms']);
    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'channel' => 'email']);
    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'channel' => 'sms']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/bulk-messages?channel=sms')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('bulk messages pagination works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    BulkMessage::factory()->count(5)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/bulk-messages?per_page=2')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2)
        ->and($response->json('meta.total'))->toBe(5);
});

test('creating a bulk message with invalid channel returns validation error', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/bulk-messages', [
            'title' => 'Test',
            'body' => 'Test body',
            'channel' => 'pigeon',
            'target_type' => 'all',
        ])
        ->assertUnprocessable();
});

test('creating a bulk message with invalid target_type returns validation error', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/bulk-messages', [
            'title' => 'Test',
            'body' => 'Test body',
            'channel' => 'sms',
            'target_type' => 'invalid',
        ])
        ->assertUnprocessable();
});

test('update endpoint is not available for bulk messages', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/bulk-messages/{$message->id}", [
            'title' => 'Updated',
        ])
        ->assertStatus(405);
});
