<?php

namespace App\Services\Ai;

class AiResponse
{
    public function __construct(
        public string $content,
        public int $tokensInput,
        public int $tokensOutput,
        public string $model,
        public ?string $stopReason = null,
    ) {}
}
