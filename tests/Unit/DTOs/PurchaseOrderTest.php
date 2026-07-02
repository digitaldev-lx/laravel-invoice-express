<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\PurchaseOrder;

it('serialises a purchase order with line items and supplier', function (): void {
    $po = new PurchaseOrder(
        date: '2026-05-01',
        deliveryDate: '2026-05-10',
        items: [new DocumentItem(name: 'Sourcing', unitPrice: '100.0')],
        supplier: ['name' => 'Vendor'],
    );

    $payload = $po->toArray();

    expect($payload['date'])->toBe('2026-05-01');
    expect($payload['delivery_date'])->toBe('2026-05-10');
    expect($payload['supplier'])->toBe(['name' => 'Vendor']);
    expect($payload['items'])->toBeArray();
    expect(array_is_list($payload['items']))->toBeTrue();
    expect($payload['items'])->toHaveCount(1);
});

it('hydrates a purchase order from a remote payload', function (): void {
    $po = PurchaseOrder::fromArray([
        'date' => '2026-05-01',
        'delivery_date' => '2026-05-10',
        'items' => ['item' => [['name' => 'Sourcing']]],
    ]);

    expect($po->date)->toBe('2026-05-01');
    expect($po->items)->toHaveCount(1);
});
