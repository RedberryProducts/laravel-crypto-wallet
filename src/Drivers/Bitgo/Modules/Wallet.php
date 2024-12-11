<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules;

use AllowDynamicProperties;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoClient;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Address\Address;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\MaximumSpendable;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\SendToManyRequest;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Transfer;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Wallet as WalletDto;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\WalletData;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\WebhookData;

#[AllowDynamicProperties]
class Wallet extends WalletDto implements WalletContract
{
    protected BitgoClient $client;

    public array $webhooks = [];

    public function __construct(
        ?string $coin = null,
        ?string $id = null
    ) {
        parent::__construct();
        $this->client = new BitgoClient;
        $this->coin = $coin;
        $this->id = $id;
    }

    public function generate(string $label, string $passphrase, string $enterpriseId, array $options = []): self
    {
        $options = array_merge([
            'label' => $label,
            'passphrase' => $passphrase,
            'enterprise' => $enterpriseId,
        ], $options);

        $wallet = $this->client->generateWallet($this->coin, $options);
        $resultData = WalletData::fromArray($wallet);
        $walletData = self::fromArray($resultData->wallet->toArray());
        $walletData->backupKeychain = $resultData->backupKeychain;
        $walletData->userKeychain = $resultData->userKeychain;
        $walletData->bitgoKeychain = $resultData->bitgoKeychain;
        $walletData->responseType = $resultData->responseType;
        $walletData->warning = $resultData->warning;

        return $walletData;
    }

    public function get(): self
    {
        $wallet = $this->client->getWallet($this->coin, $this->id);

        return self::fromArray($wallet);
    }

    public function addWebhook(int $numConfirmations = 0, ?string $callbackUrl = null): WebhookData
    {
        $webhookData = $this->client->addWalletWebhook($this->coin, $this->id, $numConfirmations, $callbackUrl);

        return WebhookData::fromArray($webhookData);
    }

    public function generateAddress(?string $label = null): Address
    {
        $address = $this->client->generateAddressOnWallet($this->coin, $this->id, $label);

        return Address::fromArray($address);
    }

    public function getTransfer(string $transferId): Transfer
    {
        $transfer = $this->client->getWalletTransfer($this->coin, $this->id, $transferId);

        return Transfer::fromArray($transfer);
    }

    /**
     * @return WalletDto[]
     */
    public function listAll(?string $coin = null, ?array $params = []): array
    {
        $wallets = $this->client->getAllWallets($coin, $params);

        return array_map(function ($element) {
            return \RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Wallet::fromArray($element);
        }, $wallets['wallets']);
    }

    public function getMaximumSpendable(?array $params = []): MaximumSpendable
    {
        $result = $this->client->getMaximumSpendable($this->coin, $this->id, $params);

        return MaximumSpendable::fromArray($result);
    }

    /**
     * @return Transfer[]
     */
    public function getTransfers(?array $params = []): array
    {
        $walletTransfers = $this->client->getWalletTransfers($this->coin, $this->id, $params);

        return array_map(function ($item) {
            return Transfer::fromArray($item);
        }, $walletTransfers['transfers']);
    }

    //TODO: add DTO
    public function sendTransferToMany(SendToManyRequest $sendToManyRequest): ?array
    {
        $transferData = $sendToManyRequest->toArray(filter: true);

        return $this->client->sendTransactionToMany(
            $this->coin,
            $this->id,
            array_filter($transferData)
        );
    }

    //TODO: add DTO
    public function consolidate(?array $params): ?array
    {
        return $this->client->consolidate($this->coin, $this->id, $params);
    }
}
