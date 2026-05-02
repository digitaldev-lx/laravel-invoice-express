<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Sequence;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('creates a sequence', function (): void {
    Http::fake([
        '*invoicexpress.com/sequences.json*' => Http::response([
            'sequence' => ['id' => 1, 'serie' => '2026'],
        ], 201),
    ]);

    $result = InvoiceExpress::sequences()->create(
        new Sequence(serie: '2026', documentType: 'Invoice'),
    );

    expect($result)->toMatchArray(['id' => 1, 'serie' => '2026']);
});

it('marks a sequence as current', function (): void {
    Http::fake([
        '*invoicexpress.com/sequences/3/use_sequence.json*' => Http::response([]),
    ]);

    InvoiceExpress::sequences()->setCurrent(3);

    Http::assertSent(static fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/sequences/3/use_sequence.json'));
});

it('registers a sequence with the AT validation code', function (): void {
    Http::fake([
        '*invoicexpress.com/sequences/3/register.json*' => Http::response([]),
    ]);

    InvoiceExpress::sequences()->register(3, 'AAJ23K');

    Http::assertSent(static function ($request): bool {
        return $request->method() === 'PUT'
            && str_contains($request->url(), '/sequences/3/register.json');
    });
});
