# Solana Payments and Provider-Neutral Wallet v2 Design

## Summary

Version 2 of `redberryproducts/laravel-crypto-wallet` will replace the current BitGo-shaped abstraction with provider-neutral wallet contracts and normalized data objects. The existing BitGo integration will be adapted to those contracts, and a new non-custodial Solana driver will support tenant-provided merchant addresses, SOL and configured SPL-token balances, transfer reads, Solana Pay request creation, and server-side payment verification through Solana JSON-RPC.

The Solana v1 driver will never accept, generate, export, or persist merchant private keys. Funds move directly from the customer's wallet to the tenant's wallet.

## Goals

- Make BitGo and Solana available through one provider-neutral manager.
- Preserve provider-specific features through optional capability contracts rather than a single oversized interface.
- Support multitenant applications where each tenant owns a different Solana merchant address.
- Create Solana Pay transfer-request URLs for SOL and configured SPL tokens, initially including USDC and USDT.
- Correlate every payment with a unique Solana reference public key.
- Verify incoming payments through Solana JSON-RPC using exact recipient, reference, mint, amount, network, success, and commitment checks.
- Keep the package stateless and database-agnostic.
- Support mocked tests, a local validator, Devnet sandbox testing, and Mainnet production configuration.

## Non-goals

The initial Solana driver will not:

- Create or custody tenant merchant wallets.
- Store private keys or seed phrases.
- Sign, send, withdraw, refund, or sweep funds.
- Convert cryptocurrency to fiat.
- Create tenant, order, or payment database tables.
- Schedule Laravel jobs or own checkout expiration policy.
- Render QR-code images.
- Run or provision a production RPC node.
- Deploy or require a custom Solana program.
- Provide WebSocket-based payment monitoring.

These capabilities can be introduced later through separate capability contracts or managed-custody drivers.

## Supported Platforms

- PHP: `>=8.1`
- Laravel components: preserve the package's declared Laravel 9+ compatibility unless the implementation plan identifies an unavoidable incompatibility.
- Required PHP extension: `ext-sodium` for Ed25519 signature operations.
- Base58 encoding: `tuupola/base58:^2.2`, configured with the Bitcoin/IPFS alphabet used by Solana addresses. GMP remains an optional performance enhancement because the library has a pure-PHP fallback.
- HTTP transport: Laravel HTTP client.
- Data objects: Spatie Laravel Data, consistent with the existing package.

## Architectural Direction

### Wallet manager

`WalletManager` resolves drivers explicitly or through the configured default:

```php
$solana = WalletManager::solana();
$solana = WalletManager::driver('solana');
$driver = WalletManager::driver();
```

The manager must use Laravel's service container so transports and driver dependencies can be replaced in tests. It must not construct concrete clients with `new` inside public manager methods.

### Core contracts

The package will define a minimal provider-neutral `WalletDriver` marker/base contract plus focused capability contracts:

- `ReadsBalances`
- `ReadsTransfers`
- `CreatesWallets`
- `GeneratesAddresses`
- `CreatesPaymentRequests`
- `VerifiesPayments`
- `SendsTransfers`

The initial Solana driver implements `ReadsBalances`, `ReadsTransfers`, `CreatesPaymentRequests`, and `VerifiesPayments`. It does not implement `CreatesWallets`, `GeneratesAddresses`, or `SendsTransfers`.

BitGo-specific features such as enterprise wallet generation, consolidation, and provider webhooks remain on the BitGo driver or behind appropriately focused provider capabilities. Solana must never implement fake or unsupported BitGo behavior.

### Normalized data objects

Provider-neutral data objects include:

- `Network`
- `Asset`
- `AddressValidationResult`
- `Balance`
- `Transfer`
- `TransferQuery`
- `CreatePaymentRequest`
- `PaymentRequest`
- `ExpectedPayment`
- `PaymentVerificationResult`

Amounts are decimal strings. Base-unit quantities are integer strings. Floating-point values are forbidden for monetary inputs, comparisons, and normalized outputs.

### Solana components

The Solana driver is decomposed into focused units:

- `SolanaDriver`: public driver and capability implementation.
- `MerchantContext`: immutable pairing of a validated merchant address and driver services.
- `SolanaRpcClientContract`: typed gateway for JSON-RPC calls.
- `SolanaRpcClient`: Laravel HTTP implementation with timeout, retries, and JSON-RPC error handling.
- `SolanaPayUrlBuilder`: deterministic Solana Pay transfer-request URI construction.
- `PaymentReferenceGenerator`: creates a random Ed25519 public key used only as a reference; no secret key is persisted.
- `PaymentVerifier`: finds and validates candidate transactions.
- `AssetRegistry`: resolves configured symbols to native SOL or exact SPL mint metadata.
- `AddressCodec`: Base58 decoding, 32-byte public-key validation, and canonical encoding.
- `AmountConverter`: exact decimal-string validation and base-unit conversion.
- `OwnershipSignatureVerifier`: detached Ed25519 message-signature verification.

