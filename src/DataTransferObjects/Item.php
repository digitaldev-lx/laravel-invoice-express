<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class Item implements DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?float $unitPrice = null,
        public ?string $unit = null,
        public ?int $taxId = null,
        public ?string $taxName = null,
        public ?float $taxRate = null,
        public ?string $observations = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $tax = array_filter([
            'id' => $this->taxId,
            'name' => $this->taxName,
            'value' => $this->taxRate,
        ], static fn (mixed $value): bool => $value !== null);

        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'unit_price' => $this->unitPrice,
            'unit' => $this->unit,
            'tax' => $tax !== [] ? $tax : null,
            'observations' => $this->observations,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $tax = isset($data['tax']) && is_array($data['tax']) ? $data['tax'] : [];

        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            unit: isset($data['unit']) && is_string($data['unit']) ? $data['unit'] : null,
            taxId: isset($tax['id']) ? (int) $tax['id'] : null,
            taxName: isset($tax['name']) && is_string($tax['name']) ? $tax['name'] : null,
            taxRate: isset($tax['value']) ? (float) $tax['value'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
        );
    }
}
