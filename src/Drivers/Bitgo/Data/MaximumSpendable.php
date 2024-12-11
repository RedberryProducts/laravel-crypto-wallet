<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class MaximumSpendable extends Data
{
    public string $maximumSpendable;

    public string $miningFee;

    public string $payGoFee;

    public string $coin;
}
