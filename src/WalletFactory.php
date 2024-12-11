<?php

namespace RedberryProducts\CryptoWallet;

use RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules\Wallet;

class WalletFactory
{
    //static method that return bitgo wallet
    public static function bitgo(?string $coin = null, ?string $walletId = null): Wallet
    {
        return new Wallet($coin, $walletId);
    }
}
