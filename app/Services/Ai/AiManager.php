<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Drivers\ClaudeDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\OpenAiDriver;
use Illuminate\Support\Manager;

/**
 * @method AiDriverInterface driver(?string $driver = null)
 */
class AiManager extends Manager
{
    public function getDefaultDriver(): string
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return $tenant->getSetting('ai_provider', config('ai.default'));
        }

        return config('ai.default');
    }

    protected function createClaudeDriver(): ClaudeDriver
    {
        $tenant = tenant();
        $apiKey = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_api_key', config('ai.providers.claude.api_key'))
            : config('ai.providers.claude.api_key');
        $model = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_model', config('ai.providers.claude.model'))
            : config('ai.providers.claude.model');
        $maxTokens = $tenant instanceof Tenant
            ? (int) $tenant->getSetting('ai_max_tokens', config('ai.providers.claude.max_tokens'))
            : (int) config('ai.providers.claude.max_tokens');

        return new ClaudeDriver(
            apiKey: $apiKey ?? '',
            model: (string) $model,
            maxTokens: $maxTokens,
        );
    }

    protected function createOpenaiDriver(): OpenAiDriver
    {
        $tenant = tenant();
        $apiKey = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_api_key', config('ai.providers.openai.api_key'))
            : config('ai.providers.openai.api_key');
        $model = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_model', config('ai.providers.openai.model'))
            : config('ai.providers.openai.model');
        $maxTokens = $tenant instanceof Tenant
            ? (int) $tenant->getSetting('ai_max_tokens', config('ai.providers.openai.max_tokens'))
            : (int) config('ai.providers.openai.max_tokens');

        return new OpenAiDriver(
            apiKey: $apiKey ?? '',
            model: (string) $model,
            maxTokens: $maxTokens,
        );
    }

    protected function createGeminiDriver(): GeminiDriver
    {
        $tenant = tenant();
        $apiKey = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_api_key', config('ai.providers.gemini.api_key'))
            : config('ai.providers.gemini.api_key');
        $model = $tenant instanceof Tenant
            ? $tenant->getSetting('ai_model', config('ai.providers.gemini.model'))
            : config('ai.providers.gemini.model');
        $maxTokens = $tenant instanceof Tenant
            ? (int) $tenant->getSetting('ai_max_tokens', config('ai.providers.gemini.max_tokens'))
            : (int) config('ai.providers.gemini.max_tokens');

        return new GeminiDriver(
            apiKey: $apiKey ?? '',
            model: (string) $model,
            maxTokens: $maxTokens,
        );
    }
}
