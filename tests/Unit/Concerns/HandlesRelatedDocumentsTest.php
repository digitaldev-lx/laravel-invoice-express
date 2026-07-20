<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

// Regression: the endpoint is `document/{id}/related_documents.json` — literal
// singular `document`, not the resource root — and the envelope is
// `{ "documents": [ … ] }`. This trait built `{root}/{id}/related_documents.json`
// (404) and the old test asserted the wrong `related_documents` key too.
it('lists related documents from document/{id}/related_documents.json', function (): void {
    Http::fake([
        '*invoicexpress.com/document/4/related_documents.json*' => Http::response([
            'documents' => [['id' => 9]],
        ]),
    ]);

    $result = InvoiceExpress::invoices()->relatedDocuments(4);

    expect($result['documents'])->toHaveCount(1);
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/document/4/related_documents.json')
        && ! str_contains($request->url(), '/invoices/'));
});
