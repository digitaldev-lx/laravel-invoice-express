<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Tests;

use DigitaldevLx\LaravelInvoiceExpress\Providers\InvoiceExpressServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            InvoiceExpressServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        /** @var Application $app */
        $app['config']->set('invoiceexpress.account_name', 'test-account');
        $app['config']->set('invoiceexpress.api_key', 'test-api-key');
        $app['config']->set('invoiceexpress.retry.times', 0);
        $app['config']->set('invoiceexpress.webhooks.enabled', true);
        $app['config']->set('invoiceexpress.webhooks.signing_secret', 'whsec_test');
        $app['config']->set('invoiceexpress.webhooks.route_prefix', 'invoiceexpress/webhooks');
        $app['config']->set('invoiceexpress.webhooks.log_payloads', true);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
