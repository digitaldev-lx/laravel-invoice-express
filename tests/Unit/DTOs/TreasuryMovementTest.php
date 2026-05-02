<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\TreasuryMovement;

it('serialises a treasury movement', function (): void {
    $movement = new TreasuryMovement(
        accountId: 1,
        amount: 250.0,
        date: '2026-05-15',
        description: 'Recebimento',
        movementType: 'credit',
        categoryId: 5,
    );

    expect($movement->toArray())->toBe([
        'account_id' => 1,
        'amount' => 250.0,
        'date' => '2026-05-15',
        'description' => 'Recebimento',
        'movement_type' => 'credit',
        'category_id' => 5,
    ]);
});

it('hydrates a treasury movement', function (): void {
    $movement = TreasuryMovement::fromArray([
        'account_id' => '1',
        'amount' => '100.0',
        'date' => '2026-05-15',
    ]);

    expect($movement->accountId)->toBe(1);
    expect($movement->amount)->toBe(100.0);
});
