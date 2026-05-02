<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('returns the JSON envelope for the PDF URL', function (): void {
    Http::fake([
        '*invoicexpress.com/invoices/5/pdf.json*' => Http::response([
            'output' => ['pdfUrl' => 'https://example/file.pdf'],
        ]),
    ]);

    $envelope = InvoiceExpress::invoices()->pdfUrl(5);

    expect($envelope['output']['pdfUrl'])->toBe('https://example/file.pdf');
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/invoices/5/pdf.json'));
});

it('appends second_copy=true when requested', function (): void {
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->pdfUrl(5, secondCopy: true);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), 'second_copy=true'));
});