Each unit is container-resolved and independently testable.

## Configuration

The published `crypto-wallet.php` configuration gains a default driver and Solana section:

```php
return [
    'default' => env('CRYPTO_WALLET_DRIVER', 'bitgo'),

    'drivers' => [
        'bitgo' => [
            // Existing BitGo configuration remains available.
        ],

        'solana' => [
            'network' => env('SOLANA_NETWORK', 'devnet'),
            'rpc_url' => env('SOLANA_RPC_URL', 'https://api.devnet.solana.com'),
            'commitment' => env('SOLANA_COMMITMENT', 'confirmed'),
            'timeout' => 10,
            'retry_times' => 2,

            'assets' => [
                'SOL' => [
                    'type' => 'native',
                    'decimals' => 9,
                ],
                'USDC' => [
                    'type' => 'spl',
                    'mint' => env('SOLANA_USDC_MINT'),
                    'decimals' => 6,
                ],
                'USDT' => [
                    'type' => 'spl',
                    'mint' => env('SOLANA_USDT_MINT'),
                    'decimals' => 6,
                ],
            ],
        ],
    ],
];
```

The RPC URL may contain credentials. Data objects, exceptions, debug output, and logs must never serialize the unredacted configured URL.

The package validates that:

- Network is one of `localnet`, `devnet`, or `mainnet-beta`.
- Commitment is one of `processed`, `confirmed`, or `finalized`.
- Native SOL has no mint and uses nine decimals.
- Every SPL asset has a valid 32-byte Base58 mint address and a non-negative decimal count.
- Mainnet and non-Mainnet asset configuration are distinct; no implicit fallback to a Mainnet mint is allowed on Devnet or localnet.

## Tenant Onboarding

Each tenant supplies a public Solana merchant address created and controlled outside the package. The host application stores the address with its network and tenant ownership.

The package exposes address validation and ownership signature verification:

```php
$validation = WalletManager::solana()->validateAddress($address);

$verified = WalletManager::solana()->verifyAddressOwnership(
    address: $address,
    message: $challengeMessage,
    signature: $signature,
);
```

Address validation decodes Base58 canonically and requires exactly 32 decoded bytes. For the receive-only merchant policy, the implementation also classifies the address through RPC when account data exists. A valid unfunded on-curve address is accepted. Addresses that are known token accounts, mint accounts, executable program accounts, or unsupported off-curve accounts are rejected as merchant wallet addresses.

The host application owns challenge construction, nonce storage, expiration, tenant association, and replay prevention. A production application should not enable payments until ownership is verified.

## Immutable Merchant Context

The driver creates a new merchant context for each tenant address:

```php
$merchant = WalletManager::solana()->forMerchant($tenantAddress);
```

The context is immutable and must never mutate singleton driver state. This prevents tenant leakage in Laravel Octane and long-running queue workers.

The context provides:

```php
$merchant->address();
$merchant->network();
$merchant->getBalance('SOL');
$merchant->getBalance('USDT');
$merchant->getBalances();
$merchant->getTransfers(new TransferQuery(limit: 25));
$merchant->getTransfer($signature);
$merchant->createPaymentRequest($request);
```

## Asset and Balance Behavior

`AssetRegistry` treats the configured symbol as a convenience alias and the mint address as the on-chain identity.

- `SOL` balance uses `getBalance` and converts lamports with nine decimals.
- SPL balance uses `getTokenAccountsByOwner`, filtered by the configured mint.
- A missing associated token account produces a zero balance, not an error.
- `getBalances()` returns configured assets only. It does not enumerate arbitrary or spam tokens.
- Amount conversion rejects scientific notation, signs, whitespace, excess decimal places, negative values, and values that overflow the supported integer-string operations.

## Transfer Reads

Transfer history uses address signatures and parsed transactions:

- `getSignaturesForAddress` retrieves paginated signatures.
- `getTransaction` with `jsonParsed`, configured commitment, and `maxSupportedTransactionVersion: 0` retrieves transaction details.
- Parsed transfers are normalized for configured assets.
- Unknown or unparseable program instructions are ignored unless they prevent safe interpretation of a requested transfer.
- `getTransfer($signature)` returns `null` when the transaction is absent at the requested commitment.

