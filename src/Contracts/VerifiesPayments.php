<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\ExpectedPayment;
use RedberryProducts\CryptoWallet\Data\PaymentVerificationResult;

interface VerifiesPayments
{
    public function verifyPayment(ExpectedPayment $payment): PaymentVerificationResult;
}
