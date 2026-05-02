<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\Events\EmailSent;

trait SendsByEmail
{
    /**
     * @return array<string, mixed>
     */
    public function email(int $id, EmailMessage $message): array
    {
        $endpoint = sprintf('%s/{id}/email-document.json', $this->endpointRoot());

        $params = ['message' => $message->toArray()['client'] ?? $message->toArray()];

        $result = $this->client->request(
            method: 'PUT',
            endpoint: $endpoint,
            params: $params,
            pathParameters: ['id' => $id],
        );

        $data = is_array($result) ? $result : [];

        EmailSent::dispatch($data, $this->documentType(), $id, $message);

        return $data;
    }
}
