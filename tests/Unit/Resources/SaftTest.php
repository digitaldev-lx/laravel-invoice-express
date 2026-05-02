<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('downloads SAF-T XML for a given period', function (): void {
    Http::fake([
        '*invoicexpress.com/saft.xml*' => Http::response(
            '<?xml version="1.0"?><AuditFile></AuditFile>',
            200,
        ),
    ]);

    $xml = InvoiceExpress::saft()->generate(2026, 4);

    expect($xml)->toContain('<AuditFile>');
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), 'saft.xml')
        && str_contains($request->url(), 'year=2026')
        && str_contains($request->url(), 'month=4'));
});
