<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\WebhookPayload;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\WebhookEvent;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentDeleted;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentPaid;
use DigitaldevLx\LaravelInvoiceExpress\Events\WebhookReceived;

final class WebhookHandler
{
    public function dispatch(WebhookPayload $payload): void
    {
        WebhookReceived::dispatch($payload);

        $documentType = $payload->documentType ?? DocumentType::Invoice;
        $documentId = $payload->documentId ?? 0;

        match ($payload->event) {
            WebhookEvent::DocumentCreated => DocumentCreated::dispatch($payload->payload, $documentType),
            WebhookEvent::DocumentFinalized => DocumentFinalized::dispatch($payload->payload, $documentType, $documentId),
            WebhookEvent::DocumentPaid => DocumentPaid::dispatch($payload->payload, $documentType, $documentId),
            WebhookEvent::DocumentCanceled => DocumentCanceled::dispatch($payload->payload, $documentType, $documentId, null),
            WebhookEvent::DocumentDeleted => DocumentDeleted::dispatch($payload->payload, $documentType, $documentId),
        };
    }
}
