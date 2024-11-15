<?php

namespace RedberryProducts\CryptoWallet\Contracts;

use Illuminate\Support\Collection;
use RedberryProducts\CryptoWallet\Data\Responses\Address;
use RedberryProducts\CryptoWallet\Data\Responses\Transfer;
use RedberryProducts\CryptoWallet\Data\Responses\Webhook;
use RedberryProducts\CryptoWallet\Data\TransferData;

interface WalletContract
{
    public function init(string $coin, ?string $id = null): self;

    public function generate(string $label, string $passphrase, string $enterpriseId, array $options = []): self;

    public function get(): self;

    public function addWebhook(int $numConfirmations = 0): Webhook;

    public function generateAddress(?string $label = null): Address;

    public function getTransfer(string $transferId): Transfer;

    public function listAll(?string $coin = null, ?array $params = []): Collection;

    public function sendTransfer(TransferData $transfer): ?array;

    public function getMaximumSpendable(?array $params = []): ?array;

    public function getTransfers(): ?array;
}
