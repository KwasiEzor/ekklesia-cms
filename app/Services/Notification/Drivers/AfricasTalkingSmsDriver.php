<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;

class AfricasTalkingSmsDriver implements SmsDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $username,
        private readonly ?string $senderId = null,
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        try {
            if (! class_exists('AfricasTalking\\SDK\\AfricasTalking')) {
                Log::warning('AfricasTalking SDK not installed. Please run: composer require africastalking/africastalking');

                return false;
            }

            $at = new \AfricasTalking\SDK\AfricasTalking($this->username, $this->apiKey);
            $sms = $at->sms();

            $result = $sms->send([
                'to' => $payload->recipient,
                'message' => $payload->body,
                'from' => $this->senderId,
            ]);

            return isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'SendMessageToAtLeastOneRecipient');
        } catch (\Throwable $e) {
            Log::error('AfricasTalking SMS failed', [
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
