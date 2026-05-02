<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\UnknownEndpointException;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use ReflectionMethod;

abstract class Resource
{
    public function __construct(
        protected readonly InvoiceExpressClient $client,
    ) {}

    /**
     * Resolves the endpoint metadata from the InvoiceExpressEndpoint attribute
     * on the calling method, then dispatches the request via the HTTP client.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, scalar>  $pathParameters
     * @return array<string, mixed>|string
     */
    protected function call(string $method, array $params = [], array $pathParameters = []): array|string
    {
        $reflection = new ReflectionMethod($this, $method);
        $attributes = $reflection->getAttributes(InvoiceExpressEndpoint::class);

        if ($attributes === []) {
            throw new UnknownEndpointException(
                "Method {$method} has no InvoiceExpressEndpoint attribute.",
            );
        }

        /** @var InvoiceExpressEndpoint $endpoint */
        $endpoint = $attributes[0]->newInstance();

        $result = $this->client->request(
            method: $endpoint->method,
            endpoint: $endpoint->path,
            params: $params,
            pathParameters: $pathParameters,
            expectsBinary: $endpoint->binary,
        );

        if ($endpoint->rootKey !== null && is_array($result) && array_key_exists($endpoint->rootKey, $result)) {
            /** @var array<string, mixed> $unwrapped */
            $unwrapped = $result[$endpoint->rootKey];

            return $unwrapped;
        }

        return $result;
    }
}
