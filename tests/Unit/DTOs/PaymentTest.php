<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Payment;
use DigitaldevLx\LaravelInvoiceExpress\Enums\PaymentMethod;

it('serialises payment with enum mechanism to its SAF-T code', function (): void {
    $payment = new Payment(
        paymentMechanism: PaymentMethod::BankTransfer,
        amount: '123.45',
        paymentDate: '2026-05-15',
        observations: 'IBAN ...',
    );

    expect($payment->toArray())->toBe([
        'payment_mechanism' => 'TB',
        'amount' => '123.45',
        'payment_date' => '2026-05-15',
        'observations' => 'IBAN ...',
    ]);
});

it('accepts raw mechanism strings', function (): void {
    $payment = new Payment(paymentMechanism: 'CUSTOM', amount: '10.0');

    expect($payment->toArray())->toMatchArray([
        'payment_mechanism' => 'CUSTOM',
    ]);
});

it('hydrates back from a remote payment payload', function (): void {
    $payment = Payment::fromArray([
        'payment_mechanism' => 'MB',
        'amount' => '50.0',
        'payment_date' => '2026-05-01',
    ]);

    expect($payment->paymentMechanism)->toBe(PaymentMethod::MultibancoReference);
    expect($payment->amount)->toBe('50.0');
});

it('preserves exact decimal strings without float rounding', function (): void {
    $payment = Payment::fromArray(['payment_mechanism' => 'MB', 'amount' => '10.50']);

    expect($payment->amount)->toBe('10.50');
    expect($payment->toArray()['amount'])->toBe('10.50');
});
