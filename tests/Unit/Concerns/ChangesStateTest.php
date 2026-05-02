<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentState;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentPaid;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('finalizes an invoice and dispatches DocumentFinalized', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices/12/change-state.json*' => Http::response(['invoice' => ['id' => 12]]),
    ]);

    InvoiceExpress::invoices()->finalize(12);

    Http::assertSent(static fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/invoices/12/change-state.json'));

    Event::assertDispatched(DocumentFinalized::class);
});

it('settles an invoice and dispatches DocumentPaid', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices/12/change-state.json*' => Http::response([], 200),
    ]);

    InvoiceExpress::invoices()->settle(12);

    Event::assertDispatched(
        DocumentPaid::class,
        static fn (DocumentPaid $event): bool => $event->documentId === 12,
    );
});

it('cancels an invoice carrying a reason', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices/12/change-state.json*' => Http::response([]),
    ]);

    InvoiceExpress::invoices()->cancel(12, 'Cliente desistiu');

    Event::assertDispatched(
        DocumentCanceled::class,
        static fn (DocumentCanceled $event): bool => $event->reason === 'Cliente desistiu',
    );
});

it('passes a Draft state without dispatching lifecycle events', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices/1/change-state.json*' => Http::response([]),
    ]);

    InvoiceExpress::invoices()->changeState(1, DocumentState::Draft);

    Event::assertNotDispatched(DocumentFinalized::class);
    Event::assertNotDispatched(DocumentPaid::class);
    Event::assertNotDispatched(DocumentCanceled::class);
});
