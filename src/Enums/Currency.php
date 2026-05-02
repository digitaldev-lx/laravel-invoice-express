<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case CHF = 'CHF';
    case BRL = 'BRL';
    case CAD = 'CAD';
    case AUD = 'AUD';
    case JPY = 'JPY';
    case CNY = 'CNY';
    case AOA = 'AOA';
    case MZN = 'MZN';
    case CVE = 'CVE';

    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD, self::CAD, self::AUD => '$',
            self::GBP => '£',
            self::CHF => 'CHF',
            self::BRL => 'R$',
            self::JPY, self::CNY => '¥',
            self::AOA => 'Kz',
            self::MZN => 'MT',
            self::CVE => 'CVE',
        };
    }
}
