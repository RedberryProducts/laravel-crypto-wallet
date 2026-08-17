<?php

namespace RedberryProducts\CryptoWallet\Enums;

enum TransferDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Self = 'self';
    case Unknown = 'unknown';
}
