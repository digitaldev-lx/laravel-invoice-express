<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\WebhookPayload;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\WebhookEvent;

it('hydrates a webhook payload', function (): void {
    $payload = WebhookPayload::fromArray([
        'event' => 'document.paid',
        'document_id' => 42,
        'document_type' => 'Invoice',
        'occurred_at' => '2026-05-01T10:00:00Z',
        'payload' => ['amount' => 100.0],
    ]);

    expect($payload->event)->toBe(WebhookEvent::DocumentPaid);
    expect($payload->documentId)->toBe(42);
    expect($payload->documentType)->toBe(DocumentType::Invoice);
    expect($payload->payload)->toBe(['amount' => 100.0]);
});

it('falls back gracefully when document_type is unknown', function (): void {
    $payload = WebhookPayload::fromArray([
        'event' => 'document.created',
        'document_type' => 'BogusType',
    ]);

    expect($payload->documentType)->toBeNull();
});

it('serialises back to a snake_cased array', function (): void {
    $payload = new WebhookPayload(
        event: WebhookEvent::DocumentCanceled,
        documentId: 9,
        documentType: DocumentType::Invoice,
        occurredAt: null,
        payload: ['reason' => 'X'],
    );

    expect($payload->toArray())->toBe([
        'event' => 'document.canceled',
        'document_id' => 9,
        'document_type' => 'Invoice',
        'occurred_at' => null,
        'payload' => ['reason' => 'X'],
    ]);
});
