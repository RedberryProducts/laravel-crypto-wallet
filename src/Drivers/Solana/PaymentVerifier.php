<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use RedberryProducts\CryptoWallet\Data\ExpectedPayment;
use RedberryProducts\CryptoWallet\Data\PaymentVerificationResult;
use RedberryProducts\CryptoWallet\Drivers\Solana\Contracts\SolanaRpcClientContract;
use RedberryProducts\CryptoWallet\Enums\PaymentStatus;
use RedberryProducts\CryptoWallet\Exceptions\NetworkMismatchException;

class PaymentVerifier
{
    public function __construct(
        private readonly string $network,
        private readonly SolanaRpcClientContract $rpc,
    ) {}

    public function verify(ExpectedPayment $payment): PaymentVerificationResult
    {
        if ($payment->network !== $this->network) {
            throw new NetworkMismatchException("Expected payment network [{$payment->network}] does not match driver network [{$this->network}].");
        }

        $signatures = $this->rpc->getSignaturesForAddress($payment->referenceAddress, 25);
        $fallback = null;

        foreach ($signatures as $candidate) {
            $signature = $candidate['signature'] ?? null;

            if (! is_string($signature) || $signature === '') {
                continue;
            }

            $transaction = $this->rpc->getTransaction($signature);

            if ($transaction === null) {
                continue;
            }

            $result = $this->verifyTransaction($payment, $signature, $transaction);

            if ($result->status === PaymentStatus::Confirmed) {
                return $result;
            }

            $fallback ??= $result;
        }

        return $fallback ?? new PaymentVerificationResult(PaymentStatus::Pending);
    }

    /** @param array<string, mixed> $transaction */
    private function verifyTransaction(ExpectedPayment $payment, string $signature, array $transaction): PaymentVerificationResult
    {
        $keys = $this->accountKeys($transaction);

        if (! in_array($payment->referenceAddress, $keys, true)) {
            return new PaymentVerificationResult(PaymentStatus::Mismatch, $signature, reason: 'Payment reference is missing.');
        }

        if (($transaction['meta']['err'] ?? null) !== null) {
            return new PaymentVerificationResult(PaymentStatus::Failed, $signature, reason: 'Transaction execution failed.');
        }

        return $payment->mintAddress === null
            ? $this->verifyNativeTransfer($payment, $signature, $transaction)
            : $this->verifyTokenTransfer($payment, $signature, $transaction, $keys);
    }

    /** @param array<string, mixed> $transaction */
    private function verifyNativeTransfer(ExpectedPayment $payment, string $signature, array $transaction): PaymentVerificationResult
    {
        foreach ($this->instructions($transaction) as $instruction) {
            $parsed = $instruction['parsed'] ?? null;

            if (($instruction['program'] ?? null) !== 'system' || ! is_array($parsed) || ($parsed['type'] ?? null) !== 'transfer') {
                continue;
            }

            $info = $parsed['info'] ?? [];
            $recipient = is_array($info) ? ($info['destination'] ?? null) : null;
            $baseUnits = is_array($info) ? ($info['lamports'] ?? null) : null;
            $baseUnits = is_int($baseUnits) || is_string($baseUnits) ? (string) $baseUnits : null;
            $matches = $recipient === $payment->recipientAddress && $baseUnits === $payment->baseUnits;

            return new PaymentVerificationResult(
                $matches ? PaymentStatus::Confirmed : PaymentStatus::Mismatch,
                $signature,
                is_string($recipient) ? $recipient : null,
                null,
                $baseUnits,
                $matches ? null : 'Native transfer recipient or amount does not match.',
            );
        }

        return new PaymentVerificationResult(PaymentStatus::Mismatch, $signature, reason: 'No native transfer was found.');
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<int, string>  $keys
     */
    private function verifyTokenTransfer(ExpectedPayment $payment, string $signature, array $transaction, array $keys): PaymentVerificationResult
    {
        foreach ($this->instructions($transaction) as $instruction) {
            $parsed = $instruction['parsed'] ?? null;
            $type = is_array($parsed) ? ($parsed['type'] ?? null) : null;

            if (($instruction['program'] ?? null) !== 'spl-token' || ! in_array($type, ['transfer', 'transferChecked'], true)) {
                continue;
            }

            $info = $parsed['info'] ?? [];
            $mint = is_array($info) ? ($info['mint'] ?? null) : null;
            $destination = is_array($info) ? ($info['destination'] ?? null) : null;
            $tokenAmount = is_array($info) ? ($info['tokenAmount'] ?? null) : null;
            $baseUnits = is_array($tokenAmount) ? ($tokenAmount['amount'] ?? null) : ($info['amount'] ?? null);
            $baseUnits = is_int($baseUnits) || is_string($baseUnits) ? (string) $baseUnits : null;
            $owner = is_string($destination) ? $this->tokenAccountOwner($transaction, $keys, $destination, is_string($mint) ? $mint : null) : null;
            $matches = $mint === $payment->mintAddress
                && $owner === $payment->recipientAddress
                && $baseUnits === $payment->baseUnits;

            return new PaymentVerificationResult(
                $matches ? PaymentStatus::Confirmed : PaymentStatus::Mismatch,
                $signature,
                $owner,
                is_string($mint) ? $mint : null,
                $baseUnits,
                $matches ? null : 'Token transfer recipient, mint, or amount does not match.',
            );
        }

        return new PaymentVerificationResult(PaymentStatus::Mismatch, $signature, reason: 'No token transfer was found.');
    }

    /** @param array<string, mixed> $transaction
     * @return array<int, string>
     */
    private function accountKeys(array $transaction): array
    {
        $rawKeys = $transaction['transaction']['message']['accountKeys'] ?? [];
        $keys = [];

        foreach (is_array($rawKeys) ? $rawKeys : [] as $key) {
            if (is_string($key)) {
                $keys[] = $key;
            } elseif (is_array($key) && is_string($key['pubkey'] ?? null)) {
                $keys[] = $key['pubkey'];
            }
        }

        return $keys;
    }

    /** @param array<string, mixed> $transaction
     * @return array<int, array<string, mixed>>
     */
    private function instructions(array $transaction): array
    {
        $instructions = $transaction['transaction']['message']['instructions'] ?? [];
        $result = is_array($instructions) ? $instructions : [];
        $innerGroups = $transaction['meta']['innerInstructions'] ?? [];

        foreach (is_array($innerGroups) ? $innerGroups : [] as $group) {
            foreach (is_array($group) ? ($group['instructions'] ?? []) : [] as $instruction) {
                if (is_array($instruction)) {
                    $result[] = $instruction;
                }
            }
        }

        return array_values(array_filter($result, 'is_array'));
    }

    /** @param array<string, mixed> $transaction
     * @param  array<int, string>  $keys
     */
    private function tokenAccountOwner(array $transaction, array $keys, string $destination, ?string $mint): ?string
    {
        $index = array_search($destination, $keys, true);

        if ($index === false) {
            return null;
        }

        $balances = $transaction['meta']['postTokenBalances'] ?? [];

        foreach (is_array($balances) ? $balances : [] as $balance) {
            if (is_array($balance)
                && ($balance['accountIndex'] ?? null) === $index
                && ($balance['mint'] ?? null) === $mint
                && is_string($balance['owner'] ?? null)) {
                return $balance['owner'];
            }
        }

        return null;
    }
}
