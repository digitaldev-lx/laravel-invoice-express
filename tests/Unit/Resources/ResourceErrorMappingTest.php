<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\AuthenticationException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\BadRequestException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\NotFoundException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\RateLimitException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ServerException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ValidationException;
use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress;
use Illuminate\Support\Facades\Http;

it('maps HTTP 401 to AuthenticationException', function (): void {
    Http::fake(['*invoicexpress.com/clients.json*' => Http::response([], 401)]);

    InvoiceExpress::clients()->all();
})->throws(AuthenticationException::class);

it('maps HTTP 404 to NotFoundException', function (): void {
    Http::fake(['*invoicexpress.com/clients/999.json*' => Http::response([], 404)]);

    InvoiceExpress::clients()->find(999);
})->throws(NotFoundException::class);

it('maps HTTP 422 to ValidationException with field errors', function (): void {
    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::response([
            'errors' => [
                'name' => ["can't be blank"],
                'fiscal_id' => ['is invalid'],
            ],
        ], 422),
    ]);

    try {
        InvoiceExpress::clients()->create(['name' => '']);
        expect(false)->toBeTrue('Expected ValidationException to be thrown.');
    } catch (ValidationException $e) {
        expect($e->hasFieldError('name'))->toBeTrue();
        expect($e->getFieldErrors())->toHaveKey('fiscal_id');
    }
});

it('maps HTTP 400 to BadRequestException', function (): void {
    Http::fake(['*invoicexpress.com/clients.json*' => Http::response('bad payload', 400)]);

    InvoiceExpress::clients()->create([]);
})->throws(BadRequestException::class);

it('maps HTTP 429 to RateLimitException carrying retry-after', function (): void {
    Http::fake([
        '*invoicexpress.com/clients.json*' => Http::response('rate limited', 429, [
            'Retry-After' => '120',
        ]),
    ]);

    try {
        InvoiceExpress::clients()->all();
        expect(false)->toBeTrue('Expected RateLimitException to be thrown.');
    } catch (RateLimitException $e) {
        expect($e->retryAfter)->toBe(120);
    }
});

it('maps HTTP 5xx to ServerException', function (): void {
    Http::fake(['*invoicexpress.com/clients.json*' => Http::response('boom', 503)]);

    InvoiceExpress::clients()->all();
})->throws(ServerException::class);
