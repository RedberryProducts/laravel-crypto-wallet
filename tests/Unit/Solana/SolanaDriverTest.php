<?php

use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaDriver;
use RedberryProducts\CryptoWallet\Enums\Commitment;
use RedberryProducts\CryptoWallet\Enums\NetworkName;
use RedberryProducts\CryptoWallet\WalletManager;

it('resolves the solana driver through the wallet manager', function () {
    $driver = WalletManager::solana();

    expect($driver)->toBeInstanceOf(SolanaDriver::class)
        ->toBeInstanceOf(WalletDriver::class)
        ->and(WalletManager::driver('solana'))->toBe($driver)
        ->and($driver->name())->toBe('solana');
});

it('exposes its configured network and assets', function () {
    $driver = WalletManager::solana();

    expect($driver->network()->name)->toBe(NetworkName::Devnet)
        ->and($driver->network()->commitment)->toBe(Commitment::Confirmed)
        ->and($driver->asset('SOL')->decimals)->toBe(9);
});
