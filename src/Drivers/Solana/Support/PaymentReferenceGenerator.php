<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Support;

class PaymentReferenceGenerator
{
    public function __construct(private readonly AddressCodec $addressCodec) {}

    public function generate(): string
    {
        $keypair = sodium_crypto_sign_keypair();

        return $this->addressCodec->encode(sodium_crypto_sign_publickey($keypair));
    }
}
