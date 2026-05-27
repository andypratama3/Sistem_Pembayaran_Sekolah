<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Midtrans payment gateway integration
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Endpoints
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'snap' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

        'api' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */

    'payment_methods' => [
        'bank_transfer' => [
            'bca' => 'BCA Virtual Account',
            'mandiri' => 'Mandiri Virtual Account',
            'bni' => 'BNI Virtual Account',
            'cimb' => 'CIMB Niaga Virtual Account',
            'btn' => 'BTN Virtual Account',
        ],
        'e_wallet' => [
            'gopay' => 'GoPay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'linkaja' => 'LinkAja',
        ],
        'Buy Now Pay Later' => [
            'kredivo' => 'Kredivo',
            'akulaku' => 'Akulaku',
        ],
        'credit_card' => [
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'jcb' => 'JCB',
        ],
        'retail' => [
            'indomaret' => 'Indomaret',
            'alfamart' => 'Alfamart',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    'currency' => env('MIDTRANS_CURRENCY', 'IDR'),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */

    'callbacks' => [
        'finish' => env('MIDTRANS_FINISH_URL', '/payment/success'),
        'unfinish' => env('MIDTRANS_UNFINISH_URL', '/payment/unfinish'),
        'error' => env('MIDTRANS_ERROR_URL', '/payment/error'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Notification URL
    |--------------------------------------------------------------------------
    |
    | The URL where Midtrans will send payment notifications.
    | Uses the route name for the notification endpoint.
    |
    */

    'webhook_url' => env('MIDTRANS_WEBHOOK_URL'),
];
