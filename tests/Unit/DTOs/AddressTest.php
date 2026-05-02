<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Address;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;

it('serialises an Address using snake_case keys', function (): void {
    $address = new Address(
        street: 'Rua das Flores 1',
        postalCode: '1000-001',
        city: 'Lisboa',
        country: Country::PT,
    );

    expect($address->toArray())->toBe([
        'address' => 'Rua das Flores 1',
        'postal_code' => '1000-001',
        'city' => 'Lisboa',
        'country' => 'PT',
    ]);
});

it('omits null fields from toArray', function (): void {
    $address = new Address(city: 'Porto');

    expect($address->toArray())->toBe(['city' => 'Porto']);
});

it('hydrates Country enum from a known code', function (): void {
    $address = Address::fromArray([
        'address' => 'Av. da Liberdade',
        'city' => 'Lisboa',
        'country' => 'PT',
    ]);

    expect($address->country)->toBe(Country::PT);
});

it('keeps unknown country strings as-is', function (): void {
    $address = Address::fromArray(['country' => 'XX']);

    expect($address->country)->toBe('XX');
});

it('supports raw country strings on construction', function (): void {
    $address = new Address(country: 'PT');

    expect($address->toArray())->toBe(['country' => 'PT']);
});
