<?php

use RedberryProducts\CryptoWallet\WalletManager;

it('can read an explicitly configured devnet merchant', function () {
    if (env('SOLANA_DEVNET_TEST') !== '1') {
        $this->markTestSkipped('Set SOLANA_DEVNET_TEST=1 to enable Devnet RPC tests.');
    }

    $address = env('SOLANA_TEST_MERCHANT_ADDRESS');

    if (! is_string($address) || $address === '') {
        $this->markTestSkipped('Set SOLANA_TEST_MERCHANT_ADDRESS to a Devnet address.');
    }

    expect(WalletManager::solana()->forMerchant($address)->getBalance('SOL')->baseUnits)->toBeString();
})->group('solana-devnet');
