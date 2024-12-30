<?php

use RedberryProducts\CryptoWallet\ExchangeRateManager;

it('can fetch exchange rates', function () {
    $res = ExchangeRateManager::bitgo()->all();
    expect($res)->toBeArray();
});

it('can get exchange rates on a coin', function () {
    $res = ExchangeRateManager::bitgo()->getByCoin('tbtc');
    expect($res)->toBeArray();
});
