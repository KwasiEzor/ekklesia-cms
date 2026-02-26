<?php

use App\Events\ContentChanged;
use App\Models\Sermon;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\ContentChangedNotification;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;

test('creating a sermon dispatches ContentChanged event', function () {
    EventFacade::fake([ContentChanged::class]);

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Sermon::factory()->create(['tenant_id' => $tenant->id]);

    EventFacade::assertDispatched(ContentChanged::class, function ($event) {
        return $event->contentType === 'Sermon' && $event->action === 'created';
    });
});

test('updating a sermon dispatches ContentChanged event', function () {
    EventFacade::fake([ContentChanged::class]);

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);

    EventFacade::assertDispatched(ContentChanged::class, function ($event) {
        return $event->action === 'created';
    });

    $sermon->update(['title' => 'Updated']);

    EventFacade::assertDispatched(ContentChanged::class, function ($event) {
        return $event->action === 'updated';
    });
});

test('deleting an event dispatches ContentChanged event', function () {
    EventFacade::fake([ContentChanged::class]);

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create(['tenant_id' => $tenant->id]);
    $event->delete();

    EventFacade::assertDispatched(ContentChanged::class, function ($e) {
        return $e->contentType === 'Event' && $e->action === 'deleted';
    });
});

test('ContentChanged broadcasts on tenant private channel', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);

    $event = new ContentChanged($sermon, 'created', 'Pasteur Jean');

    $channels = $event->broadcastOn();
    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-tenant.{$tenant->id}");
    expect($event->broadcastAs())->toBe('content.changed');
});

test('ContentChanged payload includes correct data', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'La foi qui déplace les montagnes',
    ]);

    $event = new ContentChanged($sermon, 'created', 'Pasteur Jean');
    $payload = $event->broadcastWith();

    expect($payload)
        ->toHaveKey('content_type', 'Sermon')
        ->toHaveKey('action', 'created')
        ->toHaveKey('content_id', $sermon->id)
        ->toHaveKey('content_title', 'La foi qui déplace les montagnes')
        ->toHaveKey('changed_by', 'Pasteur Jean')
        ->toHaveKey('timestamp');
});

test('other tenant admins receive notification on content change', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Admin One']);
    $admin2 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Admin Two']);

    // Simulate admin1 creating a sermon — dispatch the event manually
    $sermon = Sermon::withoutEvents(function () use ($tenant) {
        return Sermon::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'test-sermon']);
    });

    $event = new ContentChanged($sermon, 'created', 'Admin One');
    (new \App\Listeners\NotifyTenantAdmins())->handle($event);

    // Admin2 should be notified, Admin1 (the author) should not
    Notification::assertSentTo($admin2, ContentChangedNotification::class);
    Notification::assertNotSentTo($admin1, ContentChangedNotification::class);
});

test('admins from other tenants do not receive notification', function () {
    Notification::fake();

    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $admin1 = User::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $admin2 = User::factory()->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);
    $sermon = Sermon::withoutEvents(function () use ($tenant1) {
        return Sermon::factory()->create(['tenant_id' => $tenant1->id, 'slug' => 'test-sermon']);
    });

    $event = new ContentChanged($sermon, 'created', null);
    (new \App\Listeners\NotifyTenantAdmins())->handle($event);

    Notification::assertSentTo($admin1, ContentChangedNotification::class);
    Notification::assertNotSentTo($admin2, ContentChangedNotification::class);
});
