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
        // Like the PDF endpoint, the QR code lives at a single, document-type
        // agnostic path — not `{root}/{id}/qr_code.json`, which returns 404.
        // The response envelope is `{ "qr_code": { "url": ... } }`.
        $result = $this->client->request(
            method: 'GET',
            endpoint: 'api/qr_codes/{id}.json',
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }
}