`TransferQuery` initially supports `limit` and `before`. The driver caps `limit` to a documented safe maximum.

## Payment Request Creation

The host application creates an order first and passes its ID as an off-chain merchant reference:

```php
$paymentRequest = $merchant->createPaymentRequest(
    new CreatePaymentRequest(
        amount: '25.00',
        asset: 'USDT',
        merchantReference: (string) $order->id,
        label: $tenant->name,
        message: "Payment for order {$order->id}",
        expiresAt: now()->addMinutes(15),
    ),
);
```

Creation performs these steps:

1. Validate the merchant address and request fields.
2. Resolve the asset and exact mint for the configured network.
3. Convert the decimal amount into exact base units.
4. Generate a unique random Ed25519 public key for the Solana Pay reference.
5. Build a Solana Pay transfer-request URL containing recipient, amount, optional SPL mint, reference, label, message, and optional memo.
6. Return a `PaymentRequest` DTO.

The reference private key is not needed and is discarded immediately. The reference cannot control or receive the payment.

`PaymentRequest` contains:

- `merchantReference`
- `recipientAddress`
- `referenceAddress`
- `asset`
- `mintAddress`
- `amount`
- `baseUnits`
- `network`
- `url`
- `expiresAt`

Payment-request creation is local and performs no RPC request after the merchant address and configuration have already been validated.

The package returns the URL but does not render a QR code.

## Host Application Persistence

The package remains stateless. The host application persists, at minimum:

- Tenant identifier.
- Order identifier.
- Network.
- Recipient address.
- Reference address.
- Asset symbol.
- Exact mint address or `null` for SOL.
- Expected decimal amount.
- Expected base units.
- Status.
- Expiration timestamp.
- Transaction signature after discovery.
- Paid timestamp after business processing.

These values are an immutable expected-payment snapshot. Later tenant-address or asset-configuration changes must not alter verification of an existing payment.

The host database must enforce a unique constraint on non-null transaction signatures so one transaction cannot settle multiple payments.

## Payment Verification

The host reconstructs an `ExpectedPayment` and requests one verification attempt:

```php
$result = WalletManager::solana()->verifyPayment(
    new ExpectedPayment(
        network: $payment->network,
        recipientAddress: $payment->recipient_address,
        referenceAddress: $payment->reference_address,
        asset: $payment->asset,
        mintAddress: $payment->mint_address,
        amount: $payment->expected_amount,
        baseUnits: $payment->expected_base_units,
    ),
);
```

The verifier:

1. Rejects a network mismatch before RPC access.
2. Calls `getSignaturesForAddress` for the unique reference address.
3. Returns `pending` when no candidate exists.
4. Calls `getTransaction` for candidate signatures.
5. Requires transaction execution success.
6. Requires the configured commitment threshold.
7. Requires the reference address in the transaction account keys.
8. Requires the exact stored recipient.
9. For SOL, validates the System Program transfer and exact lamports.
10. For SPL tokens, validates the Token Program transfer, exact mint, recipient ownership/ATA relationship, and exact base-unit delta.
11. Returns normalized actual values and the signature.

`PaymentVerificationResult` statuses are:

- `pending`: no matching transaction is available yet.
- `confirmed`: all expected fields match at the required commitment.
- `failed`: the referenced transaction executed with an error.
- `mismatch`: a transaction references the payment but recipient, mint, or amount does not match.

Checkout expiration is not a blockchain property and is not returned by the verifier. The host application owns `expired`, `late_payment`, manual-review, refund, and order-state policies.

The verifier does not query the host database for duplicate signatures. It returns the signature, and the host's unique constraint provides the authoritative idempotency boundary.

## Polling

The package performs a single verification attempt per call. The host application schedules Laravel jobs and applies backoff. Recommended application behavior is:

- First minute: every 5 seconds.
- Next four minutes: every 15 seconds.
- Afterward: every 60 seconds until application expiration.

Transient RPC errors use bounded HTTP retries inside the RPC client. They remain distinguishable from a normal `pending` result so the host can observe provider degradation.

WebSocket monitoring is deferred because it requires a persistent connection, reconnection behavior, and provider-specific operational constraints.

## JSON-RPC Transport

`SolanaRpcClientContract` exposes typed methods or a protected generic call primitive. The concrete client sends JSON-RPC 2.0 POST requests through Laravel's HTTP client.

It must handle both transport and protocol errors:

- Non-2xx HTTP status.
- Connection failure and timeout.
- HTTP 429 rate limiting, honoring `Retry-After` when practical.
- HTTP 200 with a JSON-RPC `error` object.
- Missing or malformed `result` envelopes.
- Response IDs that do not match the request.

