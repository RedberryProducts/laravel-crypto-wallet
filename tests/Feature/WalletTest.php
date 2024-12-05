<?php

use RedberryProducts\CryptoWallet\Data\Transfer;
use RedberryProducts\CryptoWallet\Facades\Wallet;

it('can generate wallet', function () {
    $wallet = Wallet::init('tbtc')
        ->generate('testing label', 'testing pass', '64065d3743b252a0e029e2faa945e233');

    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id');
});

//it can get wallet by id
it('can get wallet by id', function () {
    $wallet = Wallet::init('tbtc', 'wallet-id')
        ->get();

    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id', 'wallet-id');
});

it('can get wallet transfers', function () {
    $transfers = Wallet::init('tbtc', 'wallet-id')
        ->getTransfers();
    expect($transfers)->toBeArray();
});

it('can generate address on the wallet', function () {
    $address = Wallet::init('tbtc', 'wallet-id')
        ->generateAddress('testing label');

    expect($address)
        ->toBeInstanceOf(\RedberryProducts\CryptoWallet\Data\Data::class)
        ->toHaveProperty('id')
        ->toHaveProperty('address')
        ->toHaveProperty('driverObject');
});

it('can get wallet transfer', function () {
    $transfer = Wallet::init('tbtc', 'wallet-id')
        ->getTransfer('transfer-id');

    expect($transfer)->toBeInstanceOf(Transfer::class);
});

it('can generate wallet with webhook', function () {
    $webhook = Wallet::init('tbtc')
        ->generate('wallet with webhook', 'testing pass', '64065d3743b252a0e029e2faa945e233')
        ->addWebhook(6, 'https://www.blockchain.com/');

    expect($webhook)
        ->toBeObject();
});

it('inits wallet correctly', function () {
    $wallet = Wallet::init('tbtc', 'walletId');
    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id', 'walletId');
});

it('can list all the available wallets', function () {
    $wallets = Wallet::listAll(params: [
        'expandBalance' => 'true',
    ]);
    expect($wallets)
        ->toBeArray()
        ->and($wallets[0])
        ->toHaveProperties(['coin', 'id']);
});

it('can send transaction', closure: function () {
    $transferData = [
        'walletPassphrase' => 'test',
        'recipients' => [
            [
                'amount' => 333,
                'address' => 'dddd',
            ],
            [
                'amount' => 333,
                'address' => 'dddd',
            ],
        ],
    ];
    $res = Wallet::init('tbtc', 'wallet-id')->sendTransferToMany($transferData);
    expect($res)->toBeArray();
});

it('can get a maximum spendable amount of the wallet', function () {
    $result = Wallet::init('tbtc', 'wallet-id')->getMaximumSpendable([
        'feeRate' => 0,
    ]);

    expect($result)->toBeArray();
});

it('can consolidate wallet balance', function () {
    $result = Wallet::init('tbtc', 'wallet-id')->consolidate([
        'walletPassphrase' => 'test',
        'bulk' => true,
        'minValue' => '0',
        'minHeight' => 0,
        'minConfirms' => 0,
    ]);

    expect($result)->toBeArray();
});
