<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class User extends Data
{
    public function __construct(
        public string $user,
        public array $permissions,
    ) {}
}
