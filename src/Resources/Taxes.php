<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\DeletesResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsSingleResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ListsResources;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Tax as TaxDto;

final class Taxes extends Resource
{
    use DeletesResource;
    use GetsSingleResource;
    use ListsResources;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'taxes.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'taxes/{id}.json', rootKey: 'tax')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @param  TaxDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'POST', path: 'taxes.json', rootKey: 'tax')]
    public function create(TaxDto|array $data): array
    {
        $params = ['tax' => $data instanceof TaxDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params);

        return $result;
    }

    /**
     * @param  TaxDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'taxes/{id}.json', rootKey: 'tax')]
    public function update(int $id, TaxDto|array $data): array
    {
        $params = ['tax' => $data instanceof TaxDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'DELETE', path: 'taxes/{id}.json')]
    public function delete(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }
}
