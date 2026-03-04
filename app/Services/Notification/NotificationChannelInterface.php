<?php

namespace App\Services\Notification;

interface NotificationChannelInterface
{
    public function send(NotificationPayload $payload): bool;

    public function isConfigured(): bool;
}
