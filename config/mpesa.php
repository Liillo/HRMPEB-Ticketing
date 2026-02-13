<?php

return [
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'passkey' => env('MPESA_PASSKEY'),
    'environment' => env('MPESA_ENV', 'sandbox'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'timeout_url' => env('MPESA_TIMEOUT_URL'),
    
    'individual_price' => env('INDIVIDUAL_TICKET_PRICE', 1000),
    'corporate_price' => env('CORPORATE_TICKET_PRICE', 40000),
    'corporate_max_scans' => env('CORPORATE_MAX_SCANS', 8),
];
