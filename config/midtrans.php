<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'endpoints' => [
        'snap' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'),
        'api' => env('MIDTRANS_API_URL', 'https://api.sandbox.midtrans.com/v2'),
    ],
    'payment_methods' => ['bank_transfer', 'credit_card', 'gopay', 'shopeepay'],
    'currency' => 'IDR',
    'callbacks' => [
        'finish' => '/dashboard/payment/success',
        'unfinish' => '/dashboard/payment/unfinish',
        'error' => '/dashboard/payment/error',
    ],
    'webhook_url' => '/api/v1/midtrans/notification',
];
