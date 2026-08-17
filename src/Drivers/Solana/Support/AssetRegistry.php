<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Support;

use RedberryProducts\CryptoWallet\Data\Asset;
use RedberryProducts\CryptoWallet\Enums\AssetType;
use RedberryProducts\CryptoWallet\Enums\NetworkName;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAddressException;
use RedberryProducts\CryptoWallet\Exceptions\InvalidAssetConfigurationException;
use RedberryProducts\CryptoWallet\Exceptions\UnsupportedAssetException;
use ValueError;

class AssetRegistry
{
    /** @param array<string, array<string, mixed>> $assets */
    public function __construct(
        private readonly string $network,
        private readonly array $assets,
        private readonly AddressCodec $addressCodec,
    ) {
        try {
            NetworkName::from($network);
        } catch (ValueError $exception) {
            throw new InvalidAssetConfigurationException("Unsupported Solana network [{$network}].", 0, $exception);
        }
    }

    public function get(string $symbol): Asset
    {
        $symbol = strtoupper($symbol);
        $configuration = $this->assets[$symbol] ?? null;

        if (! is_array($configuration)) {
            throw new UnsupportedAssetException("Solana asset [{$symbol}] is not configured for [{$this->network}].");
        }

        $type = $configuration['type'] ?? null;
        $decimals = $configuration['decimals'] ?? null;

        if (! is_int($decimals) || $decimals < 0) {
            throw new InvalidAssetConfigurationException("Solana asset [{$symbol}] must define non-negative integer decimals.");
        }

        if ($type === 'native') {
            if ($symbol !== 'SOL' || $decimals !== 9 || isset($configuration['mint'])) {
                throw new InvalidAssetConfigurationException('Native SOL must use nine decimals and cannot define a mint.');
            }

            return new Asset($symbol, AssetType::Native, $decimals, $this->network);
        }

        if ($type !== 'spl') {
            throw new InvalidAssetConfigurationException("Solana asset [{$symbol}] has an unsupported type.");
        }

        $mint = $configuration['mint'] ?? null;

        if (! is_string($mint)) {
            throw new InvalidAssetConfigurationException("SPL asset [{$symbol}] must define a mint for [{$this->network}].");
        }

        try {
            $this->addressCodec->decode($mint);
        } catch (InvalidAddressException $exception) {
            throw new InvalidAssetConfigurationException("SPL asset [{$symbol}] has an invalid mint for [{$this->network}].", 0, $exception);
        }

        return new Asset($symbol, AssetType::Token, $decimals, $this->network, $mint);
    }

    /** @return array<string, Asset> */
    public function all(): array
    {
        $resolved = [];

        foreach (array_keys($this->assets) as $symbol) {
            $resolved[$symbol] = $this->get($symbol);
        }

        return $resolved;
    }

    public function findByMint(string $mint): ?Asset
    {
        foreach ($this->assets as $symbol => $configuration) {
            if (($configuration['mint'] ?? null) === $mint) {
                return $this->get($symbol);
            }
        }

        return null;
    }
}
