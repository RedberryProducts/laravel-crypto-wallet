<?php

use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Transfer;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Exceptions\BitgoGatewayException;
use RedberryProducts\CryptoWallet\WalletManager;

it(/**
 * @throws BitgoGatewayException
 */ 'can generate wallet', function () {
    $wallet = WalletManager::bitgo('tbtc')
        ->generate('testing label', 'testing pass', '64065d3743b252a0e029e2faa945e233');

    expect($wallet)
        ->toBeObject()
        ->and($wallet)
        ->toHaveProperty('id')
        ->toHaveProperty('coin', 'tbtc');

});

//it can get wallet by id
it('can get wallet by id', function () {
    $wallet = WalletManager::bitgo('tbtc', 'wallet-id')
        ->get();

    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id', 'wallet-id');
});

it('can get wallet transfers', function () {
    $transfers = WalletManager::bitgo('tbtc', 'wallet-id')
        ->getTransfers();
    expect($transfers)->toBeArray()
        ->and($transfers[0])
        ->toBeInstanceOf(Transfer::class)
        ->toHaveProperty('id')
        ->toHaveProperty('coin', 'tbtc');
});

it('can generate address on the wallet', function () {
    $address = WalletManager::bitgo('tbtc', 'wallet-id')
        ->generateAddress('testing label');

    expect($address)
        ->toBeInstanceOf(\RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Address\Address::class)
        ->toHaveProperty('id')
        ->toHaveProperty('address')
        ->toHaveProperty('coin', 'tbtc');
});

it('can get wallet transfer', function () {
    $transfer = WalletManager::bitgo('tbtc', 'wallet-id')
        ->getTransfer('transfer-id');

    expect($transfer)
        ->toBeInstanceOf(Transfer::class)
        ->toHaveProperty('id')
        ->toHaveProperty('coin', 'tbtc');
});

it('can generate wallet with webhook', function () {
    $webhook = WalletManager::bitgo('tbtc')
        ->generate('wallet with webhook', 'testing pass', '64065d3743b252a0e029e2faa945e233')
        ->addWebhook(6, 'https://www.blockchain.com/');

    expect($webhook)
        ->toBeObject()
        ->toBeInstanceOf(\RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\WebhookData::class)
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('url', 'https://www.blockchain.com/');

});

it('inits wallet correctly', function () {
    $wallet = WalletManager::bitgo('tbtc', 'wallet-id');
    expect($wallet)
        ->toBeObject()
        ->toHaveProperty('coin', 'tbtc')
        ->toHaveProperty('id', 'wallet-id');
});

it('can list all the available wallets', function () {
    $wallets = WalletManager::bitgo()->listAll();

    expect($wallets)
        ->toBeArray()
        ->and($wallets[0])
        ->toBeInstanceOf(\RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\Wallet::class)
        ->toHaveProperties(['coin', 'id']);
});

it('can send transaction', closure: function () {

    $sendTransferData = new \RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\SendToManyRequest(
        recipients: [
            new \RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\Recipient(address: 'address-1', amount: 333),
            new \RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\Recipient(address: 'address-2', amount: 333),
        ],
        walletPassphrase: 'test',
    );
    $res = WalletManager::bitgo('tbtc', 'wallet-id')->sendTransferToMany($sendTransferData);
    expect($res)->toBeArray();
});

it('can get a maximum spendable amount of the wallet', function () {
    $result = WalletManager::bitgo('tbtc', 'wallet-id')->getMaximumSpendable([
        'feeRate' => 0,
    ]);

    expect($result)
        ->toBeInstanceOf(\RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\MaximumSpendable::class)
        ->toHaveProperties(['coin', 'maximumSpendable']);
});

it('can consolidate wallet balance', function () {
    $result = WalletManager::bitgo('tbtc', 'wallet-id')->consolidate([
        'walletPassphrase' => 'test',
        'bulk' => true,
        'minValue' => '0',
        'minHeight' => 0,
        'minConfirms' => 0,
    ]);

    expect($result)->toBeArray();
});
