<?php

namespace RedberryProducts\CryptoWallet\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RedberryProducts\CryptoWallet\Adapters\BitgoAdapter;
use RedberryProducts\CryptoWallet\Contracts\BitgoAdapterContract;
use RedberryProducts\CryptoWallet\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\CryptoWalletServiceProvider;

class TestCase extends Orchestra
{
    use BitgoHttpMocks;

    public BitgoAdapterContract $adapter;

    public WalletContract $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new BitgoAdapter;
        if (config('crypto-wallet.use_mocks')) {
            self::setupMocks();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            CryptoWalletServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
