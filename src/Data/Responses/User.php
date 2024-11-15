<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class User extends Data
{
    /**
     * id of the user
     */
    public string $user;

    /**
     * Array of permissions for the user
     *
     * @var array<string>
     */
    public array $permissions;
}
