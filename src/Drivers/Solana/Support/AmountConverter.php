<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana\Support;

use RedberryProducts\CryptoWallet\Exceptions\InvalidAmountException;

class AmountConverter
{
    public function toBaseUnits(string $amount, int $decimals): string
    {
        $this->assertDecimals($decimals);

        if (! preg_match('/^(?:0|[0-9]+)(?:\.([0-9]+))?$/', $amount, $matches)) {
            throw new InvalidAmountException('Amount must be an unsigned decimal string.');
        }

        $fraction = $matches[1] ?? '';

        if (strlen($fraction) > $decimals) {
            throw new InvalidAmountException("Amount has more than {$decimals} decimal places.");
        }

        $whole = strstr($amount, '.', true);
        $whole = $whole === false ? $amount : $whole;
        $baseUnits = $whole.str_pad($fraction, $decimals, '0');

        return ltrim($baseUnits, '0') ?: '0';
    }

    public function toDecimal(string $baseUnits, int $decimals): string
    {
        $this->assertDecimals($decimals);

        if (! preg_match('/^[0-9]+$/', $baseUnits)) {
            throw new InvalidAmountException('Base units must be an unsigned integer string.');
        }

        $baseUnits = ltrim($baseUnits, '0') ?: '0';

        if ($decimals === 0) {
            return $baseUnits;
        }

        $padded = str_pad($baseUnits, $decimals + 1, '0', STR_PAD_LEFT);
        $whole = substr($padded, 0, -$decimals);
        $fraction = rtrim(substr($padded, -$decimals), '0');

        return $fraction === '' ? $whole : $whole.'.'.$fraction;
    }

    private function assertDecimals(int $decimals): void
    {
        if ($decimals < 0) {
            throw new InvalidAmountException('Decimals cannot be negative.');
        }
    }
}
