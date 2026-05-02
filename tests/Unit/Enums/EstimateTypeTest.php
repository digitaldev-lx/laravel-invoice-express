<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\EstimateType;

it('returns the payload key per estimate type', function (): void {
    expect(EstimateType::Quote->payloadKey())->toBe('quote');
    expect(EstimateType::Proforma->payloadKey())->toBe('proforma');
    expect(EstimateType::FeesNote->payloadKey())->toBe('fees_note');
    expect(EstimateType::Estimate->payloadKey())->toBe('estimate');
});

it('returns the endpoint path per estimate type', function (): void {
    expect(EstimateType::Quote->endpointPath())->toBe('quotes');
    expect(EstimateType::Proforma->endpointPath())->toBe('proformas');
    expect(EstimateType::FeesNote->endpointPath())->toBe('fees_notes');
    expect(EstimateType::Estimate->endpointPath())->toBe('estimates');
});
