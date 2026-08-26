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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
        'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'auth_url' => env('MPESA_AUTH_URL'),

        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),

        // STK Push
        'stk_url' => env('MPESA_STK_URL'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
        'callback_secret' => env('MPESA_CALLBACK_SECRET'),

        // B2C settings
        'b2c_url' => env('MPESA_B2C_URL'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
        'result_url' => env('MPESA_RESULT_URL'),
        'timeout_url' => env('MPESA_TIMEOUT_URL'),

        // Optional but useful
        'transaction_status_url' => env('MPESA_TRANSACTION_STATUS_URL', env('MPESA_TRANSACTION_QUERY_URL')),
        'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
    ],
    'digitax' => [
        'base_url' => env('DIGITAX_BASE_URL', 'https://api.digitax.tech/ke/v2'),
        'callback_url' => env('DIGITAX_CALLBACK_URL'),
        // Long random token appended to the callback URL path so the
        // inbound webhook can be authenticated without relying on
        // DigiTax supporting HMAC signing. Set this, then update
        // DIGITAX_CALLBACK_URL (and the URL registered with DigiTax) to
        // end in /digitax/callback/{this value}.
        'callback_secret' => env('DIGITAX_CALLBACK_SECRET'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
