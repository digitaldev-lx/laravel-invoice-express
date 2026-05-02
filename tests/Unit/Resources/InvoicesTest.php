<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Invoice as InvoiceDto;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('lists invoices via GET invoices.json', function (): void {
    Http::fake([
        '*invoicexpress.com/invoices.json*' => Http::response([
            'invoices' => [['id' => 1]],
        ]),
    ]);

    $result = InvoiceExpress::invoices()->all();

    expect($result)->toHaveKey('invoices');
});

it('finds a single invoice', function (): void {
    Http::fake([
        '*invoicexpress.com/invoices/12.json*' => Http::response([
            'invoice' => ['id' => 12, 'status' => 'draft'],
        ]),
    ]);

    $invoice = InvoiceExpress::invoices()->find(12);

    expect($invoice['id'])->toBe(12);
});

it('creates an invoice and dispatches DocumentCreated', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices.json*' => Http::response([
            'invoice' => ['id' => 99, 'status' => 'draft'],
        ], 201),
    ]);

    $invoice = new InvoiceDto(
        type: DocumentType::Invoice,
        date: '2026-04-01',
        dueDate: '2026-04-30',
        items: [
            new DocumentItem(
                name: 'Consulting',
                quantity: 1,
                unitPrice: 100.0,
                tax: new Tax(name: 'IVA23', value: 23.0),
            ),
        ],
        client: ['name' => 'Acme'],
    );

    $result = InvoiceExpress::invoices()->create($invoice);

    expect($result['id'])->toBe(99);
    Event::assertDispatched(
        DocumentCreated::class,
        static fn (DocumentCreated $event): bool => $event->type === DocumentType::Invoice,
    );
});

it('routes credit notes to the credit_notes endpoint', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/credit_notes.json*' => Http::response([
            'credit_note' => ['id' => 7, 'status' => 'draft'],
        ], 201),
    ]);

    $result = InvoiceExpress::invoices()->create(
        new InvoiceDto(type: DocumentType::CreditNote, date: '2026-04-01'),
    );

    expect($result['id'])->toBe(7);
    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/credit_notes.json'));
});
