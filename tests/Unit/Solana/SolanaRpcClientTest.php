<?php

use Illuminate\Support\Facades\Http;
use RedberryProducts\CryptoWallet\Drivers\Solana\SolanaRpcClient;
use RedberryProducts\CryptoWallet\Exceptions\MalformedSolanaResponseException;
use RedberryProducts\CryptoWallet\Exceptions\SolanaRpcException;
use RedberryProducts\CryptoWallet\Exceptions\SolanaRpcRateLimitException;

function solanaRpcClient(): SolanaRpcClient
{
    return new SolanaRpcClient(
        Http::getFacadeRoot(),
        'https://secret-token@example-rpc.test/path',
        'confirmed',
        10,
        0,
    );
}

it('returns a valid json rpc result', function () {
    Http::fake(fn ($request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'result' => ['value' => 123],
    ]));

    expect(solanaRpcClient()->call('getBalance', ['address']))->toBe(['value' => 123]);

    Http::assertSent(fn ($request) => $request['jsonrpc'] === '2.0'
        && $request['method'] === 'getBalance'
        && $request['params'] === ['address']);
});

it('throws a distinct rate limit exception', function () {
    Http::fake(['*' => Http::response([], 429, ['Retry-After' => '2'])]);

    solanaRpcClient()->call('getBalance');
})->throws(SolanaRpcRateLimitException::class);

it('throws for non successful http responses without exposing the rpc url', function () {
    Http::fake(['*' => Http::response('gateway failure', 503)]);

    try {
        solanaRpcClient()->call('getBalance');
    } catch (SolanaRpcException $exception) {
        expect($exception->getMessage())->not->toContain('secret-token')
            ->and($exception->getMessage())->toContain('503');

        return;
    }

    $this->fail('Expected an RPC exception.');
});

it('throws for json rpc errors', function () {
    Http::fake(fn ($request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'error' => ['code' => -32602, 'message' => 'Invalid params'],
    ]));

    solanaRpcClient()->call('getBalance');
})->throws(SolanaRpcException::class, 'Solana RPC error [-32602]: Invalid params');

it('rejects malformed or mismatched response envelopes', function (array $response) {
    Http::fake(['*' => Http::response($response)]);

    solanaRpcClient()->call('getBalance');
})->with([
    'missing result' => [['jsonrpc' => '2.0', 'id' => 1]],
    'mismatched id' => [['jsonrpc' => '2.0', 'id' => 999, 'result' => []]],
])->throws(MalformedSolanaResponseException::class);

it('provides typed rpc helpers with commitment', function () {
    Http::fake(fn ($request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'result' => ['context' => ['slot' => 1], 'value' => 5000],
    ]));

    expect(solanaRpcClient()->getBalance('merchant'))->toBe('5000');

    Http::assertSent(fn ($request) => $request['params'] === [
        'merchant',
        ['commitment' => 'confirmed'],
    ]);
});

it('rejects malformed signature page records', function (array $result) {
    Http::fake(fn ($request) => Http::response([
        'jsonrpc' => '2.0',
        'id' => $request['id'],
        'result' => $result,
    ]));

    solanaRpcClient()->getSignaturesForAddress('merchant', 2);
})->with([
    'non-array record' => [['not-an-array']],
    'missing signature' => [[['confirmationStatus' => 'confirmed']]],
    'empty signature' => [[['signature' => '']]],
    'non-string signature' => [[['signature' => 123]]],
    'non-string confirmation status' => [[['signature' => 'signature-1', 'confirmationStatus' => 123]]],
    'page longer than requested limit' => [[
        ['signature' => 'signature-1'],
        ['signature' => 'signature-2'],
        ['signature' => 'signature-3'],
    ]],
])->throws(MalformedSolanaResponseException::class);
