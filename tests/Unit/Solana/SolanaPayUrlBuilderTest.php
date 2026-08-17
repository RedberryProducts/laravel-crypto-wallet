<?php

use RedberryProducts\CryptoWallet\Data\PaymentRequest;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaPayUrlBuilder;

it('builds a native sol transfer request', function () {
    $url = (new SolanaPayUrlBuilder)->build(new PaymentRequest(
        merchantReference: 'order-1',
        recipientAddress: str_repeat('1', 32),
        referenceAddress: 'reference',
        asset: 'SOL',
        mintAddress: null,
        amount: '1.25',
        baseUnits: '1250000000',
        network: 'devnet',
        url: '',
        label: 'Tenant Shop',
        message: 'Order #1',
        memo: 'public memo',
    ));

    expect($url)->toBe('solana:'.str_repeat('1', 32).'?amount=1.25&reference=reference&label=Tenant%20Shop&message=Order%20%231&memo=public%20memo')
        ->not->toContain('merchantReference')
        ->not->toContain('order-1');
});

it('includes the exact spl token mint', function () {
    $url = (new SolanaPayUrlBuilder)->build(new PaymentRequest(
        merchantReference: 'order-2',
        recipientAddress: 'recipient',
        referenceAddress: 'reference',
        asset: 'USDT',
        mintAddress: 'mint',
        amount: '25.00',
        baseUnits: '25000000',
        network: 'devnet',
        url: '',
    ));

    expect($url)->toBe('solana:recipient?amount=25.00&spl-token=mint&reference=reference');
});
