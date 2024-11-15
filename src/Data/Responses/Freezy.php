<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class Freezy extends Data
{
    /**
     * Time when the wallet becomes frozen
     *
     * @var string date-time
     */
    public string $time;

    /**
     * Time when the wallet is unfrozen and allowed to spend
     *
     * @var string date-time
     */
    public string $expires;
}
