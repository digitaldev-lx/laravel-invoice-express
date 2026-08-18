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

it('parses the unkeyed list of errors the document endpoints return', function (): void {
    $exception = ValidationException::fromResponse([
        'errors' => [
            ['error' => 'Razão de isenção deve ter uma opção selecionada'],
            ['error' => 'A série não corresponde ao tipo de documento'],
        ],
    ]);

    expect($exception->messages)->toBe([
        'Razão de isenção deve ter uma opção selecionada',
        'A série não corresponde ao tipo de documento',
    ]);
    expect($exception->getMessage())->toContain('Razão de isenção deve ter uma opção selecionada');
    // No field names to key on in this shape.
    expect($exception->errors)->toBe([]);
});

it('parses a bare list of error strings', function (): void {
    $exception = ValidationException::fromResponse(['errors' => ['Nome é obrigatório']]);

    expect($exception->messages)->toBe(['Nome é obrigatório']);
});

it('still exposes messages for the keyed shape', function (): void {
    $exception = ValidationException::fromResponse([
        'errors' => ['name' => ["can't be blank"]],
    ]);

    expect($exception->messages)->toBe(["name: can't be blank"]);
    expect($exception->getFirstError('name'))->toBe("can't be blank");
});
