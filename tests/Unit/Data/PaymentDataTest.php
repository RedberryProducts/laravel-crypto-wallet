<?php

use RedberryProducts\CryptoWallet\Data\Balance;
use RedberryProducts\CryptoWallet\Data\CreatePaymentRequest;
use RedberryProducts\CryptoWallet\Data\ExpectedPayment;
use RedberryProducts\CryptoWallet\Data\PaymentVerificationResult;
use RedberryProducts\CryptoWallet\Enums\PaymentStatus;

it('preserves monetary values as strings', function () {
    $balance = new Balance('USDT', '25.00', '25000000', 6, 'mint');

    expect($balance->amount)->toBeString()->toBe('25.00')
        ->and($balance->baseUnits)->toBeString()->toBe('25000000')
        ->and($balance->toArray())->toMatchArray([
            'asset' => 'USDT',
            'amount' => '25.00',
            'baseUnits' => '25000000',
            'decimals' => 6,
            'mintAddress' => 'mint',
        ]);
});

it('captures an immutable payment request input', function () {
    $expiresAt = new DateTimeImmutable('2026-08-12T12:00:00+00:00');
    $request = new CreatePaymentRequest(
        amount: '25.00',
        asset: 'USDT',
        merchantReference: 'order-123',
        label: 'Tenant A',
        message: 'Payment for order 123',
        expiresAt: $expiresAt,
    );

    expect($request->merchantReference)->toBe('order-123')
        ->and($request->expiresAt)->toBe($expiresAt);
});

it('captures the exact expected on-chain payment snapshot', function () {
    $expected = new ExpectedPayment(
        network: 'devnet',
        recipientAddress: 'recipient',
        referenceAddress: 'reference',
        asset: 'USDT',
        mintAddress: 'mint',
        amount: '25.00',
        baseUnits: '25000000',
    );

    expect($expected->baseUnits)->toBe('25000000')
        ->and($expected->network)->toBe('devnet');
});

it('uses explicit payment verification states', function () {
    $result = new PaymentVerificationResult(PaymentStatus::Confirmed, 'signature');

    expect($result->status)->toBe(PaymentStatus::Confirmed)
        ->and($result->signature)->toBe('signature')
        ->and(PaymentStatus::cases())->toHaveCount(4);
});
