<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

// Regression: the QR code endpoint is the single, document-type agnostic
// `api/qr_codes/{id}.json` and its envelope is `{ "qr_code": { "url": … } }`.
// This trait built `{root}/{id}/qr_code.json` (404) and the old test also
// asserted the wrong `output.url` shape — wrong path AND wrong response shape.
it('hits api/qr_codes/{id}.json and returns the qr_code.url envelope', function (): void {
    Http::fake([
        '*invoicexpress.com/api/qr_codes/8.json*' => Http::response([
            'qr_code' => ['url' => 'https://qr/example.png'],
        ]),
    ]);

    $envelope = InvoiceExpress::invoices()->qrCode(8);

    expect($envelope['qr_code']['url'])->toBe('https://qr/example.png');
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), '/api/qr_codes/8.json')
        && ! str_contains($request->url(), '/invoices/'));
});
