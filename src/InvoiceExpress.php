<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress;

use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;

final class InvoiceExpress
{
    /** @var array<class-string<Resources\Resource>, Resources\Resource> */
    private array $resources = [];

    public function __construct(
        private InvoiceExpressClient $client,
    ) {}

    public function client(): InvoiceExpressClient
    {
        return $this->client;
    }

    /**
     * Switch to a different InvoiceXpress account at runtime.
     *
     * Returns a clone of the manager bound to the new account, leaving the
     * default singleton untouched.
     */
    public function useAccount(string $accountName, string $apiKey): self
    {
        $clone = clone $this;
        $clone->client = $this->client->useAccount($accountName, $apiKey);
        $clone->resources = [];

        return $clone;
    }

    public function clients(): Resources\Clients
    {
        return $this->resolve(Resources\Clients::class);
    }

    public function items(): Resources\Items
    {
        return $this->resolve(Resources\Items::class);
    }

    public function sequences(): Resources\Sequences
    {
        return $this->resolve(Resources\Sequences::class);
    }

    public function taxes(): Resources\Taxes
    {
        return $this->resolve(Resources\Taxes::class);
    }

    public function accounts(): Resources\Accounts
    {
        return $this->resolve(Resources\Accounts::class);
    }

    public function treasury(): Resources\Treasury
    {
        return $this->resolve(Resources\Treasury::class);
    }

    public function saft(): Resources\Saft
    {
        return $this->resolve(Resources\Saft::class);
    }

    public function invoices(): Resources\Documents\Invoices
    {
        return $this->resolve(Resources\Documents\Invoices::class);
    }

    public function estimates(): Resources\Documents\Estimates
    {
        return $this->resolve(Resources\Documents\Estimates::class);
    }

    public function guides(): Resources\Documents\Guides
    {
        return $this->resolve(Resources\Documents\Guides::class);
    }

    public function purchaseOrders(): Resources\Documents\PurchaseOrders
    {
        return $this->resolve(Resources\Documents\PurchaseOrders::class);
    }

    /**
     * @template T of Resources\Resource
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function resolve(string $class): Resources\Resource
    {
        /** @var T */
        return $this->resources[$class] ??= new $class($this->client);
    }
}
