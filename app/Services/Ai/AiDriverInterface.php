<?php

namespace App\Services\Ai;

interface AiDriverInterface
{
    /**
     * Send a chat request and return the full response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(string $system, array $messages, ?int $maxTokens = null): AiResponse;

    /**
     * Send a chat request with streaming. Each chunk calls $onChunk(string).
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chatStream(string $system, array $messages, callable $onChunk, ?int $maxTokens = null): AiResponse;

    /**
     * List available models for this provider.
     *
     * @return array<string, string>
     */
    public function models(): array;
}
