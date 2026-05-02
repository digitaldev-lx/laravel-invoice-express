<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\PaymentMethod;

it('exposes the SAF-T compatible code', function (): void {
    expect(PaymentMethod::Cash->code())->toBe('NU');
    expect(PaymentMethod::BankTransfer->code())->toBe('TB');
    expect(PaymentMethod::MultibancoReference->code())->toBe('MB');
    expect(PaymentMethod::MBWay->code())->toBe('MW');
    expect(PaymentMethod::CreditCard->code())->toBe('CC');
});

it('returns Portuguese labels', function (): void {
    expect(PaymentMethod::Cash->label())->toBe('Numerário');
    expect(PaymentMethod::BankTransfer->label())->toBe('Transferência Bancária');
    expect(PaymentMethod::MultibancoReference->label())->toBe('Referência Multibanco');
});
