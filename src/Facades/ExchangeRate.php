<?php

namespace RedberryProducts\CryptoWallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \RedberryProducts\CryptoWallet\ExchangeRate all()
 * @method static \RedberryProducts\CryptoWallet\ExchangeRate getByCoin(string $string)
 */
class ExchangeRate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RedberryProducts\CryptoWallet\ExchangeRate::class;
    }
}
