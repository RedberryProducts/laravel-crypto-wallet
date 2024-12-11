<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

class Transfer extends Data
{
    public string $id;

    public string $coin;

    public string $wallet;

    public string $walletType;

    public string $enterprise;

    public string $txid;

    public string $txidType;

    public int $height;

    public string $heightId;

    public string $date;

    public int $confirmations;

    public string $type;

    public int $value;

    public string $valueString;

    public string $intendedValueString;

    public int $baseValue;

    public string $baseValueString;

    public int $baseValueWithoutFees;

    public string $baseValueWithoutFeesString;

    public string $feeString;

    public int $payGoFee;

    public string $payGoFeeString;

    public float $usd;

    public float $usdRate;

    public string $state;

    public bool $instant;

    public bool $isReward;

    public bool $isUnlock;

    public bool $isFee;

    public bool $senderInformationVerified;

    public array $tags;

    public array $history;

    public ?string $signedDate;

    public int $vSize;

    public array $coinSpecific;

    public bool $usersNotified;

    public array $metadata;

    public string $confirmedTime;

    public string $signedTime;

    public string $createdTime;

    public string $label;

    public array $entries;

    public array $outputs;

    public array $inputs;
}
