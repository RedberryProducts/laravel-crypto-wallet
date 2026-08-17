<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\CreatePaymentRequest;
use RedberryProducts\CryptoWallet\Data\PaymentRequest;

interface CreatesPaymentRequests
{
    public function createPaymentRequest(CreatePaymentRequest $request): PaymentRequest;
}
