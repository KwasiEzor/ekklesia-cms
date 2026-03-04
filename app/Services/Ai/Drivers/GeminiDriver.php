<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\AiDriverInterface;
use App\Services\Ai\AiResponse;
use Gemini;
use Gemini\Data\Content;
use Gemini\Enums\Role;

class GeminiDriver implements AiDriverInterface
{
    private readonly \Gemini\Client $client;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $maxTokens,
    ) {
        $this->client = Gemini::client($this->apiKey);
    }

    public function chat(string $system, array $messages, ?int $maxTokens = null): AiResponse
    {
        $effectiveMaxTokens = $maxTokens ?? $this->maxTokens;
        if ($effectiveMaxTokens < 1) {
            $effectiveMaxTokens = 1;
        }

        $generativeModel = $this->client->generativeModel(model: $this->model)
            ->withSystemInstruction(Content::parse(part: $system, role: Role::USER));

        $history = $this->formatHistory($messages);
        $lastMessage = array_pop($history);

        $chat = $generativeModel->startChat(history: $history);
        $result = $chat->sendMessage($lastMessage->parts[0]->text);

        return new AiResponse(
            content: $result->text(),
            tokensInput: $result->usageMetadata->promptTokenCount ?? 0,
            tokensOutput: $result->usageMetadata->candidatesTokenCount ?? 0,
            model: $this->model,
            stopReason: (string) ($result->candidates[0]->finishReason ?? ''),
        );
    }

    public function chatStream(string $system, array $messages, callable $onChunk, ?int $maxTokens = null): AiResponse
    {
        $effectiveMaxTokens = $maxTokens ?? $this->maxTokens;
        if ($effectiveMaxTokens < 1) {
            $effectiveMaxTokens = 1;
        }

        $generativeModel = $this->client->generativeModel(model: $this->model)
            ->withSystemInstruction(Content::parse(part: $system, role: Role::USER));

        $history = $this->formatHistory($messages);
        $lastMessage = array_pop($history);

        $chat = $generativeModel->startChat(history: $history);
        $stream = $chat->streamSendMessage($lastMessage->parts[0]->text);

        $fullContent = '';
        $inputTokens = 0;
        $outputTokens = 0;
        $stopReason = null;

        foreach ($stream as $response) {
            $chunk = $response->text();
            if ($chunk !== '') {
                $fullContent .= $chunk;
                $onChunk($chunk);
            }

            if (isset($response->usageMetadata)) {
                $inputTokens = $response->usageMetadata->promptTokenCount ?? $inputTokens;
                $outputTokens = $response->usageMetadata->candidatesTokenCount ?? $outputTokens;
            }

            if (isset($response->candidates[0]->finishReason)) {
                $stopReason = $response->candidates[0]->finishReason->value;
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
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-2.0-pro' => 'Gemini 2.0 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return Content[]
     */
    private function formatHistory(array $messages): array
    {
        return array_map(fn (array $msg): \Gemini\Data\Content => Content::parse(
            part: $msg['content'],
            role: $msg['role'] === 'user' ? Role::USER : Role::MODEL,
        ), $messages);
    }
}
