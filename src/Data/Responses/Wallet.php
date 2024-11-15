<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class Wallet extends Data
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
}
