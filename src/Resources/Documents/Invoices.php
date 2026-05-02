<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources\Documents;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ChangesState;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GeneratesPayment;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GeneratesPdf;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsQrCode;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\HandlesRelatedDocuments;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\SendsByEmail;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Invoice as InvoiceDto;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;

class Invoices extends Document
{
    use ChangesState;
    use GeneratesPayment;
    use GeneratesPdf;
    use GetsQrCode;
    use HandlesRelatedDocuments;
    use SendsByEmail;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'invoices.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'invoices/{id}.json', rootKey: 'invoice')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * Create an invoice (or any other invoice-like document via the optional $type override).
     *
     * @param  InvoiceDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(InvoiceDto|array $data, ?DocumentType $type = null): array
    {
        $invoice = $data instanceof InvoiceDto ? $data : InvoiceDto::fromArray($data);
        $effectiveType = $type ?? $invoice->type;

        $rootKey = $this->payloadRootKey($effectiveType);
        $params = [$rootKey => $data instanceof InvoiceDto ? $data->toArray() : $data];

        $result = $this->client->request(
            method: 'POST',
            endpoint: $effectiveType->endpointRoot().'.json',
            params: $params,
        );

        if (! is_array($result)) {
            $result = [];
        }

        $unwrapped = $result[$rootKey] ?? $result;
        /** @var array<string, mixed> $unwrappedArray */
        $unwrappedArray = is_array($unwrapped) ? $unwrapped : [];

        DocumentCreated::dispatch($unwrappedArray, $effectiveType);

        return $unwrappedArray;
    }

    /**
     * Update an invoice in place.
     *
     * @param  InvoiceDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'invoices/{id}.json', rootKey: 'invoice')]
    public function update(int $id, InvoiceDto|array $data): array
    {
        $params = ['invoice' => $data instanceof InvoiceDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    protected function endpointRoot(): string
    {
        return 'invoices';
    }

    protected function documentType(): DocumentType
    {
        return DocumentType::Invoice;
    }

    protected function statePayloadRootKey(): string
    {
        return 'invoice';
    }

    private function payloadRootKey(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::Invoice => 'invoice',
            DocumentType::SimplifiedInvoice => 'simplified_invoice',
            DocumentType::InvoiceReceipt => 'invoice_receipt',
            DocumentType::CreditNote => 'credit_note',
            DocumentType::DebitNote => 'debit_note',
            DocumentType::Receipt => 'receipt',
            DocumentType::CashInvoice => 'cash_invoice',
            DocumentType::VatMossInvoice => 'vat_moss_invoice',
            default => 'invoice',
        };
    }
}
