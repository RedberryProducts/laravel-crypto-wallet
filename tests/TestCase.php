<?php

namespace RedberryProducts\CryptoWallet\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RedberryProducts\CryptoWallet\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\CryptoWalletServiceProvider;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoClient;

class TestCase extends Orchestra
{
    use BitgoHttpMocks;

    public BitgoClient $client;

    public WalletContract $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new BitgoClient;
        if (config('crypto-wallet.drivers.bitgo.use_mocks')) {
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
