<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Item;

it('nests tax fields under a tax key', function (): void {
    $item = new Item(
        name: 'Widget',
        unitPrice: 9.99,
        unit: 'pcs',
        taxId: 7,
        taxName: 'IVA23',
        taxRate: 23.0,
    );

    expect($item->toArray())->toBe([
        'name' => 'Widget',
        'unit_price' => 9.99,
        'unit' => 'pcs',
        'tax' => [
            'id' => 7,
            'name' => 'IVA23',
            'value' => 23.0,
        ],
    ]);
});

it('drops the tax key when no tax info is present', function (): void {
    $item = new Item(name: 'Plain', unitPrice: 1.0);

    expect($item->toArray())->toBe([
        'name' => 'Plain',
        'unit_price' => 1.0,
    ]);
});

it('hydrates tax from a flat tax payload', function (): void {
    $item = Item::fromArray([
        'name' => 'Widget',
        'unit_price' => '9.99',
        'tax' => ['id' => '1', 'name' => 'IVA23', 'value' => '23'],
    ]);

    expect($item->unitPrice)->toBe(9.99);
    expect($item->taxId)->toBe(1);
    expect($item->taxRate)->toBe(23.0);
});
