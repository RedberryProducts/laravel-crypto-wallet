<?php

namespace RedberryProducts\CryptoWallet\Data\Responses;

use RedberryProducts\CryptoWallet\Data\Data;

class PendingApproval extends Data
{
    /**
     * Time when the wallet becomes frozen
     *
     * @var string (Id) ^[0-9a-f]{32}$
     */
    public string $id;

    /**
     * A cryptocurrency or token ticker symbol.
     */
    public string $coin;

    /**
     * @var string (Id) ^[0-9a-f]{32}$
     */
    public string $wallet;

    /**
     * @var string (Id) ^[0-9a-f]{32}$
     */
    public string $enterprise;

    /**
     * @var string (Id) ^[0-9a-f]{32}$
     */
    public string $creator;

    /**
     * @var string date-time
     */
    public string $createDate;

    public array $info;

    public array $state;

    /**
     * What kind of entity the Pending Approval is tied to
     */
    public string $scope;

    /**
     * All the Users who should see this Pending Approval
     */
    public string $userIds;

    public int $approvalsRequired;

    public string $walletLabel;
}
