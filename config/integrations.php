<?php

return [
    'providers' => [
        'whatsapp' => [
            'label' => 'WhatsApp',
            'description' => 'WhatsApp Business API integration for messaging and alerts.',
            'fields' => [
                ['key' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'required' => true],
                ['key' => 'api_version', 'label' => 'API Version', 'type' => 'text', 'required' => false],
                ['key' => 'phone_number_id', 'label' => 'Phone Number ID', 'type' => 'text', 'required' => true],
                ['key' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'required' => true],
            ],
        ],
        'email' => [
            'label' => 'Email',
            'description' => 'SMTP configuration for transactional and notification emails.',
            'fields' => [
                ['key' => 'host', 'label' => 'SMTP Host', 'type' => 'text', 'required' => true],
                ['key' => 'port', 'label' => 'SMTP Port', 'type' => 'number', 'required' => true],
                ['key' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
                ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
                ['key' => 'encryption', 'label' => 'Encryption', 'type' => 'text', 'required' => false],
            ],
        ],
        'flutterwave' => [
            'label' => 'Flutterwave',
            'description' => 'Flutterwave API credentials for payment collections.',
            'fields' => [
                ['key' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'required' => false],
                ['key' => 'public_key', 'label' => 'Public Key', 'type' => 'password', 'required' => true],
                ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ['key' => 'encryption_key', 'label' => 'Encryption Key', 'type' => 'password', 'required' => false],
            ],
        ],
    ],
];
