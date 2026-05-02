<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum EstimateType: string
{
    case Quote = 'Quote';
    case Proforma = 'Proforma';
    case FeesNote = 'FeesNote';
    case Estimate = 'Estimate';

    public function payloadKey(): string
    {
        return match ($this) {
            self::Quote => 'quote',
            self::Proforma => 'proforma',
            self::FeesNote => 'fees_note',
            self::Estimate => 'estimate',
        };
    }

    public function endpointPath(): string
    {
        return match ($this) {
            self::Quote => 'quotes',
            self::Proforma => 'proformas',
            self::FeesNote => 'fees_notes',
            self::Estimate => 'estimates',
        };
    }
}
