<?php

namespace RedberryProducts\CryptoWallet\Facades;

use Illuminate\Support\Facades\Facade;
use RedberryProducts\CryptoWallet\WalletRegistry;

class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WalletRegistry::class;
    }
}
