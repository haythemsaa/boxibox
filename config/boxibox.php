<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration Boxibox
    |--------------------------------------------------------------------------
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Configuration SEPA
    |--------------------------------------------------------------------------
    */

    'sepa' => [
        'creditor_id' => env('SEPA_CREDITOR_ID'),
        'creditor_name' => env('SEPA_CREDITOR_NAME', config('app.name')),
        'creditor_iban' => env('SEPA_CREDITOR_IBAN'),
        'creditor_bic' => env('SEPA_CREDITOR_BIC'),
        'currency' => 'EUR',
        'country' => 'FR',
        'pre_notification_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Facturation
    |--------------------------------------------------------------------------
    */

    'invoice' => [
        'prefix' => 'FAC',
        'number_length' => 6,
        'tva_rate' => 20.00,
        'due_days' => 30,
        'late_fee_rate' => 1.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Contrats
    |--------------------------------------------------------------------------
    */

    'contract' => [
        'prefix' => 'CTR',
        'number_length' => 6,
        'default_notice_days' => 30,
        'max_duration_months' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Boxes
    |--------------------------------------------------------------------------
    */

    'box' => [
        'statuses' => [
            'libre' => [
                'label' => 'Libre',
                'color' => '#28a745',
                'icon' => 'fa-unlock'
            ],
            'occupe' => [
                'label' => 'Occupé',
                'color' => '#dc3545',
                'icon' => 'fa-lock'
            ],
            'reserve' => [
                'label' => 'Réservé',
                'color' => '#ffc107',
                'icon' => 'fa-clock'
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'color' => '#fd7e14',
                'icon' => 'fa-tools'
            ],
            'hors_service' => [
                'label' => 'Hors service',
                'color' => '#6c757d',
                'icon' => 'fa-ban'
            ],
        ],
        'surface_ranges' => [
            'xs' => ['min' => 0, 'max' => 5, 'label' => 'Moins de 5m²'],
            's' => ['min' => 5, 'max' => 10, 'label' => '5-10m²'],
            'm' => ['min' => 10, 'max' => 20, 'label' => '10-20m²'],
            'l' => ['min' => 20, 'max' => 50, 'label' => '20-50m²'],
            'xl' => ['min' => 50, 'max' => null, 'label' => 'Plus de 50m²'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Emails
    |--------------------------------------------------------------------------
    */

    'email' => [
        'from_name' => env('MAIL_FROM_NAME', config('app.name')),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@boxibox.com'),
        'templates' => [
            'invoice' => 'emails.invoice',
            'reminder' => 'emails.reminder',
            'contract' => 'emails.contract',
            'welcome' => 'emails.welcome',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Documents
    |--------------------------------------------------------------------------
    */

    'documents' => [
        'storage_path' => 'documents',
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        'max_size' => 10 * 1024 * 1024, // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Exports
    |--------------------------------------------------------------------------
    */

    'exports' => [
        'formats' => ['excel', 'pdf', 'csv'],
        'batch_size' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Notification
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'contract_expiry_days' => 30,
        'invoice_due_days' => 3,
        'payment_overdue_days' => 7,
    ],

];