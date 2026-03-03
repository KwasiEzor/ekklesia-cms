<?php

namespace App\Services\Ai;

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

        if ($tenant) {
            return $tenant->ai_provider ?? config('ai.default');
        }

        return config('ai.default');
    }

    protected function createClaudeDriver(): ClaudeDriver
    {
        $apiKey = tenant()?->ai_api_key ?? config('ai.providers.claude.api_key');

        return new ClaudeDriver(
            apiKey: $apiKey ?? '',
            model: tenant()?->ai_model ?? config('ai.providers.claude.model'),
            maxTokens: (int) (tenant()?->ai_max_tokens ?? config('ai.providers.claude.max_tokens')),
        );
    }

    protected function createOpenaiDriver(): OpenAiDriver
    {
        $apiKey = tenant()?->ai_api_key ?? config('ai.providers.openai.api_key');

        return new OpenAiDriver(
            apiKey: $apiKey ?? '',
            model: tenant()?->ai_model ?? config('ai.providers.openai.model'),
            maxTokens: (int) (tenant()?->ai_max_tokens ?? config('ai.providers.openai.max_tokens')),
        );
    }

    protected function createGeminiDriver(): GeminiDriver
    {
        $apiKey = tenant()?->ai_api_key ?? config('ai.providers.gemini.api_key');

        return new GeminiDriver(
            apiKey: $apiKey ?? '',
            model: tenant()?->ai_model ?? config('ai.providers.gemini.model'),
            maxTokens: (int) (tenant()?->ai_max_tokens ?? config('ai.providers.gemini.max_tokens')),
        );
    }
}
