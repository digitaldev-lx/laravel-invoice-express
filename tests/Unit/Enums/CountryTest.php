<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;

it('flags Portugal correctly', function (): void {
    expect(Country::PT->isPortugal())->toBeTrue();
    expect(Country::ES->isPortugal())->toBeFalse();
    expect(Country::BR->isPortugal())->toBeFalse();
});

it('flags EU member states', function (): void {
    expect(Country::PT->isEU())->toBeTrue();
    expect(Country::ES->isEU())->toBeTrue();
    expect(Country::DE->isEU())->toBeTrue();
    expect(Country::GB->isEU())->toBeFalse();
    expect(Country::US->isEU())->toBeFalse();
    expect(Country::BR->isEU())->toBeFalse();
});
