<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class KeySignature extends Data
{
    /**
     * Signature for the backup pub
     */
    public string $backupPub;

    /**
     * Signature for the BitGo pub
     */
    public string $bitgoPub;
}
