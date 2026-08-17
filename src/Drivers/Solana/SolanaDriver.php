<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use RedberryProducts\CryptoWallet\Contracts\VerifiesPayments;
use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Data\AddressValidationResult;
use RedberryProducts\CryptoWallet\Data\Asset;
use RedberryProducts\CryptoWallet\Data\ExpectedPayment;
use RedberryProducts\CryptoWallet\Data\Network;
use RedberryProducts\CryptoWallet\Data\PaymentVerificationResult;
use RedberryProducts\CryptoWallet\Drivers\Solana\Contracts\SolanaRpcClientContract;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\OwnershipSignatureVerifier;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\PaymentReferenceGenerator;

class SolanaDriver implements VerifiesPayments, WalletDriver
{
    public function __construct(
        private readonly Network $configuredNetwork,
        private readonly AssetRegistry $assetRegistry,
        private readonly AddressCodec $addressCodec,
        private readonly OwnershipSignatureVerifier $ownershipVerifier,
        private readonly SolanaRpcClientContract $rpc,
        private readonly AmountConverter $amountConverter,
        private readonly PaymentReferenceGenerator $referenceGenerator,
        private readonly SolanaPayUrlBuilder $urlBuilder,
        private readonly PaymentVerifier $paymentVerifier,
        private readonly TransferParser $transferParser,
    ) {}

    public function name(): string
    {
        return 'solana';
    }

    public function network(): Network
    {
        return $this->configuredNetwork;
    }

    /** @return array<string, Asset> */
    public function assets(): array
    {
        return $this->assetRegistry->all();
    }

    public function asset(string $symbol): Asset
    {
        return $this->assetRegistry->get($symbol);
    }

    public function validateAddress(string $address): AddressValidationResult
    {
        return $this->addressCodec->validate($address);
    }

    public function verifyAddressOwnership(string $address, string $message, string $signature): bool
    {
        return $this->ownershipVerifier->verify($address, $message, $signature);
    }

    public function forMerchant(string $address): MerchantContext
    {
        $this->addressCodec->decode($address);

        return new MerchantContext(
            $address,
            $this->configuredNetwork->name->value,
            $this->rpc,
            $this->assetRegistry,
            $this->amountConverter,
            $this->referenceGenerator,
            $this->urlBuilder,
            $this->transferParser,
        );
    }

    public function verifyPayment(ExpectedPayment $payment): PaymentVerificationResult
    {
        return $this->paymentVerifier->verify($payment);
    }
}
