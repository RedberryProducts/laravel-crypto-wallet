<?php

namespace Khomeriki\BitgoWallet\Tests;

use Illuminate\Support\Facades\Http;

trait BitgoHttpMocks
{
    public static function setupMocks(): void
    {
        $walletData = [
            'id' => 'wallet-id',
            'coin' => 'tbtc',
            'receiveAddress' => [
                'id' => '627e1ca8f763d80007d954b9a4a2477d',
                'address' => '2NGMqpJLQRYjvGkp9oM3Rgejf1a5tceDzCK',
            ],
        ];

        $webhookData = [
            'id' => '631272d4358f790007d72487601864cf',
            'created' => '2022-09-02T21:17:08.805Z',
            'walletId' => '631272d36334c60007a2a61645fb770f',
            'coin' => 'tbtc',
            'type' => 'transfer',
            'url' => 'https://webhook.site/dd306829-30cb-41c8-a514-13a4a0db4a3b',
            'version' => 2,
            'numConfirmations' => 0,
            'state' => 'active',
            'successiveFailedAttempts' => 0,
            'listenToFailureStates' => false,
            'allToken' => false,
        ];
        $exchangeRateMock = [
            'marketData' => [
                [
                    'blockchain' => [
                        'cacheTime' => 1662827616853,
                        'totalbc' => 19146787.5,
                        'transactions' => 258193,
                    ],
                    'currencies' => [
                        'EUR' => [
                            '24h_avg' => 60.8404113,
                        ],
                    ],
                    'coin' => 'tltc',
                ],
            ],
        ];

        $maximumSpendable = [
            'maximumSpendable' => '47523',
            'miningFee' => '1026',
            'payGoFee' => '0',
            'coin' => 'tbtc',
        ];

        Http::preventStrayRequests();
        $testingUrl = config('bitgo.testnet_api_url').'/'.config('bitgo.v2_api_prefix');
        $expressUrl = config('bitgo.express_api_url').'/'.config('bitgo.v2_api_prefix');
        Http::fake([
            "{$testingUrl}tbtc/wallet/62b1ba9f2c7e8e000781fb2ae5c5dbff/transfer" => Http::response(['transfers' => [$webhookData]]),
            "{$testingUrl}tbtc/wallet/62b1ba9f2c7e8e000781fb2ae5c5dbff/transfer/62b1c6168e0b9e0007b421314aba0654" => Http::response($webhookData),
            "{$testingUrl}tbtc/wallet/62b1ba9f2c7e8e000781fb2ae5c5dbff/maximumSpendable?feeRate=0" => Http::response($maximumSpendable),
            "{$expressUrl}tbtc/wallet/generate" => Http::response(['wallet' => $walletData]),
            "{$testingUrl}wallets*" => Http::response(['wallets' => [$walletData]]),
            "{$testingUrl}market/latest*" => Http::response($exchangeRateMock),

            "{$expressUrl}tbtc/wallet/wallet-id/address" => Http::response([]),
            "{$expressUrl}tbtc/wallet/wallet-id/webhooks" => Http::response([]),
            "{$expressUrl}ping" => Http::response([]),
            "{$testingUrl}ping" => Http::response([]),
            "{$testingUrl}user/me" => Http::response([
                'user' => 'fake',
            ]),
            "{$expressUrl}tbtc/wallet/wallet-id/sendmany" => Http::response([]),
            "{$expressUrl}tbtc/wallet/62b1ba9f2c7e8e000781fb2ae5c5dbff/consolidateunspents" => Http::response([]),
        ]);
    }
}
