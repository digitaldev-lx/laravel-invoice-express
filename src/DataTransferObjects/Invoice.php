<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;

final readonly class Invoice implements DataTransferObject
{
    /**
     * @param  array<int, DocumentItem>|null  $items
     * @param  array<string, mixed>|null  $client
     * @param  array<string, mixed>|null  $extra
     */
    public function __construct(
        public DocumentType $type = DocumentType::Invoice,
        public ?string $date = null,
        public ?string $dueDate = null,
        public ?string $reference = null,
        public ?string $observations = null,
        public ?string $retention = null,
        public ?string $taxExemption = null,
        public ?int $sequenceId = null,
        public ?int $manualSequenceNumber = null,
        public ?array $items = null,
        public ?array $client = null,
        public ?string $mbReference = null,
        public ?string $owner = null,
        public ?array $extra = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $base = array_filter([
            'date' => $this->date,
            'due_date' => $this->dueDate,
            'reference' => $this->reference,
            'observations' => $this->observations,
            'retention' => $this->retention,
            'tax_exemption' => $this->taxExemption,
            'sequence_id' => $this->sequenceId,
            'manual_sequence_number' => $this->manualSequenceNumber,
            'items' => $this->items === null
                ? null
                : array_map(static fn (DocumentItem $item): array => $item->toArray(), $this->items),
            'client' => $this->client,
            'mb_reference' => $this->mbReference,
            'owner_name' => $this->owner,
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
        $rawItems = $data['items'] ?? null;
        if (is_array($rawItems)) {
            // Accept both the plain JSON array [ {...} ] the API now receives and the
            // legacy { "item": [ {...} ] } envelope some responses may still return.
            $list = array_is_list($rawItems) ? $rawItems : ($rawItems['item'] ?? null);

            if (is_array($list)) {
                $items = array_map(
                    static fn (array $item): DocumentItem => DocumentItem::fromArray($item),
                    $list,
                );
            }
        }

        $type = isset($data['type']) && is_string($data['type'])
            ? (DocumentType::tryFrom($data['type']) ?? DocumentType::Invoice)
            : DocumentType::Invoice;

        return new self(
            type: $type,
            date: isset($data['date']) && is_string($data['date']) ? $data['date'] : null,
            dueDate: isset($data['due_date']) && is_string($data['due_date']) ? $data['due_date'] : null,
            reference: isset($data['reference']) && is_string($data['reference']) ? $data['reference'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
            retention: isset($data['retention']) && is_string($data['retention']) ? $data['retention'] : null,
            taxExemption: isset($data['tax_exemption']) && is_string($data['tax_exemption']) ? $data['tax_exemption'] : null,
            sequenceId: isset($data['sequence_id']) ? (int) $data['sequence_id'] : null,
            manualSequenceNumber: isset($data['manual_sequence_number']) ? (int) $data['manual_sequence_number'] : null,
            items: $items,
            client: isset($data['client']) && is_array($data['client']) ? $data['client'] : null,
            mbReference: isset($data['mb_reference']) && is_string($data['mb_reference']) ? $data['mb_reference'] : null,
            owner: isset($data['owner_name']) && is_string($data['owner_name']) ? $data['owner_name'] : null,
        );
    }
}
