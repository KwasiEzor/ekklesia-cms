<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationPayload;

interface SmsDriverInterface
{
    public function send(NotificationPayload $payload): bool;

    public function isConfigured(): bool;
}
