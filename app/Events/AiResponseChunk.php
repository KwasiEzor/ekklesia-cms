<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiResponseChunk implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public int $conversationId,
        public string $chunk,
        public bool $isComplete = false,
        public ?int $tokensInput = null,
        public ?int $tokensOutput = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("ai-chat.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ai.chunk';
    }

    public function broadcastWith(): array
    {
        $data = [
            'chunk' => $this->chunk,
            'is_complete' => $this->isComplete,
            'conversation_id' => $this->conversationId,
        ];

        if ($this->isComplete) {
            $data['tokens_input'] = $this->tokensInput;
            $data['tokens_output'] = $this->tokensOutput;
        }

        return $data;
    }
}
