<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\PaymentReferenceGenerator;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAddressException;

it('round trips a canonical 32 byte public key', function () {
    $codec = new AddressCodec;
    $bytes = str_repeat("\0", 32);
    $address = str_repeat('1', 32);

    expect($codec->encode($bytes))->toBe($address)
        ->and($codec->decode($address))->toBe($bytes)
        ->and($codec->validate($address)->valid)->toBeTrue();
});

it('rejects invalid characters and non 32 byte values', function (string $address) {
    (new AddressCodec)->decode($address);
})->with(['0OIl', '111', str_repeat('1', 33)])->throws(InvalidAddressException::class);

it('generates unique valid payment reference public keys', function () {
    $generator = new PaymentReferenceGenerator(new AddressCodec);
    $first = $generator->generate();
    $second = $generator->generate();

    expect($first)->not->toBe($second)
        ->and((new AddressCodec)->validate($first)->valid)->toBeTrue()
        ->and((new AddressCodec)->validate($second)->valid)->toBeTrue();
});
