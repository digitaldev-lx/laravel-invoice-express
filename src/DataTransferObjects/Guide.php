<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\GuideType;

final readonly class Guide implements DataTransferObject
{
    /**
     * @param  array<int, DocumentItem>|null  $items
     * @param  array<string, mixed>|null  $client
     * @param  array<string, mixed>|null  $extra
     */
    public function __construct(
        public GuideType $type = GuideType::Transport,
        public ?string $date = null,
        public ?string $loadedAt = null,
        public ?string $loadedFrom = null,
        public ?string $loadedTo = null,
        public ?string $vehicleRegistration = null,
        public ?string $reference = null,
        public ?string $observations = null,
        public ?int $sequenceId = null,
        public ?int $manualSequenceNumber = null,
        public ?array $items = null,
        public ?array $client = null,
        public ?array $extra = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $base = array_filter([
            'date' => $this->date,
            'loaded_at' => $this->loadedAt,
            'loaded_from' => $this->loadedFrom,
            'loaded_to' => $this->loadedTo,
            'vehicle_registration' => $this->vehicleRegistration,
            'reference' => $this->reference,
            'observations' => $this->observations,
            'sequence_id' => $this->sequenceId,
            'manual_sequence_number' => $this->manualSequenceNumber,
            'items' => $this->items === null
                ? null
                : ['item' => array_map(static fn (DocumentItem $item): array => $item->toArray(), $this->items)],
            'client' => $this->client,
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

        $type = isset($data['type']) && is_string($data['type'])
            ? (GuideType::tryFrom($data['type']) ?? GuideType::Transport)
            : GuideType::Transport;

        return new self(
            type: $type,
            date: isset($data['date']) && is_string($data['date']) ? $data['date'] : null,
            loadedAt: isset($data['loaded_at']) && is_string($data['loaded_at']) ? $data['loaded_at'] : null,
            loadedFrom: isset($data['loaded_from']) && is_string($data['loaded_from']) ? $data['loaded_from'] : null,
            loadedTo: isset($data['loaded_to']) && is_string($data['loaded_to']) ? $data['loaded_to'] : null,
            vehicleRegistration: isset($data['vehicle_registration']) && is_string($data['vehicle_registration']) ? $data['vehicle_registration'] : null,
            reference: isset($data['reference']) && is_string($data['reference']) ? $data['reference'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
            sequenceId: isset($data['sequence_id']) ? (int) $data['sequence_id'] : null,
            manualSequenceNumber: isset($data['manual_sequence_number']) ? (int) $data['manual_sequence_number'] : null,
            items: $items,
            client: isset($data['client']) && is_array($data['client']) ? $data['client'] : null,
        );
    }
}
