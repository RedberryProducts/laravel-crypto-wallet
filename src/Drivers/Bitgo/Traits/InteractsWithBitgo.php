<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait InteractsWithBitgo
{
    private static function getConfig(string $key)
    {
        return config("crypto-wallet.drivers.bitgo.{$key}");
    }

    private static function http($apiUrl): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.self::getConfig('api_key'),
        ])->baseUrl("{$apiUrl}");
    }

    private static function bitgoApi(): PendingRequest
    {
        if (config('crypto-wallet.drivers.bitgo.testnet')) {
            $apiUrl = self::getConfig('testnet_api_url');
        } else {
            $apiUrl = self::getConfig('mainnet_api_url');
        }

        return self::http($apiUrl);
    }

    private static function bitgoExpressApi(): PendingRequest
    {
        $apiUrl = self::getConfig('express_api_url');

        return self::http($apiUrl);
    }

    /**
     * @throws ConnectionException
     */
    protected static function httpGet(string $endpoint, ?array $data = []): Response
    {
        return self::bitgoApi()->get($endpoint, $data);
    }

    /**
     * @throws ConnectionException
     */
    protected static function httPost(string $endpoint, array $data): Response
    {
        /** @phpstan-ignore-next-line */
        return self::bitgoApi()->get($endpoint, $data);
    }

    /**
     * @throws ConnectionException
     */
    protected static function httpPostExpress(string $endpoint, array $data): Response
    {
        /** @phpstan-ignore-next-line */
        return self::bitgoExpressApi()->post($endpoint, $data);
    }

    /**
     * @throws ConnectionException
     */
    protected static function httpGetExpress(string $endpoint): Response
    {
        /** @phpstan-ignore-next-line */
        return self::bitgoExpressApi()->get($endpoint);
    }
}
