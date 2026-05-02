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
        $endpoint = sprintf('%s/{id}/related_documents.json', $this->endpointRoot());

        $result = $this->client->request(
            method: 'GET',
            endpoint: $endpoint,
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }
}
