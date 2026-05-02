<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources\Documents;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ChangesState;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GeneratesPdf;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\HandlesRelatedDocuments;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\SendsByEmail;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\PurchaseOrder as PurchaseOrderDto;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;

class PurchaseOrders extends Document
{
    use ChangesState;
    use GeneratesPdf;
    use HandlesRelatedDocuments;
    use SendsByEmail;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'purchase_orders.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'purchase_orders/{id}.json', rootKey: 'purchase_order')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @param  PurchaseOrderDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(PurchaseOrderDto|array $data): array
    {
        $params = ['purchase_order' => $data instanceof PurchaseOrderDto ? $data->toArray() : $data];

        $result = $this->client->request(
            method: 'POST',
            endpoint: 'purchase_orders.json',
            params: $params,
        );

        $resultArray = is_array($result) ? $result : [];
        $unwrapped = $resultArray['purchase_order'] ?? $resultArray;
        /** @var array<string, mixed> $unwrappedArray */
        $unwrappedArray = is_array($unwrapped) ? $unwrapped : [];

        DocumentCreated::dispatch($unwrappedArray, DocumentType::PurchaseOrder);

        return $unwrappedArray;
    }

    /**
     * @param  PurchaseOrderDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'purchase_orders/{id}.json', rootKey: 'purchase_order')]
    public function update(int $id, PurchaseOrderDto|array $data): array
    {
        $params = ['purchase_order' => $data instanceof PurchaseOrderDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    protected function endpointRoot(): string
    {
        return 'purchase_orders';
    }

    protected function documentType(): DocumentType
    {
        return DocumentType::PurchaseOrder;
    }

    protected function statePayloadRootKey(): string
    {
        return 'purchase_order';
    }
}
