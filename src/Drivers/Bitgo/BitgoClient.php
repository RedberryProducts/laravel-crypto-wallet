<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Exceptions\BitgoGatewayException;

class BitgoClient
{
    private const MAINNET_API_URL = 'https://app.bitgo.com/api/v2/';

    private const TESTNET_API_URL = 'https://app.bitgo-test.com/api/v2/';

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
            $apiUrl = self::TESTNET_API_URL;
        } else {
            $apiUrl = self::MAINNET_API_URL;
        }

        return self::http($apiUrl);
    }

    private static function bitgoExpressApi(): PendingRequest
    {
        $apiUrl = self::getConfig('express_api_url');

        return self::http($apiUrl);
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    protected static function httpGet(string $endpoint, ?array $data = []): Response
    {
        $response = self::bitgoApi()->get($endpoint, $data);
        if ($response->status() >= 400) {
            throw new BitgoGatewayException($response->json()['error'], $response->status(), $response->json());
        }

        return $response;
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    protected static function httpPostExpress(string $endpoint, array $data): Response
    {

        $response = self::bitgoExpressApi()->post($endpoint, $data);
        if ($response->status() >= 400) {
            throw new BitgoGatewayException($response->json()['error'], $response->status(), $response->json());
        }

        return $response;
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    protected static function httpGetExpress(string $endpoint): Response
    {
        $response = self::bitgoExpressApi()->get($endpoint);
        if ($response->status() >= 400) {
            throw new BitgoGatewayException($response->json()['error'], $response->status(), $response->json());
        }

        return $response;
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function me(): ?array
    {
        return $this->httpGet('user/me')->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getExchangeRates(?string $coin = null): ?array
    {
        $coinFilter = $coin ? "coin=$coin" : '';
        $response = $this->httpGet('market/latest?'.$coinFilter);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function pingExpress(): Response
    {
        $response = $this->httpGetExpress('ping');
        if ($response->status() >= 400) {
            throw new BitgoGatewayException($response->json()['error'], $response->status(), $response->json());
        }

        return $response;
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function ping(): Response
    {
        return $this->httpGet('ping');
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function generateWallet(string $coin, array $generateWalletData): ?array
    {
        $endpoint = "$coin/wallet/generate";
        $response = $this->httpPostExpress($endpoint, $generateWalletData);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getWallet(string $coin, ?string $walletId): ?array
    {
        $endpoint = "$coin/wallet/{$walletId}";
        $response = $this->httpGet($endpoint);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function generateAddressOnWallet(string $coin, string $walletId, ?string $label = null): ?array
    {
        $endpoint = "$coin/wallet/$walletId/address";
        $response = $this->httpPostExpress($endpoint, ['label' => $label]);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function addWalletWebhook(string $coin, string $walletId, int $numConfirmations = 0, ?string $callbackUrl = null): ?array
    {
        $callbackUrl = $callbackUrl ?: config('crypto-wallet.drivers.bitgo.webhook_callback_url');
        $endpoint = "$coin/wallet/$walletId/webhooks";
        $response = $this->httpPostExpress($endpoint, [
            'type' => 'transfer', //TODO::should be dynamic
            'url' => $callbackUrl,
            'numConfirmations' => $numConfirmations,
        ]);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getWalletTransfers(string $coin, string $walletId, ?array $params = []): ?array
    {
        $query = http_build_query($params);
        $endpoint = "$coin/wallet/$walletId/transfer?$query";
        $response = $this->httpGet($endpoint);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getWalletTransfer(string $coin, string $walletId, string $transferId): ?array
    {
        $endpoint = "$coin/wallet/$walletId/transfer/$transferId";
        $response = $this->httpGet($endpoint);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getAllWallets(?string $coin = null, ?array $params = []): ?array
    {
        $params['coin'] = $coin;
        $query = http_build_query($params);

        $endpoint = "wallets?$query";
        $response = $this->httpGet($endpoint);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function sendTransactionToMany(string $coin, string $walletId, array $transferParams): ?array
    {
        $endpoint = "$coin/wallet/$walletId/sendmany";
        $response = $this->httpPostExpress($endpoint, $transferParams);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function getMaximumSpendable(string $coin, string $walletId, ?array $params = []): ?array
    {
        $endpoint = "$coin/wallet/$walletId/maximumSpendable";
        $response = $this->httpGet($endpoint, $params);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function listWalletTransfers(string $coin, string $walletId): ?array
    {
        $endpoint = "$coin/wallet/$walletId/transfer";
        $response = $this->httpGet($endpoint);

        return $response->json();
    }

    /**
     * @throws BitgoGatewayException
     * @throws ConnectionException
     */
    public function consolidate(string $coin, string $walletId, ?array $params = []): ?array
    {
        $endpoint = "$coin/wallet/$walletId/consolidateunspents";
        $response = $this->httpPostExpress($endpoint, $params);

        return $response->json();
    }
}
