<?php

use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoDriver;
use RedberryProducts\CryptoWallet\Exceptions\UnsupportedDriverException;
use RedberryProducts\CryptoWallet\WalletManager;
use RedberryProducts\CryptoWallet\WalletRegistry;

it('resolves the configured bitgo driver', function () {
    $driver = app(WalletRegistry::class)->driver('bitgo');

    expect($driver)
        ->toBeInstanceOf(WalletDriver::class)
        ->toBeInstanceOf(BitgoDriver::class)
        ->and($driver->name())->toBe('bitgo');
});

it('resolves the configured default driver', function () {
    config()->set('crypto-wallet.default', 'bitgo');

    expect(app(WalletRegistry::class)->driver())->toBeInstanceOf(BitgoDriver::class);
});

it('exposes drivers through the wallet manager', function () {
    expect(WalletManager::driver('bitgo'))
        ->toBe(app(WalletRegistry::class)->driver('bitgo'));
});

it('rejects an unconfigured driver', function () {
    app(WalletRegistry::class)->driver('missing');
})->throws(UnsupportedDriverException::class, 'Crypto wallet driver [missing] is not configured.');

it('keeps the existing bitgo wallet entry point', function () {
    $wallet = WalletManager::bitgo('tbtc', 'wallet-id');

    expect($wallet->coin)->toBe('tbtc')
        ->and($wallet->id)->toBe('wallet-id');
});