The client never logs full RPC URLs, request authorization material, or unredacted provider payloads that could contain credentials.

## Error Model

Typed exceptions cover programmer/configuration errors and infrastructure failures:

- `InvalidAddressException`
- `UnsupportedAssetException`
- `InvalidAssetConfigurationException`
- `InvalidAmountException`
- `NetworkMismatchException`
- `SolanaRpcException`
- `SolanaRpcRateLimitException`
- `MalformedSolanaResponseException`
- `OwnershipVerificationException`

Expected payment states such as `pending`, `failed`, and `mismatch` are result statuses rather than exceptions.

## Multitenancy

- RPC connection, network, and asset registry are application-level configuration shared by default.
- Every tenant has its own verified merchant address.
- Every payment has its own unique reference address.
- Merchant contexts are immutable and request-scoped.
- Expected-payment records always include `tenant_id` and are queried through the host application's tenant scope.
- Verification uses the recipient snapshot stored at payment creation, not the tenant's current address.
- A tenant changing addresses affects new payments only.

Separate tenant RPC credentials are outside the initial scope. The container-driven architecture must not prevent a future application from resolving tenant-specific driver instances.

## Testing Strategy

### Unit and package integration tests

Laravel HTTP fakes and deterministic JSON fixtures cover:

- Valid and invalid Base58 addresses.
- Canonical 32-byte address validation.
- Ownership signature success, failure, malformed signature, expired/reused challenge responsibility boundaries.
- Exact amount conversions for SOL, six-decimal stablecoins, zero, large amounts, and invalid precision.
- SOL and SPL balance normalization.
- Missing ATA as zero balance.
- Solana Pay URL construction and encoding.
- Unique reference generation.
- Payment pending, confirmed, failed, and mismatch outcomes.
- Wrong recipient, reference, mint, token program, amount, and network.
- Duplicate-signature responsibility documented and represented in host-application example tests.
- JSON-RPC HTTP errors, protocol errors, malformed results, timeouts, and rate limits.
- Credential redaction.
- Immutable merchant contexts under alternating tenant usage.
- BitGo regression behavior and provider capability checks.

No default CI test makes a public network request.

### Local validator tests

An opt-in test group targets `http://127.0.0.1:8899` and validates real SOL and test SPL-token transfers against a resettable local ledger.

### Devnet sandbox tests

An opt-in test group uses a configured Devnet RPC endpoint, funded payer and merchant test wallets, and configured six-decimal test mints labeled USDC and USDT. These tokens have no relationship to production issuers. Devnet tests validate end-to-end request creation, wallet payment, discovery, and verification.

### Production readiness

Production documentation requires an explicit `mainnet-beta` network, a managed private RPC endpoint, official verified mint addresses supplied by the host application, ownership-verified tenant addresses, queue monitoring, and database signature uniqueness.

## BitGo Migration

Version 2 is a major release. Existing BitGo behavior will be adapted behind provider-neutral capabilities while direct BitGo access remains available where practical.

The implementation plan must include:

- Removal or correction of the nonexistent Composer facade alias.
- Container-based driver construction.
- Application of configured default values instead of nullable implicit BitGo parameters.
- A documented migration table from current `WalletManager::bitgo(...)` calls to v2 calls.
- Regression tests for supported existing BitGo operations.
- Explicit documentation for provider-specific methods that cannot be normalized.

No unrelated BitGo refactor is included beyond what is necessary to create stable shared boundaries and preserve behavior.

## Documentation Deliverables

The README will document:

- Version 2 migration and driver selection.
- Solana terminology: wallet address, mint, token account, ATA, reference address, and transaction signature.
- Tenant onboarding and ownership verification.
- SOL, USDC, USDT, and custom SPL configuration.
- Payment creation and verification examples.
- Application persistence and queue responsibilities.
- Local validator and Devnet sandbox setup.
- Production RPC and key-safety requirements.
- Unsupported custody and outgoing-transfer operations.

## Acceptance Criteria

The design is implemented when:

- The driver manager resolves BitGo and Solana through the container.
- Capability contracts accurately describe provider support.
- Alternating tenant contexts cannot leak addresses.
- SOL and configured SPL balances normalize without floats.
- Payment requests produce valid deterministic Solana Pay URLs with unique references.
- Payment verification rejects every mismatch and confirms only exact successful transactions.
- No merchant private key is accepted, returned, logged, or persisted.
- Default tests pass without network access.
- Local-validator and Devnet tests are opt-in and documented.
- PHPStan and the supported Laravel/PHP test matrix pass.
- The README documents the complete public API and migration path.
