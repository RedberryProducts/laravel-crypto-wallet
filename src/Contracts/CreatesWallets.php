<?php

namespace RedberryProducts\CryptoWallet\Contracts;

interface CreatesWallets
{
    /** @param array<string, mixed> $options */
    public function createWallet(array $options = []): mixed;
}
