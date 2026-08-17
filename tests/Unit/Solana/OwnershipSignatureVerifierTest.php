<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\OwnershipSignatureVerifier;
use RedberryProducts\CryptoWallet\Exceptions\OwnershipVerificationException;

it('verifies a detached ed25519 ownership signature', function () {
    $codec = new AddressCodec;
    $keypair = sodium_crypto_sign_keypair();
    $address = $codec->encode(sodium_crypto_sign_publickey($keypair));
    $message = 'Verify tenant wallet for nonce 123';
    $signature = $codec->encode(sodium_crypto_sign_detached($message, sodium_crypto_sign_secretkey($keypair)));
    $verifier = new OwnershipSignatureVerifier($codec);

    expect($verifier->verify($address, $message, $signature))->toBeTrue()
        ->and($verifier->verify($address, $message.'x', $signature))->toBeFalse();
});

it('rejects malformed signatures', function () {
    (new OwnershipSignatureVerifier(new AddressCodec))->verify(str_repeat('1', 32), 'message', 'bad');
})->throws(OwnershipVerificationException::class);
