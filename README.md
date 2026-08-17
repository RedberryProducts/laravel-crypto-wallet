# Laravel Crypto Wallet

## Table of Contents
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage](#usage)
5. [Solana Showcase App](#solana-showcase-app)
6. [Testing](#testing)
7. [Contributing](#contributing)

## Introduction

Laravel Crypto Wallet provides provider-specific drivers behind a common Laravel registry. It supports custodial BitGo wallet operations and non-custodial Solana payment collection for SOL and configured SPL tokens such as USDC and USDT.

The Solana driver never accepts or stores private keys. Each tenant supplies and controls its public merchant address.

Currently, we support **Bitgo** for operations such as:

- [Generating a Wallet](#generating-a-wallet)
- [Getting a Wallet](#getting-a-wallet)
- [Listing Wallets](#listing-wallets)
- [Generating Addresses](#generating-addresses)
- [Getting Wallet Transfers](#getting-wallet-transfers)
- [Sending Transactions](#sending-transactions)
- [Getting Maximum Spendable](#getting-maximum-spendable)
- [Consolidating Wallet Balances](#consolidating-wallet-balances)
- [Adding Webhooks](#adding-webhooks)
- [Exchange Rates](#exchange-rates)

The package uses a clear, fluent API and is fully testable, making it simpler to integrate cryptocurrency-related features into your Laravel application.

## Installation

1. **Install via Composer**:

```bash
composer require redberryproducts/laravel-crypto-wallet
```

## Bitgo Express Docker

To run Bitgo Express locally using Docker, you can use the following commands:
```bash
docker pull bitgo/express:latest
docker run -it -p 3080:3080 bitgo/express:latest
```
For more information on Bitgo Express Docker, refer to the official [Bitgo Express Docker documentation](https://developers.bitgo.com/guides/get-started/express/install).

## Configuration

Published configuration is available at `config/crypto-wallet.php`. BitGo settings live under `drivers.bitgo`; Solana settings live under `drivers.solana`.

```php
return [
    'drivers' => [
        'bitgo' => [
            'use_mocks' => env('BITGO_USE_MOCKS', false),
            'testnet' => env('BITGO_TESTNET', true),
            'api_key' => env('BITGO_API_KEY'),
            'express_api_url' => env('BITGO_EXPRESS_API_URL'),
            'default_coin' => env('BITGO_DEFAULT_COIN', 'tbtc4'),//This is not a typo or just a result of a lazy developer :). BitGo is moving to Testnet4, so the Bitcoin testnet is now TBTC4.
            'webhook_callback_url' => env('BITGO_WEBHOOK_CALLBACK'),
        ],
    ],
];

```

**.env Example**:

```dotenv
BITGO_USE_MOCKS = false
BITGO_TESTNET = true
BITGO_API_KEY = YOUR-BITGO-API-KEY
BITGO_EXPRESS_API_URL = http://localhost:3080/api/v2/
BITGO_DEFAULT_COIN = tbtc4
BITGO_WEBHOOK_CALLBACK = https://yourapp.com/webhook/bitgo
```

Adjust these environment variables according to your needs.

Solana configuration uses exact network-specific token mints:

```dotenv
SOLANA_NETWORK=devnet
SOLANA_RPC_URL=https://api.devnet.solana.com
SOLANA_COMMITMENT=confirmed
SOLANA_USDC_MINT=your-devnet-usdc-mint
SOLANA_USDT_MINT=your-devnet-usdt-mint
```

## Usage

### Solana payments

```php
use RedberryProducts\CryptoWallet\Data\CreatePaymentRequest;
use RedberryProducts\CryptoWallet\WalletManager;

$solana = WalletManager::solana(); // or WalletManager::driver('solana')

$validation = $solana->validateAddress($tenant->solana_address);
$verified = $solana->verifyAddressOwnership(
    address: $tenant->solana_address,
    message: $challenge->message,
    signature: $submittedSignature,
);

$merchant = $solana->forMerchant($tenant->solana_address);
$solBalance = $merchant->getBalance('SOL');
$usdtBalance = $merchant->getBalance('USDT');

$paymentRequest = $merchant->createPaymentRequest(new CreatePaymentRequest(
    amount: '25.00',
    asset: 'USDT',
    merchantReference: (string) $order->id,
    label: $tenant->name,
    message: "Payment for order {$order->id}",
    expiresAt: now()->addMinutes(15),
));
```

The host application renders `$paymentRequest->url` as a QR code and persists its immutable recipient, reference, asset, mint, network, amount, base units, and expiry. The `merchantReference` is the host application's order ID; it is not automatically written on-chain.

Verify one polling attempt from a queued job:

```php
use RedberryProducts\CryptoWallet\Data\ExpectedPayment;

$result = WalletManager::solana()->verifyPayment(new ExpectedPayment(
    network: $payment->network,
    recipientAddress: $payment->recipient_address,
    referenceAddress: $payment->reference_address,
    asset: $payment->asset,
    mintAddress: $payment->mint_address,
    amount: $payment->expected_amount,
    baseUnits: $payment->expected_base_units,
));
```

Results are `pending`, `confirmed`, `failed`, or `mismatch`. The host owns polling/backoff, checkout expiration, late-payment policy, order transitions, and a unique database constraint on transaction signatures.

For multitenancy, create a fresh immutable context with `forMerchant()` for each tenant. Shared drivers never retain a tenant address. SOL, USDC, USDT, and custom tokens use exact decimal strings and network-specific mint configuration.

For sandbox testing, use a local Solana validator or Devnet and configure `SOLANA_NETWORK`, `SOLANA_RPC_URL`, `SOLANA_USDC_MINT`, and `SOLANA_USDT_MINT`. Never reuse Mainnet mints implicitly on another network. Production should use a managed Mainnet RPC endpoint.

The Solana driver is receive/read/verify only. It does not create custodial wallets, accept seed phrases, sign transfers, sweep funds, or issue refunds.

### Bitgo Driver

All Bitgo functionality is encapsulated within the **Bitgo** driver, which is used by default when calling `WalletManager::bitgo()`.

### Wallet Factory

The core entry point for all wallet-related activities is the `WalletManager` class. You can call static methods (like `bitgo()`) to instantiate a specific driver. For example:

```php
use RedberryProducts\CryptoWallet\WalletManager;

$wallet = WalletManager::bitgo();
```

Optionally, you can specify a coin and/or wallet ID:

```php
$wallet = WalletManager::bitgo(coin: 'tbtc', walletId: 'my-wallet-id');
```

### Generating a Wallet

```php
$wallet = WalletManager::bitgo(coin: 'tbtc')
    ->generate(
        label: 'Test Wallet',
        passphrase: 'test-passphrase',
        enterpriseId: 'enterprise-id',
    );
```

### Getting a Wallet

```php
$wallet = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')
        ->get();
```

### Listing Wallets

```php
$wallets = WalletManager::bitgo()->listAll();
```

### Generating Addresses

```php
$address = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')
    ->generateAddress(label: 'My Address');
```

### Getting Wallet Transfers

```php
$transfers = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')
    ->getTransfers();
```

### Sending Transactions

**Send to multiple recipients:**

```php
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\SendToManyRequest;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany\Recipient;

$sendTransferData = new SendToManyRequest(
    recipients: [
        new Recipient(address: 'tb1psv9q9zlp94s9jncnlye4kj0acyp56suxf28hn4k34vyrmsrp4qtsc9eqlq', amount: 4368),
    ],

    walletPassphrase: 'test',
    feeRate: 250,
);

$response = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')
    ->sendTransferToMany(sendToManyRequest: $sendTransferData);
```

### Getting Maximum Spendable

```php
$maxSpendable = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')
    ->getMaximumSpendable([
        'feeRate' => 0,
    ]);
```

### Consolidating Wallet Balances

```php
$result = WalletManager::bitgo(coin: 'tbtc', walletId: 'wallet-id')->consolidate([
    'walletPassphrase' => 'testing-pass',
    'bulk' => true,
    'minValue' => '0',
    'minHeight' => 0,
    'minConfirms' => 0,
]);
```

### Adding Webhooks

When you generate a wallet, you can easily attach a webhook:

```php
$webhook = WalletManager::bitgo('tbtc')
    ->generate(
        label: 'wallet with webhook', 
        passphrase: 'test-passphrase',
        enterpriseId: 'enterprise-id'
    )
    ->addWebhook(
        numConfirmations: 6, 
        callbackUrl: 'https://yourapp.com/webhook/bitgo'
    );

```

## Exchange Rates

You can easily fetch current exchange rates using the `ExchangeRateManager`.

Currently, we provide a **Bitgo** driver implementation.

### Fetch All Exchange Rates

```php
use RedberryProducts\CryptoWallet\ExchangeRateManager;

$rates = ExchangeRateManager::bitgo()->all();
```

### Fetch Exchange Rates for a Specific Coin

```php
use RedberryProducts\CryptoWallet\ExchangeRateManager;

$tbtcRates = ExchangeRateManager::bitgo()->getByCoin('tbtc');
```

## Solana Showcase App

A companion Laravel Inertia React showcase application demonstrates the complete Devnet flow: verify control of a merchant wallet, inspect balances, create SOL or configured SPL-token Solana Pay requests, pay from a second wallet, verify the transaction through RPC, and inspect transfer history. It is maintained as a separate application alongside this package.

The showcase is non-custodial and Devnet-only. Its guide covers local setup, externally created USDC-like and USDT-like demo mints, wallet funding, the opt-in live smoke test, and all verification commands.

## Testing

We use [Pest PHP](https://pestphp.com/) to ensure all functionalities work as expected. You can find our test files under `tests/`. To run the tests:

```bash
./vendor/bin/pest
# or
php artisan test
```

Default tests mock Solana RPC. Network smoke tests are explicitly opt-in:

```bash
SOLANA_DEVNET_TEST=1 SOLANA_TEST_MERCHANT_ADDRESS=... ./vendor/bin/pest --group=solana-devnet
SOLANA_LOCAL_TEST=1 SOLANA_TEST_MERCHANT_ADDRESS=... ./vendor/bin/pest --group=solana-local
```

## Contributing

1. Fork the repository
2. Create a new branch (`git checkout -b feature/someFeature`)
3. Make your changes
4. Write or update tests
5. Commit your changes (`git commit -m 'feat: Add some feature'`)
6. Push to the branch (`git push origin feature/someFeature`)
7. Create a Pull Request

We welcome all contributions that help improve this package!
