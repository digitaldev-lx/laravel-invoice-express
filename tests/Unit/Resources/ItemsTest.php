<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Item as ItemDto;
use DigitaldevLx\LaravelInvoiceExpress\Events\ItemCreated;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('creates an item via POST items.json', function (): void {
    Event::fake();

    Http::fake([
        '*invoicexpress.com/items.json*' => Http::response([
            'item' => ['id' => 5, 'name' => 'Widget'],
        ], 201),
    ]);

    $result = InvoiceExpress::items()->create(new ItemDto(
        name: 'Widget',
        description: 'A small widget',
        unitPrice: '9.99',
    ));

    expect($result['id'])->toBe(5);
    Event::assertDispatched(ItemCreated::class);
});

it('deletes an item via DELETE items/{id}.json', function (): void {
    Http::fake([
        '*invoicexpress.com/items/3.json*' => Http::response([], 204),
    ]);

    InvoiceExpress::items()->delete(3);

    Http::assertSent(static fn ($request): bool => $request->method() === 'DELETE'
        && str_contains($request->url(), 'items/3.json'));
});
