<?php

namespace RedberryProducts\CryptoWallet\Data;

use Spatie\LaravelData\Data;

class ExpectedPayment extends Data
{
    public function __construct(
        public readonly string $network,
        public readonly string $recipientAddress,
        public readonly string $referenceAddress,
        public readonly string $asset,
        public readonly ?string $mintAddress,
        public readonly string $amount,
        public readonly string $baseUnits,
    ) {}
}
