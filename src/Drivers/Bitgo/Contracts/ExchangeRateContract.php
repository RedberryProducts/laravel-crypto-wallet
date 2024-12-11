<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Contracts;

interface ExchangeRateContract
{
    public function all(): ?array;

    public function getByCoin(?string $coin = null): ?array;
}
