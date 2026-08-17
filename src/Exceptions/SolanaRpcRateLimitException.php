<?php

namespace RedberryProducts\CryptoWallet\Exceptions;

class SolanaRpcRateLimitException extends SolanaRpcException
{
    public function __construct(public readonly ?int $retryAfter = null)
    {
        parent::__construct('Solana RPC rate limit exceeded.');
    }
}
