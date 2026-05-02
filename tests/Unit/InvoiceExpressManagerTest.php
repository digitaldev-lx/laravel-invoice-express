<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;

it('returns a fresh manager when switching accounts', function (): void {
    $manager = app(InvoiceExpress::class);

    $other = $manager->useAccount('another-account', 'another-key');

    expect($other)->not->toBe($manager);
    expect($other->client()->accountName())->toBe('another-account');
    expect($manager->client()->accountName())->toBe('test-account');
});
