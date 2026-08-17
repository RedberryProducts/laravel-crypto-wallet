<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Support;

use RedberryProducts\CryptoWallet\Data\AddressValidationResult;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAddressException;
use Throwable;
use Tuupola\Base58;

class AddressCodec
{
    private readonly Base58 $base58;

    public function __construct()
    {
        $this->base58 = new Base58(['characters' => Base58::BITCOIN]);
    }

    public function decode(string $address): string
    {
        try {
            $decoded = $this->decodeValue($address);
        } catch (Throwable $exception) {
            throw new InvalidAddressException('The Solana address is not valid Base58.', 0, $exception);
        }

        if (strlen($decoded) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES || $this->encode($decoded) !== $address) {
            throw new InvalidAddressException('A Solana address must be a canonical Base58 encoded 32-byte public key.');
        }

        return $decoded;
    }

    public function encode(string $bytes): string
    {
        return $this->base58->encode($bytes);
    }

    public function decodeValue(string $encoded): string
    {
        if ($encoded === '') {
            throw new InvalidAddressException('A Base58 value cannot be empty.');
        }

        return $this->base58->decode($encoded);
    }

    public function validate(string $address): AddressValidationResult
    {
        try {
            $this->decode($address);

            return new AddressValidationResult(true, $address);
        } catch (InvalidAddressException $exception) {
            return new AddressValidationResult(false, $address, $exception->getMessage());
        }
    }
}
