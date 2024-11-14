# Laravel BitGo Wallet Package

This package is a Laravel wrapper for BitGo's API, allowing developers to interact with cryptocurrency wallets, manage transactions, and handle other wallet-related operations efficiently within Laravel applications.

## 📋 Overview

The `Khomeriki\BitgoWallet` package provides a set of features to:

- Generate, manage, and list wallets
- Fetch and consolidate wallet balances
- Create, send, and monitor transfers
- Add webhooks to track wallet events in real time

### **Core Features**

- **Wallet Management**: Generate and initialize wallets, manage wallet properties, and retrieve wallet information.
- **Transfers**: Support for sending multi-recipient transactions and fetching wallet transfers.
- **Webhooks**: Automated notifications for wallet activities.
- **Balance Management**: Get spendable amounts, consolidate balances, and monitor wallet details.

## Setting Up BitGo Express with Docker
To use the `Khomeriki\BitgoWallet` package, BitGo Express needs to be running as a local service. BitGo Express acts as a bridge between your application and the BitGo API, securely managing wallet interactions and signing transactions.

Pulling the BitGo Express Docker Image
First, pull the latest BitGo Express image from Docker Hub:

    docker pull bitgosdk/express

## 🛠 Installation

1. Install via Composer:
    
    ```bash
    composer require khomeriki/bitgo-wallet
    ```
    
2. Publish the configuration file and set up your BitGo API credentials:
    
    ```bash
    php artisan vendor:publish --provider="Khomeriki\BitgoWallet\BitgoWalletServiceProvider"
    ```
    
3. Update `.env` file with BitGo credentials:
    
    ```
    BITGO_API_KEY=your-bitgo-api-key
    BITGO_EXPRESS_API_URL=http://bitgo-express:3080/
    BITGO_DEFAULT_COIN=tbtc  # Example default coin (use mainnet symbols like btc for production
    ```
    

## Usage

The following sections provide usage examples for core features of the package.

### 1. **Wallet Initialization & Generation**

Initialize a wallet instance for a specific coin:

```php
use Khomeriki\BitgoWallet\Facades\Wallet;

// Initialize an existing wallet
$wallet = Wallet::init('tbtc', 'wallet-id');
```

Generate a new wallet with a label and passphrase:

```php
$wallet = Wallet::init('tbtc')
    ->generate('My Test Wallet', 'strongpassphrase', 'enterprise-id');
```

### 2. **Listing Wallets**

Fetch all available wallets with optional parameters:

```php
$wallets = Wallet::listAll([
    'expandBalance' => 'true',
])
```

### 3. **Address Management**

Generate a new address within the wallet:

```php
$address = $wallet->generateAddress('Deposit Address');
```

### 4. **Transfers**

### **Creating a Transfer**

Create a transfer to multiple recipients:

```php
use Khomeriki\BitgoWallet\Data\TransferData;
use Khomeriki\BitgoWallet\Data\TransferRecipientData;

$transferData = TransferData::fromArray([
    'walletPassphrase' => 'strongpassphrase',
    'recipients' => [
        TransferRecipientData::fromArray(['amount' => 1000, 'address' => 'recipient-address']),
        TransferRecipientData::fromArray(['amount' => 2000, 'address' => 'another-address']),
    ],
]);

$response = $wallet->sendTransfer($transferData);
```

### **Fetching Transfers**

Retrieve all transfers associated with a wallet:

```php
$transfers = $wallet->getTransfers(['limit' => 500])
```

Get a specific transfer by its ID:

```php
$transfer = $wallet->getTransfer('transfer-id')
```

### 5. **Webhook Management**

Add a webhook for a wallet with a callback URL to track transaction confirmations:

```php
$webhook = $wallet->addWebhook(6, 'http://your-app.com/callback')
```

### 6. **Balance Management**

### **Get Maximum Spendable Balance**

Calculate the maximum spendable amount:

```php
$maxSpendable = $wallet->getMaximumSpendable([
    'feeRate' => 0,
]);
```

### **Consolidate Wallet Balance**

Consolidate wallet UTXOs to optimize the wallet balance:

```php
$result = $wallet->consolidate([
    'walletPassphrase' => 'strongpassphrase',
    'bulk' => true,
    'minValue' => 1000,
]);
```

## API Reference

Each of the package methods is designed to directly map to BitGo’s API. Refer to *[the BitGo API documentation](https://developers.bitgo.com/)*. for more details on the data structure and optional parameters that can be included.

## Contributing

1. Fork the repository and clone it.
2. Set up your local development environment with appropriate BitGo credentials.
3. Make your changes and write tests to verify functionality.
4. Submit a pull request with a clear description of changes.

## 🧪 Testing

This package includes test cases to verify core functionality. Run the test suite using:

```bash
./vendor/bin/pest
```

## 📜 License

This package is open-sourced software licensed under the [MIT license](https://www.notion.so/redberry/LICENSE.md).
