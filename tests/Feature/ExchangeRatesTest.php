<?php

use RedberryProducts\CryptoWallet\Facades\ExchangeRate;

it('can fetch exchange rates', function () {
    $res = ExchangeRate::all();
    expect($res)->toHaveKey('marketData');
});

it('can get exchange rates on a coin', function () {
    $res = ExchangeRate::getByCoin('tbtc');
    expect($res)->toHaveKeys([
        'blockchain',
        'currencies',
        'coin',
    ]);
});
