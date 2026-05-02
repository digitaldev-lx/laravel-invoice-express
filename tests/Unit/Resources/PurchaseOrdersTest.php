<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\PurchaseOrder;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('creates a purchase order and dispatches DocumentCreated', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/purchase_orders.json*' => Http::response([
            'purchase_order' => ['id' => 1],
        ], 201),
    ]);

    InvoiceExpress::purchaseOrders()->create(
        new PurchaseOrder(date: '2026-05-01', supplier: ['name' => 'Acme']),
    );

    Http::assertSent(static fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/purchase_orders.json'));

    Event::assertDispatched(
        DocumentCreated::class,
        static fn (DocumentCreated $event): bool => $event->type === DocumentType::PurchaseOrder,
    );
});

it('finalizes a purchase order via change-state', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/purchase_orders/2/change-state.json*' => Http::response([]),
    ]);

    InvoiceExpress::purchaseOrders()->finalize(2);

    Http::assertSent(static fn ($request): bool => $request->method() === 'PUT');
});
