<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\DeletesResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsSingleResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ListsResources;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Account as AccountDto;

final class Accounts extends Resource
{
    use DeletesResource;
    use GetsSingleResource;
    use ListsResources;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'accounts.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'accounts/{id}.json', rootKey: 'account')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @param  AccountDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'POST', path: 'accounts.json', rootKey: 'account')]
    public function create(AccountDto|array $data): array
    {
        $params = ['account' => $data instanceof AccountDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params);

        return $result;
    }

    /**
     * @param  AccountDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'accounts/{id}.json', rootKey: 'account')]
    public function update(int $id, AccountDto|array $data): array
    {
        $params = ['account' => $data instanceof AccountDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'DELETE', path: 'accounts/{id}.json')]
    public function delete(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }
}
