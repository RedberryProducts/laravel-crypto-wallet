<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo;

use RedberryProducts\CryptoWallet\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\Data\Address as AddressData;
use RedberryProducts\CryptoWallet\Data\Transfer as TransferData;

class Wallet extends \RedberryProducts\CryptoWallet\Data\Wallet implements WalletContract
{
    protected BitgoClient $client;

    public array $webhooks = [];

    public function __construct()
    {
        parent::__construct();
        $this->client = new BitgoClient;
        $this->coin = config('crypto-wallet.drivers.bitgo.default_coin');
    }

    private function setProperties(array $propertyList): void
    {
        foreach ($propertyList as $key => $value) {
            $this->$key = $value;
        }
    }

    public function init(string $coin, ?string $id = null): self
    {
        $this->coin = $coin;
        $this->id = $id;

        return $this;
    }

    public function generate(string $label, string $passphrase, string $enterpriseId, array $options = []): self
    {
        $options = array_merge([
            'label' => $label,
            'passphrase' => $passphrase,
            'enterprise' => $enterpriseId,
        ], $options);

        $wallet = $this->client->generateWallet($this->coin, $options);
        $this->setProperties($wallet['wallet']);

        return self::fromArray([
            'id' => $this->id,
            'coin' => $this->coin,
            'label' => $this->label,
            'driverObject' => $this,
        ]);
    }

    public function get(): self
    {
        $wallet = $this->client->getWallet($this->coin, $this->id);

        return self::fromArray([
            'id' => $wallet['id'],
            'coin' => $wallet['coin'],
            'label' => $wallet['label'],
            'driverObject' => $wallet,
        ]);
    }

    public function addWebhook(int $numConfirmations = 0, ?string $callbackUrl = null): self
    {
        $this->webhooks[] = $this->client->addWalletWebhook($this->coin, $this->id, $numConfirmations, $callbackUrl);

        return $this;
    }

    public function generateAddress(?string $label = null): AddressData
    {
        $address = $this->client->generateAddressOnWallet($this->coin, $this->id, $label);

        return AddressData::fromArray([
            'id' => $address['id'],
            'address' => $address['address'],
            'driverObject' => $address,
        ]);
    }

    public function getTransfer(string $transferId): TransferData
    {
        $transfer = $this->client->getWalletTransfer($this->coin, $this->id, $transferId);

        return TransferData::fromArray([
            'id' => $transfer['id'],
            'value' => $transfer['valueString'],
            'coin' => $transfer['coin'],
            'driverObject' => $transfer,
        ]);
    }

    public function listAll(?string $coin = null, ?array $params = []): array
    {
        $wallets = $this->client->getAllWallets($coin, $params)['wallets'];

        return array_map(function ($element) {
            return \RedberryProducts\CryptoWallet\Data\Wallet::fromArray($element);
        }, $wallets);
    }

    public function sendTransferToMany(array $transfer): ?array
    {
        return $this->client->sendTransactionToMany(
            $this->coin,
            $this->id,
            $transfer
        );
    }

    public function getMaximumSpendable(?array $params = []): ?array
    {
        return $this->client->getMaximumSpendable($this->coin, $this->id, $params);
    }

    public function getTransfers(?array $params = []): ?array
    {
        $walletTransfers = $this->client->getWalletTransfers($this->coin, $this->id, $params);

        return array_map(function ($item) {
            return TransferData::fromArray($item);
        }, $walletTransfers['transfers']);
    }

    public function consolidate(?array $params): ?array
    {
        return $this->client->consolidate($this->coin, $this->id, $params);
    }
}
