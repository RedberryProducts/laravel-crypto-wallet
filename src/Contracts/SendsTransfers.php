<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\Transfer;

interface SendsTransfers
{
    public function sendTransfer(string $recipientAddress, string $amount, string $asset): Transfer;
}
