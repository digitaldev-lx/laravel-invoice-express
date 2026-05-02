<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Client;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Language;

it('serialises a Client to InvoiceXpress snake_case', function (): void {
    $client = new Client(
        name: 'Acme Lda',
        code: 'ACM-001',
        email: 'finance@acme.pt',
        address: 'Rua A',
        city: 'Lisboa',
        postalCode: '1000-001',
        country: Country::PT,
        fiscalId: '500000000',
        language: Language::PT,
    );

    $array = $client->toArray();

    expect($array['name'])->toBe('Acme Lda');
    expect($array['code'])->toBe('ACM-001');
    expect($array['email'])->toBe('finance@acme.pt');
    expect($array['fiscal_id'])->toBe('500000000');
    expect($array['country'])->toBe('PT');
    expect($array['language'])->toBe('pt');
});

it('omits null fields from toArray', function (): void {
    $client = new Client(name: 'Bare client');

    expect($client->toArray())->toBe(['name' => 'Bare client']);
});

it('hydrates from a remote response', function (): void {
    $client = Client::fromArray([
        'name' => 'Acme',
        'fiscal_id' => '500000000',
        'country' => 'PT',
        'language' => 'pt',
        'discount' => '5.0',
    ]);

    expect($client->name)->toBe('Acme');
    expect($client->fiscalId)->toBe('500000000');
    expect($client->country)->toBe(Country::PT);
    expect($client->language)->toBe(Language::PT);
    expect($client->discount)->toBe(5.0);
});

it('falls back to raw strings for unknown enum values', function (): void {
    $client = Client::fromArray([
        'name' => 'X',
        'country' => 'ZZ',
        'language' => 'zz',
    ]);

    expect($client->country)->toBe('ZZ');
    expect($client->language)->toBe('zz');
});
