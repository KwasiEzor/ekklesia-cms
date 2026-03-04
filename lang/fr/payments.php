<?php

return [
    'label' => 'Transaction',
    'plural_label' => 'Transactions',
    'navigation_group' => 'Finance',
    'uuid' => 'Référence',
    'amount' => 'Montant',
    'currency' => 'Devise',
    'provider' => 'Fournisseur',
    'status' => 'Statut',
    'payment_method' => 'Méthode de paiement',
    'phone_number' => 'Numéro de téléphone',
    'campaign_id' => 'Campagne',
    'member' => 'Membre',
    'campus' => 'Campus',
    'paid_at' => 'Payé le',
    'failed_at' => 'Échoué le',
    'failure_reason' => 'Raison de l\'échec',
    'created_at' => 'Créé le',
    'provider_reference' => 'Réf. fournisseur',

    // Statuses
    'statuses' => [
        'pending' => 'En attente',
        'processing' => 'En cours',
        'completed' => 'Complété',
        'failed' => 'Échoué',
        'refunded' => 'Remboursé',
        'cancelled' => 'Annulé',
    ],

    // Methods
    'methods' => [
        'mtn_momo' => 'MTN Mobile Money',
        'orange_money' => 'Orange Money',
        'wave' => 'Wave',
        'moov_money' => 'Moov Money',
        'free_money' => 'Free Money',
        'card' => 'Carte bancaire',
    ],

    // Providers
    'providers' => [
        'cinetpay' => 'CinetPay',
        'stripe' => 'Stripe',
    ],

    // Settings
    'settings_tab' => 'Paiements',
    'settings_section' => 'Configuration des paiements',
    'settings_section_desc' => 'Fournisseur par défaut et clés API',
    'payment_provider' => 'Fournisseur de paiement',
    'cinetpay_api_key' => 'Clé API CinetPay',
    'cinetpay_site_id' => 'Site ID CinetPay',
    'cinetpay_secret_key' => 'Clé secrète CinetPay',
    'stripe_secret_key' => 'Clé secrète Stripe',
    'stripe_publishable_key' => 'Clé publique Stripe',
];
