<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\UnknownEndpointException;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use DigitaldevLx\LaravelInvoiceExpress\Resources\Resource;

it('throws UnknownEndpointException when the called method has no attribute', function (): void {
    $client = new InvoiceExpressClient(accountName: 'co', apiKey: 'k', retryTimes: 0);

    $resource = new class($client) extends Resource
    {
        public function unknown(): array
        {
            /** @var array<string, mixed> */
            return $this->call('unknown');
        }
    };

    $resource->unknown();
})->throws(UnknownEndpointException::class);
