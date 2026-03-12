<?php

return [
    'default' => env('PAYMENT_DEFAULT_PROVIDER', 'cinetpay'),

    'providers' => [
        'cinetpay' => [
            'api_key' => env('CINETPAY_API_KEY'),
            'site_id' => env('CINETPAY_SITE_ID'),
            'secret_key' => env('CINETPAY_SECRET_KEY'),
            'base_url' => env('CINETPAY_BASE_URL', 'https://api-checkout.cinetpay.com/v2'),
        ],
        'stripe' => [
            'secret_key' => env('STRIPE_SECRET'),
            'publishable_key' => env('STRIPE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'fedapay' => [
            'secret_key' => env('FEDAPAY_SECRET_KEY'),
            'sandbox' => env('FEDAPAY_SANDBOX', true),
            'base_url' => env('FEDAPAY_SANDBOX', true)
                ? 'https://sandbox-api.fedapay.com/v1'
                : 'https://api.fedapay.com/v1',
        ],
    ],

    'currencies' => [
        'XOF', 'XAF', 'EUR', 'USD', 'GBP', 'CAD',
    ],

    'default_currency' => env('PAYMENT_DEFAULT_CURRENCY', 'XOF'),

    'pending_check_interval' => 5, // minutes
];
