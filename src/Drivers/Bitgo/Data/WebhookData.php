<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Spatie\LaravelData\Data;

class WebhookData extends Data
{
    public string $id;

    public string $created;

    public string $scope;

    public string $walletId;

    public string $coin;

    public string $type;

    public string $url;

    public int $version;

    public int $numConfirmations;

    public string $state;

    public int $successiveFailedAttempts;

    public bool $listenToFailureStates;

    public array $txRequestStates = [];

    public array $txRequestTransactionStates = [];
}
