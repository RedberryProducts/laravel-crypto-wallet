<?php

namespace RedberryProducts\CryptoWallet\Data;

use Spatie\LaravelData\Data;

class TransferQuery extends Data
{
    /**
     * @param  int  $limit  Maximum number of raw Solana signature records to request (1–100).
     * @param  string|null  $before  Opaque Solana signature cursor forwarded to the RPC.
     */
    public function __construct(
        public readonly int $limit = 25,
        public readonly ?string $before = null,
    ) {}
}
