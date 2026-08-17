<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Support;

use RedberryProducts\CryptoWallet\Exceptions\OwnershipVerificationException;
use Throwable;

class OwnershipSignatureVerifier
{
    public function __construct(private readonly AddressCodec $addressCodec) {}

    public function verify(string $address, string $message, string $signature): bool
    {
        try {
            $publicKey = $this->addressCodec->decode($address);
            $signatureBytes = $this->addressCodec->decodeValue($signature);
        } catch (Throwable $exception) {
            throw new OwnershipVerificationException('The address or signature is not valid canonical Base58.', 0, $exception);
        }

        if (strlen($signatureBytes) !== SODIUM_CRYPTO_SIGN_BYTES) {
            throw new OwnershipVerificationException('An Ed25519 signature must contain 64 bytes.');
        }

        return sodium_crypto_sign_verify_detached($signatureBytes, $message, $publicKey);
    }
}
