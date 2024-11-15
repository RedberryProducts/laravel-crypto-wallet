<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class Address extends Data
{
    public string $id;

    public string $address;

    public int $chain;

    public int $index;

    public string $wallet;

    public array $coinSpecific;
}
