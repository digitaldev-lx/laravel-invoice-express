<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

trait GetsQrCode
{
    /**
     * @return array<string, mixed>
     */
    public function qrCode(int $id): array
    {
        $endpoint = sprintf('%s/{id}/qr_code.json', $this->endpointRoot());

        $result = $this->client->request(
            method: 'GET',
            endpoint: $endpoint,
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }
}
