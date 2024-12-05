<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use RedberryProducts\CryptoWallet\Data\Address;
use RedberryProducts\CryptoWallet\Data\Transfer;

interface WalletContract
{
    public function init(string $coin, ?string $id = null): self;

    public function generate(string $label, string $passphrase, string $enterpriseId, array $options = []): self;

    public function get(): self;

    public function addWebhook(int $numConfirmations = 0);

    public function generateAddress(?string $label = null): Address;

    public function getTransfer(string $transferId): Transfer;

    public function listAll(?string $coin = null, ?array $params = []): array;

    public function sendTransferToMany(array $transfer): ?array;

    public function getMaximumSpendable(?array $params = []): ?array;

    public function getTransfers(): ?array;
}
