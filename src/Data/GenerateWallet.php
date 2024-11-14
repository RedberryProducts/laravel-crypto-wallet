<?php

namespace Khomeriki\BitgoWallet\Data;

class GenerateWallet extends Data
{
    /**
     * WalletLabel
     */
    public string $label;

    /***
     * Passphrase to be used to encrypt the user key on the wallet
     * @var string
     */
    public string $passphrase;

    /**
     * User provided public key
     */
    public string $userKey;

    /**
     * public part of a key pair
     */
    public string $backupXpub;

    /**
     * Optional key recovery service to provide and store the backup key
     */
    public string $backupXpubProvider;

    /**
     *  (Id) ^[0-9a-f]{32}$
     */
    public string $enterprise;

    /**
     * Flag for disabling wallet transaction notifications
     */
    public bool $disableTransactionNotifications;

    /**
     * The passphrase used for decrypting the encrypted user private key during wallet recovery
     */
    public string $passcodeEncryptionCode;

    /**
     * Seed used to derive an extended user key for a cold wallet
     */
    public string $coldDerivationSeed;

    /**
     * Gas price to use when deploying an Ethereum wallet
     */
    public int $gasPrice;

    /**
     * Flag for preventing KRS from sending email after creating backup key
     */
    public bool $disableKRSEmail;

    /**
     * (ETH only) Specify the wallet creation contract version used when creating a wallet contract.
     * Use 0 for the old wallet creation,
     * 1 for the new wallet creation, where it is only deployed upon receiving funds.
     */
    public int $walletVersion = 1;
}
