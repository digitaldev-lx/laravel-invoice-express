<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Guide;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\GuideType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('creates a transport guide on the transports endpoint', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/transports.json*' => Http::response(['transport' => ['id' => 1]], 201),
    ]);

    InvoiceExpress::guides()->create(
        new Guide(type: GuideType::Transport, date: '2026-05-01', vehicleRegistration: '00-AA-00'),
    );

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/transports.json'));
    Event::assertDispatched(
        DocumentCreated::class,
        static fn (DocumentCreated $event): bool => $event->type === DocumentType::TransportGuide,
    );
});

it('routes shipping guides to the shippings endpoint', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/shippings.json*' => Http::response(['shipping' => ['id' => 2]], 201),
    ]);

    InvoiceExpress::guides()->create(
        new Guide(type: GuideType::Shipping, date: '2026-05-01'),
    );

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/shippings.json'));
});
