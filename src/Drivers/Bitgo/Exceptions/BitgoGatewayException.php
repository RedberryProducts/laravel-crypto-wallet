<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Exceptions;

use Exception;

class BitgoGatewayException extends Exception
{
    public array $response = [];

    public function __construct($message, $code, ?array $response = [], ?Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }
}
