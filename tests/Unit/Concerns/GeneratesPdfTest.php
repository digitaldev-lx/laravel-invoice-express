<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

// Regression: the PDF endpoint is the single, document-type agnostic
// `api/pdf/{id}.json`. This trait built `{root}/{id}/pdf.json` (e.g.
// `invoices/5/pdf.json`), a path that does not exist — the API returned 404 for
// documents that were issued, paid and visible in InvoiceXpress. The old test
// mocked the wrong path, so it passed against a fabricated endpoint.
it('returns the JSON envelope for the PDF URL from api/pdf/{id}.json', function (): void {
    Http::fake([
        '*invoicexpress.com/api/pdf/5.json*' => Http::response([
            'output' => ['pdfUrl' => 'https://example/file.pdf'],
        ]),
    ]);

    $envelope = InvoiceExpress::invoices()->pdfUrl(5);

    expect($envelope['output']['pdfUrl'])->toBe('https://example/file.pdf');
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/api/pdf/5.json')
        && ! str_contains($request->url(), '/invoices/'));
});

it('uses the same endpoint regardless of the resource type', function (): void {
    Http::fake([
        '*invoicexpress.com/api/pdf/9.json*' => Http::response([
            'output' => ['pdfUrl' => 'https://example/guide.pdf'],
        ]),
    ]);

    // A different resource (endpointRoot 'guides') must still hit api/pdf/{id}.json,
    // not guides/9/pdf.json.
    InvoiceExpress::guides()->pdfUrl(9);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), '/api/pdf/9.json')
        && ! str_contains($request->url(), '/guides/'));
});

it('appends second_copy=true when requested', function (): void {
    Http::fake(['*' => Http::response([])]);

    InvoiceExpress::invoices()->pdfUrl(5, secondCopy: true);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), 'second_copy=true'));
});
