<?php

namespace RedberryProducts\CryptoWallet\Data;

use RedberryProducts\CryptoWallet\Enums\TransferDirection;
use Spatie\LaravelData\Data;

class Transfer extends Data
{
    public function __construct(
        public readonly string $signature,
        public readonly TransferDirection $direction,
        public readonly string $asset,
        public readonly string $amount,
        public readonly string $baseUnits,
        public readonly ?string $mintAddress = null,
        public readonly ?string $fromAddress = null,
        public readonly ?string $toAddress = null,
        public readonly ?int $slot = null,
        public readonly ?int $blockTime = null,
        public readonly ?string $confirmationStatus = null,
    ) {}
}
