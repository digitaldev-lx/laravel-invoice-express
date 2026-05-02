<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('hits the qr_code.json endpoint', function (): void {
    Http::fake([
        '*invoicexpress.com/invoices/8/qr_code.json*' => Http::response([
            'output' => ['url' => 'https://qr/example.png'],
        ]),
    ]);

    $envelope = InvoiceExpress::invoices()->qrCode(8);

    expect($envelope['output']['url'])->toBe('https://qr/example.png');
});
