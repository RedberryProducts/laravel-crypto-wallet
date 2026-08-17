<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\Balance;

interface ReadsBalances
{
    public function getBalance(string $asset): Balance;

    /** @return array<string, Balance> */
    public function getBalances(): array;
}
