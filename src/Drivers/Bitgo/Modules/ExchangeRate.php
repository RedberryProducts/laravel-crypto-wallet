<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoClient;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Exceptions\BitgoGatewayException;

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

    /**
     * @throws BitgoGatewayException
     */
    public function all(): ?array
    {
        return $this->client->getExchangeRates();
    }

    /**
     * @throws BitgoGatewayException
     */
    public function getByCoin(?string $coin = null): ?array
    {
        $rates = $this->all();

        return Arr::first(array_filter($rates['marketData'], function ($rate) use ($coin) {
            return $rate['coin'] == $coin;
        }));
    }
}
