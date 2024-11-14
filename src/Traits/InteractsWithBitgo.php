<?php

namespace Khomeriki\BitgoWallet\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait InteractsWithBitgo
{
    protected static function httpGet(string $endpoint, ?array $data = []): Response
    {
        /** @phpstan-ignore-next-line */
        return Http::bitgoApi()->get($endpoint, $data);
    }

    protected static function httPost(string $endpoint, array $data): Response
    {
        /** @phpstan-ignore-next-line */
        return Http::bitgoApi()->get($endpoint, $data);
    }

    protected static function httpPostExpress(string $endpoint, array $data): Response
    {
        /** @phpstan-ignore-next-line */
        return Http::bitgoExpressApi()->post($endpoint, $data);
    }

    protected static function httpGetExpress(string $endpoint): Response
    {
        /** @phpstan-ignore-next-line */
        return Http::bitgoExpressApi()->get($endpoint);
    }
}
