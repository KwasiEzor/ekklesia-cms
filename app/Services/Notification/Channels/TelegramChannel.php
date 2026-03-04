<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly string $botToken,
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Telegram channel not configured');

            return false;
        }

        try {
            $response = Http::post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $payload->recipient,
                    'text' => $payload->body,
                    'parse_mode' => 'HTML',
                ]
            );

            return $response->successful() && ($response->json('ok') === true);
        } catch (\Throwable $e) {
            Log::error('Telegram notification failed', [
                'recipient' => $payload->recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return $this->botToken !== '' && $this->botToken !== '0';
    }
}
