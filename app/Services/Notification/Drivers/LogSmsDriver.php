<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;

class LogSmsDriver implements SmsDriverInterface
{
    public function __construct(
        private readonly string $channel = 'stack'
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        Log::channel($this->channel)->info('SMS Simulation:', [
            'to' => $payload->recipient,
            'message' => $payload->body,
        ]);

        return true;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}
