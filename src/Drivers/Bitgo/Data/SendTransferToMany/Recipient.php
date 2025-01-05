<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany;

use Spatie\LaravelData\Data;

class Recipient extends Data
{
    public function __construct(
        public string $address,
        public int $amount,
        public ?string $tokenName = null,
        public ?TokenData $tokenData = null,
    ) {}
}
