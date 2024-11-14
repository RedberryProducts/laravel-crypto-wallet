<?php

namespace Khomeriki\BitgoWallet\Data;

final class TransferRecipientData extends Data
{
    public int $amount;

    public string $address;
}
