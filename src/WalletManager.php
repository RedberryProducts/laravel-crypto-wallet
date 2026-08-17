<?php

namespace RedberryProducts\CryptoWallet;

use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoDriver;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules\Wallet;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaDriver;

class WalletManager
{
    public static function driver(?string $name = null): WalletDriver
    {
        return app(WalletRegistry::class)->driver($name);
    }

    public static function bitgo(?string $coin = null, ?string $walletId = null): Wallet
    {
        $driver = self::driver('bitgo');

        if (! $driver instanceof BitgoDriver) {
            throw new \UnexpectedValueException('The [bitgo] driver is not a BitgoDriver instance.');
        }

        return $driver->wallet($coin, $walletId);
    }

    public static function solana(): SolanaDriver
    {
        $driver = self::driver('solana');

        if (! $driver instanceof SolanaDriver) {
            throw new \UnexpectedValueException('The [solana] driver is not a SolanaDriver instance.');
        }

        return $driver;
    }
}
