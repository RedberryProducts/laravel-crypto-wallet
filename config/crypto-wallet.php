<?php

use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoDriver;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaDriver;

return [
    'default' => env('CRYPTO_WALLET_DRIVER', 'bitgo'),

    'drivers' => [
        'bitgo' => [
            'driver' => BitgoDriver::class,

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
            'api_key' => env('BITGO_API_KEY', null),

            /*
            |--------------------------------------------------------------------------
            | Express API URL
            |--------------------------------------------------------------------------
            |
            | This option sets the Express API URL for the Bitgo API.
            |
            */
            'express_api_url' => env('BITGO_EXPRESS_API_URL', 'http://localhost:3080/api/v2/'),

            /*
            |--------------------------------------------------------------------------
            | Default Coin
            |--------------------------------------------------------------------------
            |
            | This option sets the default coin for Bitgo API calls.
            |
            */
            'default_coin' => env('BITGO_DEFAULT_COIN', 'tbtc4'),

            /*
            |--------------------------------------------------------------------------
            | Webhook Callback URL
            |--------------------------------------------------------------------------
            |
            | This option sets the webhook callback URL for the Bitgo API.
            |
            */
            'webhook_callback_url' => env('BITGO_WEBHOOK_CALLBACK', null),
        ],

        'solana' => [
            'driver' => SolanaDriver::class,
            'network' => env('SOLANA_NETWORK', 'devnet'),
            'rpc_url' => env('SOLANA_RPC_URL', 'https://api.devnet.solana.com'),
            'commitment' => env('SOLANA_COMMITMENT', 'confirmed'),
            'timeout' => (int) env('SOLANA_RPC_TIMEOUT', 10),
            'retry_times' => (int) env('SOLANA_RPC_RETRY_TIMES', 2),
            'assets' => [
                'SOL' => [
                    'type' => 'native',
                    'decimals' => 9,
                ],
                'USDC' => [
                    'type' => 'spl',
                    'mint' => env('SOLANA_USDC_MINT'),
                    'decimals' => 6,
                ],
                'USDT' => [
                    'type' => 'spl',
                    'mint' => env('SOLANA_USDT_MINT'),
                    'decimals' => 6,
                ],
            ],
        ],
    ],

];
