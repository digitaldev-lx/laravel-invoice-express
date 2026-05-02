<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailRecipient;
use DigitaldevLx\LaravelInvoiceExpress\Events\EmailSent;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('sends an invoice by email and dispatches EmailSent', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/invoices/12/email-document.json*' => Http::response([], 200),
    ]);

    $message = new EmailMessage(
        to: new EmailRecipient(email: 'cliente@example.com'),
        subject: 'A sua fatura',
        body: 'Em anexo.',
    );

    InvoiceExpress::invoices()->email(12, $message);

    Http::assertSent(static fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/invoices/12/email-document.json'));

    Event::assertDispatched(EmailSent::class);
});
