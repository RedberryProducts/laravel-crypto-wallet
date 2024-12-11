<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data\SendTransferToMany;

use Spatie\LaravelData\Data;

class SendToManyRequest extends Data
{
    /**
     * @param  Recipient[]  $recipients
     * @param  array|null  $eip1559  ['maxPriorityFeePerGas' => int|string, 'maxFeePerGas' => int|string]
     * @param  array|null  $memo  ['type' => string, 'value' => string]
     * @param  array|null  $trustlines  [{'token' => string, 'action' => string, 'limit' => string}]
     * @param  array|null  $stakingOptions  ['amount' => int|string, 'validator' => string]
     * @param  array|null  $reservation  ['expireTime' => string]
     */
    public function __construct(
        public readonly array $recipients,
        public readonly ?string $otp = null,
        public readonly ?string $walletPassphrase = null,
        public readonly ?string $prv = null,
        public readonly ?string $type = null,
        public readonly ?int $numBlocks = null,
        public readonly int|string|null $feeRate = null,
        public readonly int|string|null $maxFeeRate = null,
        public readonly float|string|null $feeMultiplier = null,
        public readonly ?int $minConfirms = null,
        public readonly ?bool $enforceMinConfirmsForChange = null,
        public readonly int|string|null $gasPrice = null,
        public readonly ?array $eip1559 = null,
        public readonly int|string|null $gasLimit = null,
        public readonly ?int $targetWalletUnspents = null,
        public readonly int|string|null $minValue = null,
        public readonly int|string|null $maxValue = null,
        public readonly ?string $sequenceId = null,
        public readonly ?string $nonce = null,
        public readonly ?bool $noSplitChange = null,
        public readonly ?array $unspents = null,
        public readonly ?string $changeAddress = null,
        public readonly ?string $txFormat = null,
        public readonly ?bool $instant = null,
        public readonly ?array $memo = null,
        public readonly ?string $comment = null,
        public readonly ?string $destinationChain = null,
        public readonly ?string $sourceChain = null,
        public readonly string|array|null $changeAddressType = null,
        public readonly ?string $startTime = null,
        public readonly ?string $consolidateId = null,
        public readonly ?int $lastLedgerSequence = null,
        public readonly ?int $ledgerSequenceDelta = null,
        public readonly ?array $rbfTxIds = null,
        public readonly ?bool $isReplaceableByFee = null,
        public readonly ?int $validFromBlock = null,
        public readonly ?int $validToBlock = null,
        public readonly ?array $trustlines = null,
        public readonly ?array $stakingOptions = null,
        public readonly ?string $messageKey = null,
        public readonly ?array $reservation = null,
        public readonly ?string $data = null
    ) {}
}
