<?php

namespace RedberryProducts\CryptoWallet\Data;

use RedberryProducts\CryptoWallet\Enums\Commitment;
use RedberryProducts\CryptoWallet\Enums\NetworkName;
use Spatie\LaravelData\Data;

class Network extends Data
{
    public function __construct(
        public readonly NetworkName $name,
        public readonly Commitment $commitment,
    ) {}
}
