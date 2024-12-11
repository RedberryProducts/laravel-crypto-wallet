<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany;

use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Data;

class Recipient extends Data
{
    public function __construct(
        public string $address,
        public string $amount,
        public ?string $tokenName = null,
        public ?TokenData $tokenData = null,
    ) {
        parent::__construct();
    }
}
