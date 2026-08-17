<?php

namespace RedberryProducts\CryptoWallet\Enums;

enum Commitment: string
{
    case Processed = 'processed';
    case Confirmed = 'confirmed';
    case Finalized = 'finalized';
}
