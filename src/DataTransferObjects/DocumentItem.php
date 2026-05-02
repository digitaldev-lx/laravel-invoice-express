<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class DocumentItem implements DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public float $quantity = 1.0,
        public ?float $unitPrice = null,
        public ?float $discount = null,
        public ?string $unit = null,
        public ?Tax $tax = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'discount' => $this->discount,
            'unit' => $this->unit,
            'tax' => $this->tax?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $tax = isset($data['tax']) && is_array($data['tax']) ? Tax::fromArray($data['tax']) : null;

        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 1.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            discount: isset($data['discount']) ? (float) $data['discount'] : null,
            unit: isset($data['unit']) && is_string($data['unit']) ? $data['unit'] : null,
            tax: $tax,
        );
    }
}
