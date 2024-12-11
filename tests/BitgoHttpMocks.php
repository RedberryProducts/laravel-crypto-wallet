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
                    'user' => '62ab90e06dfda30007974f0a52a12995',
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
                '62f002e79b12b800077bea85071f633f',
                '62f002e759cafb0007f3002f885697e9',
                '62f002e77bd7400007ee7f10ed8c5af3',
            ],
            'keySignatures' => [
                'backupPub' => '20f0854d0af1b22fad685a7580a4b8b45fc22a8a35a426f6c86c450faca5bdc9666bc2a88d7961ecdf9724a4247abcdc05bebef679d013ab309c15be9097c64cee',
                'bitgoPub' => '1ffdb32d0618b3ef93e0b85f9499d6d4a7a96fb47b2e9851d31e57ff790bb7c49f2c0456ccb666fedfd5f51c2b477456c48fe69c4b171ae7bfbaf8851432b8be45',
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
                'id' => '62f002e7b1440900072b848123196453',
                'address' => '2MwMtk2qWsP54LHqBqEk8cbgqtmG2qV5XSi',
                'chain' => 10,
                'index' => 1,
                'coin' => 'tbtc',
                'wallet' => '62f002e7b1440900072b8472fc8a9de8',
                'coinSpecific' => [
                    'redeemScript' => '00206ad786997ee4798fcaa70651b041e26b798127aaea3898a6dce306b6de2ce0e4',
                    'witnessScript' => '522103c6b657f7a39b7a7f956f3b46ab9cc62f9ed0a184549d5ae0b6b54de0052ac7f721034116165f883397724e86f14918031d70231c64187cfacded4aa51a6c658b9bc2210285d4c028327832c85ce57e921500a758038aefb841a542c3775793137295c52e53ae',
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
                'id' => '62f002e79b12b800077bea85071f633f',
                'pub' => 'xpub661MyMwAqRbcGqD3oX9sGtEjJZVQrtMYHqWPkDaeVpj8SugdVJwnF5zkiSTZoMo181UNkGDx5HKDtWSxNMZvdxnkrw2aXr19UQKxGiPxmhX',
                'ethAddress' => '0x500e9d8a71d51dc0d9e0f0e98d12276dbe177ee4',
                'source' => 'user',
                'type' => 'independent',
                'encryptedPrv' => '{"iv":"GD26lmXg0os1DLrXYXMMPA==","v":1,"iter":10000,"ks":256,"ts":64,"mode":"ccm","adata":"","cipher":"aes","salt":"KsRm/x1BTRw=","ct":"rqRbdviwV2B6rrqNYEUvUhSdN0acfM+D4fBSVn0BK/LePdCePW1K73QbIWOeYH0KujjGCjJ7eyRLNPokY7jx43a6n6vJlyvKaXZnVyMhVnlI8WipdPiE/jwcXAESqKaTNV2TsZF5X8jGb4jHmE3rdoAvbcDShvo="}',
                'prv' => 'xprv9s21ZrQH143K4M8ahVcrukHzkXevTRdgvcanwqB2wVC9a7MUwmdXhHgGs8SGmt3kZLh7BMKmb8H6ZWEcjUWCyhVk8Vcrs5jPRbJwJBHUAi9',
            ],
            'backupKeychain' => [
                'id' => '62f002e759cafb0007f3002f885697e9',
                'pub' => 'xpub661MyMwAqRbcGYpcqRc8eafSixRBaEdtpXF1nxEaHuUQoHHs8rJLSSp1bpE3dS4rVC943wBzZqGkzeed91GoaMRXuiJo3evDydLVRsgnQNi',
                'ethAddress' => '0xbd0a837c9d01b7d33915059f39793aa4b1391847',
                'source' => 'backup',
                'type' => 'independent',
                'prv' => 'xprv9s21ZrQH143K44k9jQ58HSiiAvahAmv3TJKQzZpxjZwRvUxibJz5teVXkZGaqV4a4HPeJon6QutcFe1pGZ89MDa1HssyGYhEQjag8S8pYPe',
            ],
            'bitgoKeychain' => [
                'id' => '62f002e77bd7400007ee7f10ed8c5af3',
                'pub' => 'xpub661MyMwAqRbcFZC9odQHUoA8C6AAj8pyHicqCHBjPFuTSygwb4UEeNXugroXVt1ChWKa3XXAo7onCjDWTYoVriVvExQkjiSoET5Fjq6ub32',
                'ethAddress' => '0x33b45cabc7251825675880385e862f5b109be96f',
                'source' => 'bitgo',
                'type' => 'independent',
                'isBitGo' => true,
            ],
            'responseType' => 'wallet',
            'warning' => 'Be sure to backup the backup keychain -- it is not stored anywhere else!',
        ];

        $webhookMock = [
            'id' => '631272d4358f790007d72487601864cf',
            'created' => '2022-09-02T21:17:08.805Z',
            'walletId' => '631272d36334c60007a2a61645fb770f',
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

        $maximumSpendableMock = [
            'maximumSpendable' => '47523',
            'miningFee' => '1026',
            'payGoFee' => '0',
            'coin' => 'tbtc',
        ];

        $transferObjectMock = [
            'id' => '62b1c6168e0b9e0007b421314aba0654',
            'coin' => 'tbtc',
            'wallet' => 'wallet-id',
            'value' => '100000000',
            'valueString' => '100000000',
        ];

        $addressObjectMock = [
            'id' => '631283e10e052800066295e210da142a',
            'address' => '2N9wCEV3KGEFsyo9xoUGjVYaSVwjSueutjz',
            'chain' => 10,
            'index' => 2,
            'coin' => 'tbtc',
            'wallet' => '6312824bf3281c0006fedaad1d667e67',
            'coinSpecific' => [
                'redeemScript' => '00200fdaf19ad268ab964d2e3fd352b69365d29d7a61cca4224eb5ef2b05fdcab106',
                'witnessScript' => '522103938ad2f9cb72f6d2001927afaa1f0fc66ed377ac0d19712565447d4c6b17c1db2102ccbaceae6b7bc2d34eec97787f8159dd2152a62103470f87c6736b0650b60aff2102c5b2bc81c2678d225f1defbe0e7acb48aca06fe15ea8beb107e4b729d788cf3e53ae',
            ],
            'addressType' => 'p2shP2wsh',
            'keychains' => [
                [
                    'id' => '6312824b86f4aa0007dd283d1253ac33',
                    'pub' => 'xpub661MyMwAqRbcEeuE59B7icbdeDQYFbCTxLRCjSkVYZmgKUZzwjwdXW1V8X33gRmR3LjQsFK9EbzgZ9NezZW2Wj55mScfPHwPitaGdiKJAe8',
                    'ethAddress' => '0x94bbb7c3582ae9fbb829daa9fd81e112184df260',
                    'source' => 'user',
                    'type' => 'independent',
                    'encryptedPrv' => '{"iv":"T+GU0GhX4XHRRg2ohnzp1w==","v":1,"iter":10000,"ks":256,"ts":64,"mode":"ccm","adata":"","cipher":"aes","salt":"Yn45uWDyOuM=","ct":"dbc8wRJU0art3tW6R/wCs2X3F8Fx4cS+sYw1/rPewTqgwuBoUUn+TO2otaxiWaw1lRKjF3mP+2IHTUif6W2n9ByCiva+xWO8l45nxWMG8RHABA5h0mmNZCZzNAD+0Eyd7MuU/9u0tqI81HBJcppgGu0FegdXbKc="}',
                ],
                [
                    'id' => '6312824bb6b93a00078c5624bc86a9b6',
                    'pub' => 'xpub661MyMwAqRbcFbRoBvNZ7hmNLD1E67drVGYjVQGBPBeZFRowbPnDNPdKK9c7oq19ekqrAQrJJjPVzepH1dDCJWdLu6Vjgt6BWuToUnkfxDw',
                    'ethAddress' => '0xbecce05e0b880092bd0d2c85b368ec563751b266',
                    'source' => 'backup',
                    'type' => 'independent',
                ],
                [
                    'id' => '6312824b86f4aa0007dd284157374ba2',
                    'pub' => 'xpub661MyMwAqRbcGcpsPgX4ZuS4PP7FHW7JzuDLeydN3iF66vka87L6SNaLMc3k4EUUnbFHswyEBX6SXsoQVH6MUvNjFAGGKB5xekSAe2yP5ne',
                    'ethAddress' => '0xc3a3c72b10d5b448533ff18b3254ca6bbf0d1ba3',
                    'source' => 'bitgo',
                    'type' => 'independent',
                    'isBitGo' => true,
                ],
            ],
        ];

        Http::preventStrayRequests();
        $testingUrl = config('crypto-wallet.drivers.bitgo.testnet_api_url').'/'.config('crypto-wallet.drivers.bitgo.v2_api_prefix');
        $expressUrl = config('crypto-wallet.drivers.bitgo.express_api_url').'/'.config('crypto-wallet.drivers.bitgo.v2_api_prefix');
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
