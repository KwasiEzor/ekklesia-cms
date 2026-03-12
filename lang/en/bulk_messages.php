<?php

return [
    'label' => 'Bulk Message',
    'plural_label' => 'Bulk Messages',
    'navigation_group' => 'Communication',

    'title' => 'Title',
    'body' => 'Message',
    'channel' => 'Channel',
    'target_type' => 'Recipients',
    'target_id' => 'Target Group',
    'status' => 'Status',
    'scheduled_at' => 'Scheduled for',
    'sent_at' => 'Sent at',
    'recipient_count' => 'Recipients',
    'sent_count' => 'Sent',
    'failed_count' => 'Failed',
    'created_at' => 'Created at',

    'section_content' => 'Message Content',
    'section_content_desc' => 'Write your bulk message',
    'section_delivery' => 'Delivery Settings',
    'section_delivery_desc' => 'Channel, recipients, and scheduling',

    'channels' => [
        'sms' => 'SMS',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
    ],

    'targets' => [
        'all' => 'All Members',
        'cell_group' => 'Cell Group',
        'campus' => 'Campus',
        'status' => 'By Status',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'sending' => 'Sending',
        'sent' => 'Sent',
        'failed' => 'Failed',
    ],

    'cannot_delete_sent' => 'Cannot delete a message that has already been sent.',
    'cannot_send_status' => 'This message cannot be sent in its current status.',
    'sending' => 'The message is currently being sent.',
    'already_subscribed' => 'This member is already subscribed to this plan.',
];
