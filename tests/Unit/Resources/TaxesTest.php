<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('creates a tax', function (): void {
    Http::fake([
        '*invoicexpress.com/taxes.json*' => Http::response([
            'tax' => ['id' => 1, 'name' => 'IVA23'],
        ], 201),
    ]);

    $result = InvoiceExpress::taxes()->create(new Tax(name: 'IVA23', value: 23.0, region: 'PT'));

    expect($result['id'])->toBe(1);
});

it('lists taxes', function (): void {
    Http::fake([
        '*invoicexpress.com/taxes.json*' => Http::response(['taxes' => []]),
    ]);

    InvoiceExpress::taxes()->all();

    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/taxes.json'));
});

it('deletes a tax', function (): void {
    Http::fake([
        '*invoicexpress.com/taxes/1.json*' => Http::response([], 204),
    ]);

    InvoiceExpress::taxes()->delete(1);

    Http::assertSent(static fn ($request): bool => $request->method() === 'DELETE'
        && str_contains($request->url(), '/taxes/1.json'));
});
