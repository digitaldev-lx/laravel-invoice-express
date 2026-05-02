<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Facades\InvoiceExpress as InvoiceExpressFacade;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;
use DigitaldevLx\LaravelInvoiceExpress\Resources\Clients;
use DigitaldevLx\LaravelInvoiceExpress\Resources\Documents\Invoices;
use DigitaldevLx\LaravelInvoiceExpress\Resources\Items;

it('binds the manager as a singleton', function (): void {
    $first = app(InvoiceExpress::class);
    $second = app(InvoiceExpress::class);

    expect($first)->toBe($second);
});

it('binds the http client as a singleton', function (): void {
    $first = app(InvoiceExpressClient::class);
    $second = app(InvoiceExpressClient::class);

    expect($first)->toBe($second);
});

it('exposes the resource accessors', function (): void {
    expect(InvoiceExpressFacade::clients())->toBeInstanceOf(Clients::class);
    expect(InvoiceExpressFacade::items())->toBeInstanceOf(Items::class);
    expect(InvoiceExpressFacade::invoices())->toBeInstanceOf(Invoices::class);
});

it('caches resource instances within the manager', function (): void {
    $manager = app(InvoiceExpress::class);

    expect($manager->clients())->toBe($manager->clients());
});
