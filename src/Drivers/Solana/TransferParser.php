<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use RedberryProducts\CryptoWallet\Data\Transfer;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Enums\TransferDirection;

class TransferParser
{
    public function __construct(
        private readonly AssetRegistry $assets,
        private readonly AmountConverter $amountConverter,
    ) {}

    /**
     * @param  array<string, mixed>  $transaction
     * @return array<int, Transfer>
     */
    public function parse(array $transaction, string $walletAddress, ?string $confirmationStatus = null): array
    {
        if (($transaction['meta']['err'] ?? null) !== null) {
            return [];
        }

        $signature = $transaction['transaction']['signatures'][0] ?? null;

        if (! is_string($signature)) {
            return [];
        }

        $result = [];

        foreach ($this->instructions($transaction) as $instruction) {
            $program = $instruction['program'] ?? null;
            $parsed = $instruction['parsed'] ?? null;
            $info = is_array($parsed) ? ($parsed['info'] ?? null) : null;

            if (! is_array($info)) {
                continue;
            }

            if ($program === 'system' && ($parsed['type'] ?? null) === 'transfer') {
                $source = $info['source'] ?? null;
                $destination = $info['destination'] ?? null;
                $baseUnits = $info['lamports'] ?? null;

                if ((! is_string($source) && ! is_string($destination)) || (! is_int($baseUnits) && ! is_string($baseUnits))) {
                    continue;
                }

                $direction = $this->direction($walletAddress, is_string($source) ? $source : null, is_string($destination) ? $destination : null);

                if ($direction === TransferDirection::Unknown) {
                    continue;
                }

                $baseUnits = (string) $baseUnits;
                $result[] = new Transfer(
                    $signature,
                    $direction,
                    'SOL',
                    $this->amountConverter->toDecimal($baseUnits, 9),
                    $baseUnits,
                    null,
                    is_string($source) ? $source : null,
                    is_string($destination) ? $destination : null,
                    is_int($transaction['slot'] ?? null) ? $transaction['slot'] : null,
                    is_int($transaction['blockTime'] ?? null) ? $transaction['blockTime'] : null,
                    $confirmationStatus,
                );

                continue;
            }

            if ($program !== 'spl-token' || ! in_array($parsed['type'] ?? null, ['transfer', 'transferChecked'], true)) {
                continue;
            }

            $mint = $info['mint'] ?? null;
            $asset = is_string($mint) ? $this->assets->findByMint($mint) : null;

            if ($asset === null) {
                continue;
            }

            $source = is_string($info['source'] ?? null) ? $info['source'] : null;
            $destination = is_string($info['destination'] ?? null) ? $info['destination'] : null;
            $sourceOwner = $source === null ? null : $this->tokenOwner($transaction, $source, $mint);
            $destinationOwner = $destination === null ? null : $this->tokenOwner($transaction, $destination, $mint);
            $direction = $this->direction($walletAddress, $sourceOwner, $destinationOwner);
            $tokenAmount = $info['tokenAmount'] ?? null;
            $baseUnits = is_array($tokenAmount) ? ($tokenAmount['amount'] ?? null) : ($info['amount'] ?? null);

            if ($direction === TransferDirection::Unknown || (! is_int($baseUnits) && ! is_string($baseUnits))) {
                continue;
            }

            $baseUnits = (string) $baseUnits;
            $result[] = new Transfer(
                $signature,
                $direction,
                $asset->symbol,
                $this->amountConverter->toDecimal($baseUnits, $asset->decimals),
                $baseUnits,
                $asset->mintAddress,
                $sourceOwner,
                $destinationOwner,
                is_int($transaction['slot'] ?? null) ? $transaction['slot'] : null,
                is_int($transaction['blockTime'] ?? null) ? $transaction['blockTime'] : null,
                $confirmationStatus,
            );
        }

        return $result;
    }

    private function direction(string $wallet, ?string $source, ?string $destination): TransferDirection
    {
        if ($source === $wallet && $destination === $wallet) {
            return TransferDirection::Self;
        }

        if ($destination === $wallet) {
            return TransferDirection::Incoming;
        }

        if ($source === $wallet) {
            return TransferDirection::Outgoing;
        }

        return TransferDirection::Unknown;
    }

    /** @param array<string, mixed> $transaction
     * @return array<int, array<string, mixed>>
     */
    private function instructions(array $transaction): array
    {
        $instructions = $transaction['transaction']['message']['instructions'] ?? [];
        $result = is_array($instructions) ? $instructions : [];

        foreach (($transaction['meta']['innerInstructions'] ?? []) as $group) {
            foreach (is_array($group) ? ($group['instructions'] ?? []) : [] as $instruction) {
                if (is_array($instruction)) {
                    $result[] = $instruction;
                }
            }
        }

        return array_values(array_filter($result, 'is_array'));
    }

    /** @param array<string, mixed> $transaction */
    private function tokenOwner(array $transaction, string $account, string $mint): ?string
    {
        $keys = $transaction['transaction']['message']['accountKeys'] ?? [];
        $normalized = array_map(fn (mixed $key): mixed => is_array($key) ? ($key['pubkey'] ?? null) : $key, is_array($keys) ? $keys : []);
        $index = array_search($account, $normalized, true);

        if ($index === false) {
            return null;
        }

        $balances = array_merge(
            is_array($transaction['meta']['preTokenBalances'] ?? null) ? $transaction['meta']['preTokenBalances'] : [],
            is_array($transaction['meta']['postTokenBalances'] ?? null) ? $transaction['meta']['postTokenBalances'] : [],
        );

        foreach ($balances as $balance) {
            if (is_array($balance) && ($balance['accountIndex'] ?? null) === $index && ($balance['mint'] ?? null) === $mint) {
                return is_string($balance['owner'] ?? null) ? $balance['owner'] : null;
            }
        }

        return null;
    }
}
