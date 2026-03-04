<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\AiDriverInterface;
use App\Services\Ai\AiResponse;
use OpenAI;

class OpenAiDriver implements AiDriverInterface
{
    private readonly \OpenAI\Client $client;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $maxTokens,
    ) {
        $this->client = OpenAI::client($this->apiKey);
    }

    public function chat(string $system, array $messages, ?int $maxTokens = null): AiResponse
    {
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $this->formatMessages($system, $messages),
            'max_tokens' => $maxTokens ?? $this->maxTokens,
        ]);

        return new AiResponse(
            content: $response->choices[0]->message->content ?? '',
            tokensInput: $response->usage->promptTokens,
            tokensOutput: $response->usage->completionTokens ?? 0,
            model: $this->model,
            stopReason: $response->choices[0]->finishReason,
        );
    }

    public function chatStream(string $system, array $messages, callable $onChunk, ?int $maxTokens = null): AiResponse
    {
        $stream = $this->client->chat()->createStreamed([
            'model' => $this->model,
            'messages' => $this->formatMessages($system, $messages),
            'max_tokens' => $maxTokens ?? $this->maxTokens,
            'stream_options' => ['include_usage' => true],
        ]);

        $fullContent = '';
        $inputTokens = 0;
        $outputTokens = 0;
        $stopReason = null;

        foreach ($stream as $chunk) {
            $delta = $chunk->choices[0]->delta->content ?? '';
            if ($delta !== '') {
                $fullContent .= $delta;
                $onChunk($delta);
            }

            if ($chunk->choices[0]->finishReason !== null) {
                $stopReason = $chunk->choices[0]->finishReason;
            }

            if (isset($chunk->usage)) {
                $inputTokens = $chunk->usage->promptTokens ?? 0;
                $outputTokens = $chunk->usage->completionTokens ?? 0;
            }
        }

        return new AiResponse(
            content: $fullContent,
            tokensInput: $inputTokens,
            tokensOutput: $outputTokens,
            model: $this->model,
            stopReason: $stopReason,
        );
    }

    public function models(): array
    {
        return [
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    private function formatMessages(string $system, array $messages): array
    {
        $formatted = [['role' => 'system', 'content' => $system]];

        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        return $formatted;
    }
}
