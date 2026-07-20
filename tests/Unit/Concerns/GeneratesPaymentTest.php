<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Payment;
use DigitaldevLx\LaravelInvoiceExpress\Enums\PaymentMethod;
use DigitaldevLx\LaravelInvoiceExpress\Events\PaymentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\PaymentReceived;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

// Partial payments live under the generic `documents/` resource, not the
// per-type root — `invoices/9/partial_payments.json` (what the old mock asserted)
// returns 404.
it('records a payment via documents/{id}/partial_payments.json', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/documents/9/partial_payments.json*' => Http::response([
            'payment' => ['id' => 1, 'amount' => 50.0],
        ], 201),
    ]);

    InvoiceExpress::invoices()->payment(
        id: 9,
        payment: new Payment(
            paymentMechanism: PaymentMethod::BankTransfer,
            amount: '50.0',
            paymentDate: '2026-05-15',
        ),
    );

    Http::assertSent(static fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/documents/9/partial_payments.json')
        && ! str_contains($request->url(), '/invoices/'));

    Event::assertDispatched(PaymentReceived::class);
});

it('cancels a payment and dispatches PaymentCanceled', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/documents/9/partial_payments/3/change-state.json*' => Http::response([]),
    ]);

    InvoiceExpress::invoices()->cancelPayment(9, 3, 'Reverteu');

    Http::assertSent(static fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/documents/9/partial_payments/3/change-state.json')
        && ! str_contains($request->url(), '/invoices/'));

    Event::assertDispatched(
        PaymentCanceled::class,
        static fn (PaymentCanceled $event): bool => $event->paymentId === 3,
    );
});
