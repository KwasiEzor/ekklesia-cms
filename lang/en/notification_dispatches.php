<?php

return [
    'label' => 'Notification',
    'plural_label' => 'Sent Notifications',
    'navigation_group' => 'Communication',
    'channel' => 'Channel',
    'type' => 'Type',
    'status' => 'Status',
    'recipient' => 'Recipient',
    'subject' => 'Subject',
    'body' => 'Body',
    'member' => 'Member',
    'sent_at' => 'Sent at',
    'delivered_at' => 'Delivered at',
    'failed_at' => 'Failed at',
    'failure_reason' => 'Failure Reason',
    'created_at' => 'Created at',

    'channels' => [
        'email' => 'Email',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
    ],

    'types' => [
        'welcome' => 'Welcome',
        'giving_receipt' => 'Giving Receipt',
        'event_reminder' => 'Event Reminder',
        'announcement' => 'Announcement',
        'birthday' => 'Birthday',
    ],

    'statuses' => [
        'pending' => 'Pending',
        'sent' => 'Sent',
        'delivered' => 'Delivered',
        'failed' => 'Failed',
    ],

    // Settings
    'settings_tab' => 'Notification Channels',
    'settings_section_sms' => 'SMS (Africa\'s Talking)',
    'settings_section_sms_desc' => 'SMS service configuration',
    'sms_api_key' => 'API Key',
    'sms_username' => 'Username',
    'sms_sender_id' => 'Sender ID',
    'settings_section_whatsapp' => 'WhatsApp (Twilio)',
    'settings_section_whatsapp_desc' => 'WhatsApp configuration via Twilio',
    'whatsapp_account_sid' => 'Account SID',
    'whatsapp_auth_token' => 'Auth Token',
    'whatsapp_from_number' => 'From Number',
    'settings_section_telegram' => 'Telegram',
    'settings_section_telegram_desc' => 'Telegram bot configuration',
    'telegram_bot_token' => 'Bot Token',
];
