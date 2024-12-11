<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class Wallet extends Data
{
    public ?string $id;

    public array $users; // Array of User objects

    public ?string $coin;

    public string $label;

    public int $m;

    public int $n;

    public array $keys; // Array of strings

    public KeySignatures $keySignatures;

    public string $enterprise;

    public string $bitgoOrg;

    public array $tags; // Array of strings

    public bool $disableTransactionNotifications;

    public array $freeze;

    public bool $deleted;

    public int $approvalsRequired;

    public bool $isCold;

    public array $coinSpecific;

    public array $admin;

    public array $clientFlags;

    public array $walletFlags;

    public bool $allowBackupKeySigning;

    public bool $recoverable;

    public string $startDate;

    public string $type;

    public array $buildDefaults;

    public array $customChangeKeySignatures;

    public bool $hasLargeNumberOfAddresses;

    public string $multisigType;

    public bool $hasReceiveTransferPolicy;

    public array $config;

    public float $balance;

    public string $balanceString;

    public float $rbfBalance;

    public string $rbfBalanceString;

    public float $confirmedBalance;

    public string $confirmedBalanceString;

    public float $spendableBalance;

    public string $spendableBalanceString;

    public int $unspentCount;

    public ReceiveAddress $receiveAddress;

    public array $pendingApprovals;
}
