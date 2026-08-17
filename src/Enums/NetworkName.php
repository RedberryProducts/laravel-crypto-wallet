<?php

namespace RedberryProducts\CryptoWallet\Enums;

enum NetworkName: string
{
    case Localnet = 'localnet';
    case Devnet = 'devnet';
    case MainnetBeta = 'mainnet-beta';
}
