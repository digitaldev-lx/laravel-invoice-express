<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class PurchaseOrder implements DataTransferObject
{
    /**
     * @param  array<int, DocumentItem>|null  $items
     * @param  array<string, mixed>|null  $supplier
     * @param  array<string, mixed>|null  $extra
     */
    public function __construct(
        public ?string $date = null,
        public ?string $deliveryDate = null,
        public ?string $reference = null,
        public ?string $observations = null,
        public ?int $sequenceId = null,
        public ?int $manualSequenceNumber = null,
        public ?array $items = null,
        public ?array $supplier = null,
        public ?array $extra = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $base = array_filter([
            'date' => $this->date,
            'delivery_date' => $this->deliveryDate,
            'reference' => $this->reference,
            'observations' => $this->observations,
            'sequence_id' => $this->sequenceId,
            'manual_sequence_number' => $this->manualSequenceNumber,
            'items' => $this->items === null
                ? null
                : ['item' => array_map(static fn (DocumentItem $item): array => $item->toArray(), $this->items)],
            'supplier' => $this->supplier,
        ], static fn (mixed $value): bool => $value !== null);

        if ($this->extra !== null) {
            $base = [...$base, ...$this->extra];
        }

        return $base;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $items = null;
        if (isset($data['items']['item']) && is_array($data['items']['item'])) {
            $items = array_map(
                static fn (array $item): DocumentItem => DocumentItem::fromArray($item),
                $data['items']['item'],
            );
        }

        return new self(
            date: isset($data['date']) && is_string($data['date']) ? $data['date'] : null,
            deliveryDate: isset($data['delivery_date']) && is_string($data['delivery_date']) ? $data['delivery_date'] : null,
            reference: isset($data['reference']) && is_string($data['reference']) ? $data['reference'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
            sequenceId: isset($data['sequence_id']) ? (int) $data['sequence_id'] : null,
            manualSequenceNumber: isset($data['manual_sequence_number']) ? (int) $data['manual_sequence_number'] : null,
            items: $items,
            supplier: isset($data['supplier']) && is_array($data['supplier']) ? $data['supplier'] : null,
        );
    }
}
