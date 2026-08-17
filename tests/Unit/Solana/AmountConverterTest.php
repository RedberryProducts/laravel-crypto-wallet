<?php

use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAmountException;

it('converts six decimal tokens without floating point arithmetic', function () {
    $converter = new AmountConverter;

    expect($converter->toBaseUnits('25.50', 6))->toBe('25500000')
        ->and($converter->toDecimal('25500000', 6))->toBe('25.5')
        ->and($converter->toBaseUnits('0.000001', 6))->toBe('1')
        ->and($converter->toDecimal('0', 9))->toBe('0');
});

it('supports integer strings larger than the platform integer size', function () {
    $converter = new AmountConverter;

    expect($converter->toBaseUnits('12345678901234567890.123456', 6))
        ->toBe('12345678901234567890123456');
});

it('rejects unsafe decimal inputs', function (string $amount) {
    (new AmountConverter)->toBaseUnits($amount, 6);
})->with(['', '-1', '+1', ' 1', '1 ', '1e3', '1.0000001', '.5', '1.'])->throws(InvalidAmountException::class);

it('rejects unsafe base unit inputs', function (string $amount) {
    (new AmountConverter)->toDecimal($amount, 6);
})->with(['', '-1', '+1', ' 1', '1.0', '1e3'])->throws(InvalidAmountException::class);
