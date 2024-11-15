<?php

namespace RedberryProducts\CryptoWallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \RedberryProducts\CryptoWallet\Wallet init(string $coin, string $id = null)
 * @method static \Illuminate\Support\Collection listAll(string $coin = null, ?array $params = []))
 */
class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RedberryProducts\CryptoWallet\Wallet::class;
    }
}
