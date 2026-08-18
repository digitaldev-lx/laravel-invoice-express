<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax;

it('serialises a line item with quantity and tax', function (): void {
    $item = new DocumentItem(
        name: 'Consultoria',
        description: 'Hora de consultoria',
        quantity: '4',
        unitPrice: '100.0',
        discount: '10.0',
        unit: 'h',
        tax: new Tax(name: 'IVA23', value: '23.0'),
    );

    expect($item->toArray())->toBe([
        'name' => 'Consultoria',
        'description' => 'Hora de consultoria',
        'quantity' => '4',
        'unit_price' => '100.0',
        'discount' => '10.0',
        'unit' => 'h',
        'tax' => ['name' => 'IVA23', 'value' => '23.0'],
    ]);
});

it('defaults the quantity to 1', function (): void {
    $item = new DocumentItem(name: 'X');

    expect($item->quantity)->toBe('1');
});

it('hydrates from a remote payload', function (): void {
    $item = DocumentItem::fromArray([
        'name' => 'Hora',
        'quantity' => '2',
        'unit_price' => '50.0',
        'tax' => ['name' => 'IVA23', 'value' => '23'],
    ]);

    expect($item->quantity)->toBe('2');
    expect($item->unitPrice)->toBe('50.0');
    expect($item->tax)->not->toBeNull();
    expect($item->tax->value)->toBe('23');
});

it('hoists the VAT exemption code to the line level', function (): void {
    $item = new DocumentItem(
        name: 'PCCF',
        description: 'Pack Corpo Completo Feminino',
        quantity: '1',
        unitPrice: '65',
        unit: 'unit',
        tax: new Tax(name: 'Regime de Isenção', value: '0', exemptionCode: 'M10'),
    );

    $payload = $item->toArray();

    // The API ignores `tax.exemption_code` and then rejects the document.
    expect($payload['tax_exemption'])->toBe('M10');
    expect($payload['tax'])->not->toHaveKey('exemption_code');
    expect($payload['tax']['name'])->toBe('Regime de Isenção');
});

it('round-trips the line-level exemption code back into the tax DTO', function (): void {
    $item = DocumentItem::fromArray([
        'name' => 'PCCF',
        'quantity' => '1',
        'unit_price' => '65',
        'tax_exemption' => 'M10',
        'tax' => ['name' => 'Regime de Isenção', 'value' => '0'],
    ]);

    expect($item->tax?->exemptionCode)->toBe('M10');
    expect($item->toArray()['tax_exemption'])->toBe('M10');
});
