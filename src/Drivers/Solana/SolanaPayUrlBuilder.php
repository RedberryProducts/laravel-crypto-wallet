<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use RedberryProducts\CryptoWallet\Data\PaymentRequest;

class SolanaPayUrlBuilder
{
    public function build(PaymentRequest $request): string
    {
        $query = ['amount' => $request->amount];

        if ($request->mintAddress !== null) {
            $query['spl-token'] = $request->mintAddress;
        }

        $query['reference'] = $request->referenceAddress;

        foreach (['label', 'message', 'memo'] as $field) {
            if ($request->{$field} !== null && $request->{$field} !== '') {
                $query[$field] = $request->{$field};
            }
        }

        return 'solana:'.$request->recipientAddress.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
