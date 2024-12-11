<?php

use RedberryProducts\CryptoWallet\ExchangeRateFactory;

it('can fetch exchange rates', function () {
    $res = ExchangeRateFactory::bitgo()->all();
    expect($res)->toBeArray();
});

it('can get exchange rates on a coin', function () {
    $res = ExchangeRateFactory::bitgo()->getByCoin('tbtc');
    expect($res)->toBeArray();
});
