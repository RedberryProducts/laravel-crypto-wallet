<?php

namespace Khomeriki\BitgoWallet;

use Illuminate\Support\Collection;
use Khomeriki\BitgoWallet\Contracts\BitgoAdapterContract;
use Khomeriki\BitgoWallet\Contracts\WalletContract;
use Khomeriki\BitgoWallet\Data\GenerateWallet;
use Khomeriki\BitgoWallet\Data\Responses\Address;
use Khomeriki\BitgoWallet\Data\Responses\Transfer;
use Khomeriki\BitgoWallet\Data\Responses\Wallet as WalletData;
use Khomeriki\BitgoWallet\Data\Responses\Webhook;
use Khomeriki\BitgoWallet\Data\TransferData;

class Wallet extends WalletData implements WalletContract
{
    public ?string $id;

    /**
     * Ids of users with access to the wallet
     */
    public array $users;

    /**
     * Name of the blockchain the wallet is on
     */
    public string $coin;

    /**
     * Name the user assigned to the wallet
     */
    public string $label;

    /**
     * Number of signatures required for the wallet to send
     */
    public int $m;

    /**
     * Number of signers on the wallet
     */
    public int $n;

    /**
     * @var array<string>
     */
    public array $keys;

    public array $keySignatures;

    /**
     * Tags set on the wallet
     *
     * @var array<string>
     */
    public array $tags;

    public array $receiveAddress;

    /**
     * Wallet balance as number
     */
    public int $balance;

    /**
     * Wallet balance as string
     */
    public string $balanceString;

    /**
     * Confirmed wallet balance as number
     */
    public int $confirmedBalance;

    /**
     * Confirmed wallet balance as string
     */
    public string $confirmedBalanceString;

    /**
     * Spendable wallet balance as number
     */
    public int $spendableBalance;

    /**
     * Spendable wallet balance as string
     */
    public string $spendableBalanceString;

    /**
     * Flag which indicates the wallet has been deleted
     */
    public bool $deleted;

    /**
     * Flag for identifying cold wallets
     */
    public bool $isCold;

    /**
     * Freeze state (used to stop the wallet from spending)
     */
    public array $freezy;

    /**
     * Flag for disabling wallet transaction notifications
     */
    public bool $disableTransactionNotifications;

    /**
     * Admin data (wallet policies)
     */
    public array $admin;

    /**
     * Flag for allowing signing with backup key
     */
    public int $approvalsRequired;

    /**
     * Pending approvals on the wallet
     */
    public array $pendingApprovals;

    /**
     * Flag for allowing signing with backup key
     */
    public bool $allowBackupKeySigning;

    /**
     * Coin-specific data
     */
    public array $coinSpecific;

    /**
     * @var array<string>
     */
    public array $clientFlags;

    /**
     * Flag indicating whether this wallet's user key is recoverable with the passphrase held by the user.
     */
    public bool $recoverable;

    /**
     * Time when this wallet was created
     *
     * @var string date-time
     */
    public string $startDate;

    /**
     * Flag indicating that this wallet is large (more than 100,000 addresses).
     * If this is set, some APIs may omit properties which are expensive to calculate
     * for wallets with many addresses (for example, the total address counts returned by the List Addresses API).
     */
    public bool $hasLargeNumberOfAddresses;

    /**
     * Custom configuration options for this wallet
     */
    public array $config;

    protected BitgoAdapterContract $adapter;

    public function __construct(BitgoAdapterContract $adapter)
    {
        $this->adapter = $adapter;
        $this->coin = config('bitgo.default_coin');
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

        $generateWalletData = GenerateWallet::fromArray($options);

        $wallet = $this->adapter->generateWallet($this->coin, $generateWalletData);
        $this->setProperties($wallet['wallet']);

        return $this;
    }

    public function get(): self
    {
        $wallet = $this->adapter->getWallet($this->coin, $this->id);
        $this->setProperties($wallet);

        return $this;
    }

    public function addWebhook(int $numConfirmations = 0, ?string $callbackUrl = null): Webhook
    {
        $webhook = $this->adapter->addWalletWebhook($this->coin, $this->id, $numConfirmations, $callbackUrl);

        return Webhook::fromArray($webhook);
    }

    public function generateAddress(?string $label = null): Address
    {
        $address = $this->adapter->generateAddressOnWallet($this->coin, $this->id, $label);

        return Address::fromArray($address);
    }

    public function getTransfer(string $transferId): Transfer
    {
        $transfer = $this->adapter->getWalletTransfer($this->coin, $this->id, $transferId);

        return Transfer::fromArray($transfer);
    }

    public function listAll(?string $coin = null, ?array $params = []): Collection
    {
        $wallets = collect($this->adapter->getAllWallets($coin, $params)['wallets'] ?? []);

        return $wallets->map(callback: function ($element) {
            $wallet = app('Wallet');
            $wallet->setProperties($element);

            return $wallet;
        });
    }

    public function sendTransfer(TransferData $transfer): ?array
    {
        return $this->adapter->sendTransactionToMany(
            $this->coin,
            $this->id,
            $transfer
        );
    }

    public function getMaximumSpendable(?array $params = []): ?array
    {
        return $this->adapter->getMaximumSpendable($this->coin, $this->id, $params);
    }

    public function getTransfers(?array $params = []): ?array
    {
        $walletTransfers = $this->adapter->getWalletTransfers($this->coin, $this->id, $params);

        return array_map(function ($item) {
            return Transfer::fromArray($item);
        }, $walletTransfers['transfers']);
    }

    public function consolidate(?array $params): ?array
    {
        return $this->adapter->consolidate($this->coin, $this->id, $params);
    }
}
