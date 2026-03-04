<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChannel implements NotificationChannelInterface
{
    public function send(NotificationPayload $payload): bool
    {
        try {
            Mail::raw($payload->body, function ($message) use ($payload): void {
                $message->to($payload->recipient)
                    ->subject($payload->subject ?? 'Notification');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Email notification failed', [
                'recipient' => $payload->recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return ! empty(config('mail.default'));
    }
}
