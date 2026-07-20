<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;

it('exposes method, path, binary and rootKey', function (): void {
    $endpoint = new InvoiceExpressEndpoint(
        method: 'GET',
        path: 'api/pdf/{id}.json',
        binary: true,
        rootKey: 'output',
    );

    expect($endpoint->method)->toBe('GET');
    expect($endpoint->path)->toBe('api/pdf/{id}.json');
    expect($endpoint->binary)->toBeTrue();
    expect($endpoint->rootKey)->toBe('output');
});
