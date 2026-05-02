<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\VatExemptionCode;

it('returns descriptions for the most common exemption codes', function (): void {
    expect(VatExemptionCode::M01->description())->toContain('Artigo 16');
    expect(VatExemptionCode::M07->description())->toContain('Artigo 9');
    expect(VatExemptionCode::M99->description())->toContain('Não sujeito');
});

it('exposes the canonical M-prefixed value', function (): void {
    expect(VatExemptionCode::M01->value)->toBe('M01');
    expect(VatExemptionCode::M99->value)->toBe('M99');
});
