<?php

use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoDriver;
use RedberryProducts\CryptoWallet\WalletManager;

it('uses the configured default coin when opening a bitgo wallet', function () {
    config()->set('crypto-wallet.drivers.bitgo.default_coin', 'tbtc4');

    $wallet = WalletManager::bitgo();

    expect($wallet->coin)->toBe('tbtc4')
        ->and(WalletManager::driver('bitgo'))->toBeInstanceOf(BitgoDriver::class);
});

it('preserves an explicitly selected bitgo coin', function () {
    expect(WalletManager::bitgo('teth')->coin)->toBe('teth');
});
