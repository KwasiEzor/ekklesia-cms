<?php

namespace App\Services\Notification;

class NotificationPayload
{
    public function __construct(
        public string $recipient,
        public string $body,
        public ?string $subject = null,
        public string $type = 'general',
        public ?int $memberId = null,
        public ?array $metadata = null,
    ) {}
}
