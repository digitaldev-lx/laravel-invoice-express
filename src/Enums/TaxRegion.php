<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum TaxRegion: string
{
    case PtMainland = 'PT';
    case Azores = 'PT-AC';
    case Madeira = 'PT-MA';

    public function label(): string
    {
        return match ($this) {
            self::PtMainland => 'Portugal Continental',
            self::Azores => 'Açores',
            self::Madeira => 'Madeira',
        };
    }

    /**
     * @return array{normal: float, intermediate: float, reduced: float}
     */
    public function defaultRates(): array
    {
        return match ($this) {
            self::PtMainland => ['normal' => 23.0, 'intermediate' => 13.0, 'reduced' => 6.0],
            self::Azores => ['normal' => 16.0, 'intermediate' => 9.0, 'reduced' => 4.0],
            self::Madeira => ['normal' => 22.0, 'intermediate' => 12.0, 'reduced' => 5.0],
        };
    }
}
