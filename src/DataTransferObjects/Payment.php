<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\PaymentMethod;

final readonly class Payment implements DataTransferObject
{
    public function __construct(
        public PaymentMethod|string $paymentMechanism,
        public float $amount,
        public ?string $paymentDate = null,
        public ?string $serie = null,
        public ?string $observations = null,
        public ?string $note = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $mechanism = $this->paymentMechanism instanceof PaymentMethod
            ? $this->paymentMechanism->value
            : $this->paymentMechanism;

        return array_filter([
            'payment_mechanism' => $mechanism,
            'amount' => $this->amount,
            'payment_date' => $this->paymentDate,
            'serie' => $this->serie,
            'observations' => $this->observations,
            'note' => $this->note,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $mechanism = isset($data['payment_mechanism']) && is_string($data['payment_mechanism'])
            ? (PaymentMethod::tryFrom($data['payment_mechanism']) ?? $data['payment_mechanism'])
            : PaymentMethod::Other;

        return new self(
            paymentMechanism: $mechanism,
            amount: isset($data['amount']) ? (float) $data['amount'] : 0.0,
            paymentDate: isset($data['payment_date']) && is_string($data['payment_date']) ? $data['payment_date'] : null,
            serie: isset($data['serie']) && is_string($data['serie']) ? $data['serie'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
            note: isset($data['note']) && is_string($data['note']) ? $data['note'] : null,
        );
    }
}
