<?php

namespace RedberryProducts\CryptoWallet\Data;

use DateTimeInterface;
use Spatie\LaravelData\Data;

class CreatePaymentRequest extends Data
{
    public function __construct(
        public readonly string $amount,
        public readonly string $asset,
        public readonly string $merchantReference,
        public readonly ?string $label = null,
        public readonly ?string $message = null,
        public readonly ?string $memo = null,
        public readonly ?DateTimeInterface $expiresAt = null,
    ) {}
}
