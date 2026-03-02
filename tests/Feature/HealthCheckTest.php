<?php

test('health endpoint returns healthy status', function () {
    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJson([
            'status' => 'healthy',
        ])
        ->assertJsonStructure([
            'status',
            'checks' => [
                'database' => ['ok'],
                'cache' => ['ok'],
            ],
            'timestamp',
        ]);
});

test('health endpoint database check is ok', function () {
    $response = $this->getJson('/health');

    expect($response->json('checks.database.ok'))->toBeTrue();
});

test('health endpoint cache check is ok', function () {
    $response = $this->getJson('/health');

    expect($response->json('checks.cache.ok'))->toBeTrue();
});
