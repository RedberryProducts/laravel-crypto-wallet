<?php

namespace RedberryProducts\CryptoWallet;

use Illuminate\Support\ServiceProvider;
use RedberryProducts\CryptoWallet\Contracts\ExchangeRateContract;
use RedberryProducts\CryptoWallet\Contracts\WalletContract;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\ExchangeRate;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\Wallet;

/**
 * CryptoWalletServiceProvider is the service provider for the Bitgo Wallet package.
 */
class CryptoWalletServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/crypto-wallet.php',
            'crypto-wallet'
        );

        $this->registerDriver();
    }

    /**
     * Register the Wallet binding.
     */
    protected function registerDriver(): void
    {
        $this->app->bind(WalletContract::class, Wallet::class);
        $this->app->bind(ExchangeRateContract::class, ExchangeRate::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/crypto-wallet.php' => config_path('crypto-wallet.php'),
        ], 'crypto-wallet-config');
    }
}
