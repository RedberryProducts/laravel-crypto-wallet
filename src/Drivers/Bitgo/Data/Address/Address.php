<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Address;

use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Data;

class Address extends Data
{
    public string $id;

    public string $address;

    public int $chain;

    public int $index;

    public string $coin;

    public string $wallet;

    public string $label;

    public array $coinSpecific;

    public string $addressType;

    public array $keychains;
}
