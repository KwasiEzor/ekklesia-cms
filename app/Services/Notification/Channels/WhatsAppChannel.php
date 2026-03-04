<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly string $accountSid,
        private readonly string $authToken,
        private readonly string $fromNumber,
    ) {}

    public function send(NotificationPayload $payload): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('WhatsApp channel not configured');

            return false;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->post(
                    "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json",
                    [
                        'To' => "whatsapp:{$payload->recipient}",
                        'From' => "whatsapp:{$this->fromNumber}",
                        'Body' => $payload->body,
                    ]
                );

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('WhatsApp notification failed', [
                'recipient' => $payload->recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return $this->accountSid !== '' && $this->accountSid !== '0' && ($this->authToken !== '' && $this->authToken !== '0') && ($this->fromNumber !== '' && $this->fromNumber !== '0');
    }
}
