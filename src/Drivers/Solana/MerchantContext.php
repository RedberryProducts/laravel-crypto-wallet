<?php

namespace RedberryProducts\CryptoWallet\Drivers\Solana;

use RedberryProducts\CryptoWallet\Contracts\CreatesPaymentRequests;
use RedberryProducts\CryptoWallet\Contracts\ReadsBalances;
use RedberryProducts\CryptoWallet\Contracts\ReadsTransfers;
use RedberryProducts\CryptoWallet\Data\Balance;
use RedberryProducts\CryptoWallet\Data\CreatePaymentRequest;
use RedberryProducts\CryptoWallet\Data\PaymentRequest;
use RedberryProducts\CryptoWallet\Data\Transfer;
use RedberryProducts\CryptoWallet\Data\TransferPage;
use RedberryProducts\CryptoWallet\Data\TransferQuery;
use RedberryProducts\CryptoWallet\Drivers\Solana\Contracts\SolanaRpcClientContract;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AmountConverter;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\AssetRegistry;
use RedberryProducts\CryptoWallet\Drivers\Solana\Support\PaymentReferenceGenerator;
use RedberryProducts\CryptoWallet\Enums\AssetType;
use RedberryProducts\CryptoWallet\Exceptions\MalformedSolanaResponseException;

class MerchantContext implements CreatesPaymentRequests, ReadsBalances, ReadsTransfers
{
    public function __construct(
        private readonly string $merchantAddress,
        private readonly string $networkName,
        private readonly SolanaRpcClientContract $rpc,
        private readonly AssetRegistry $assetRegistry,
        private readonly AmountConverter $amountConverter,
        private readonly PaymentReferenceGenerator $referenceGenerator,
        private readonly SolanaPayUrlBuilder $urlBuilder,
        private readonly TransferParser $transferParser,
    ) {}

    public function address(): string
    {
        return $this->merchantAddress;
    }

    public function network(): string
    {
        return $this->networkName;
    }

    public function getBalance(string $asset): Balance
    {
        $resolved = $this->assetRegistry->get($asset);

        if ($resolved->type === AssetType::Native) {
            $baseUnits = $this->rpc->getBalance($this->merchantAddress);
        } else {
            $baseUnits = '0';

            foreach ($this->rpc->getTokenAccountsByOwner($this->merchantAddress, (string) $resolved->mintAddress) as $account) {
                $tokenAmount = $account['account']['data']['parsed']['info']['tokenAmount'] ?? null;
                $amount = is_array($tokenAmount) ? ($tokenAmount['amount'] ?? null) : null;
                $decimals = is_array($tokenAmount) ? ($tokenAmount['decimals'] ?? null) : null;

                if (! is_string($amount) || ! ctype_digit($amount) || $decimals !== $resolved->decimals) {
                    throw new MalformedSolanaResponseException('Solana token account contains an invalid token amount.');
                }

                $baseUnits = $this->addIntegerStrings($baseUnits, $amount);
            }
        }

        return new Balance(
            $resolved->symbol,
            $this->amountConverter->toDecimal($baseUnits, $resolved->decimals),
            $baseUnits,
            $resolved->decimals,
            $resolved->mintAddress,
        );
    }

    public function getBalances(): array
    {
        $balances = [];

        foreach ($this->assetRegistry->all() as $asset) {
            $balances[$asset->symbol] = $this->getBalance($asset->symbol);
        }

        return $balances;
    }

    public function createPaymentRequest(CreatePaymentRequest $request): PaymentRequest
    {
        if ($request->merchantReference === '') {
            throw new \InvalidArgumentException('Merchant reference cannot be empty.');
        }

        $asset = $this->assetRegistry->get($request->asset);
        $baseUnits = $this->amountConverter->toBaseUnits($request->amount, $asset->decimals);
        $reference = $this->referenceGenerator->generate();
        $draft = new PaymentRequest(
            merchantReference: $request->merchantReference,
            recipientAddress: $this->merchantAddress,
            referenceAddress: $reference,
            asset: $asset->symbol,
            mintAddress: $asset->mintAddress,
            amount: $request->amount,
            baseUnits: $baseUnits,
            network: $this->networkName,
            url: '',
            expiresAt: $request->expiresAt,
            label: $request->label,
            message: $request->message,
            memo: $request->memo,
        );

        return new PaymentRequest(
            merchantReference: $draft->merchantReference,
            recipientAddress: $draft->recipientAddress,
            referenceAddress: $draft->referenceAddress,
            asset: $draft->asset,
            mintAddress: $draft->mintAddress,
            amount: $draft->amount,
            baseUnits: $draft->baseUnits,
            network: $draft->network,
            url: $this->urlBuilder->build($draft),
            expiresAt: $draft->expiresAt,
            label: $draft->label,
            message: $draft->message,
            memo: $draft->memo,
        );
    }

    public function getTransfers(?TransferQuery $query = null): array
    {
        return $this->getTransferPage($query)->transfers;
    }

    /**
     * Loads one raw Solana signature page and normalizes every known transfer it contains.
     *
     * TransferQuery::limit controls the raw signature page size, so the normalized transfer
     * count may be smaller or larger when transactions are filtered or contain multiple transfers.
     */
    public function getTransferPage(?TransferQuery $query = null): TransferPage
    {
        $query ??= new TransferQuery;
        $limit = max(1, min(100, $query->limit));
        $results = [];
        $signaturePage = $this->rpc->getSignaturesForAddress($this->merchantAddress, $limit, $query->before);
        $lastValidSignature = null;

        foreach ($signaturePage as $signatureInfo) {
            if (! is_array($signatureInfo)) {
                continue;
            }

            $signature = $signatureInfo['signature'] ?? null;

            if (! is_string($signature)) {
                continue;
            }

            $lastValidSignature = $signature;
            $transaction = $this->rpc->getTransaction($signature);

            if ($transaction !== null) {
                array_push($results, ...$this->transferParser->parse(
                    $transaction,
                    $this->merchantAddress,
                    is_string($signatureInfo['confirmationStatus'] ?? null) ? $signatureInfo['confirmationStatus'] : null,
                ));
            }
        }

        return new TransferPage(
            array_values($results),
            count($signaturePage) === $limit ? $lastValidSignature : null,
        );
    }

    public function getTransfer(string $signature): ?Transfer
    {
        return $this->getTransfersForSignature($signature)[0] ?? null;
    }

    /** @return list<Transfer> */
    public function getTransfersForSignature(string $signature): array
    {
        $transaction = $this->rpc->getTransaction($signature);

        if ($transaction === null) {
            return [];
        }

        return array_values($this->transferParser->parse($transaction, $this->merchantAddress));
    }

    private function addIntegerStrings(string $left, string $right): string
    {
        $leftIndex = strlen($left) - 1;
        $rightIndex = strlen($right) - 1;
        $carry = 0;
        $result = '';

        while ($leftIndex >= 0 || $rightIndex >= 0 || $carry > 0) {
            $sum = ($leftIndex >= 0 ? (int) $left[$leftIndex--] : 0)
                + ($rightIndex >= 0 ? (int) $right[$rightIndex--] : 0)
                + $carry;
            $result = (string) ($sum % 10).$result;
            $carry = intdiv($sum, 10);
        }

        return ltrim($result, '0') ?: '0';
    }
}
