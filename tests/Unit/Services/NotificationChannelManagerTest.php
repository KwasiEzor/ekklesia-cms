<?php

use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SmsChannel;
use App\Services\Notification\Channels\TelegramChannel;
use App\Services\Notification\Channels\WhatsAppChannel;
use App\Services\Notification\NotificationChannelManager;

test('notification channel manager resolves email driver by default', function () {
    $manager = new NotificationChannelManager(app());
    expect($manager->driver())->toBeInstanceOf(EmailChannel::class);
});

test('notification channel manager resolves sms driver', function () {
    $manager = new NotificationChannelManager(app());
    expect($manager->driver('sms'))->toBeInstanceOf(SmsChannel::class);
});

test('notification channel manager resolves whatsapp driver', function () {
    $manager = new NotificationChannelManager(app());
    expect($manager->driver('whatsapp'))->toBeInstanceOf(WhatsAppChannel::class);
});

test('notification channel manager resolves telegram driver', function () {
    $manager = new NotificationChannelManager(app());
    expect($manager->driver('telegram'))->toBeInstanceOf(TelegramChannel::class);
});

test('notification channel manager returns default driver as email', function () {
    $manager = new NotificationChannelManager(app());
    expect($manager->getDefaultDriver())->toBe('email');
});

test('email channel is always configured', function () {
    $channel = new EmailChannel;
    expect($channel->isConfigured())->toBeTrue();
});

test('sms channel is not configured without credentials', function () {
    $channel = new SmsChannel('', '', null);
    expect($channel->isConfigured())->toBeFalse();
});

test('whatsapp channel is not configured without credentials', function () {
    $channel = new WhatsAppChannel('', '', '');
    expect($channel->isConfigured())->toBeFalse();
});

test('telegram channel is not configured without bot token', function () {
    $channel = new TelegramChannel('');
    expect($channel->isConfigured())->toBeFalse();
});
