<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Support\Decimals;

final readonly class DocumentItem implements DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public string $quantity = '1',
        public ?string $unitPrice = null,
        public ?string $discount = null,
        public ?string $unit = null,
        public ?Tax $tax = null,
    ) {}

    /**
     * The VAT exemption reason travels as `tax_exemption` on the LINE, not as
     * `exemption_code` nested inside `tax` — the API ignores the nested form and then
     * rejects the document with "Razão de isenção deve ter uma opção selecionada",
     * even when the code is also set at document level.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $tax = $this->tax?->toArray();
        $taxExemption = null;

        if ($tax !== null && isset($tax['exemption_code'])) {
            $taxExemption = $tax['exemption_code'];
            unset($tax['exemption_code']);
        }

        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'discount' => $this->discount,
            'unit' => $this->unit,
            'tax_exemption' => $taxExemption,
            'tax' => $tax,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $rawTax = isset($data['tax']) && is_array($data['tax']) ? $data['tax'] : null;

        // Round-trip the line-level `tax_exemption` back into the Tax DTO.
        if ($rawTax !== null && ! isset($rawTax['exemption_code']) && isset($data['tax_exemption']) && is_string($data['tax_exemption'])) {
            $rawTax['exemption_code'] = $data['tax_exemption'];
        }

        $tax = $rawTax !== null ? Tax::fromArray($rawTax) : null;

        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            quantity: Decimals::toString($data['quantity'] ?? null) ?? '1',
            unitPrice: Decimals::toString($data['unit_price'] ?? null),
            discount: Decimals::toString($data['discount'] ?? null),
            unit: isset($data['unit']) && is_string($data['unit']) ? $data['unit'] : null,
            tax: $tax,
        );
    }
}
