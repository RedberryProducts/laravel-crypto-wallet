<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Address;

use Spatie\LaravelData\Data;

class KeyChain extends Data
{
    public string $id;

    public string $pub;

    public string $ethAddress;

    public string $source;

    public string $type;

    public ?string $encryptedPrv;

    public ?bool $isBitGo;
}
