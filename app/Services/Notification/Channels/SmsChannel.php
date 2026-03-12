<?php

namespace App\Services\Notification\Channels;

use App\Models\Tenant;
use App\Services\Notification\Drivers\AfricasTalkingSmsDriver;
use App\Services\Notification\Drivers\LogSmsDriver;
use App\Services\Notification\Drivers\SmsDriverInterface;
use App\Services\Notification\Drivers\TwilioSmsDriver;
use App\Services\Notification\NotificationChannelInterface;
use App\Services\Notification\NotificationPayload;

class SmsChannel implements NotificationChannelInterface
{
    protected SmsDriverInterface $driver;

    public function __construct()
    {
        $this->driver = $this->resolveDriver();
    }

    public function send(NotificationPayload $payload): bool
    {
        return $this->driver->send($payload);
    }

    public function isConfigured(): bool
    {
        return $this->driver->isConfigured();
    }

    protected function resolveDriver(): SmsDriverInterface
    {
        $tenant = tenant();
        $provider = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('sms_provider', config('notifications-channels.sms.default'))
            : (string) config('notifications-channels.sms.default');

        return match ($provider) {
            'africastalking' => $this->createAfricasTalkingDriver($tenant),
            'twilio' => $this->createTwilioDriver($tenant),
            default => new LogSmsDriver(config('notifications-channels.sms.providers.log.channel', 'stack')),
        };
    }

    protected function createAfricasTalkingDriver(?Tenant $tenant): AfricasTalkingSmsDriver
    {
        $apiKey = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('sms_api_key', config('notifications-channels.sms.providers.africastalking.api_key'))
            : (string) config('notifications-channels.sms.providers.africastalking.api_key');

        $username = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('sms_username', config('notifications-channels.sms.providers.africastalking.username'))
            : (string) config('notifications-channels.sms.providers.africastalking.username');

        $senderId = $tenant instanceof Tenant
            ? $tenant->getSetting('sms_sender_id', config('notifications-channels.sms.providers.africastalking.sender_id'))
            : config('notifications-channels.sms.providers.africastalking.sender_id');

        return new AfricasTalkingSmsDriver($apiKey, $username, is_string($senderId) ? $senderId : null);
    }

    protected function createTwilioDriver(?Tenant $tenant): TwilioSmsDriver
    {
        $sid = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('twilio_sid', config('notifications-channels.sms.providers.twilio.account_sid'))
            : (string) config('notifications-channels.sms.providers.twilio.account_sid');

        $token = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('twilio_token', config('notifications-channels.sms.providers.twilio.auth_token'))
            : (string) config('notifications-channels.sms.providers.twilio.auth_token');

        $from = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('twilio_from', config('notifications-channels.sms.providers.twilio.from'))
            : (string) config('notifications-channels.sms.providers.twilio.from');

        return new TwilioSmsDriver($sid, $token, $from);
    }
}
