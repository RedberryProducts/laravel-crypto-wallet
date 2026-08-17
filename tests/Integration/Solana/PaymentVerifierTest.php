<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Data\ExpectedPayment;
use RedberryProducts\CryptoWallet\Enums\PaymentStatus;
use RedberryProducts\CryptoWallet\Exceptions\NetworkMismatchException;
use RedberryProducts\CryptoWallet\WalletManager;

it('returns pending when the reference has no signatures', function () {
    fakeSolanaRpc([]);

    $result = WalletManager::solana()->verifyPayment(expectedSolPayment());

    expect($result->status)->toBe(PaymentStatus::Pending)
        ->and($result->signature)->toBeNull();
});

it('rejects a network mismatch before making an rpc request', function () {
    Http::fake();
    $payment = expectedSolPayment(network: 'mainnet-beta');

    try {
        WalletManager::solana()->verifyPayment($payment);
    } finally {
        Http::assertNothingSent();
    }
})->throws(NetworkMismatchException::class);

it('confirms an exact native sol transfer', function () {
    fakeSolanaRpc([signatureResult()], solTransaction('recipient', '2500000000'));

    $result = WalletManager::solana()->verifyPayment(expectedSolPayment());

    expect($result->status)->toBe(PaymentStatus::Confirmed)
        ->and($result->signature)->toBe('signature-1')
        ->and($result->actualRecipientAddress)->toBe('recipient')
        ->and($result->actualBaseUnits)->toBe('2500000000');
});

it('returns failed when the referenced transaction execution failed', function () {
    $transaction = solTransaction('recipient', '2500000000');
    $transaction['meta']['err'] = ['InstructionError' => [0, 'Custom']];
    fakeSolanaRpc([signatureResult()], $transaction);

    expect(WalletManager::solana()->verifyPayment(expectedSolPayment())->status)
        ->toBe(PaymentStatus::Failed);
});

it('returns mismatch for an incorrect sol recipient or amount', function (string $recipient, string $amount) {
    fakeSolanaRpc([signatureResult()], solTransaction($recipient, $amount));

    expect(WalletManager::solana()->verifyPayment(expectedSolPayment())->status)
        ->toBe(PaymentStatus::Mismatch);
})->with([
    ['wrong-recipient', '2500000000'],
    ['recipient', '1'],
]);

it('continues past a mismatching candidate to find an exact payment', function () {
    Http::fake(function (Request $request) {
        if ($request['method'] === 'getSignaturesForAddress') {
            return Http::response([
                'jsonrpc' => '2.0',
                'id' => $request['id'],
                'result' => [
                    ['signature' => 'wrong', 'err' => null, 'confirmationStatus' => 'confirmed'],
                    ['signature' => 'correct', 'err' => null, 'confirmationStatus' => 'confirmed'],
                ],
            ]);
        }

        $transaction = solTransaction(
            'recipient',
            $request['params'][0] === 'correct' ? '2500000000' : '1',
        );

        return Http::response(['jsonrpc' => '2.0', 'id' => $request['id'], 'result' => $transaction]);
    });

    $result = WalletManager::solana()->verifyPayment(expectedSolPayment());

    expect($result->status)->toBe(PaymentStatus::Confirmed)
        ->and($result->signature)->toBe('correct');
});

it('confirms an exact spl token transfer to an account owned by the merchant', function () {
    config()->set('crypto-wallet.drivers.solana.assets.USDT.mint', str_repeat('1', 32));
    fakeSolanaRpc([signatureResult()], splTransaction(str_repeat('1', 32), 'recipient', '25000000'));

    $result = WalletManager::solana()->verifyPayment(expectedTokenPayment());

    expect($result->status)->toBe(PaymentStatus::Confirmed)
        ->and($result->actualMintAddress)->toBe(str_repeat('1', 32))
        ->and($result->actualRecipientAddress)->toBe('recipient');
});

it('returns mismatch for the wrong token mint, owner, or amount', function (string $mint, string $owner, string $amount) {
    config()->set('crypto-wallet.drivers.solana.assets.USDT.mint', str_repeat('1', 32));
    fakeSolanaRpc([signatureResult()], splTransaction($mint, $owner, $amount));

    expect(WalletManager::solana()->verifyPayment(expectedTokenPayment())->status)
        ->toBe(PaymentStatus::Mismatch);
})->with([
    [str_repeat('2', 32), 'recipient', '25000000'],
    [str_repeat('1', 32), 'wrong-owner', '25000000'],
    [str_repeat('1', 32), 'recipient', '1'],
]);

function expectedSolPayment(string $network = 'devnet'): ExpectedPayment
{
    return new ExpectedPayment($network, 'recipient', 'reference', 'SOL', null, '2.5', '2500000000');
}

function expectedTokenPayment(): ExpectedPayment
{
    return new ExpectedPayment('devnet', 'recipient', 'reference', 'USDT', str_repeat('1', 32), '25.00', '25000000');
}

function signatureResult(): array
{
    return ['signature' => 'signature-1', 'err' => null, 'confirmationStatus' => 'confirmed'];
}

function fakeSolanaRpc(array $signatures, ?array $transaction = null): void
{
    Http::fake(function (Request $request) use ($signatures, $transaction) {
        $result = $request['method'] === 'getSignaturesForAddress' ? $signatures : $transaction;

        return Http::response(['jsonrpc' => '2.0', 'id' => $request['id'], 'result' => $result]);
    });
}

function solTransaction(string $recipient, string $lamports): array
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
                    'parsed' => [
                        'type' => 'transfer',
                        'info' => ['source' => 'payer', 'destination' => $recipient, 'lamports' => $lamports],
                    ],
                ]],
            ],
        ],
    ];
}

function splTransaction(string $mint, string $owner, string $amount): array
{
    return [
        'slot' => 100,
        'meta' => [
            'err' => null,
            'innerInstructions' => [],
            'postTokenBalances' => [[
                'accountIndex' => 2,
                'mint' => $mint,
                'owner' => $owner,
                'uiTokenAmount' => ['amount' => $amount, 'decimals' => 6],
            ]],
        ],
        'transaction' => [
            'signatures' => ['signature-1'],
            'message' => [
                'accountKeys' => ['payer-token', 'reference', 'recipient-token-account'],
                'instructions' => [[
                    'program' => 'spl-token',
                    'parsed' => [
                        'type' => 'transferChecked',
                        'info' => [
                            'source' => 'payer-token',
                            'destination' => 'recipient-token-account',
                            'mint' => $mint,
                            'tokenAmount' => ['amount' => $amount, 'decimals' => 6],
                        ],
                    ],
                ]],
            ],
        ],
    ];
}
