<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentState;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentDeleted;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentPaid;

trait ChangesState
{
    /**
     * @return array<string, mixed>
     */
    public function changeState(int $id, DocumentState $state, ?string $message = null): array
    {
        $payloadKey = $this->statePayloadRootKey();
        $params = [
            $payloadKey => array_filter([
                'state' => $state->apiAction(),
                'message' => $message,
            ], static fn (mixed $value): bool => $value !== null),
        ];

        $endpoint = sprintf('%s/{id}/change-state.json', $this->endpointRoot());

        $result = $this->client->request(
            method: 'PUT',
            endpoint: $endpoint,
            params: $params,
            pathParameters: ['id' => $id],
        );

        $data = is_array($result) ? $result : [];

        match ($state) {
            DocumentState::Final => DocumentFinalized::dispatch($data, $this->documentType(), $id),
            DocumentState::Settled => DocumentPaid::dispatch($data, $this->documentType(), $id),
            DocumentState::Canceled => DocumentCanceled::dispatch($data, $this->documentType(), $id, $message),
            DocumentState::Deleted => DocumentDeleted::dispatch($data, $this->documentType(), $id),
            default => null,
        };

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function finalize(int $id, ?string $message = null): array
    {
        return $this->changeState($id, DocumentState::Final, $message);
    }

    /**
     * @return array<string, mixed>
     */
    public function cancel(int $id, ?string $message = null): array
    {
        return $this->changeState($id, DocumentState::Canceled, $message);
    }

    /**
     * @return array<string, mixed>
     */
    public function settle(int $id, ?string $message = null): array
    {
        return $this->changeState($id, DocumentState::Settled, $message);
    }

    protected function statePayloadRootKey(): string
    {
        return rtrim($this->endpointRoot(), 's');
    }
}
