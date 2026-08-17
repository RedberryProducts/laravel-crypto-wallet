<?php

namespace RedberryProducts\CryptoWallet\Data;

use RedberryProducts\CryptoWallet\Enums\AssetType;
use Spatie\LaravelData\Data;

class Asset extends Data
{
    public function __construct(
        public readonly string $symbol,
        public readonly AssetType $type,
        public readonly int $decimals,
        public readonly string $network,
        public readonly ?string $mintAddress = null,
    ) {}
}
