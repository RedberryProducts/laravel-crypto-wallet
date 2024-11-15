<?php

namespace RedberryProducts\CryptoWallet;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use RedberryProducts\CryptoWallet\Adapters\BitgoAdapter;
use RedberryProducts\CryptoWallet\Contracts\BitgoAdapterContract;

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

        $this->registerBitgoAdapter();
        $this->registerWallet();
        $this->registerExchangeRate();
    }

    /**
     * Register the BitgoAdapter binding.
     */
    protected function registerBitgoAdapter(): void
    {
        $this->app->bind(BitgoAdapterContract::class, BitgoAdapter::class);
    }

    /**
     * Register the Wallet binding.
     */
    protected function registerWallet(): void
    {
        $this->app->bind('Wallet', function () {
            return new Wallet(
                app(BitgoAdapterContract::class)
            );
        });
    }

    /**
     * Register the ExchangeRate binding.
     */
    protected function registerExchangeRate(): void
    {
        $this->app->bind('ExchangeRate', function () {
            return new ExchangeRate(
                app(BitgoAdapterContract::class)
            );
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/crypto-wallet.php' => config_path('crypto-wallet.php'),
        ], 'bitgo-config');

        $this->registerHttpMacros();
    }

    /**
     * Register Http macros for Bitgo API and Bitgo Express API.
     */
    public function registerHttpMacros()
    {
        $apiUrl = config('crypto-wallet.testnet') ? config('crypto-wallet.testnet_api_url') : config('crypto-wallet.mainnet_api_url');

        Http::macro('bitgoApi', function () use ($apiUrl) {
            return Http::withHeaders([
                'Authorization' => 'Bearer '.config('crypto-wallet.api_key'),
            ])->baseUrl("{$apiUrl}");
        });

        Http::macro('bitgoExpressApi', function () {
            return Http::withHeaders([
                'Authorization' => 'Bearer '.config('crypto-wallet.api_key'),
            ])->baseUrl(config('crypto-wallet.express_api_url'));
        });
    }
}
