<?php

use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Data\CreatePaymentRequest;
use RedberryProducts\CryptoWallet\WalletManager;

beforeEach(function () {
    config()->set('crypto-wallet.drivers.solana.assets.USDT.mint', str_repeat('1', 32));
    Http::fake();
});

it('creates unique exact token payment requests without rpc calls', function () {
    $merchant = WalletManager::solana()->forMerchant(str_repeat('1', 32));
    $input = new CreatePaymentRequest(
        amount: '25.00',
        asset: 'USDT',
        merchantReference: 'order-123',
        label: 'Tenant A',
        expiresAt: new DateTimeImmutable('+15 minutes'),
    );

    $first = $merchant->createPaymentRequest($input);
    $second = $merchant->createPaymentRequest($input);

    expect($first->referenceAddress)->not->toBe($second->referenceAddress)
        ->and($first->baseUnits)->toBe('25000000')
        ->and($first->recipientAddress)->toBe(str_repeat('1', 32))
        ->and($first->mintAddress)->toBe(str_repeat('1', 32))
        ->and($first->url)->toContain('spl-token='.str_repeat('1', 32))
        ->and($first->url)->toContain('reference='.$first->referenceAddress);

    Http::assertNothingSent();
});

it('creates a native sol payment request without a token mint', function () {
    $request = WalletManager::solana()
        ->forMerchant(str_repeat('1', 32))
        ->createPaymentRequest(new CreatePaymentRequest('0.5', 'SOL', 'order-456'));

    expect($request->baseUnits)->toBe('500000000')
        ->and($request->mintAddress)->toBeNull()
        ->and($request->url)->not->toContain('spl-token');
});
