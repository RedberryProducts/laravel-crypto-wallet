<?php

namespace RedberryProducts\CryptoWallet\Facades;

use Illuminate\Support\Facades\Facade;
use RedberryProducts\CryptoWallet\Contracts\WalletContract;

/**
 * @method static WalletContract init(string $coin, ?string $id = null)
 * @method static array listAll(?string $coin = null, ?array $params = [])
 */
class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WalletContract::class;
    }
}
