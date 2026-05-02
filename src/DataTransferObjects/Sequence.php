<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class Sequence implements DataTransferObject
{
    public function __construct(
        public string $serie,
        public ?string $documentType = null,
        public ?int $currentSequenceNumber = null,
        public ?bool $defaultSequence = null,
        public ?bool $current = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'serie' => $this->serie,
            'document_type' => $this->documentType,
            'current_sequence_number' => $this->currentSequenceNumber,
            'default_sequence' => $this->defaultSequence,
            'current' => $this->current,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            serie: isset($data['serie']) && is_string($data['serie']) ? $data['serie'] : '',
            documentType: isset($data['document_type']) && is_string($data['document_type']) ? $data['document_type'] : null,
            currentSequenceNumber: isset($data['current_sequence_number']) ? (int) $data['current_sequence_number'] : null,
            defaultSequence: isset($data['default_sequence']) ? (bool) $data['default_sequence'] : null,
            current: isset($data['current']) ? (bool) $data['current'] : null,
        );
    }
}
