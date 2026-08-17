<?php

namespace RedberryProducts\CryptoWallet\Data;

use DateTimeInterface;
use Spatie\LaravelData\Data;

class PaymentRequest extends Data
{
    public function __construct(
        public readonly string $merchantReference,
        public readonly string $recipientAddress,
        public readonly string $referenceAddress,
        public readonly string $asset,
        public readonly ?string $mintAddress,
        public readonly string $amount,
        public readonly string $baseUnits,
        public readonly string $network,
        public readonly string $url,
        public readonly ?DateTimeInterface $expiresAt = null,
        public readonly ?string $label = null,
        public readonly ?string $message = null,
        public readonly ?string $memo = null,
    ) {}
}
