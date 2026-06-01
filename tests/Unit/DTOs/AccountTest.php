<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Account;

it('serialises and hydrates an Account', function (): void {
    $account = new Account(name: 'Caixa', accountType: 'cash', openingBalance: '100.50');

    expect($account->toArray())->toBe([
        'name' => 'Caixa',
        'account_type' => 'cash',
        'opening_balance' => '100.50',
    ]);

    $hydrated = Account::fromArray([
        'name' => 'Banco',
        'account_type' => 'bank',
        'opening_balance' => '500.0',
    ]);

    expect($hydrated->name)->toBe('Banco');
    expect($hydrated->openingBalance)->toBe('500.0');
});
