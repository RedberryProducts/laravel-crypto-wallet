<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Contracts;

interface SolanaRpcClientContract
{
    /** @param array<int, mixed> $params */
    public function call(string $method, array $params = []): mixed;

    public function getBalance(string $address): string;

    /** @return array<string, mixed>|null */
    public function getAccountInfo(string $address): ?array;

    /** @return array<int, array<string, mixed>> */
    public function getTokenAccountsByOwner(string $address, string $mint): array;

    /** @return array<int, array<string, mixed>> */
    public function getSignaturesForAddress(string $address, int $limit = 25, ?string $before = null): array;

    /** @return array<string, mixed>|null */
    public function getTransaction(string $signature): ?array;
}
