<?php

return [
    'label' => 'Giving Record',
    'plural_label' => 'Giving Records',
    'navigation_group' => 'Finance',
    'details' => 'Giving Details',
    'member' => 'Member',
    'member_help' => 'Leave empty for anonymous giving',
    'amount' => 'Amount',
    'currency' => 'Currency',
    'date' => 'Date',
    'method' => 'Payment Method',
    'reference' => 'Reference',
    'campaign' => 'Campaign',
    'anonymous' => 'Anonymous',
    'custom_fields' => 'Custom Fields',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',
    'methods' => [
        'mobile_money' => 'Mobile Money',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
    ],

    // Sections
    'section_info' => 'Giving Information',
    'section_info_desc' => 'Member, amount, currency and payment method',
    'section_tracking' => 'Tracking',
    'section_tracking_desc' => 'Transaction reference and associated campaign',

    // Placeholders
    'amount_placeholder' => 'E.g. 50000',
    'reference_placeholder' => 'E.g. TX-2026-001',
    'campaign_placeholder' => 'E.g. Temple construction',

    // Void / Adjustments
    'void' => 'Void',
    'voided' => 'Voided',
    'void_heading' => 'Void this record',
    'void_description' => 'Are you sure you want to void this record? This action is irreversible and will be logged in the audit trail.',
    'void_reason' => 'Reason for voiding',
];
