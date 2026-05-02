<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Sequence;

it('serialises and hydrates a sequence', function (): void {
    $sequence = new Sequence(
        serie: '2026',
        documentType: 'Invoice',
        currentSequenceNumber: 100,
        defaultSequence: true,
    );

    expect($sequence->toArray())->toBe([
        'serie' => '2026',
        'document_type' => 'Invoice',
        'current_sequence_number' => 100,
        'default_sequence' => true,
    ]);

    $hydrated = Sequence::fromArray([
        'serie' => '2027',
        'current_sequence_number' => '50',
        'default_sequence' => 1,
    ]);

    expect($hydrated->serie)->toBe('2027');
    expect($hydrated->currentSequenceNumber)->toBe(50);
    expect($hydrated->defaultSequence)->toBeTrue();
});
