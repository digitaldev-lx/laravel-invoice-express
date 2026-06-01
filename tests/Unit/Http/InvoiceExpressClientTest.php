<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\InvoiceExpressException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\RateLimitException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ServerException;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use Illuminate\Support\Facades\Http;

it('builds the URL using the account subdomain and api_key query param', function (): void {
    Http::fake(['*' => Http::response(['ok' => true])]);

    $client = new InvoiceExpressClient(
        accountName: 'mycompany',
        apiKey: 'secret-key',
        retryTimes: 0,
    );

    $client->request('GET', 'clients.json');

    Http::assertSent(static fn ($request): bool => str_starts_with($request->url(), 'https://mycompany.app.invoicexpress.com/clients.json')
        && str_contains($request->url(), 'api_key=secret-key'));
});

it('replaces {id} placeholders in the endpoint with url-encoded values', function (): void {
    Http::fake(['*' => Http::response(['ok' => true])]);

    $client = new InvoiceExpressClient(accountName: 'co', apiKey: 'k', retryTimes: 0);
    $client->request('GET', 'clients/{id}/invoices.json', pathParameters: ['id' => 42]);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/clients/42/invoices.json'));
});

it('returns the raw body for binary endpoints', function (): void {
    Http::fake(['*' => Http::response('%PDF-binary-bytes', 200, [
        'Content-Type' => 'application/pdf',
    ])]);

    $client = new InvoiceExpressClient(accountName: 'co', apiKey: 'k', retryTimes: 0);

    $body = $client->request('GET', 'invoices/1/pdf', expectsBinary: true);

    expect($body)->toBe('%PDF-binary-bytes');
});

it('rejects unsupported HTTP methods', function (): void {
    $client = new InvoiceExpressClient(accountName: 'co', apiKey: 'k', retryTimes: 0);

    $client->request('PATCH', 'clients.json');
})->throws(InvoiceExpressException::class, 'Unsupported HTTP method');

it('returns a fresh client when switching accounts', function (): void {
    $base = new InvoiceExpressClient(accountName: 'first', apiKey: 'a');
    $other = $base->useAccount('second', 'b');

    expect($base)->not->toBe($other);
    expect($base->accountName())->toBe('first');
    expect($other->accountName())->toBe('second');
});

it('truncates large upstream error bodies embedded in exception messages', function (): void {
    Http::fake(['*' => Http::response(str_repeat('A', 5000), 500)]);

    $client = new InvoiceExpressClient(accountName: 'co', apiKey: 'k', retryTimes: 0);

    try {
        $client->request('GET', 'clients.json');
        $this->fail('Expected ServerException was not thrown.');
    } catch (ServerException $e) {
        expect(mb_strlen($e->getMessage()))->toBeLessThan(700);
        expect($e->getMessage())->toContain('...');
        expect(substr_count($e->getMessage(), 'A'))->toBeLessThanOrEqual(510);
    }
});

it('throws RateLimitException locally before reaching the threshold', function (): void {
    $cache = app('cache')->store('array');
    $cache->put('invoiceexpress:rate:co:'.((int) (time() / 60)), 950, 70);

    $client = new InvoiceExpressClient(
        accountName: 'co',
        apiKey: 'k',
        retryTimes: 0,
        rateLimitPerMinute: 1000,
        cache: $cache,
    );

    $client->request('GET', 'clients.json');
})->throws(RateLimitException::class, 'Local InvoiceXpress rate limit reached');
