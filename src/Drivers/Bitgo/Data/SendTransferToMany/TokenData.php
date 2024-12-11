<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany;

use Spatie\LaravelData\Data;

class TokenData extends Data
{
    public ?string $tokenName = null;

    public ?string $tokenContractAddress = null;

    public ?int $decimalPlaces = null;

    public ?string $tokenType = null;

    public ?string $tokenId = null;

    public ?string $tokenQuantity = null;
}
