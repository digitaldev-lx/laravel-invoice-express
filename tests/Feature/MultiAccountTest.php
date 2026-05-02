<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('routes requests to a different account when useAccount() is called', function (): void {
    Http::fake([
        '*' => Http::response(['clients' => []]),
    ]);

    InvoiceExpress::useAccount('outra-empresa', 'outra-key')
        ->clients()
        ->all();

    Http::assertSent(static fn ($request): bool => str_starts_with(
        $request->url(),
        'https://outra-empresa.app.invoicexpress.com/clients.json',
    ) && str_contains($request->url(), 'api_key=outra-key'));
});

it('does not contaminate the default singleton with the new credentials', function (): void {
    $manager = app(DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress::class);
    $other = $manager->useAccount('other', 'other-key');

    expect($manager->client()->accountName())->toBe('test-account');
    expect($other->client()->accountName())->toBe('other');
});

it('caches resources separately per account clone', function (): void {
    $manager = app(DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress::class);
    $other = $manager->useAccount('other', 'other-key');

    expect($manager->clients())->not->toBe($other->clients());
});
