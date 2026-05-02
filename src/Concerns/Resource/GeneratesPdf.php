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

        $endpoint = sprintf('%s/%d/pdf.json', $this->endpointRoot(), $id);

        $result = $this->client->request(
            method: 'GET',
            endpoint: $endpoint,
            params: $params,
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Download the raw PDF binary for this document.
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

        $bytes = (string) file_get_contents($url);

        PdfGenerated::dispatch($this->documentType(), $id, strlen($bytes));

        return $bytes;
    }
}
