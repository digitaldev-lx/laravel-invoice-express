<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum Language: string
{
    case PT = 'pt';
    case EN = 'en';
    case ES = 'es';
    case FR = 'fr';

    public function label(): string
    {
        return match ($this) {
            self::PT => 'Português',
            self::EN => 'English',
            self::ES => 'Español',
            self::FR => 'Français',
        };
    }
}
