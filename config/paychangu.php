<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayChangu API Private Key
    |--------------------------------------------------------------------------
    |
    | This is the private key used to authenticate with the PayChangu API.
    |
    */
    'private_key' => env('PAYCHANGU_API_PRIVATE_KEY'), // @phpstan-ignore-line

    /*
    |--------------------------------------------------------------------------
    | PayChangu API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the root URL for the PayChangu API.
    | Specific endpoints (checkout, mobile-money) will be constructed from this.
    |
    */
    'api_base_url' => env('PAYCHANGU_API_BASE_URL', 'https://api.paychangu.com/'), // @phpstan-ignore-line

    /*
    |--------------------------------------------------------------------------
    | PayChangu Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret is used to verify incoming webhook signatures.
    |
    */
    'secret' => env('PAYCHANGU_WEBHOOK_SECRET'), // @phpstan-ignore-line

    /*
    |--------------------------------------------------------------------------
    | PayChangu Callback URL
    |--------------------------------------------------------------------------
    |
    | Override the callback URL used in hosted checkout (useful when behind
    | a tunnel like Expose during local development). Defaults to APP_URL.
    |
    */
    'callback_url' => env('PAYCHANGU_CALLBACK_URL'), // @phpstan-ignore-line
];
