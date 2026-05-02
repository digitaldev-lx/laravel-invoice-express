<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Estimate;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\EstimateType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('creates a quote on the quotes endpoint', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/quotes.json*' => Http::response(['quote' => ['id' => 50]], 201),
    ]);

    $result = InvoiceExpress::estimates()->create(
        new Estimate(type: EstimateType::Quote, date: '2026-05-01', items: [new DocumentItem(name: 'X')]),
    );

    expect($result['id'])->toBe(50);
    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/quotes.json'));
    Event::assertDispatched(
        DocumentCreated::class,
        static fn (DocumentCreated $event): bool => $event->type === DocumentType::Quote,
    );
});

it('routes proformas to the proformas endpoint', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/proformas.json*' => Http::response(['proforma' => ['id' => 7]], 201),
    ]);

    InvoiceExpress::estimates()->create(
        new Estimate(type: EstimateType::Proforma, date: '2026-05-01'),
    );

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/proformas.json'));
});

it('lists fees notes when given the FeesNote type', function (): void {
    Http::fake([
        '*invoicexpress.com/fees_notes.json*' => Http::response(['fees_notes' => []]),
    ]);

    InvoiceExpress::estimates()->all(EstimateType::FeesNote);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/fees_notes.json'));
});
