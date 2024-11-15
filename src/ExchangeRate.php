<?php

namespace RedberryProducts\CryptoWallet;

use Illuminate\Support\Arr;
use RedberryProducts\CryptoWallet\Contracts\BitgoAdapterContract;

class ExchangeRate
{
    protected BitgoAdapterContract $adapter;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected mixed $coin;

    public function __construct(BitgoAdapterContract $adapter)
    {
        $this->adapter = $adapter;
        $this->coin = config('crypto-wallet.default_coin');
    }

    public function all(): ?array
    {
        return $this->adapter->getExchangeRates();
    }

    public function getByCoin(?string $coin = null): ?array
    {
        $rates = $this->adapter->getExchangeRates($coin);

        return Arr::first($rates['marketData']);
    }
}
