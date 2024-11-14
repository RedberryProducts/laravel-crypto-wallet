<?php

namespace Khomeriki\BitgoWallet\Data;

final class TransferData extends Data
{
    public string $walletPassphrase;

    /**
     * @var array<TransferRecipientData>
     */
    public array $recipients;

    public int $feeRate;
}
