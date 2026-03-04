<?php

return [
    'label' => 'Transaction',
    'plural_label' => 'Transactions',
    'navigation_group' => 'Finance',
    'uuid' => 'Reference',
    'amount' => 'Amount',
    'currency' => 'Currency',
    'provider' => 'Provider',
    'status' => 'Status',
    'payment_method' => 'Payment Method',
    'phone_number' => 'Phone Number',
    'campaign_id' => 'Campaign',
    'member' => 'Member',
    'campus' => 'Campus',
    'paid_at' => 'Paid at',
    'failed_at' => 'Failed at',
    'failure_reason' => 'Failure Reason',
    'created_at' => 'Created at',
    'provider_reference' => 'Provider Ref.',

    // Statuses
    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ],

    // Methods
    'methods' => [
        'mtn_momo' => 'MTN Mobile Money',
        'orange_money' => 'Orange Money',
        'wave' => 'Wave',
        'moov_money' => 'Moov Money',
        'free_money' => 'Free Money',
        'card' => 'Credit/Debit Card',
    ],

    // Providers
    'providers' => [
        'cinetpay' => 'CinetPay',
        'stripe' => 'Stripe',
    ],

    // Settings
    'settings_tab' => 'Payments',
    'settings_section' => 'Payment Configuration',
    'settings_section_desc' => 'Default provider and API keys',
    'payment_provider' => 'Payment Provider',
    'cinetpay_api_key' => 'CinetPay API Key',
    'cinetpay_site_id' => 'CinetPay Site ID',
    'cinetpay_secret_key' => 'CinetPay Secret Key',
    'stripe_secret_key' => 'Stripe Secret Key',
    'stripe_publishable_key' => 'Stripe Publishable Key',
];
