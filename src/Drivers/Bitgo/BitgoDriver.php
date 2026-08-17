<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo;

use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules\Wallet;

class BitgoDriver implements WalletDriver
{
    public function __construct(private readonly string $defaultCoin) {}

    public function name(): string
    {
        return 'bitgo';
    }

    public function wallet(?string $coin = null, ?string $walletId = null): Wallet
    {
        return new Wallet($coin ?? $this->defaultCoin, $walletId);
    }
}
