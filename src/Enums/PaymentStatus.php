<?php

namespace RedberryProducts\CryptoWallet\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
    case Mismatch = 'mismatch';
}
