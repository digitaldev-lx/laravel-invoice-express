<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Account;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('lists accounts', function (): void {
    Http::fake([
        '*invoicexpress.com/accounts.json*' => Http::response(['accounts' => []]),
    ]);

    InvoiceExpress::accounts()->all();

    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/accounts.json'));
});

it('creates an account', function (): void {
    Http::fake([
        '*invoicexpress.com/accounts.json*' => Http::response([
            'account' => ['id' => 5, 'name' => 'Caixa'],
        ], 201),
    ]);

    $result = InvoiceExpress::accounts()->create(
        new Account(name: 'Caixa', accountType: 'cash'),
    );

    expect($result['id'])->toBe(5);
});

it('deletes an account', function (): void {
    Http::fake([
        '*invoicexpress.com/accounts/3.json*' => Http::response([], 204),
    ]);

    InvoiceExpress::accounts()->delete(3);

    Http::assertSent(static fn ($request): bool => $request->method() === 'DELETE');
});
