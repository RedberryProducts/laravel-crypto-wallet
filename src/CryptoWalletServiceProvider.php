<?php

namespace RedberryProducts\CryptoWallet;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use RedberryProducts\CryptoWallet\Data\Network;
use RedberryProducts\CryptoWallet\Drivers\Bitgo\BitgoDriver;
use RedberryProducts\CryptoWallet\Drivers\Solana\Contracts\SolanaRpcClientContract;
use RedberryProducts\CryptoWallet\Drivers\Solana\PaymentVerifier;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaDriver;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaPayUrlBuilder;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaRpcClient;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AddressCodec;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\OwnershipSignatureVerifier;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\PaymentReferenceGenerator;
use RedberryProducts\CryptoWallet\Drivers\Solana\TransferParser;
use RedberryProducts\CryptoWallet\Enums\Commitment;
use RedberryProducts\CryptoWallet\Enums\NetworkName;

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

        $this->app->bind(WalletRegistry::class, function (Application $app): WalletRegistry {
            return new WalletRegistry(
                $app,
                $app['config']->get('crypto-wallet.drivers', []),
                $app['config']->get('crypto-wallet.default', 'bitgo'),
            );
        });

        $this->app->singleton(BitgoDriver::class, function (Application $app): BitgoDriver {
            return new BitgoDriver((string) $app['config']->get('crypto-wallet.drivers.bitgo.default_coin', 'tbtc4'));
        });

        $this->app->singleton(SolanaRpcClientContract::class, function (Application $app): SolanaRpcClient {
            $config = $app['config']->get('crypto-wallet.drivers.solana', []);

            return new SolanaRpcClient(
                $app->make(Factory::class),
                (string) ($config['rpc_url'] ?? ''),
                (string) ($config['commitment'] ?? 'confirmed'),
                (int) ($config['timeout'] ?? 10),
                (int) ($config['retry_times'] ?? 2),
            );
        });

        $this->app->singleton(SolanaDriver::class, function (Application $app): SolanaDriver {
            $config = $app['config']->get('crypto-wallet.drivers.solana', []);
            $network = (string) ($config['network'] ?? 'devnet');
            $commitment = (string) ($config['commitment'] ?? 'confirmed');
            $addressCodec = $app->make(AddressCodec::class);
            $assetRegistry = new AssetRegistry($network, $config['assets'] ?? [], $addressCodec);
            $rpc = $app->make(SolanaRpcClientContract::class);

            return new SolanaDriver(
                new Network(NetworkName::from($network), Commitment::from($commitment)),
                $assetRegistry,
                $addressCodec,
                new OwnershipSignatureVerifier($addressCodec),
                $rpc,
                $app->make(AmountConverter::class),
                new PaymentReferenceGenerator($addressCodec),
                $app->make(SolanaPayUrlBuilder::class),
                new PaymentVerifier($network, $rpc),
                new TransferParser($assetRegistry, $app->make(AmountConverter::class)),
            );
        });
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
