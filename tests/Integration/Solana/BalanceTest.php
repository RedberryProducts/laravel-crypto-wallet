<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\WalletManager;

beforeEach(function () {
    config()->set('crypto-wallet.drivers.solana.assets.USDT.mint', str_repeat('1', 32));
});

it('reads an exact sol balance', function () {
    Http::fake(function (Request $request) {
        expect($request['method'])->toBe('getBalance');

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => ['context' => ['slot' => 1], 'value' => 1_234_567_890],
        ]);
    });

    $balance = WalletManager::solana()
        ->forMerchant(str_repeat('1', 32))
        ->getBalance('SOL');

    expect($balance->baseUnits)->toBe('1234567890')
        ->and($balance->amount)->toBe('1.23456789')
        ->and($balance->mintAddress)->toBeNull();
});

it('sums exact balances across token accounts for a configured mint', function () {
    Http::fake(function (Request $request) {
        expect($request['method'])->toBe('getTokenAccountsByOwner')
            ->and($request['params'][1])->toBe(['mint' => str_repeat('1', 32)]);

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => ['value' => [
                tokenAccount('25000000'),
                tokenAccount('500000'),
            ]],
        ]);
    });

    $balance = WalletManager::solana()
        ->forMerchant((new AddressCodec)->encode(str_repeat("\2", 32)))
        ->getBalance('USDT');

    expect($balance->baseUnits)->toBe('25500000')
        ->and($balance->amount)->toBe('25.5')
        ->and($balance->mintAddress)->toBe(str_repeat('1', 32));
});

it('returns zero when a merchant has no token account for the mint', function () {
    Http::fake(fn (Request $request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'result' => ['value' => []],
    ]));

    $balance = WalletManager::solana()
        ->forMerchant(str_repeat('1', 32))
        ->getBalance('USDT');

    expect($balance->baseUnits)->toBe('0')
        ->and($balance->amount)->toBe('0');
});

function tokenAccount(string $amount): array
{
    return [
        'pubkey' => str_repeat('1', 32),
        'account' => [
            'data' => [
                'parsed' => [
                    'info' => [
                        'tokenAmount' => [
                            'amount' => $amount,
                            'decimals' => 6,
                        ],
                    ],
                ],
            ],
        ],
    ];
}
