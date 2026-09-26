<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentState;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentDeleted;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentPaid;

trait ChangesState
{
    /**
     * Change a document's state.
     *
     * InvoiceXpress exposes change-state under the per-type root
     * (`invoices/`, `credit_notes/`, `invoice_receipts/`, ...). Pass `$type`
     * for anything that is not a plain invoice; omitted, the resource's own
     * root is used (unchanged behaviour).
     *
     * @return array<string, mixed>
     */
    public function changeState(int $id, DocumentState $state, ?string $message = null, ?DocumentType $type = null): array
    {
        $payloadKey = $this->statePayloadRootKey();
        $params = [
            $payloadKey => array_filter([
                'state' => $state->apiAction(),
                'message' => $message,
            ], static fn (mixed $value): bool => $value !== null),
        ];

        $endpoint = sprintf('%s/{id}/change-state.json', $type?->endpointRoot() ?? $this->endpointRoot());
        $eventType = $type ?? $this->documentType();
        $account = $this->client->accountName();

        $result = $this->client->request(
            method: 'PUT',
            endpoint: $endpoint,
            params: $params,
            pathParameters: ['id' => $id],
        );

        $data = is_array($result) ? $result : [];

        match ($state) {
            DocumentState::Final => DocumentFinalized::dispatch($data, $eventType, $id, $account),
            DocumentState::Settled => DocumentPaid::dispatch($data, $eventType, $id, $account),
            DocumentState::Canceled => DocumentCanceled::dispatch($data, $eventType, $id, $message, $account),
            DocumentState::Deleted => DocumentDeleted::dispatch($data, $eventType, $id, $account),
            default => null,
        };

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function finalize(int $id, ?string $message = null, ?DocumentType $type = null): array
    {
        return $this->changeState($id, DocumentState::Final, $message, $type);
    }

    /**
     * @return array<string, mixed>
     */
    public function cancel(int $id, ?string $message = null, ?DocumentType $type = null): array
    {
        return $this->changeState($id, DocumentState::Canceled, $message, $type);
    }

    /**
     * @return array<string, mixed>
     */
    public function settle(int $id, ?string $message = null, ?DocumentType $type = null): array
    {
        return $this->changeState($id, DocumentState::Settled, $message, $type);
    }

    protected function statePayloadRootKey(): string
    {
        return rtrim($this->endpointRoot(), 's');
    }
}
