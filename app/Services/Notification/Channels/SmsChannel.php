<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $username,
        private readonly ?string $senderId = null,
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('SMS channel not configured');

            return false;
        }

        try {
            if (! class_exists('AfricasTalking\\SDK\\AfricasTalking')) {
                Log::warning('SMS SDK not installed');

                return false;
            }

            $at = new \AfricasTalking\SDK\AfricasTalking($this->username, $this->apiKey);
            $sms = $at->sms();

            $result = $sms->send([
                'to' => $payload->recipient,
                'message' => $payload->body,
                'from' => $this->senderId,
            ]);

            return ($result['status'] ?? '') === 'success';
        } catch (\Throwable $e) {
            Log::error('SMS notification failed', [
                'recipient' => $payload->recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== '0' && ($this->username !== '' && $this->username !== '0');
    }
}
