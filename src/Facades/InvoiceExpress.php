<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient client()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress useAccount(string $accountName, string $apiKey)
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Clients clients()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Items items()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Sequences sequences()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Taxes taxes()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Accounts accounts()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Treasury treasury()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Saft saft()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Documents\Invoices invoices()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Documents\Estimates estimates()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Documents\Guides guides()
 * @method static \DigitaldevLx\LaravelInvoiceExpress\Resources\Documents\PurchaseOrders purchaseOrders()
 *
 * @see \DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress
 */
final class InvoiceExpress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress::class;
    }
}
