<?php

namespace App\Events;

use App\Models\PaymentTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->transaction->tenant_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->transaction->uuid,
            'amount' => $this->transaction->formatted_amount,
            'provider' => $this->transaction->provider,
            'payment_method' => $this->transaction->payment_method,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
