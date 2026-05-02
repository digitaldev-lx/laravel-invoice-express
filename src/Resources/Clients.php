<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsSingleResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ListsResources;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Client as ClientDto;
use DigitaldevLx\LaravelInvoiceExpress\Events\ClientCreated;
use DigitaldevLx\LaravelInvoiceExpress\Events\ClientUpdated;

final class Clients extends Resource
{
    use GetsSingleResource;
    use ListsResources;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'clients.json', rootKey: 'clients')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'clients/{id}.json', rootKey: 'client')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'clients/find-by-name.json', rootKey: 'client')]
    public function findByName(string $name): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, ['client_name' => $name]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'clients/find-by-code.json', rootKey: 'client')]
    public function findByCode(string $code): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, ['client_code' => $code]);

        return $result;
    }

    /**
     * @param  ClientDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'POST', path: 'clients.json', rootKey: 'client')]
    public function create(ClientDto|array $data): array
    {
        $params = ['client' => $data instanceof ClientDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params);

        event(new ClientCreated($result));

        return $result;
    }

    /**
     * @param  ClientDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'clients/{id}.json', rootKey: 'client')]
    public function update(int $id, ClientDto|array $data): array
    {
        $params = ['client' => $data instanceof ClientDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        event(new ClientUpdated($result));

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'clients/{id}/invoices.json')]
    public function invoices(int $id, array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters, ['id' => $id]);

        return $result;
    }
}
