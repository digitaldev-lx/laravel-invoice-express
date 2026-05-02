<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ValidationException;

it('parses errors wrapped in an "errors" key', function (): void {
    $exception = ValidationException::fromResponse([
        'errors' => [
            'name' => ["can't be blank"],
            'fiscal_id' => ['is invalid'],
        ],
    ]);

    expect($exception->errors)
        ->toHaveKey('name')
        ->toHaveKey('fiscal_id');
    expect($exception->getFieldErrors())->toBe([
        'name' => "can't be blank",
        'fiscal_id' => 'is invalid',
    ]);
    expect($exception->hasFieldError('name'))->toBeTrue();
    expect($exception->hasFieldError('email'))->toBeFalse();
});

it('parses errors that arrive at the top level', function (): void {
    $exception = ValidationException::fromResponse([
        'name' => ['cannot be empty'],
    ]);

    expect($exception->getFirstError('name'))->toBe('cannot be empty');
    expect($exception->getFirstError())->toBe('cannot be empty');
});

it('returns null when querying an unknown field', function (): void {
    $exception = ValidationException::fromResponse([]);

    expect($exception->getFirstError('foo'))->toBeNull();
});
