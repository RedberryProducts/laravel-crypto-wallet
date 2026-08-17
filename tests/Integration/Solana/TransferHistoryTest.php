<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Data\TransferQuery;
use RedberryProducts\CryptoWallet\WalletManager;

it('reads transfer history with capped pagination', function () {
    Http::fake(function (Request $request) {
        if ($request['method'] === 'getSignaturesForAddress') {
            expect($request['params'][1]['limit'])->toBe(100)
                ->and($request['params'][1]['before'])->toBe('cursor');

            return Http::response([
                'jsonrpc' => '2.0',
                'id' => $request['id'],
                'result' => [historySignatureResult()],
            ]);
        }

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => historySolTransaction(str_repeat('1', 32), '1500000000'),
        ]);
    });

    $transfers = WalletManager::solana()
        ->forMerchant(str_repeat('1', 32))
        ->getTransfers(new TransferQuery(500, 'cursor'));

    expect($transfers)->toHaveCount(1)
        ->and($transfers[0]->amount)->toBe('1.5');
});

it('returns null when an individual transaction is unavailable', function () {
    Http::fake(fn (Request $request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'result' => null,
    ]));

    $transfer = WalletManager::solana()
        ->forMerchant(str_repeat('1', 32))
        ->getTransfer('missing-signature');

    expect($transfer)->toBeNull();
});

it('uses the full raw signature page cursor when normalized transfers are filtered or missing', function () {
    $merchant = str_repeat('1', 32);
    $signatureReads = 0;

    Http::fake(function (Request $request) use ($merchant, &$signatureReads) {
        if ($request['method'] === 'getSignaturesForAddress') {
            $signatureReads++;
            expect($request['params'][1]['limit'])->toBe(3);
            $before = $request['params'][1]['before'] ?? null;

            return Http::response([
                'jsonrpc' => '2.0',
                'id' => $request['id'],
                'result' => $before === null
                    ? [
                        historySignatureResult('known-signature'),
                        historySignatureResult('missing-signature'),
                        historySignatureResult('unknown-token-signature'),
                    ]
                    : [historySignatureResult('older-signature')],
            ]);
        }

        $signature = $request['params'][0];
        $transaction = match ($signature) {
            'known-signature' => historySolTransaction($merchant, '1', $signature),
            'unknown-token-signature' => historyUnknownSplTransaction($merchant, $signature),
            'missing-signature' => null,
            'older-signature' => historySolTransaction($merchant, '2', $signature),
        };

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => $transaction,
        ]);
    });

    $context = WalletManager::solana()->forMerchant($merchant);
    $first = $context->getTransferPage(new TransferQuery(3));
    $older = $context->getTransferPage(new TransferQuery(3, $first->nextBefore));

    expect($first->transfers)->toHaveCount(1)
        ->and($first->transfers[0]->signature)->toBe('known-signature')
        ->and($first->nextBefore)->toBe('unknown-token-signature')
        ->and($older->transfers)->toHaveCount(1)
        ->and($older->transfers[0]->signature)->toBe('older-signature')
        ->and($older->nextBefore)->toBeNull()
        ->and($signatureReads)->toBe(2);
});

it('keeps all normalized records when one raw signature contains multiple transfers', function () {
    $merchant = str_repeat('1', 32);

    Http::fake(function (Request $request) use ($merchant) {
        $result = $request['method'] === 'getSignaturesForAddress'
            ? [historySignatureResult('multi-signature')]
            : historyMultiSolTransaction($merchant, 'multi-signature');

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => $result,
        ]);
    });

    $context = WalletManager::solana()->forMerchant($merchant);
    $page = $context->getTransferPage(new TransferQuery(1));
    $legacyTransfers = $context->getTransfers(new TransferQuery(1));
    $signatureTransfers = $context->getTransfersForSignature('multi-signature');
    $legacyDetail = $context->getTransfer('multi-signature');

    expect($page->transfers)->toHaveCount(2)
        ->and($page->transfers[0]->baseUnits)->toBe('1')
        ->and($page->transfers[1]->baseUnits)->toBe('2')
        ->and($page->nextBefore)->toBe('multi-signature')
        ->and($legacyTransfers)->toHaveCount(2)
        ->and($signatureTransfers)->toHaveCount(2)
        ->and($signatureTransfers[1]->baseUnits)->toBe('2')
        ->and($legacyDetail?->baseUnits)->toBe('1');
});

function historySignatureResult(string $signature = 'signature-1'): array
{
    return ['signature' => $signature, 'err' => null, 'confirmationStatus' => 'confirmed'];
}

function historySolTransaction(string $recipient, string $lamports, string $signature = 'signature-1'): array
{
    return [
        'slot' => 100,
        'blockTime' => 1_700_000_000,
        'meta' => ['err' => null, 'innerInstructions' => []],
        'transaction' => [
            'signatures' => [$signature],
            'message' => [
                'accountKeys' => ['payer', $recipient],
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

function historyUnknownSplTransaction(string $owner, string $signature): array
{
    $mint = str_repeat('2', 32);

    return [
        'slot' => 101,
        'blockTime' => 1_700_000_001,
        'meta' => [
            'err' => null,
            'innerInstructions' => [],
            'postTokenBalances' => [[
                'accountIndex' => 1,
                'mint' => $mint,
                'owner' => $owner,
                'uiTokenAmount' => ['amount' => '5', 'decimals' => 6],
            ]],
        ],
        'transaction' => [
            'signatures' => [$signature],
            'message' => [
                'accountKeys' => ['source-token', 'destination-token'],
                'instructions' => [[
                    'program' => 'spl-token',
                    'parsed' => ['type' => 'transferChecked', 'info' => [
                        'source' => 'source-token',
                        'destination' => 'destination-token',
                        'mint' => $mint,
                        'tokenAmount' => ['amount' => '5', 'decimals' => 6],
                    ]],
                ]],
            ],
        ],
    ];
}

function historyMultiSolTransaction(string $recipient, string $signature): array
{
    $transaction = historySolTransaction($recipient, '1', $signature);
    $transaction['transaction']['message']['instructions'][] = [
        'program' => 'system',
        'parsed' => ['type' => 'transfer', 'info' => [
            'source' => 'payer',
            'destination' => $recipient,
            'lamports' => '2',
        ]],
    ];

    return $transaction;
}
