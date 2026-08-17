<?php

namespace RedberryProducts\CryptoWallet\Data;

use Spatie\LaravelData\Data;

class TransferPage extends Data
{
    /** @param list<Transfer> $transfers */
    public function __construct(
        public readonly array $transfers,
        public readonly ?string $nextBefore,
    ) {}
}
