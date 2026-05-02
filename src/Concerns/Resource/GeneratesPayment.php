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
        $endpoint = sprintf('%s/{id}/partial_payments.json', $this->endpointRoot());

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
        $endpoint = sprintf('%s/{id}/partial_payments/{paymentId}/change-state.json', $this->endpointRoot());

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
