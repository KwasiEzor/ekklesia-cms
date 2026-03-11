<?php

use App\Jobs\SendBulkMessageJob;
use App\Models\BulkMessage;
use App\Models\Member;
use App\Models\NotificationDispatch;
use App\Models\Tenant;
use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationChannelManager;
use Illuminate\Support\Facades\Queue;

test('SendBulkMessageJob marks message as sending then sent', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'phone' => '+237600000000',
    ]);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'channel' => 'sms',
        'target_type' => 'all',
    ]);

    $mockChannel = Mockery::mock(NotificationChannelInterface::class);
    $mockChannel->shouldReceive('send')->andReturn(true);

    $mockManager = Mockery::mock(NotificationChannelManager::class);
    $mockManager->shouldReceive('driver')->with('sms')->andReturn($mockChannel);

    app()->instance(NotificationChannelManager::class, $mockManager);

    $job = new SendBulkMessageJob($message);
    $job->handle($mockManager);

    $fresh = $message->fresh();
    expect($fresh->status)->toBe('sent')
        ->and($fresh->sent_at)->not->toBeNull()
        ->and($fresh->recipient_count)->toBe(2)
        ->and($fresh->sent_count)->toBe(2)
        ->and($fresh->failed_count)->toBe(0);
});

test('SendBulkMessageJob creates notification dispatch records', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'phone' => '+237600000000',
    ]);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'channel' => 'sms',
        'target_type' => 'all',
    ]);

    $mockChannel = Mockery::mock(NotificationChannelInterface::class);
    $mockChannel->shouldReceive('send')->andReturn(true);

    $mockManager = Mockery::mock(NotificationChannelManager::class);
    $mockManager->shouldReceive('driver')->with('sms')->andReturn($mockChannel);

    $job = new SendBulkMessageJob($message);
    $job->handle($mockManager);

    expect(NotificationDispatch::where('type', 'bulk_message')->count())->toBe(2);
});

test('SendBulkMessageJob counts failures when channel throws', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'phone' => '+237600000000',
    ]);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'channel' => 'sms',
        'target_type' => 'all',
    ]);

    $callCount = 0;
    $mockChannel = Mockery::mock(NotificationChannelInterface::class);
    $mockChannel->shouldReceive('send')->andReturnUsing(function () use (&$callCount): bool {
        $callCount++;
        if ($callCount === 2) {
            throw new \RuntimeException('SMS provider error');
        }

        return true;
    });

    $mockManager = Mockery::mock(NotificationChannelManager::class);
    $mockManager->shouldReceive('driver')->with('sms')->andReturn($mockChannel);

    $job = new SendBulkMessageJob($message);
    $job->handle($mockManager);

    $fresh = $message->fresh();
    expect($fresh->status)->toBe('sent')
        ->and($fresh->sent_count)->toBe(2)
        ->and($fresh->failed_count)->toBe(1);

    // Failed dispatch should have failure_reason
    expect(NotificationDispatch::where('status', 'failed')->count())->toBe(1);
});

test('SendBulkMessageJob skips members without contact info', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    // SMS message but member has email only (no phone)
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'phone' => '+237600000000',
    ]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'phone' => null,
        'email' => 'test@example.com',
    ]);

    $message = BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'channel' => 'sms',
        'target_type' => 'all',
    ]);

    $mockChannel = Mockery::mock(NotificationChannelInterface::class);
    $mockChannel->shouldReceive('send')->once()->andReturn(true);

    $mockManager = Mockery::mock(NotificationChannelManager::class);
    $mockManager->shouldReceive('driver')->with('sms')->andReturn($mockChannel);

    $job = new SendBulkMessageJob($message);
    $job->handle($mockManager);

    // getRecipients already filters for phone != null, so only 1 recipient
    $fresh = $message->fresh();
    expect($fresh->recipient_count)->toBe(1)
        ->and($fresh->sent_count)->toBe(1)
        ->and($fresh->failed_count)->toBe(0);
});

test('SendScheduledBulkMessages command dispatches jobs for due messages', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    // Ready to send (scheduled, past due)
    BulkMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinutes(10),
    ]);

    // Not yet due
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    // Draft (not scheduled)
    BulkMessage::factory()->create(['tenant_id' => $tenant->id]);

    tenancy()->end();

    $this->artisan('bulk-messages:send-scheduled')
        ->assertSuccessful();

    Queue::assertPushed(SendBulkMessageJob::class, 1);
});

test('SendScheduledBulkMessages command processes multiple tenants', function () {
    Queue::fake();

    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    BulkMessage::factory()->create([
        'tenant_id' => $tenant1->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinutes(5),
    ]);
    tenancy()->end();

    tenancy()->initialize($tenant2);
    BulkMessage::factory()->create([
        'tenant_id' => $tenant2->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinutes(5),
    ]);
    BulkMessage::factory()->create([
        'tenant_id' => $tenant2->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinutes(3),
    ]);
    tenancy()->end();

    $this->artisan('bulk-messages:send-scheduled')
        ->assertSuccessful();

    Queue::assertPushed(SendBulkMessageJob::class, 3);
});

test('SendScheduledBulkMessages command does nothing when no messages are due', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    BulkMessage::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    BulkMessage::factory()->scheduled()->create(['tenant_id' => $tenant->id]);

    tenancy()->end();

    $this->artisan('bulk-messages:send-scheduled')
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatched 0 scheduled bulk messages');

    Queue::assertNothingPushed();
});
