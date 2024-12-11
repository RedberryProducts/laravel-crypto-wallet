<?php

test('ping Bitgo rest api', function () {
    $response = $this->client->ping();
    $this->assertTrue($response->ok());
});

test('ping BitgoExpress rest api', function () {
    $response = $this->client->pingExpress();
    $this->assertTrue($response->ok());
});

it('can detect current bitgo user', function () {
    $response = $this->client->me();
    expect($response)
        ->toBeArray()
        ->toHaveKey('user');
});
