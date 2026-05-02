<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum Country: string
{
    case PT = 'PT';
    case ES = 'ES';
    case FR = 'FR';
    case DE = 'DE';
    case IT = 'IT';
    case GB = 'GB';
    case IE = 'IE';
    case NL = 'NL';
    case BE = 'BE';
    case LU = 'LU';
    case AT = 'AT';
    case DK = 'DK';
    case SE = 'SE';
    case FI = 'FI';
    case PL = 'PL';
    case CZ = 'CZ';
    case GR = 'GR';
    case BR = 'BR';
    case US = 'US';
    case CA = 'CA';
    case MX = 'MX';
    case AO = 'AO';
    case MZ = 'MZ';
    case CV = 'CV';
    case GW = 'GW';
    case ST = 'ST';
    case TL = 'TL';
    case CH = 'CH';

    public function isPortugal(): bool
    {
        return $this === self::PT;
    }

    public function isEU(): bool
    {
        return in_array($this, [
            self::PT, self::ES, self::FR, self::DE, self::IT, self::IE,
            self::NL, self::BE, self::LU, self::AT, self::DK, self::SE,
            self::FI, self::PL, self::CZ, self::GR,
        ], true);
    }
}
