<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;

class TwilioSmsDriver implements SmsDriverInterface
{
    public function __construct(
        private readonly string $accountSid,
        private readonly string $authToken,
        private readonly string $from,
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        try {
            if (! class_exists('Twilio\\Rest\\Client')) {
                Log::warning('Twilio SDK not installed. Please run: composer require twilio/sdk');

                return false;
            }

            $client = new \Twilio\Rest\Client($this->accountSid, $this->authToken);

            $client->messages->create(
                $payload->recipient,
                [
                    'from' => $this->from,
                    'body' => $payload->body,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('Twilio SMS failed', [
                'recipient' => $payload->recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return $this->accountSid !== '' && $this->accountSid !== '0' && ($this->authToken !== '' && $this->authToken !== '0') && ($this->from !== '' && $this->from !== '0');
    }
}
