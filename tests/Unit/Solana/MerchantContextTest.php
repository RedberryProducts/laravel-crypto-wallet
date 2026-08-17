<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\WalletManager;

it('keeps merchant addresses isolated across tenant contexts', function () {
    $codec = new AddressCodec;
    $addressA = $codec->encode(str_repeat("\0", 32));
    $addressB = $codec->encode(str_repeat("\1", 32));
    $driver = WalletManager::solana();

    $tenantA = $driver->forMerchant($addressA);
    $tenantB = $driver->forMerchant($addressB);

    expect($tenantA->address())->toBe($addressA)
        ->and($tenantB->address())->toBe($addressB)
        ->and($tenantA->address())->toBe($addressA)
        ->and($tenantA->network())->toBe('devnet');
});

it('validates merchant addresses without mutating the driver', function () {
    $driver = WalletManager::solana();

    expect($driver->validateAddress(str_repeat('1', 32))->valid)->toBeTrue()
        ->and($driver->validateAddress('bad')->valid)->toBeFalse();
});
