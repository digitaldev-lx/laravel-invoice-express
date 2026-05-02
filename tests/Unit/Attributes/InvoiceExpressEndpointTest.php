<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;

it('exposes method, path, binary and rootKey', function (): void {
    $endpoint = new InvoiceExpressEndpoint(
        method: 'GET',
        path: 'invoices/{id}/pdf.json',
        binary: true,
        rootKey: 'output',
    );

    expect($endpoint->method)->toBe('GET');
    expect($endpoint->path)->toBe('invoices/{id}/pdf.json');
    expect($endpoint->binary)->toBeTrue();
    expect($endpoint->rootKey)->toBe('output');
});
