<?php

namespace RedberryProducts\CryptoWallet\Data;

use RedberryProducts\CryptoWallet\Enums\PaymentStatus;
use Spatie\LaravelData\Data;

class PaymentVerificationResult extends Data
{
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?string $signature = null,
        public readonly ?string $actualRecipientAddress = null,
        public readonly ?string $actualMintAddress = null,
        public readonly ?string $actualBaseUnits = null,
        public readonly ?string $reason = null,
    ) {}
}
