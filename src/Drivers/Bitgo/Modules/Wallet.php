<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Modules;

use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoClient;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Address\Address;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\BackupKeyChain;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\BitGoKeychain;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\MaximumSpendable;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\SendToManyRequest;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Transfer;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\UserKeyChain;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Wallet as WalletDto;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\WalletData;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\WebhookData;

class Wallet extends WalletDto implements WalletContract
{
    protected BitgoClient $client;

    public ?string $coin = null;

    public ?string $id = null;

    public ?BackupKeyChain $backupKeychain;

    public ?UserKeyChain $userKeychain;

    public ?BitGoKeychain $bitgoKeychain;

    public string $responseType;

    public ?string $warning;

    public function __construct(
        ?string $coin = null,
        ?string $id = null,

    ) {
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
        $resultData = WalletData::from($wallet);

        $wallet = self::from($resultData->wallet->toArray());

        $wallet->backupKeychain = $resultData->backupKeychain;
        $wallet->userKeychain = $resultData->userKeychain;
        $wallet->bitgoKeychain = $resultData->bitgoKeychain;
        $wallet->responseType = $resultData->responseType;
        $wallet->warning = $resultData->warning;

        return $wallet;
    }

    public function get(): self
    {
        $wallet = $this->client->getWallet($this->coin, $this->id);

        return self::from($wallet);
    }

    public function addWebhook(int $numConfirmations = 0, ?string $callbackUrl = null): WebhookData
    {
        $webhookData = $this->client->addWalletWebhook($this->coin, $this->id, $numConfirmations, $callbackUrl);

        return WebhookData::from($webhookData);
    }

    public function generateAddress(?string $label = null): Address
    {
        $address = $this->client->generateAddressOnWallet($this->coin, $this->id, $label);

        return Address::from($address);
    }

    public function getTransfer(string $transferId): Transfer
    {
        $transfer = $this->client->getWalletTransfer($this->coin, $this->id, $transferId);

        return Transfer::from($transfer);
    }

    /**
     * @return WalletDto[]
     */
    public function listAll(?string $coin = null, ?array $params = []): array
    {
        $wallets = $this->client->getAllWallets($coin, $params);

        return array_map(function ($element) {
            return \RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Wallet::from($element);
        }, $wallets['wallets']);
    }

    public function getMaximumSpendable(?array $params = []): MaximumSpendable
    {
        $result = $this->client->getMaximumSpendable($this->coin, $this->id, $params);

        return MaximumSpendable::from($result);
    }

    /**
     * @return Transfer[]
     */
    public function getTransfers(?array $params = []): array
    {
        $walletTransfers = $this->client->getWalletTransfers($this->coin, $this->id, $params);

        return array_map(function ($item) {
            return Transfer::from($item);
        }, $walletTransfers['transfers']);
    }

    public function sendTransferToMany(SendToManyRequest $sendToManyRequest): ?array
    {
        $transferData = $sendToManyRequest->toArray();

        return $this->client->sendTransactionToMany(
            $this->coin,
            $this->id,
            array_filter($transferData)
        );
    }

    public function consolidate(?array $params): ?array
    {
        return $this->client->consolidate($this->coin, $this->id, $params);
    }
}
