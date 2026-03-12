<?php

return [
    'sms' => [
        'default' => env('SMS_PROVIDER', 'log'),
        'providers' => [
            'africastalking' => [
                'api_key' => env('AFRICASTALKING_API_KEY'),
                'username' => env('AFRICASTALKING_USERNAME'),
                'sender_id' => env('AFRICASTALKING_SENDER_ID'),
            ],
            'twilio' => [
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            'log' => [
                'channel' => env('SMS_LOG_CHANNEL', 'stack'),
            ],
        ],
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_WHATSAPP_FROM'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    ],
];
