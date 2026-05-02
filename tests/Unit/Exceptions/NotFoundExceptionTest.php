<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\NotFoundException;

it('captures resource and id', function (): void {
    $exception = new NotFoundException(
        message: 'Not found',
        resource: 'invoices',
        resourceId: 42,
    );

    expect($exception->resource)->toBe('invoices');
    expect($exception->resourceId)->toBe(42);
    expect($exception->getCode())->toBe(404);
});
