<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class ReceiveAddress extends Data
{
    public string $id;

    public string $address;

    public int $chain;

    public int $index;

    public string $coin;

    public string $wallet;

    public array $coinSpecific;
}
