<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsSingleResource;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ListsResources;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Sequence as SequenceDto;

final class Sequences extends Resource
{
    use GetsSingleResource;
    use ListsResources;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'sequences.json')]
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $filters);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'sequences/{id}.json', rootKey: 'sequence')]
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * @param  SequenceDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'POST', path: 'sequences.json', rootKey: 'sequence')]
    public function create(SequenceDto|array $data): array
    {
        $params = ['sequence' => $data instanceof SequenceDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params);

        return $result;
    }

    /**
     * @param  SequenceDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'sequences/{id}.json', rootKey: 'sequence')]
    public function update(int $id, SequenceDto|array $data): array
    {
        $params = ['sequence' => $data instanceof SequenceDto ? $data->toArray() : $data];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }

    /**
     * Mark a sequence as the current/default for its document type.
     *
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'sequences/{id}/use_sequence.json')]
    public function setCurrent(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, [], ['id' => $id]);

        return $result;
    }

    /**
     * Register a previously-validated sequence with the AT.
     *
     * @return array<string, mixed>
     */
    #[InvoiceExpressEndpoint(method: 'PUT', path: 'sequences/{id}/register.json')]
    public function register(int $id, ?string $atValidationCode = null): array
    {
        $params = $atValidationCode !== null
            ? ['sequence' => ['at_validation_code' => $atValidationCode]]
            : [];

        /** @var array<string, mixed> $result */
        $result = $this->call(__FUNCTION__, $params, ['id' => $id]);

        return $result;
    }
}
