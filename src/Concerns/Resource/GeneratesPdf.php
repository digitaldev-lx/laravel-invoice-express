<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

use DigitaldevLx\LaravelInvoiceExpress\Events\PdfGenerated;

trait GeneratesPdf
{
    /**
     * Get a JSON envelope with a temporary PDF download URL (output.pdfUrl).
     *
     * @return array<string, mixed>
     */
    public function pdfUrl(int $id, bool $secondCopy = false): array
    {
        $params = $secondCopy ? ['second_copy' => 'true'] : [];

        // The PDF endpoint is a single, document-type agnostic path — unlike the
        // rest of the API it does NOT follow `{root}/{id}/...`. Building it from
        // endpointRoot() (invoices/{id}/pdf.json, receipts/{id}/pdf.json, …) hits a
        // path that does not exist: the API returns 404 even for issued documents.
        $result = $this->client->request(
            method: 'GET',
            endpoint: 'api/pdf/{id}.json',
            params: $params,
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Download the raw PDF binary for this document.
     *
     * Uses the configured timeout; throws PdfDownloadException on failure.
     */
    public function pdf(int $id, bool $secondCopy = false): string
    {
        $envelope = $this->pdfUrl($id, $secondCopy);

        $url = null;
        if (isset($envelope['output']['pdfUrl']) && is_string($envelope['output']['pdfUrl'])) {
            $url = $envelope['output']['pdfUrl'];
        } elseif (isset($envelope['pdfUrl']) && is_string($envelope['pdfUrl'])) {
            $url = $envelope['pdfUrl'];
        }

        if ($url === null) {
            return '';
        }

        $bytes = $this->client->download($url);

        PdfGenerated::dispatch($this->documentType(), $id, strlen($bytes), $this->client->accountName());

        return $bytes;
    }
}
