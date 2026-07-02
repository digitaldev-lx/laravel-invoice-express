<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Invoice;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;

it('serialises items as a plain JSON array as required by the API', function (): void {
    $invoice = new Invoice(
        type: DocumentType::Invoice,
        date: '2026-05-01',
        dueDate: '2026-05-31',
        items: [
            new DocumentItem(
                name: 'Hora',
                quantity: '2',
                unitPrice: '50.0',
                tax: new Tax(name: 'IVA23', value: '23.0'),
            ),
            new DocumentItem(name: 'Setup', unitPrice: '100.0'),
        ],
        client: ['name' => 'Acme'],
    );

    $payload = $invoice->toArray();

    expect($payload['date'])->toBe('2026-05-01');
    expect($payload['due_date'])->toBe('2026-05-31');
    expect($payload['items'])->toBeArray();
    expect(array_is_list($payload['items']))->toBeTrue();
    expect($payload['items'])->toHaveCount(2);
    expect($payload['items'][0]['name'])->toBe('Hora');
    expect($payload['client'])->toBe(['name' => 'Acme']);
});

it('lets caller append API-specific fields via extra', function (): void {
    $invoice = new Invoice(
        type: DocumentType::Invoice,
        date: '2026-05-01',
        extra: ['mb_reference' => 'MB:12345'],
    );

    expect($invoice->toArray())->toMatchArray([
        'date' => '2026-05-01',
        'mb_reference' => 'MB:12345',
    ]);
});

it('hydrates back including items', function (): void {
    $invoice = Invoice::fromArray([
        'type' => 'Invoice',
        'date' => '2026-05-01',
        'items' => [
            'item' => [
                ['name' => 'Hora', 'quantity' => '2', 'unit_price' => '50.0'],
            ],
        ],
    ]);

    expect($invoice->type)->toBe(DocumentType::Invoice);
    expect($invoice->items)->toHaveCount(1);
    expect($invoice->items[0]->name)->toBe('Hora');
});

it('hydrates back from a plain items array', function (): void {
    $invoice = Invoice::fromArray([
        'type' => 'Invoice',
        'date' => '2026-05-01',
        'items' => [
            ['name' => 'Hora', 'quantity' => '2', 'unit_price' => '50.0'],
        ],
    ]);

    expect($invoice->items)->toHaveCount(1);
    expect($invoice->items[0]->name)->toBe('Hora');
});

it('falls back to Invoice when type is unknown', function (): void {
    $invoice = Invoice::fromArray(['type' => 'NotARealType']);

    expect($invoice->type)->toBe(DocumentType::Invoice);
});
