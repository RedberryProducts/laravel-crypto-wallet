<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class WalletData extends Data
{
    public function __construct(
        public Wallet $wallet,

        public UserKeyChain $userKeychain,

        public BackupKeyChain $backupKeychain,

        public BitGoKeychain $bitgoKeychain,

        public string $responseType,

        public ?string $warning,
    ) {}
}
