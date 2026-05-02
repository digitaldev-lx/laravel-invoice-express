<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\DocumentItem;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Guide;
use DigitaldevLx\LaravelInvoiceExpress\Enums\GuideType;

it('serialises transport-specific fields', function (): void {
    $guide = new Guide(
        type: GuideType::Transport,
        date: '2026-05-01',
        loadedAt: '2026-05-01 10:00',
        loadedFrom: 'Lisboa',
        loadedTo: 'Porto',
        vehicleRegistration: '00-AA-00',
        items: [new DocumentItem(name: 'Pallet', quantity: 2)],
    );

    $payload = $guide->toArray();

    expect($payload['loaded_from'])->toBe('Lisboa');
    expect($payload['loaded_to'])->toBe('Porto');
    expect($payload['vehicle_registration'])->toBe('00-AA-00');
});

it('hydrates from an API response', function (): void {
    $guide = Guide::fromArray([
        'type' => 'ShippingGuide',
        'date' => '2026-05-01',
        'loaded_at' => '2026-05-01 10:00',
    ]);

    expect($guide->type)->toBe(GuideType::Shipping);
    expect($guide->loadedAt)->toBe('2026-05-01 10:00');
});
