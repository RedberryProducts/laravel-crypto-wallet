<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use RedberryProducts\CryptoWallet\Drivers\Solana\Contracts\SolanaRpcClientContract;
use RedberryProducts\CryptoWallet\Exceptions\MalformedSolanaResponseException;
use RedberryProducts\CryptoWallet\Exceptions\SolanaRpcException;
use RedberryProducts\CryptoWallet\Exceptions\SolanaRpcRateLimitException;
use Throwable;

class SolanaRpcClient implements SolanaRpcClientContract
{
    private int $requestId = 0;

    public function __construct(
        private readonly Factory $http,
        private readonly string $rpcUrl,
        private readonly string $commitment,
        private readonly int $timeout,
        private readonly int $retryTimes,
    ) {}

    public function call(string $method, array $params = []): mixed
    {
        $requestId = ++$this->requestId;

        try {
            $response = $this->http
                ->timeout($this->timeout)
                ->retry(max(1, $this->retryTimes + 1), 100, fn (Throwable $exception): bool => $exception instanceof ConnectionException)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'id' => $requestId,
                    'method' => $method,
                    'params' => $params,
                ]);
        } catch (ConnectionException $exception) {
            throw new SolanaRpcException('Unable to connect to the configured Solana RPC endpoint.', 0, $exception);
        }

        if ($response->status() === 429) {
            $retryAfter = $response->header('Retry-After');

            throw new SolanaRpcRateLimitException(is_numeric($retryAfter) ? (int) $retryAfter : null);
        }

        if (! $response->successful()) {
            throw new SolanaRpcException("Solana RPC returned HTTP status {$response->status()}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['id'] ?? null) !== $requestId) {
            throw new MalformedSolanaResponseException('Solana RPC returned a response with an invalid request ID.');
        }

        if (isset($payload['error'])) {
            $code = is_array($payload['error']) ? ($payload['error']['code'] ?? 'unknown') : 'unknown';
            $message = is_array($payload['error']) ? ($payload['error']['message'] ?? 'Unknown RPC error') : 'Unknown RPC error';

            throw new SolanaRpcException("Solana RPC error [{$code}]: {$message}");
        }

        if (! array_key_exists('result', $payload)) {
            throw new MalformedSolanaResponseException('Solana RPC response is missing its result.');
        }

        return $payload['result'];
    }

    public function getBalance(string $address): string
    {
        $result = $this->call('getBalance', [$address, ['commitment' => $this->commitment]]);
        $value = is_array($result) ? ($result['value'] ?? null) : null;

        if (! is_int($value) && (! is_string($value) || ! ctype_digit($value))) {
            throw new MalformedSolanaResponseException('Solana getBalance response has an invalid value.');
        }

        return (string) $value;
    }

    public function getAccountInfo(string $address): ?array
    {
        $result = $this->call('getAccountInfo', [$address, [
            'commitment' => $this->commitment,
            'encoding' => 'jsonParsed',
        ]]);

        return $this->nullableValue($result, 'getAccountInfo');
    }

    public function getTokenAccountsByOwner(string $address, string $mint): array
    {
        $result = $this->call('getTokenAccountsByOwner', [$address, ['mint' => $mint], [
            'commitment' => $this->commitment,
            'encoding' => 'jsonParsed',
        ]]);

        return $this->listValue($result, 'getTokenAccountsByOwner');
    }

    public function getSignaturesForAddress(string $address, int $limit = 25, ?string $before = null): array
    {
        $effectiveLimit = max(1, min(100, $limit));
        $options = ['commitment' => $this->commitment, 'limit' => $effectiveLimit];

        if ($before !== null) {
            $options['before'] = $before;
        }

        $result = $this->call('getSignaturesForAddress', [$address, $options]);

        if (! is_array($result) || ! array_is_list($result)) {
            throw new MalformedSolanaResponseException('Solana getSignaturesForAddress response is malformed.');
        }

        if (count($result) > $effectiveLimit) {
            throw new MalformedSolanaResponseException('Solana getSignaturesForAddress response exceeds the requested limit.');
        }

        foreach ($result as $signatureInfo) {
            if (! is_array($signatureInfo)) {
                throw new MalformedSolanaResponseException('Solana signature record is malformed.');
            }

            $signature = $signatureInfo['signature'] ?? null;

            if (! is_string($signature) || $signature === '') {
                throw new MalformedSolanaResponseException('Solana signature record is missing a valid signature.');
            }

            $confirmationStatus = $signatureInfo['confirmationStatus'] ?? null;

            if ($confirmationStatus !== null && ! is_string($confirmationStatus)) {
                throw new MalformedSolanaResponseException('Solana signature record has an invalid confirmation status.');
            }
        }

        return $result;
    }

    public function getTransaction(string $signature): ?array
    {
        $result = $this->call('getTransaction', [$signature, [
            'commitment' => $this->commitment,
            'encoding' => 'jsonParsed',
            'maxSupportedTransactionVersion' => 0,
        ]]);

        if ($result !== null && ! is_array($result)) {
            throw new MalformedSolanaResponseException('Solana getTransaction response is malformed.');
        }

        return $result;
    }

    /** @return array<string, mixed>|null */
    private function nullableValue(mixed $result, string $method): ?array
    {
        $value = is_array($result) ? ($result['value'] ?? null) : null;

        if ($value !== null && ! is_array($value)) {
            throw new MalformedSolanaResponseException("Solana {$method} response is malformed.");
        }

        return $value;
    }

    /** @return array<int, array<string, mixed>> */
    private function listValue(mixed $result, string $method): array
    {
        $value = is_array($result) ? ($result['value'] ?? null) : null;

        if (! is_array($value) || ! array_is_list($value)) {
            throw new MalformedSolanaResponseException("Solana {$method} response is malformed.");
        }

        return $value;
    }
}
