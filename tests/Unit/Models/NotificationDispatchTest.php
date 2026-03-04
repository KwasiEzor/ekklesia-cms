<?php

use App\Models\Member;
use App\Models\NotificationDispatch;
use App\Models\Tenant;

test('notification dispatch casts metadata as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create([
        'tenant_id' => $tenant->id,
        'metadata' => ['key' => 'value'],
    ]);

    expect($dispatch->fresh()->metadata)->toBe(['key' => 'value']);
});

test('notification dispatch casts timestamps', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->sent()->create([
        'tenant_id' => $tenant->id,
    ]);

    expect($dispatch->sent_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('notification dispatch hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create(['tenant_id' => $tenant->id]);
    $array = $dispatch->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('notification dispatch belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $dispatch = NotificationDispatch::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($dispatch->member->id)->toBe($member->id);
});

test('mark as sent sets status and sent_at', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $dispatch->markAsSent();
    $dispatch->refresh();

    expect($dispatch->status)->toBe('sent')
        ->and($dispatch->sent_at)->not->toBeNull();
});

test('mark as delivered sets status and delivered_at', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'sent',
    ]);

    $dispatch->markAsDelivered();
    $dispatch->refresh();

    expect($dispatch->status)->toBe('delivered')
        ->and($dispatch->delivered_at)->not->toBeNull();
});

test('mark as failed sets status and failure reason', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $dispatch->markAsFailed('Connection timeout');
    $dispatch->refresh();

    expect($dispatch->status)->toBe('failed')
        ->and($dispatch->failed_at)->not->toBeNull()
        ->and($dispatch->failure_reason)->toBe('Connection timeout');
});

test('notification dispatch factory creates valid model', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $dispatch = NotificationDispatch::factory()->create(['tenant_id' => $tenant->id]);

    expect($dispatch)->toBeInstanceOf(NotificationDispatch::class)
        ->and($dispatch->channel)->toBeIn(['email', 'sms', 'whatsapp', 'telegram'])
        ->and($dispatch->type)->toBeIn(['welcome', 'giving_receipt', 'event_reminder', 'announcement', 'birthday'])
        ->and($dispatch->status)->toBe('pending');
});
