<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\TaxRegion;

it('returns Portuguese region labels', function (): void {
    expect(TaxRegion::PtMainland->label())->toBe('Portugal Continental');
    expect(TaxRegion::Azores->label())->toBe('Açores');
    expect(TaxRegion::Madeira->label())->toBe('Madeira');
});

it('returns the default VAT rates per region', function (): void {
    expect(TaxRegion::PtMainland->defaultRates())->toBe([
        'normal' => 23.0,
        'intermediate' => 13.0,
        'reduced' => 6.0,
    ]);
    expect(TaxRegion::Azores->defaultRates()['normal'])->toBe(16.0);
    expect(TaxRegion::Madeira->defaultRates()['normal'])->toBe(22.0);
});
