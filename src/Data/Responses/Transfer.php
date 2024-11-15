<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class Transfer extends Data
{
    public string $coin;

    public string $id;

    public string $wallet;

    public string $enterprise;

    public string $txid;

    public int $height;

    public string $heightId;

    public string $date;

    public string $type;

    public int $value;

    public string $valueString;

    public int $baseValue;

    public string $baseValueString;

    public string $feeString;

    public int $payGoFee;

    public string $payGoFeeString;

    public float $usd;

    public float $usdRate;

    public string $state;

    public array $tags;

    public array $history;

    public ?string $comment;

    public int $vSize;

    public int $nSegwitInputs;

    public array $coinSpecific;

    public string $sequenceId;

    public array $entries;

    public bool $usersNotified;

    public string $confirmations;

    public array $inputs;

    public array $outputs;
}
