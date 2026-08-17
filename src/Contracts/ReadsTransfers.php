<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\Transfer;
use RedberryProducts\CryptoWallet\Data\TransferQuery;

interface ReadsTransfers
{
    /** @return array<int, Transfer> */
    public function getTransfers(?TransferQuery $query = null): array;

    public function getTransfer(string $signature): ?Transfer;
}
