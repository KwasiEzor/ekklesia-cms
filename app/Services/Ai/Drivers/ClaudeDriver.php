<?php

namespace App\Services\Ai\Drivers;

use Anthropic\Client;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\RawMessageDeltaEvent;
use Anthropic\Messages\RawMessageStartEvent;
use App\Services\Ai\AiDriverInterface;
use App\Services\Ai\AiResponse;

class ClaudeDriver implements AiDriverInterface
{
    private Client $client;

    public function __construct(
        private string $apiKey,
        private string $model,
        private int $maxTokens,
    ) {
        $this->client = new Client(apiKey: $this->apiKey);
    }

    public function chat(string $system, array $messages, ?int $maxTokens = null): AiResponse
    {
        $message = $this->client->messages->create(
            maxTokens: $maxTokens ?? $this->maxTokens,
            messages: $this->formatMessages($messages),
            model: $this->model,
            system: $system,
        );

        $content = collect($message->content)
            ->filter(fn ($block) => isset($block->text))
            ->map(fn ($block) => $block->text)
            ->implode('');

        return new AiResponse(
            content: $content,
            tokensInput: $message->usage->inputTokens,
            tokensOutput: $message->usage->outputTokens,
            model: $this->model,
            stopReason: $message->stopReason?->value ?? $message->stopReason,
        );
    }

    public function chatStream(string $system, array $messages, callable $onChunk, ?int $maxTokens = null): AiResponse
    {
        $stream = $this->client->messages->createStream(
            maxTokens: $maxTokens ?? $this->maxTokens,
            messages: $this->formatMessages($messages),
            model: $this->model,
            system: $system,
        );

        $fullContent = '';
        $inputTokens = 0;
        $outputTokens = 0;
        $stopReason = null;

        foreach ($stream as $event) {
            if ($event instanceof RawMessageStartEvent) {
                $inputTokens = $event->message->usage->inputTokens ?? 0;
            }

            if ($event instanceof RawContentBlockDeltaEvent && isset($event->delta->text)) {
                $chunk = $event->delta->text;
                $fullContent .= $chunk;
                $onChunk($chunk);
            }

            if ($event instanceof RawMessageDeltaEvent) {
                $outputTokens = $event->usage->outputTokens ?? 0;
                $stopReason = $event->delta->stopReason ?? null;
            }
        }

        return new AiResponse(
            content: $fullContent,
            tokensInput: $inputTokens,
            tokensOutput: $outputTokens,
            model: $this->model,
            stopReason: is_string($stopReason) ? $stopReason : ($stopReason?->value ?? null),
        );
    }

    public function models(): array
    {
        return [
            'claude-sonnet-4-6' => 'Claude Sonnet 4.6',
            'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5',
            'claude-opus-4-6' => 'Claude Opus 4.6',
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    private function formatMessages(array $messages): array
    {
        return array_map(fn (array $msg) => [
            'role' => $msg['role'],
            'content' => $msg['content'],
        ], $messages);
    }
}
