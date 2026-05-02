<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Invoice as InvoiceDto;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Payment;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;

/**
 * Trait that gives an Eloquent model first-class shortcuts to InvoiceXpress
 * invoices linked to it. The host model is expected to expose the columns:
 *
 *  - invoiceexpress_document_id  (bigint, nullable)
 *  - invoiceexpress_document_type (string, nullable)
 *  - invoiceexpress_state (string, nullable)
 *  - invoiceexpress_account_name (string, nullable)
 *
 * Callers can opt out of any specific column by overriding the corresponding
 * method below.
 */
trait HasInvoiceExpressDocuments
{
    /**
     * @param  InvoiceDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createInvoiceXpressInvoice(InvoiceDto|array $data, ?DocumentType $type = null): array
    {
        $result = InvoiceExpress::invoices()->create($data, $type);

        $documentType = $type ?? ($data instanceof InvoiceDto ? $data->type : DocumentType::Invoice);

        $this->forceFill([
            'invoiceexpress_document_id' => isset($result['id']) ? (int) $result['id'] : null,
            'invoiceexpress_document_type' => $documentType->value,
            'invoiceexpress_state' => isset($result['status']) && is_string($result['status'])
                ? $result['status']
                : 'draft',
            'invoiceexpress_account_name' => InvoiceExpress::client()->accountName(),
        ])->save();

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function finalizeInvoiceXpress(): array
    {
        $id = (int) $this->invoiceexpress_document_id;

        $result = InvoiceExpress::invoices()->finalize($id);

        $this->forceFill(['invoiceexpress_state' => 'final'])->save();

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function settleInvoiceXpress(?Payment $payment = null): array
    {
        $id = (int) $this->invoiceexpress_document_id;

        $result = $payment !== null
            ? InvoiceExpress::invoices()->payment($id, $payment)
            : InvoiceExpress::invoices()->settle($id);

        $this->forceFill(['invoiceexpress_state' => 'settled'])->save();

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelInvoiceXpress(?string $reason = null): array
    {
        $id = (int) $this->invoiceexpress_document_id;

        $result = InvoiceExpress::invoices()->cancel($id, $reason);

        $this->forceFill(['invoiceexpress_state' => 'canceled'])->save();

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function emailInvoiceXpress(EmailMessage $email): array
    {
        return InvoiceExpress::invoices()->email((int) $this->invoiceexpress_document_id, $email);
    }

    public function downloadInvoiceXpressPdf(): string
    {
        return InvoiceExpress::invoices()->pdf((int) $this->invoiceexpress_document_id);
    }

    public function invoiceXpressDocumentId(): ?int
    {
        return $this->invoiceexpress_document_id !== null
            ? (int) $this->invoiceexpress_document_id
            : null;
    }

    public function invoiceXpressIsFinalized(): bool
    {
        return $this->invoiceexpress_state === 'final';
    }

    public function invoiceXpressIsPaid(): bool
    {
        return $this->invoiceexpress_state === 'settled';
    }

    public function invoiceXpressIsCanceled(): bool
    {
        return $this->invoiceexpress_state === 'canceled';
    }
}
