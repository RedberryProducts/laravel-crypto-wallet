<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

class WalletData extends Data
{
    public Wallet $wallet;

    public UserKeyChain $userKeychain;

    public BackupKeyChain $backupKeychain;

    public BitGoKeychain $bitgoKeychain;

    public string $responseType;

    public ?string $warning;
}
