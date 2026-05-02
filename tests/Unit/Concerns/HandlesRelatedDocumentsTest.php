<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('lists related documents for an invoice', function (): void {
    Http::fake([
        '*invoicexpress.com/invoices/4/related_documents.json*' => Http::response([
            'related_documents' => [['id' => 9]],
        ]),
    ]);

    $result = InvoiceExpress::invoices()->relatedDocuments(4);

    expect($result['related_documents'])->toHaveCount(1);
});
