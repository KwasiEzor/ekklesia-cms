<?php

namespace App\Services\Notification;

use App\Models\Tenant;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SmsChannel;
use App\Services\Notification\Channels\TelegramChannel;
use App\Services\Notification\Channels\WhatsAppChannel;
use Illuminate\Support\Manager;

/**
 * @method NotificationChannelInterface driver(?string $driver = null)
 */
class NotificationChannelManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return 'email';
    }

    protected function createEmailDriver(): EmailChannel
    {
        return new EmailChannel;
    }

    protected function createSmsDriver(): SmsChannel
    {
        return new SmsChannel;
    }

    protected function createWhatsappDriver(): WhatsAppChannel
    {
        $tenant = tenant();
        $accountSid = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('whatsapp_account_sid', config('notifications-channels.whatsapp.account_sid'))
            : (string) config('notifications-channels.whatsapp.account_sid');
        $authToken = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('whatsapp_auth_token', config('notifications-channels.whatsapp.auth_token'))
            : (string) config('notifications-channels.whatsapp.auth_token');
        $fromNumber = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('whatsapp_from_number', config('notifications-channels.whatsapp.from_number'))
            : (string) config('notifications-channels.whatsapp.from_number');

        return new WhatsAppChannel(
            accountSid: $accountSid,
            authToken: $authToken,
            fromNumber: $fromNumber,
        );
    }

    protected function createTelegramDriver(): TelegramChannel
    {
        $tenant = tenant();
        $botToken = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('telegram_bot_token', config('notifications-channels.telegram.bot_token'))
            : (string) config('notifications-channels.telegram.bot_token');

        return new TelegramChannel(
            botToken: $botToken,
        );
    }
}
