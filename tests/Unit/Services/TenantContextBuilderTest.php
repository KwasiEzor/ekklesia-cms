<?php

use App\Models\Sermon;
use App\Models\Tenant;
use App\Services\Ai\SkillRegistry;
use App\Services\TenantContextBuilder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

test('context builder includes tenant name and pastor', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Église Test',
    ]);
    // stancl VirtualColumn: non-custom attrs stored in data JSON
    $tenant->pastor_name = 'Pasteur Jean';
    $tenant->save();
    tenancy()->initialize($tenant->fresh());

    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('fr');

    expect($prompt)->toContain('Église Test');
    expect($prompt)->toContain('Pasteur Jean');
});

test('context builder includes aggregate stats', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Sermon::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'slug' => fn () => fake()->unique()->slug(),
    ]);

    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('fr');

    expect($prompt)->toContain('Prédications : 3');
});

test('context builder never includes PII', function () {
    $tenant = Tenant::factory()->create();
    $tenant->phone = '+33612345678';
    $tenant->email = 'test@example.com';
    $tenant->save();
    tenancy()->initialize($tenant->fresh());

    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('fr');

    expect($prompt)->not->toContain('+33612345678');
    expect($prompt)->not->toContain('test@example.com');
});

test('context builder includes skills list', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('fr');

    expect($prompt)->toContain('/sermon-outline');
    expect($prompt)->toContain('/translate');
});

test('context builder supports english locale', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('en');

    expect($prompt)->toContain('AI assistant');
    expect($prompt)->toContain('NEVER reveal');
});

test('context builder works without tenant', function () {
    $builder = new TenantContextBuilder(app(SkillRegistry::class));
    $prompt = $builder->buildSystemPrompt('fr');

    expect($prompt)->toContain('Ekklesia CMS');
});
