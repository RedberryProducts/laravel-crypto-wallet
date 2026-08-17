<?php

namespace RedberryProducts\CryptoWallet\Data;

use Spatie\LaravelData\Data;

class AddressValidationResult extends Data
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $address,
        public readonly ?string $reason = null,
    ) {}
}
