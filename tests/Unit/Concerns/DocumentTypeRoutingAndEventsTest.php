<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailRecipient;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Payment;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentState;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\PaymentMethod;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\EmailSent;
use DigitaldevLx\LaravelInvoiceExpress\Events\PaymentReceived;
use DigitaldevLx\LaravelInvoiceExpress\Events\PdfGenerated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

dataset('stateRoots', [
    'invoice' => [DocumentType::Invoice, 'invoices'],
    'simplified' => [DocumentType::SimplifiedInvoice, 'simplified_invoices'],
    'invoice receipt' => [DocumentType::InvoiceReceipt, 'invoice_receipts'],
    'credit note' => [DocumentType::CreditNote, 'credit_notes'],
    'debit note' => [DocumentType::DebitNote, 'debit_notes'],
]);

it('routes change-state to the per-type root', function (DocumentType $type, string $root): void {
    Event::fake();
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->changeState(7, DocumentState::Final, null, $type);

    Http::assertSent(static fn (Request $r): bool => $r->method() === 'PUT'
        && str_contains($r->url(), "/{$root}/7/change-state.json")
        && $r['invoice']['state'] === 'finalized');

    Event::assertDispatched(
        DocumentFinalized::class,
        static fn (DocumentFinalized $e): bool => $e->type === $type,
    );
})->with('stateRoots');

it('keeps the invoices root when no type is given (backwards compatible)', function (): void {
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->finalize(3);

    Http::assertSent(static fn (Request $r): bool => str_contains($r->url(), '/invoices/3/change-state.json'));
});

it('accepts the type on cancel and settle shortcuts', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->cancel(4, 'erro', DocumentType::CreditNote);
    InvoiceExpress::invoices()->settle(5, null, DocumentType::InvoiceReceipt);

    Http::assertSent(static fn (Request $r): bool => str_contains($r->url(), '/credit_notes/4/change-state.json')
        && $r['invoice']['message'] === 'erro');
    Http::assertSent(static fn (Request $r): bool => str_contains($r->url(), '/invoice_receipts/5/change-state.json'));
    Event::assertDispatched(DocumentCanceled::class, static fn ($e): bool => $e->reason === 'erro' && $e->type === DocumentType::CreditNote);
});

it('routes the email to the per-type root when a type is given', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->email(
        9,
        new EmailMessage(to: new EmailRecipient(email: 'a@b.pt'), subject: 's', body: 'b'),
        DocumentType::CreditNote,
    );

    Http::assertSent(static fn (Request $r): bool => str_contains($r->url(), '/credit_notes/9/email-document.json'));
    Event::assertDispatched(EmailSent::class, static fn (EmailSent $e): bool => $e->type === DocumentType::CreditNote);
});

it('carries the account name on every document event for multi-tenant filtering', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response(['invoice' => ['id' => 1]])]);

    $scoped = InvoiceExpress::useAccount('tenant-a', 'key-a');
    $scoped->invoices()->create(['type' => 'Invoice', 'date' => '01/01/2026', 'due_date' => '01/02/2026', 'items' => []]);
    $scoped->invoices()->finalize(1);
    $scoped->invoices()->cancel(1, 'x');
    $scoped->invoices()->payment(1, new Payment(paymentMechanism: PaymentMethod::BankTransfer, amount: '10.00'));

    foreach ([DocumentCreated::class, DocumentFinalized::class, DocumentCanceled::class, PaymentReceived::class] as $event) {
        Event::assertDispatched($event, static fn ($e): bool => $e->accountName === 'tenant-a');
    }
});

it('carries the default account name when not using useAccount', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->finalize(1);

    Event::assertDispatched(DocumentFinalized::class, static fn ($e): bool => $e->accountName === 'test-account');
});

it('carries the account name on PdfGenerated', function (): void {
    Event::fake();
    Http::fake([
        '*api/pdf/1.json*' => Http::response(['output' => ['pdfUrl' => 'https://files.example/x.pdf']]),
        'files.example/*' => Http::response('PDF'),
    ]);

    InvoiceExpress::useAccount('tenant-b', 'k')->invoices()->pdf(1);

    Event::assertDispatched(PdfGenerated::class, static fn (PdfGenerated $e): bool => $e->accountName === 'tenant-b' && $e->bytes === 3);
});

it('keeps accountName optional so existing event construction still works', function (): void {
    $event = new DocumentFinalized([], DocumentType::Invoice, 1);

    expect($event->accountName)->toBeNull();
});
