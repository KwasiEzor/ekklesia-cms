<?php

use App\Models\Member;
use App\Models\Tenant;
use App\Models\Testimony;

test('testimony hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    expect($testimony->toArray())->not->toHaveKey('tenant_id');
});

test('testimony belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $testimony = Testimony::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($testimony->member->id)->toBe($member->id);
});

test('testimony scope approved works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Testimony::factory()->create(['tenant_id' => $tenant->id, 'status' => 'submitted']);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);

    expect(Testimony::approved()->count())->toBe(2);
});

test('testimony scope pending works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Testimony::factory()->create(['tenant_id' => $tenant->id, 'status' => 'submitted']);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);

    expect(Testimony::pending()->count())->toBe(1);
});

test('testimony scope featured works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Testimony::factory()->featured()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->create(['tenant_id' => $tenant->id]);

    expect(Testimony::featured()->count())->toBe(1);
});

test('testimony scope ofCategory works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'healing']);
    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'healing']);
    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'provision']);

    expect(Testimony::ofCategory('healing')->count())->toBe(2);
});

test('testimony approve method works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $testimony->approve('Pasteur Marc');

    $fresh = $testimony->fresh();
    expect($fresh->status)->toBe('approved')
        ->and($fresh->approved_at)->not->toBeNull()
        ->and($fresh->approved_by)->toBe('Pasteur Marc');
});

test('testimony reject method works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $testimony->reject('Contenu inapproprié');

    $fresh = $testimony->fresh();
    expect($fresh->status)->toBe('rejected')
        ->and($fresh->rejection_reason)->toBe('Contenu inapproprié');
});

test('testimony addReaction works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $testimony->addReaction('amen');
    $testimony->addReaction('amen');
    $testimony->addReaction('gloire_a_dieu');

    $fresh = $testimony->fresh();
    expect($fresh->reactions['amen'])->toBe(2)
        ->and($fresh->reactions['gloire_a_dieu'])->toBe(1)
        ->and($fresh->reaction_count)->toBe(3);
});

test('testimony reaction_count accessor works with empty reactions', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    expect($testimony->reaction_count)->toBe(0);
});

test('testimony casts booleans correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $testimony = Testimony::factory()->create([
        'tenant_id' => $tenant->id,
        'is_anonymous' => true,
        'is_featured' => true,
    ]);

    $fresh = $testimony->fresh();
    expect($fresh->is_anonymous)->toBeTrue()
        ->and($fresh->is_featured)->toBeTrue();
});
