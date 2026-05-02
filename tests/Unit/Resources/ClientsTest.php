<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Client as ClientDto;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;
use DigitaldevLx\LaravelInvoiceExpress\Events\ClientCreated;
use DigitaldevLx\LaravelInvoiceExpress\Events\ClientUpdated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('lists clients via GET clients.json', function (): void {
    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::response([
            'clients' => [
                ['id' => 1, 'name' => 'Acme Lda'],
                ['id' => 2, 'name' => 'Beta SA'],
            ],
        ]),
    ]);

    $result = InvoiceExpress::clients()->all();

    expect($result)->toHaveCount(2);
    Http::assertSent(static fn ($request): bool => $request->method() === 'GET'
        && str_contains($request->url(), 'clients.json')
        && str_contains($request->url(), 'api_key=test-api-key'));
});

it('finds a client by id', function (): void {
    Http::fake([
        '*invoicexpress.com/clients/42.json*' => Http::response([
            'client' => ['id' => 42, 'name' => 'Acme Lda'],
        ]),
    ]);

    $client = InvoiceExpress::clients()->find(42);

    expect($client['id'])->toBe(42);
    expect($client['name'])->toBe('Acme Lda');
});

it('creates a client and dispatches ClientCreated', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::response([
            'client' => ['id' => 100, 'name' => 'New Client'],
        ], 201),
    ]);

    $dto = new ClientDto(
        name: 'New Client',
        email: 'new@example.com',
        country: Country::PT,
    );

    $result = InvoiceExpress::clients()->create($dto);

    expect($result['id'])->toBe(100);
    Event::assertDispatched(ClientCreated::class);
});

it('updates a client and dispatches ClientUpdated', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/clients/7.json*' => Http::response([
            'client' => ['id' => 7, 'name' => 'Updated'],
        ]),
    ]);

    $result = InvoiceExpress::clients()->update(7, ['name' => 'Updated']);

    expect($result['name'])->toBe('Updated');
    Event::assertDispatched(ClientUpdated::class);
});
