<?php

namespace RedberryProducts\CryptoWallet\Facades;

use Illuminate\Support\Facades\Facade;
use RedberryProducts\CryptoWallet\Contracts\ExchangeRateContract;

/**
 * @method static ExchangeRateContract all()
 * @method static ExchangeRateContract getByCoin(string $string)
 */
class ExchangeRate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ExchangeRateContract::class;
    }
}
