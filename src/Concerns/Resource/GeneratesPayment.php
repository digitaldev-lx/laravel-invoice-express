<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Payment;
use DigitaldevLx\LaravelInvoiceExpress\Events\PaymentCanceled;
use DigitaldevLx\LaravelInvoiceExpress\Events\PaymentReceived;

trait GeneratesPayment
{
    /**
     * Register a payment against this document.
     *
     * @return array<string, mixed>
     */
    public function payment(int $id, Payment $payment): array
    {
        // Partial payments live under the generic `documents/` resource, not the
        // per-type root — `invoices/{id}/partial_payments.json` returns 404. See
        // the InvoiceXpress V2 partial-payments API. NOTE: unlike the PDF/QR/
        // related-documents fixes, this path is corrected from the API reference
        // and not yet exercised against a live account (it is a financial write).
        $endpoint = 'documents/{id}/partial_payments.json';

        $params = ['partial_payment' => $payment->toArray()];

        $result = $this->client->request(
            method: 'POST',
            endpoint: $endpoint,
            params: $params,
            pathParameters: ['id' => $id],
        );

        $data = is_array($result) ? $result : [];

        PaymentReceived::dispatch($data, $this->documentType(), $id, $payment);

        return $data;
    }

    /**
     * Cancel a previously-registered payment.
     *
     * @return array<string, mixed>
     */
    public function cancelPayment(int $id, int $paymentId, ?string $note = null): array
    {
        // Same generic `documents/` resource as payment() — see the note there.
        $endpoint = 'documents/{id}/partial_payments/{paymentId}/change-state.json';

        $params = [
            'partial_payment' => array_filter([
                'state' => 'canceled',
                'note' => $note,
            ], static fn (mixed $value): bool => $value !== null),
        ];

        $result = $this->client->request(
            method: 'PUT',
            endpoint: $endpoint,
            params: $params,
            pathParameters: ['id' => $id, 'paymentId' => $paymentId],
        );

        $data = is_array($result) ? $result : [];

        PaymentCanceled::dispatch($data, $this->documentType(), $id, $paymentId);

        return $data;
    }
}
