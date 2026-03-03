<?php

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Tenant;
use App\Models\User;

test('ai conversation belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant1->id,
        'user_id' => $user1->id,
    ]);

    tenancy()->initialize($tenant2);
    expect(AiConversation::find($conversation->id))->toBeNull();
});

test('ai conversation count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    AiConversation::factory()->count(3)->create([
        'tenant_id' => $tenant1->id,
        'user_id' => $user1->id,
    ]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    AiConversation::factory()->count(1)->create([
        'tenant_id' => $tenant2->id,
        'user_id' => $user2->id,
    ]);

    expect(AiConversation::count())->toBe(1);

    tenancy()->initialize($tenant1);
    expect(AiConversation::count())->toBe(3);
});

test('ai messages are isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $conversation1 = AiConversation::factory()->create([
        'tenant_id' => $tenant1->id,
        'user_id' => $user1->id,
    ]);
    AiMessage::factory()->count(5)->create([
        'tenant_id' => $tenant1->id,
        'ai_conversation_id' => $conversation1->id,
    ]);

    tenancy()->initialize($tenant2);
    expect(AiMessage::count())->toBe(0);
});

test('ai conversations cannot leak between tenants via user', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user = User::factory()->create(['tenant_id' => $tenant1->id]);
    AiConversation::factory()->create([
        'tenant_id' => $tenant1->id,
        'user_id' => $user->id,
        'title' => 'Secret Conversation',
    ]);

    tenancy()->initialize($tenant2);
    $conversations = AiConversation::where('user_id', $user->id)->get();
    expect($conversations)->toHaveCount(0);
});
