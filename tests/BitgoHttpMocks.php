<?php

namespace RedberryProducts\CryptoWallet\Tests;

use Illuminate\Support\Facades\Http;

trait BitgoHttpMocks
{
    public static function setupMocks(): void
    {

        $walletMock = [
            'id' => 'wallet-id',
            'users' => [
                [
                    'user' => '62ab90e06df',
                    'permissions' => [
                        'admin',
                        'view',
                        'spend',
                    ],
                ],
            ],
            'coin' => 'tbtc',
            'label' => 'Generated TBTC Wallet',
            'm' => 2,
            'n' => 3,
            'keys' => [
                '62f002e79b1',
                '62f002e759c',
                '62f002e77bd',
            ],
            'keySignatures' => [
                'backupPub' => '20f0854d0af1b22fad685a7580a4b8b4',
                'bitgoPub' => '1ffdb32d0618b3ef93e0b85f9499d6d4a',
            ],
            'enterprise' => '62c5ae8174ac860007aff138a2d74df7',
            'tags' => [
                '62f002e7b1440900072b8472fc8a9de8',
                '62c5ae8174ac860007aff138a2d74df7',
            ],
            'disableTransactionNotifications' => false,
            'freeze' => [
                'time' => '2024-01-01T00:00:00.000Z',
                'expires' => '2024-01-02T00:00:00.000Z',
            ],
            'deleted' => false,
            'approvalsRequired' => 1,
            'isCold' => false,
            'coinSpecific' => [
                'addressType' => 'p2sh',
            ],
            'admin' => [],
            'clientFlags' => [],
            'walletFlags' => [],
            'allowBackupKeySigning' => false,
            'recoverable' => false,
            'startDate' => '2022-08-07T18:22:31.000Z',
            'type' => 'hot',
            'buildDefaults' => [],
            'customChangeKeySignatures' => [],
            'hasLargeNumberOfAddresses' => false,
            'multisigType' => 'onchain',
            'config' => [],
            'balance' => 0,
            'confirmedBalance' => 0,
            'spendableBalance' => 0,
            'balanceString' => '0',
            'confirmedBalanceString' => '0',
            'spendableBalanceString' => '0',
            'receiveAddress' => [
                'id' => '62f002e7b1',
                'address' => '2MwMtk2qWsP54',
                'chain' => 10,
                'index' => 1,
                'coin' => 'tbtc',
                'wallet' => '62f002e7b144090',
                'coinSpecific' => [
                    'redeemScript' => '00206ad786997ee4798fcaa70651b041',
                    'witnessScript' => '522103c6b657f7a39b7a7f956f3b46',
                ],
            ],
            'pendingApprovals' => [],
            'disableKRSEmail' => false,
            'minColdConfirmations' => 2,
            'walletVersion' => 3,
            'lastChainIndex' => [
                '0' => 0,
                '10' => 1,
            ],
            'transferCount' => 0,
        ];

        $generateWalletRequestMock = [
            'wallet' => $walletMock,
            'userKeychain' => [
                'id' => '62f002e79b12ea85071f633f',
                'pub' => 'xpub661MyMwAqRbcGqD3oX9sGtEGiPxmhX',
                'ethAddress' => '0x500e9d8a71d98d12276dbe177ee4',
                'source' => 'user',
                'type' => 'independent',
                'encryptedPrv' => '{"iv":"GD26lmrXYXMMPA==","v":1,"iter":10000,"ks":256,"ts":64,"mode":"ccm","adata":"","cipher":"aes","salt":"KsRm/x1BTRw=","ct":"rq-ujjDShvo="}',
                'prv' => 'xprv9s21ZrQH143K4M8ahVcrukHzkXevTRdgvcaH6ZWEcjUWCyhVk8Vcrs5jPRbJwJBHUAi9',
            ],
            'backupKeychain' => [
                'id' => '62f002e7593002f885697e9',
                'pub' => 'xpub661MyMwfSixRBaEdtpC943wRsgnQNi',
                'ethAddress' => '0xba4b1391847',
                'source' => 'backup',
                'type' => 'independent',
                'prv' => 'xprv9s21ZrQH143KssyGYhEQjag8S8pYPe',
            ],
            'bitgoKeychain' => [
                'id' => '62f002e77bd75af3',
                'pub' => 'xpub661MyMwAqRbcFZjq6ub32',
                'ethAddress' => '0x3e96f',
                'source' => 'bitgo',
                'type' => 'independent',
                'isBitGo' => true,
            ],
            'responseType' => 'wallet',
            'warning' => 'Be sure to backup the backup keychain -- it is not stored anywhere else!',
        ];

        $webhookMock = [
            'id' => '631272d4357601864cf',
            'created' => '2022-09-02T21:17:08.805Z',
            'walletId' => '631272d36a61645fb770f',
            'coin' => 'tbtc',
            'type' => 'transfer',
            'url' => 'https://www.blockchain.com/',
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
                        'cacheTime' => 166216853,
                        'totalbc' => 19146787.5,
                        'transactions' => 258193,
                    ],
                    'currencies' => [
                        'EUR' => [
                            '24h_avg' => 60.8404113,
                        ],
                        'USD' => [
                            '24h_avg' => 70.0,
                        ],
                    ],
                    'coin' => 'tbtc',
                ],
            ],
        ];

        $maximumSpendableMock = [
            'maximumSpendable' => '47523',
            'miningFee' => '1026',
            'payGoFee' => '0',
            'coin' => 'tbtc',
        ];

        $transferObjectMock = [
            'id' => '62b1c616b9e0654',
            'coin' => 'tbtc',
            'wallet' => 'wallet-id',
            'value' => '100000000',
            'valueString' => '100000000',
        ];

        $addressObjectMock = [
            'id' => '631283e10ee142a',
            'address' => '2N9wCj210Sueutjz',
            'chain' => 10,
            'index' => 2,
            'coin' => 'tbtc',
            'wallet' => '6312824bad1d667e67',
            'coinSpecific' => [
                'redeemScript' => '00200fdb106',
                'witnessScript' => '522103938ad2788cf3e53ae',
            ],
            'addressType' => 'p2shP2wsh',
            'keychains' => [
                [
                    'id' => '6312824b86f3d1253ac33',
                    'pub' => 'xpub661MyMJAe8',
                    'ethAddress' => '0x944df260',
                    'source' => 'user',
                    'type' => 'independent',
                    'encryptedPrv' => '{"iv":"T+GU0GHRRg2ohnzp1w==","v":1,"iter":10000,"ks":256,"ts":64,"mode":"ccm","adata":"","cipher":"aes","salt":"Yn45uWDyOuM=","ct":"dbc8Yw1/rPewTqgwuBoUUn+TO2otaxiWaw1/9u0tqI81HBJcppgGu0FegdXbKc="}',
                ],
                [
                    'id' => '6312824bb686a9b6',
                    'pub' => 'xpub661MyMwAqRbcoqoUnkfxDw',
                    'ethAddress' => '0xbecc1b266',
                    'source' => 'backup',
                    'type' => 'independent',
                ],
                [
                    'id' => '6317374ba2',
                    'pub' => 'xpub661MyMP5ne',
                    'ethAddress' => '0xc3a3c70d1ba3',
                    'source' => 'bitgo',
                    'type' => 'independent',
                    'isBitGo' => true,
                ],
            ],
        ];

        Http::preventStrayRequests();
        $testingUrl = 'https://app.bitgo-test.com/api/v2/';
        $expressUrl = 'http://localhost:3080/api/v2/';
        Http::fake([
            "{$testingUrl}tbtc/wallet/wallet-id/transfer" => Http::response(['transfers' => [$transferObjectMock]]),
            "{$testingUrl}tbtc/wallet/wallet-id/transfer/transfer-id" => Http::response($transferObjectMock),
            "{$testingUrl}tbtc/wallet/wallet-id/maximumSpendable?feeRate=0" => Http::response($maximumSpendableMock),
            "{$expressUrl}tbtc/wallet/generate" => Http::response($generateWalletRequestMock),
            "{$testingUrl}wallets*" => Http::response(['wallets' => [$walletMock]]),
            "{$testingUrl}tbtc/wallet/wallet-id" => Http::response($walletMock),
            "{$testingUrl}market/latest*" => Http::response($exchangeRateMock),

            "{$expressUrl}tbtc/wallet/wallet-id/address" => Http::response($addressObjectMock),
            "{$expressUrl}tbtc/wallet/wallet-id/webhooks" => Http::response($webhookMock),
            "{$expressUrl}ping" => Http::response([]),
            "{$testingUrl}ping" => Http::response([]),
            "{$testingUrl}user/me" => Http::response([
                'user' => 'fake',
            ]),
            "{$expressUrl}tbtc/wallet/wallet-id/sendmany" => Http::response([]),
            "{$expressUrl}tbtc/wallet/wallet-id/consolidateunspents" => Http::response([]),
        ]);
    }
}
