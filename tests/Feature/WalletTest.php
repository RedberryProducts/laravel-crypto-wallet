<?php

use Khomeriki\BitgoWallet\Data\Responses\Transfer;
use Khomeriki\BitgoWallet\Data\Responses\Webhook;
use Khomeriki\BitgoWallet\Data\TransferData;
use Khomeriki\BitgoWallet\Data\TransferRecipientData;
use Khomeriki\BitgoWallet\Facades\Wallet;

it('can generate wallet', function () {
    $wallet = Wallet::init('tbtc')
        ->generate('testing label', 'testing pass', '64065d3743b252a0e029e2faa945e233');

    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id');
});

it('can get wallet transfers', function () {
    $transfers = Wallet::init('tbtc', '62b1ba9f2c7e8e000781fb2ae5c5dbff')
        ->getTransfers();
    expect($transfers)->toBeArray();
});

it('can get wallet transfer', function () {
    $transfer = Wallet::init('tbtc', '62b1ba9f2c7e8e000781fb2ae5c5dbff')
        ->getTransfer('62b1c6168e0b9e0007b421314aba0654');

    expect($transfer)->toBeInstanceOf(Transfer::class);
});

it('can generate wallet with webhook', function () {
    $webhook = Wallet::init('tbtc')
        ->generate('wallet with webhook', 'testing pass', 'asd')
        ->addWebhook(6, 'http://localhost/api/callback');

    expect($webhook)
        ->toBeObject()
        ->toBeInstanceOf(Webhook::class);
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
    $wallet = $wallets->first();
    expect($wallet)
        ->toBeInstanceOf(\Khomeriki\BitgoWallet\Wallet::class)
        ->toHaveProperties(['coin', 'id']);
});

it('can build TransferRecipient object', function () {
    $recipient = TransferRecipientData::fromArray([
        'amount' => 4934,
        'address' => 'address',
    ]);
    expect($recipient)
        ->toHaveProperty('amount', 4934)
        ->toHaveProperty('address', 'address');
});

it('can build transfer object', function () {
    $transferData = TransferData::fromArray([
        'walletPassphrase' => 'test',
        'recipients' => [
            TransferRecipientData::fromArray([
                'amount' => 4934,
                'address' => 'address',
            ]),
            TransferRecipientData::fromArray([
                'amount' => 4931,
                'address' => 'address1',
            ]),
        ],
    ]);

    expect($transferData)
        ->toHaveProperty('walletPassphrase', 'test')
        ->toHaveProperty('recipients', [
            TransferRecipientData::fromArray([
                'amount' => 4934,
                'address' => 'address',
            ]),
            TransferRecipientData::fromArray([
                'amount' => 4931,
                'address' => 'address1',
            ]),
        ]);
});

it('can send transaction', closure: function () {
    $transferData = TransferData::fromArray([
        'walletPassphrase' => 'test',
        'recipients' => [
            TransferRecipientData::fromArray([
                'amount' => 333,
                'address' => 'dddd',
            ]),
            TransferRecipientData::fromArray([
                'amount' => 333,
                'address' => 'dddd',
            ]),
        ],
    ]);

    $res = Wallet::init('tbtc', 'wallet-id')->sendTransfer($transferData);
    expect($res)->toBeArray();
});

it('can get a maximum spendable amount of the wallet', function () {
    $result = Wallet::init('tbtc', '62b1ba9f2c7e8e000781fb2ae5c5dbff')->getMaximumSpendable([
        'feeRate' => 0,
    ]);

    expect($result)->toBeArray();
});

it('can consolidate wallet balance', function () {
    $result = Wallet::init('tbtc', '62b1ba9f2c7e8e000781fb2ae5c5dbff')->consolidate([
        'walletPassphrase' => 'test',
        'bulk' => true,
        'minValue' => '0',
        'minHeight' => 0,
        'minConfirms' => 0,
    ]);

    expect($result)->toBeArray();
});
