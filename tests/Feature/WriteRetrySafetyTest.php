<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ServerException;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function retryClient(bool $retryWrites = false): InvoiceExpressClient
{
    return new InvoiceExpressClient(
        accountName: 'co',
        apiKey: 'k',
        timeout: 5,
        retryTimes: 3,
        retryBackoffMs: 1,
        retryWrites: $retryWrites,
    );
}

it('does not retry a POST that returns 5xx, to avoid duplicating a fiscal document', function (): void {
    Http::fake(['*invoicexpress.com/invoices.json*' => Http::response('boom', 503)]);

    expect(fn () => retryClient()->request('POST', 'invoices.json', ['invoice' => []]))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('does not retry a PUT that returns 5xx by default', function (): void {
    Http::fake(['*' => Http::response('boom', 502)]);

    expect(fn () => retryClient()->request('PUT', 'invoices/1/change-state.json'))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('does not retry a POST on connection failure by default', function (): void {
    $attempts = 0;
    Http::fake(['*' => function () use (&$attempts): never {
        $attempts++;

        throw new ConnectionException('cURL error 28: timeout');
    }]);

    expect(fn () => retryClient()->request('POST', 'invoices.json'))->toThrow(Exception::class);

    expect($attempts)->toBe(1);
});

it('still retries GET on connection failure', function (): void {
    $attempts = 0;
    Http::fake(['*' => function () use (&$attempts): mixed {
        $attempts++;

        return $attempts < 3 ? throw new ConnectionException('timeout') : Http::response(['ok' => true]);
    }]);

    expect(retryClient()->request('GET', 'clients.json'))->toBe(['ok' => true]);
    expect($attempts)->toBe(3);
});

it('still retries GET on 5xx', function (): void {
    Http::fake(['*' => Http::sequence()->push('boom', 500)->push(['ok' => true], 200)]);

    expect(retryClient()->request('GET', 'clients.json'))->toBe(['ok' => true]);

    Http::assertSentCount(2);
});

it('retries a POST on 429 because the request was rejected, not executed', function (): void {
    Http::fake(['*' => Http::sequence()->push('slow down', 429)->push(['ok' => true], 200)]);

    expect(retryClient()->request('POST', 'invoices.json'))->toBe(['ok' => true]);

    Http::assertSentCount(2);
});

it('restores the legacy write retry when retry.writes is enabled', function (): void {
    Http::fake(['*' => Http::sequence()->push('boom', 503)->push(['ok' => true], 200)]);

    expect(retryClient(retryWrites: true)->request('POST', 'invoices.json'))->toBe(['ok' => true]);

    Http::assertSentCount(2);
});

it('reads retry.writes from config in the service provider', function (): void {
    config()->set('invoiceexpress.retry.times', 3);
    config()->set('invoiceexpress.retry.backoff_ms', 1);
    config()->set('invoiceexpress.retry.writes', true);
    app()->forgetInstance(InvoiceExpressClient::class);

    Http::fake(['*' => Http::sequence()->push('boom', 503)->push(['ok' => true], 200)]);

    expect(app(InvoiceExpressClient::class)->request('POST', 'invoices.json'))->toBe(['ok' => true]);
});

it('keeps retry.writes disabled by default', function (): void {
    expect(config('invoiceexpress.retry.writes'))->toBeFalse();
});

it('keeps the request idempotency semantics when switching account', function (): void {
    Http::fake(['*' => Http::response('boom', 503)]);

    $client = retryClient()->useAccount('other', 'k2');

    expect(fn () => $client->request('POST', 'invoices.json'))->toThrow(ServerException::class);

    Http::assertSent(static fn (Request $r): bool => str_contains($r->url(), 'other.app'));
    Http::assertSentCount(1);
});
