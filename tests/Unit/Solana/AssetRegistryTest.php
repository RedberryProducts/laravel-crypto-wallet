<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Enums\AssetType;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAssetConfigurationException;
use RedberryProducts\CryptoWallet\Exceptions\UnsupportedAssetException;

it('resolves native and spl assets for the configured network', function () {
    $registry = new AssetRegistry('devnet', [
        'SOL' => ['type' => 'native', 'decimals' => 9],
        'USDT' => ['type' => 'spl', 'decimals' => 6, 'mint' => str_repeat('1', 32)],
    ], new AddressCodec);

    expect($registry->get('sol')->type)->toBe(AssetType::Native)
        ->and($registry->get('USDT')->type)->toBe(AssetType::Token)
        ->and($registry->get('USDT')->mintAddress)->toBe(str_repeat('1', 32));
});

it('does not validate an incomplete asset until selected', function () {
    $registry = new AssetRegistry('devnet', [
        'SOL' => ['type' => 'native', 'decimals' => 9],
        'USDT' => ['type' => 'spl', 'decimals' => 6, 'mint' => null],
    ], new AddressCodec);

    expect($registry->get('SOL')->symbol)->toBe('SOL');

    $registry->get('USDT');
})->throws(InvalidAssetConfigurationException::class);

it('rejects unknown assets', function () {
    (new AssetRegistry('devnet', [], new AddressCodec))->get('USDT');
})->throws(UnsupportedAssetException::class);
