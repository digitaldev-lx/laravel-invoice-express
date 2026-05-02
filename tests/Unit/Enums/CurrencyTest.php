<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\Currency;

it('returns the symbol for each known currency', function (): void {
    expect(Currency::EUR->symbol())->toBe('€');
    expect(Currency::USD->symbol())->toBe('$');
    expect(Currency::GBP->symbol())->toBe('£');
    expect(Currency::BRL->symbol())->toBe('R$');
    expect(Currency::JPY->symbol())->toBe('¥');
});
