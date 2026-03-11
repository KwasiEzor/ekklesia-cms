<?php

use App\Models\BulkMessage;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;

test('bulk message hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    expect($message->toArray())->not->toHaveKey('tenant_id');
});

test('bulk message casts scheduled_at as datetime', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    expect($message->scheduled_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('bulk message casts sent_at as datetime', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    expect($message->sent_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('bulk message casts counts as integers', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    expect($message->recipient_count)->toBeInt()
        ->and($message->sent_count)->toBeInt()
        ->and($message->failed_count)->toBeInt();
});

test('bulk message casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['key' => 'value'],
    ]);

    expect($message->fresh()->custom_fields)->toBeArray()
        ->and($message->fresh()->custom_fields['key'])->toBe('value');
});

test('bulk message belongs to creator', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'created_by' => $user->id,
    ]);

    expect($message->createdBy->id)->toBe($user->id);
});

test('bulk message scope draft works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    expect(BulkMessage::draft()->count())->toBe(1);
});

test('bulk message scope scheduled works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    expect(BulkMessage::scheduled()->count())->toBe(2);
});

test('bulk message scope sent works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->sent()->create(['tenant_id' => $tenant->id]);

    expect(BulkMessage::sent()->count())->toBe(1);
});

test('bulk message scope sending works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id]);
    BulkMessage::factory()->sending()->create(['tenant_id' => $tenant->id]);

    expect(BulkMessage::sending()->count())->toBe(1);
});

test('bulk message scope readyToSend returns scheduled messages due now', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    // Past scheduled_at → should be picked up
    BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinutes(5),
    ]);

    // Future scheduled_at → not yet ready
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    // Draft → not ready
    BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    expect(BulkMessage::readyToSend()->count())->toBe(1);
});

test('markAsSending changes status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->create(['tenant_id' => $tenant->id]);
    $message->markAsSending();

    expect($message->fresh()->status)->toBe('sending');
});

test('markAsSent updates status, sent_at, and counts', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->sending()->create(['tenant_id' => $tenant->id]);
    $message->markAsSent(45, 3);

    $fresh = $message->fresh();
    expect($fresh->status)->toBe('sent')
        ->and($fresh->sent_at)->not->toBeNull()
        ->and($fresh->sent_count)->toBe(45)
        ->and($fresh->failed_count)->toBe(3);
});

test('markAsFailed changes status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->sending()->create(['tenant_id' => $tenant->id]);
    $message->markAsFailed();

    expect($message->fresh()->status)->toBe('failed');
});

test('getRecipients returns all members with phone for target_type all', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->count(3)->create(['tenant_id' => $tenant->id, 'phone' => '+237600000000']);
    Member::factory()->create(['tenant_id' => $tenant->id, 'phone' => null]);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'target_type' => 'all',
    ]);

    expect($message->getRecipients())->toHaveCount(3);
});

test('getRecipients returns empty collection for unknown target_type', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'target_type' => 'unknown',
    ]);

    expect($message->getRecipients())->toHaveCount(0);
});
