<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'kaltim_api' => [
        'key' => env('KALTIM_API_KEY'),
    ],

    'whatsapp' => config('integrations.whatsapp'),

    'anthropic' => config('integrations.ai.anthropic'),

    'deepseek' => config('integrations.ai.deepseek'),

    'ai' => [
        'driver' => config('integrations.ai.driver'),
    ],

    'sms' => config('integrations.sms'),

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    'ai' => [
        // Driver for the template AI provider.
        // Supported: "stub" (offline default).
        // Future: "claude", "openai".
        'driver' => env('AI_DRIVER', 'stub'),
    ],

    'sms' => [
        'enabled' => env('SMS_GATEWAY_ENABLED', false),
        'gateway' => env('SMS_GATEWAY', 'fonnte'), // fonnte, twilio, or zenziva

        // Fonnte SMS Gateway
        'fonnte' => [
            'token' => env('SMS_FONNTE_TOKEN'),
        ],

        // Twilio SMS Gateway
        'twilio' => [
            'account_sid' => env('SMS_TWILIO_ACCOUNT_SID'),
            'auth_token' => env('SMS_TWILIO_AUTH_TOKEN'),
            'from_number' => env('SMS_TWILIO_FROM_NUMBER'),
        ],

        // Zenziva SMS Gateway
        'zenziva' => [
            'userkey' => env('SMS_ZENZIVA_USERKEY'),
            'passkey' => env('SMS_ZENZIVA_PASSKEY'),
        ],
    ],

];
