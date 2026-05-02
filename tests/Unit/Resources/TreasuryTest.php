<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\TreasuryMovement;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('creates a treasury movement', function (): void {
    Http::fake([
        '*invoicexpress.com/treasury_movements.json*' => Http::response([
            'treasury_movement' => ['id' => 1, 'amount' => 100.0],
        ], 201),
    ]);

    $result = InvoiceExpress::treasury()->create(
        new TreasuryMovement(
            accountId: 1,
            amount: 100.0,
            date: '2026-05-15',
            movementType: 'credit',
        ),
    );

    expect($result['id'])->toBe(1);
});

it('lists treasury accounts', function (): void {
    Http::fake([
        '*invoicexpress.com/treasury_accounts.json*' => Http::response([]),
    ]);

    InvoiceExpress::treasury()->accounts();

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/treasury_accounts.json'));
});

it('lists treasury movement categories', function (): void {
    Http::fake([
        '*invoicexpress.com/treasury_movement_categories.json*' => Http::response([]),
    ]);

    InvoiceExpress::treasury()->categories();

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/treasury_movement_categories.json'));
});
