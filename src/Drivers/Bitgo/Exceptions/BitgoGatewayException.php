<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Exceptions;

use Exception;

class BitgoGatewayException extends Exception
{
    public function __construct($message = 'Gateway Exception', $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
