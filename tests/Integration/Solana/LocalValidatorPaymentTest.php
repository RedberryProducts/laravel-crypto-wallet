<?php

use RedberryProducts\CryptoWallet\WalletManager;

it('can read an explicitly configured local validator merchant', function () {
    if (env('SOLANA_LOCAL_TEST') !== '1') {
        $this->markTestSkipped('Set SOLANA_LOCAL_TEST=1 to enable local-validator RPC tests.');
    }

    $address = env('SOLANA_TEST_MERCHANT_ADDRESS');

    if (! is_string($address) || $address === '') {
        $this->markTestSkipped('Set SOLANA_TEST_MERCHANT_ADDRESS to a local-validator address.');
    }

    expect(WalletManager::solana()->forMerchant($address)->getBalance('SOL')->baseUnits)->toBeString();
})->group('solana-local');
