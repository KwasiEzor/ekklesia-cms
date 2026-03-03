<?php

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Tenant;
use App\Models\User;

test('ai conversation casts metadata as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'metadata' => ['key' => 'value'],
    ]);

    expect($conversation->fresh()->metadata)->toBe(['key' => 'value']);
});

test('ai conversation hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    expect($conversation->toArray())->not->toHaveKey('tenant_id');
});

test('ai conversation belongs to user', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    expect($conversation->user->id)->toBe($user->id);
});

test('ai conversation has many messages', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    AiMessage::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'ai_conversation_id' => $conversation->id,
    ]);

    expect($conversation->messages)->toHaveCount(3);
});

test('ai conversation defaults locale to fr', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    expect($conversation->locale)->toBe('fr');
});

test('ai message belongs to conversation', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    $message = AiMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'ai_conversation_id' => $conversation->id,
    ]);

    expect($message->conversation->id)->toBe($conversation->id);
});

test('ai message casts tokens as integers', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    $message = AiMessage::factory()->create([
        'tenant_id' => $tenant->id,
        'ai_conversation_id' => $conversation->id,
        'tokens_input' => 100,
        'tokens_output' => 200,
    ]);

    $fresh = $message->fresh();
    expect($fresh->tokens_input)->toBeInt()->toBe(100);
    expect($fresh->tokens_output)->toBeInt()->toBe(200);
});

test('deleting conversation cascades to messages', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $conversation = AiConversation::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);

    AiMessage::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'ai_conversation_id' => $conversation->id,
    ]);

    $conversation->delete();

    expect(AiMessage::where('ai_conversation_id', $conversation->id)->count())->toBe(0);
});
