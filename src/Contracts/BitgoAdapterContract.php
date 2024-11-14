<?php

namespace Khomeriki\BitgoWallet\Contracts;

use Illuminate\Http\Client\Response;
use Khomeriki\BitgoWallet\Data\GenerateWallet;
use Khomeriki\BitgoWallet\Data\TransferData;

interface BitgoAdapterContract
{
    public function me(): Response;

    public function getExchangeRates(?string $coin = null): ?array;

    public function pingExpress(): Response;

    public function ping(): Response;

    public function generateWallet(string $coin, GenerateWallet $generateWalletData): ?array;

    public function getWallet(string $coin, ?string $walletId): ?array;

    public function generateAddressOnWallet(string $coin, string $walletId, ?string $label = null): ?array;

    public function addWalletWebhook(string $coin, string $walletId, int $numConfirmations = 0, ?string $callbackUrl = null): ?array;

    public function getWalletTransfers(string $coin, string $walletId, ?array $params = []): ?array;

    public function getWalletTransfer(string $coin, string $walletId, string $transferId): ?array;

    public function getAllWallets(?string $coin = null, ?array $params = []): ?array;

    public function sendTransactionToMany(string $coin, string $walletId, TransferData $transfer): ?array;

    public function getMaximumSpendable(string $coin, string $walletId, ?array $params = []): ?array;

    public function listTraWalletTransfers(string $coin, string $walletId): ?array;

    public function consolidate(string $coin, string $walletId, ?array $params = []): ?array;
}
