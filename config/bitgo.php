<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Use Mocks
    |--------------------------------------------------------------------------
    |
    | This option determines if the application should use mocks for Bitgo
    | API calls. This is useful for testing purposes.
    |
    */
    'use_mocks' => env('BITGO_USE_MOCKS', false),

    /*
    |--------------------------------------------------------------------------
    | Testnet
    |--------------------------------------------------------------------------
    |
    | This option determines if the application should use the Bitgo testnet
    | instead of the mainnet. Set this to true for testing and development.
    |
    */
    'testnet' => env('BITGO_TESTNET', true),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | This option sets the API key for the Bitgo API.
    |
    */
    'api_key' => env('BITGO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    |
    | This option sets the API prefix for the Bitgo API.
    |
    */
    'v2_api_prefix' => 'api/v2/',

    /*
    |--------------------------------------------------------------------------
    | Testnet and Mainnet API URLs
    |--------------------------------------------------------------------------
    |
    | These options set the API URLs for the Bitgo testnet and mainnet.
    |
    */
    'testnet_api_url' => 'https://app.bitgo-test.com',
    'mainnet_api_url' => 'https://app.bitgo.com',

    /*
    |--------------------------------------------------------------------------
    | Express API URL
    |--------------------------------------------------------------------------
    |
    | This option sets the Express API URL for the Bitgo API.
    |
    */
    'express_api_url' => env('BITGO_EXPRESS_API_URL'),

    /*
    |-------------------------------------ddddddd-------------------------------------
    | Default Coin
    |--------------------------------------------------------------------------
    |
    | This option sets the default coin for Bitgo API calls.
    |
    */
    'default_coin' => env('BITGO_DEFAULT_COIN', 'tbtc'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Callback URL
    |--------------------------------------------------------------------------
    |
    | This option sets the webhook callback URL for the Bitgo API.
    |
    */
    'webhook_callback_url' => env('BITGO_WEBHOOK_CALLBACK'),
];
