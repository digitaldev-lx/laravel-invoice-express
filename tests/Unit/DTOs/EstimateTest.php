<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Estimate;
use DigitaldevLx\LaravelInvoiceExpress\Enums\EstimateType;

it('serialises an estimate with its line items', function (): void {
    $estimate = new Estimate(
        type: EstimateType::Quote,
        date: '2026-05-01',
        items: [new DocumentItem(name: 'Hour', unitPrice: '50.0')],
        client: ['name' => 'Acme'],
    );

    $payload = $estimate->toArray();

    expect($payload['date'])->toBe('2026-05-01');
    expect($payload['items']['item'])->toHaveCount(1);
});

it('hydrates from an API response', function (): void {
    $estimate = Estimate::fromArray([
        'type' => 'Proforma',
        'date' => '2026-05-01',
        'items' => ['item' => [['name' => 'X']]],
    ]);

    expect($estimate->type)->toBe(EstimateType::Proforma);
    expect($estimate->items)->toHaveCount(1);
});

it('falls back to Quote when type is unknown', function (): void {
    $estimate = Estimate::fromArray(['type' => 'Bogus']);

    expect($estimate->type)->toBe(EstimateType::Quote);
});
