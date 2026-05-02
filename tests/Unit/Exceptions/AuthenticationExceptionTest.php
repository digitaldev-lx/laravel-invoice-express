<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\AuthenticationException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\InvoiceExpressException;

it('captures the offending account name', function (): void {
    $exception = new AuthenticationException(
        message: 'auth failed',
        accountName: 'mycompany',
    );

    expect($exception)->toBeInstanceOf(InvoiceExpressException::class);
    expect($exception->accountName)->toBe('mycompany');
    expect($exception->getCode())->toBe(401);
});
