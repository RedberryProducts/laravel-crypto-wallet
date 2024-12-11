<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoClient;

class ExchangeRate
{
    protected BitgoClient $client;

    /**
     * @var Repository|Application|mixed
     */
    protected mixed $coin;

    public function __construct()
    {
        $this->client = new BitgoClient;
        $this->coin = config('crypto-wallet.drivers.bitgo.default_coin');
    }

    public function all(): ?array
    {
        return $this->client->getExchangeRates();
    }

    public function getByCoin(?string $coin = null): ?array
    {
        return $this->client->getExchangeRates($coin);
    }
}
