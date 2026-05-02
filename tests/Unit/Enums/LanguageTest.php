<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\Language;

it('returns the human label for each language', function (): void {
    expect(Language::PT->label())->toBe('Português');
    expect(Language::EN->label())->toBe('English');
    expect(Language::ES->label())->toBe('Español');
    expect(Language::FR->label())->toBe('Français');
});
