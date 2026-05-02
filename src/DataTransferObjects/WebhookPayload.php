<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\WebhookEvent;

final readonly class WebhookPayload implements DataTransferObject
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public WebhookEvent $event,
        public ?int $documentId,
        public ?DocumentType $documentType,
        public ?string $occurredAt,
        public array $payload,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event->value,
            'document_id' => $this->documentId,
            'document_type' => $this->documentType?->value,
            'occurred_at' => $this->occurredAt,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $event = isset($data['event']) && is_string($data['event'])
            ? (WebhookEvent::tryFrom($data['event']) ?? WebhookEvent::DocumentCreated)
            : WebhookEvent::DocumentCreated;

        $documentType = isset($data['document_type']) && is_string($data['document_type'])
            ? DocumentType::tryFrom($data['document_type'])
            : null;

        $payload = isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : $data;

        return new self(
            event: $event,
            documentId: isset($data['document_id']) ? (int) $data['document_id'] : null,
            documentType: $documentType,
            occurredAt: isset($data['occurred_at']) && is_string($data['occurred_at']) ? $data['occurred_at'] : null,
            payload: $payload,
        );
    }
}
