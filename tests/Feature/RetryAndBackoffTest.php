<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use Illuminate\Support\Facades\Http;

it('retries 5xx responses up to retryTimes and then succeeds', function (): void {
    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::sequence()
            ->push('boom', 503)
            ->push('boom', 502)
            ->push(['clients' => []], 200),
    ]);

    $client = new InvoiceExpressClient(
        accountName: 'co',
        apiKey: 'k',
        timeout: 5,
        retryTimes: 3,
        retryBackoffMs: 1,
    );

    $result = $client->request('GET', 'clients.json');

    expect($result)->toBe(['clients' => []]);
});

it('retries 429 responses transparently', function (): void {
    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::sequence()
            ->push('rate limited', 429, ['Retry-After' => '1'])
            ->push(['clients' => []], 200),
    ]);

    $client = new InvoiceExpressClient(
        accountName: 'co',
        apiKey: 'k',
        retryTimes: 2,
        retryBackoffMs: 1,
    );

    $result = $client->request('GET', 'clients.json');

    expect($result)->toBe(['clients' => []]);
});
