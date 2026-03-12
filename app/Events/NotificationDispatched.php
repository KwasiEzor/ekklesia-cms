<?php

namespace App\Events;

use App\Models\NotificationDispatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationDispatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public NotificationDispatch $dispatch,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->dispatch->tenant_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.dispatched';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->dispatch->id,
            'channel' => $this->dispatch->channel,
            'type' => $this->dispatch->type,
            'status' => $this->dispatch->status,
            'recipient' => $this->dispatch->recipient,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
