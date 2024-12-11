<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class KeySignatures extends Data
{
    public string $backupPub;

    public string $bitgoPub;
}
