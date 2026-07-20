<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

trait HandlesRelatedDocuments
{
    /**
     * @return array<string, mixed>
     */
    public function relatedDocuments(int $id): array
    {
        // Endpoint is `document/{id}/related_documents.json` — literal singular
        // `document`, not the resource root (`invoices`, `estimates`, …). Building
        // it from endpointRoot() returns 404. Response envelope is
        // `{ "documents": [ … ] }` (not `related_documents`).
        $result = $this->client->request(
            method: 'GET',
            endpoint: 'document/{id}/related_documents.json',
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }
}
