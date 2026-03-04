<?php

return [
    'label' => 'Notification',
    'plural_label' => 'Notifications envoyées',
    'navigation_group' => 'Communication',
    'channel' => 'Canal',
    'type' => 'Type',
    'status' => 'Statut',
    'recipient' => 'Destinataire',
    'subject' => 'Sujet',
    'body' => 'Contenu',
    'member' => 'Membre',
    'sent_at' => 'Envoyé le',
    'delivered_at' => 'Livré le',
    'failed_at' => 'Échoué le',
    'failure_reason' => 'Raison de l\'échec',
    'created_at' => 'Créé le',

    'channels' => [
        'email' => 'Email',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
    ],

    'types' => [
        'welcome' => 'Bienvenue',
        'giving_receipt' => 'Reçu de don',
        'event_reminder' => 'Rappel d\'événement',
        'announcement' => 'Annonce',
        'birthday' => 'Anniversaire',
    ],

    'statuses' => [
        'pending' => 'En attente',
        'sent' => 'Envoyé',
        'delivered' => 'Livré',
        'failed' => 'Échoué',
    ],

    // Settings
    'settings_tab' => 'Canaux de notification',
    'settings_section_sms' => 'SMS (Africa\'s Talking)',
    'settings_section_sms_desc' => 'Configuration du service SMS',
    'sms_api_key' => 'Clé API',
    'sms_username' => 'Nom d\'utilisateur',
    'sms_sender_id' => 'ID expéditeur',
    'settings_section_whatsapp' => 'WhatsApp (Twilio)',
    'settings_section_whatsapp_desc' => 'Configuration WhatsApp via Twilio',
    'whatsapp_account_sid' => 'Account SID',
    'whatsapp_auth_token' => 'Auth Token',
    'whatsapp_from_number' => 'Numéro d\'envoi',
    'settings_section_telegram' => 'Telegram',
    'settings_section_telegram_desc' => 'Configuration du bot Telegram',
    'telegram_bot_token' => 'Token du bot',
];
