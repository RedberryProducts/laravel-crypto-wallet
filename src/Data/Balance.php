<?php

namespace RedberryProducts\CryptoWallet\Data;

use Spatie\LaravelData\Data;

class Balance extends Data
{
    public function __construct(
        public readonly string $asset,
        public readonly string $amount,
        public readonly string $baseUnits,
        public readonly int $decimals,
        public readonly ?string $mintAddress = null,
    ) {}
}
