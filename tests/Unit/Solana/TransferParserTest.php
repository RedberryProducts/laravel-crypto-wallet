<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Drivers\Solana\TransferParser;
use RedberryProducts\CryptoWallet\Enums\TransferDirection;

it('normalizes an incoming native sol transfer', function () {
    $parser = transferParser();
    $transaction = parserSolTransaction('merchant', '1500000000');

    $transfers = $parser->parse($transaction, 'merchant', 'confirmed');

    expect($transfers)->toHaveCount(1)
        ->and($transfers[0]->signature)->toBe('signature-1')
        ->and($transfers[0]->direction)->toBe(TransferDirection::Incoming)
        ->and($transfers[0]->asset)->toBe('SOL')
        ->and($transfers[0]->amount)->toBe('1.5');
});

it('normalizes a configured spl token transfer', function () {
    $parser = transferParser();
    $transaction = parserSplTransaction(str_repeat('1', 32), 'merchant', '25000000');

    $transfers = $parser->parse($transaction, 'merchant', 'finalized');

    expect($transfers)->toHaveCount(1)
        ->and($transfers[0]->asset)->toBe('USDT')
        ->and($transfers[0]->mintAddress)->toBe(str_repeat('1', 32))
        ->and($transfers[0]->baseUnits)->toBe('25000000')
        ->and($transfers[0]->confirmationStatus)->toBe('finalized');
});

it('does not report instructions from a failed transaction as transfers', function () {
    $parser = transferParser();
    $transaction = parserSolTransaction('merchant', '1500000000');
    $transaction['meta']['err'] = ['InstructionError' => [0, 'Custom']];

    expect($parser->parse($transaction, 'merchant', 'confirmed'))->toBe([]);
});

function transferParser(): TransferParser
{
    return new TransferParser(
        new AssetRegistry('devnet', [
            'SOL' => ['type' => 'native', 'decimals' => 9],
            'USDT' => ['type' => 'spl', 'decimals' => 6, 'mint' => str_repeat('1', 32)],
        ], new AddressCodec),
        new AmountConverter,
    );
}

function parserSolTransaction(string $recipient, string $lamports): array
{
    return [
        'slot' => 100,
        'blockTime' => 1_700_000_000,
        'meta' => ['err' => null, 'innerInstructions' => []],
        'transaction' => [
            'signatures' => ['signature-1'],
            'message' => [
                'accountKeys' => ['payer', 'reference', $recipient],
                'instructions' => [[
                    'program' => 'system',
                    'parsed' => ['type' => 'transfer', 'info' => [
                        'source' => 'payer', 'destination' => $recipient, 'lamports' => $lamports,
                    ]],
                ]],
            ],
        ],
    ];
}

function parserSplTransaction(string $mint, string $owner, string $amount): array
{
    return [
        'slot' => 100,
        'blockTime' => 1_700_000_000,
        'meta' => [
            'err' => null,
            'innerInstructions' => [],
            'postTokenBalances' => [[
                'accountIndex' => 2, 'mint' => $mint, 'owner' => $owner,
                'uiTokenAmount' => ['amount' => $amount, 'decimals' => 6],
            ]],
        ],
        'transaction' => [
            'signatures' => ['signature-1'],
            'message' => [
                'accountKeys' => ['payer-token', 'reference', 'recipient-token-account'],
                'instructions' => [[
                    'program' => 'spl-token',
                    'parsed' => ['type' => 'transferChecked', 'info' => [
                        'source' => 'payer-token', 'destination' => 'recipient-token-account',
                        'mint' => $mint, 'tokenAmount' => ['amount' => $amount, 'decimals' => 6],
                    ]],
                ]],
            ],
        ],
    ];
}
