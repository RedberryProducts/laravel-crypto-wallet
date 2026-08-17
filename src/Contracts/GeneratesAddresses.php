<?php

namespace RedberryProducts\CryptoWallet\Contracts;

interface GeneratesAddresses
{
    public function generateAddress(?string $label = null): mixed;
}
