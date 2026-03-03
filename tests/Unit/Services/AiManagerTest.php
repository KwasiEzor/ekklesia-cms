<?php

use App\Services\Ai\AiManager;
use App\Services\Ai\Drivers\ClaudeDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\OpenAiDriver;

test('ai manager resolves claude driver by default', function () {
    config(['ai.default' => 'claude']);
    config(['ai.providers.claude.api_key' => 'test-key']);

    $manager = app(AiManager::class);
    $driver = $manager->driver('claude');

    expect($driver)->toBeInstanceOf(ClaudeDriver::class);
});

test('ai manager resolves openai driver', function () {
    config(['ai.providers.openai.api_key' => 'test-key']);

    $manager = app(AiManager::class);
    $driver = $manager->driver('openai');

    expect($driver)->toBeInstanceOf(OpenAiDriver::class);
});

test('ai manager resolves gemini driver', function () {
    config(['ai.providers.gemini.api_key' => 'test-key']);

    $manager = app(AiManager::class);
    $driver = $manager->driver('gemini');

    expect($driver)->toBeInstanceOf(GeminiDriver::class);
});

test('ai manager returns default driver from config', function () {
    config(['ai.default' => 'openai']);
    config(['ai.providers.openai.api_key' => 'test-key']);

    $manager = new AiManager(app());

    expect($manager->getDefaultDriver())->toBe('openai');
});

test('claude driver lists available models', function () {
    $driver = new ClaudeDriver(apiKey: 'test', model: 'claude-sonnet-4-6', maxTokens: 2048);

    $models = $driver->models();

    expect($models)->toBeArray();
    expect($models)->toHaveKey('claude-sonnet-4-6');
});

test('openai driver lists available models', function () {
    $driver = new OpenAiDriver(apiKey: 'test', model: 'gpt-4o', maxTokens: 2048);

    $models = $driver->models();

    expect($models)->toBeArray();
    expect($models)->toHaveKey('gpt-4o');
});

test('gemini driver lists available models', function () {
    $driver = new GeminiDriver(apiKey: 'test', model: 'gemini-2.0-flash', maxTokens: 2048);

    $models = $driver->models();

    expect($models)->toBeArray();
    expect($models)->toHaveKey('gemini-2.0-flash');
});
