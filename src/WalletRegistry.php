<?php

namespace RedberryProducts\CryptoWallet;

use Illuminate\Contracts\Container\Container;
use RedberryProducts\CryptoWallet\Contracts\WalletDriver;
use RedberryProducts\CryptoWallet\Exceptions\UnsupportedDriverException;
use UnexpectedValueException;

class WalletRegistry
{
    /** @var array<string, WalletDriver> */
    private array $resolved = [];

    /**
     * @param  array<string, array<string, mixed>>  $drivers
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $drivers,
        private readonly string $defaultDriver,
    ) {}

    public function driver(?string $name = null): WalletDriver
    {
        $name ??= $this->defaultDriver;

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $driverClass = $this->drivers[$name]['driver'] ?? null;

        if (! is_string($driverClass)) {
            throw new UnsupportedDriverException("Crypto wallet driver [{$name}] is not configured.");
        }

        $driver = $this->container->make($driverClass);

        if (! $driver instanceof WalletDriver) {
            throw new UnexpectedValueException("Crypto wallet driver [{$name}] must implement ".WalletDriver::class.'.');
        }

        return $this->resolved[$name] = $driver;
    }
}
