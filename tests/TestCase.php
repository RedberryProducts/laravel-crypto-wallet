<?php

namespace Khomeriki\BitgoWallet\Tests;

use Khomeriki\BitgoWallet\Adapters\BitgoAdapter;
use Khomeriki\BitgoWallet\BitgoServiceProvider;
use Khomeriki\BitgoWallet\Contracts\BitgoAdapterContract;
use Khomeriki\BitgoWallet\Contracts\WalletContract;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use BitgoHttpMocks;

    public BitgoAdapterContract $adapter;

    public WalletContract $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new BitgoAdapter();
        if (config('bitgo.use_mocks')) {
            self::setupMocks();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            BitgoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
