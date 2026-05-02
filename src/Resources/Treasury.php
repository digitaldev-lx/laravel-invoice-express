<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\DeletesResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsSingleResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ListsResources;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\TreasuryMovement;

final class Treasury extends Resource
{
    use DeletesResource;
    use GetsSingleResource;
    use ListsResources;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'treasury_movements.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'treasury_movements/{id}.json', rootKey: 'treasury_movement')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @param  TreasuryMovement|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'POST', path: 'treasury_movements.json', rootKey: 'treasury_movement')]
    public function create(TreasuryMovement|array $data): array
    {
        $params = ['treasury_movement' => $data instanceof TreasuryMovement ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params);

        return $result;
    }

    /**
     * @param  TreasuryMovement|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'treasury_movements/{id}.json', rootKey: 'treasury_movement')]
    public function update(int $id, TreasuryMovement|array $data): array
    {
        $params = ['treasury_movement' => $data instanceof TreasuryMovement ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'DELETE', path: 'treasury_movements/{id}.json')]
    public function delete(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'treasury_movement_categories.json')]
    public function categories(): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'treasury_accounts.json')]
    public function accounts(): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__);

        return $result;
    }
}
