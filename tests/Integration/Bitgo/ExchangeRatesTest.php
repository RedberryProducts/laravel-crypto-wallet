<?php

use RedberryProducts\CryptoWallet\ExchangeRateManager;

it('can fetch exchange rates', function () {
    $res = ExchangeRateManager::bitgo()->all();
    expect($res)->toBeArray();
});

it('can get exchange rates on a coin', function () {
    $res = ExchangeRateManager::bitgo()->getByCoin('tbtc');
    expect($res)
        ->toBeArray()
        ->toHaveKey('coin', 'tbtc')
        ->toHaveKey('currencies');
});

it('can not get exchange rates on an invalid coin', function () {
    $res = ExchangeRateManager::bitgo()->getByCoin('invalid-coin');
    expect($res)->toBeNull();
});
