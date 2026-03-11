<?php

return [
    'label' => 'Message groupé',
    'plural_label' => 'Messages groupés',
    'navigation_group' => 'Communication',

    'title' => 'Titre',
    'body' => 'Message',
    'channel' => 'Canal',
    'target_type' => 'Destinataires',
    'target_id' => 'Groupe cible',
    'status' => 'Statut',
    'scheduled_at' => 'Programmé pour',
    'sent_at' => 'Envoyé le',
    'recipient_count' => 'Destinataires',
    'sent_count' => 'Envoyés',
    'failed_count' => 'Échoués',
    'created_at' => 'Créé le',

    'section_content' => 'Contenu du message',
    'section_content_desc' => 'Rédigez votre message groupé',
    'section_delivery' => 'Paramètres d\'envoi',
    'section_delivery_desc' => 'Canal, destinataires et programmation',

    'channels' => [
        'sms' => 'SMS',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
    ],

    'targets' => [
        'all' => 'Tous les membres',
        'cell_group' => 'Groupe de cellule',
        'campus' => 'Campus',
        'status' => 'Par statut',
    ],

    'statuses' => [
        'draft' => 'Brouillon',
        'scheduled' => 'Programmé',
        'sending' => 'En cours d\'envoi',
        'sent' => 'Envoyé',
        'failed' => 'Échoué',
    ],

    'cannot_delete_sent' => 'Impossible de supprimer un message déjà envoyé.',
    'cannot_send_status' => 'Ce message ne peut pas être envoyé dans son état actuel.',
    'sending' => 'Le message est en cours d\'envoi.',
    'already_subscribed' => 'Ce membre est déjà inscrit à ce plan.',
];
