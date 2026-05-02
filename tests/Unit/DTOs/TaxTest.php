<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax;

it('serialises a Tax with all fields set', function (): void {
    $tax = new Tax(
        name: 'IVA23',
        value: 23.0,
        region: 'PT',
        code: 'NOR',
        defaultTax: true,
        exemptionCode: 'M01',
    );

    expect($tax->toArray())->toBe([
        'name' => 'IVA23',
        'value' => 23.0,
        'region' => 'PT',
        'code' => 'NOR',
        'default_tax' => true,
        'exemption_code' => 'M01',
    ]);
});

it('round-trips through fromArray', function (): void {
    $payload = [
        'name' => 'IVA6',
        'value' => '6.0',
        'region' => 'PT-MA',
        'code' => 'RED',
    ];

    $tax = Tax::fromArray($payload);

    expect($tax->name)->toBe('IVA6');
    expect($tax->value)->toBe(6.0);
    expect($tax->region)->toBe('PT-MA');
    expect($tax->code)->toBe('RED');
});

it('omits null fields', function (): void {
    expect((new Tax)->toArray())->toBe([]);
});
