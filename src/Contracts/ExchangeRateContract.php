<?php

namespace RedberryProducts\CryptoWallet\Contracts;

interface ExchangeRateContract
{
    public function all(): ?array;

    public function getByCoin(?string $coin = null): ?array;
}
