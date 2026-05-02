<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Providers;

use DigitaldevLx\LaravelInvoiceExpress\Console\GenerateSaftCommand;
use DigitaldevLx\LaravelInvoiceExpress\Console\SyncSequencesCommand;
use DigitaldevLx\LaravelInvoiceExpress\Console\TestConnectionCommand;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks\WebhookHandler;
use DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks\WebhookSignatureVerifier;
use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class InvoiceExpressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/invoiceexpress.php', 'invoiceexpress');

        $this->app->singleton(InvoiceExpressClient::class, function (Application $app): InvoiceExpressClient {
            /** @var ConfigRepository $config */
            $config = $app['config'];

            $cacheStore = $config->get('invoiceexpress.cache_store');
            $cache = null;

            if (is_string($cacheStore) && $cacheStore !== '') {
                /** @var CacheRepository $cache */
                $cache = $app['cache']->store($cacheStore);
            }

            $logger = null;
            if ((bool) $config->get('invoiceexpress.log_requests', false)) {
                /** @var LoggerInterface $logger */
                $logger = $app['log']->channel((string) $config->get('invoiceexpress.log_channel', 'stack'));
            }

            return new InvoiceExpressClient(
                accountName: (string) $config->get('invoiceexpress.account_name', ''),
                apiKey: (string) $config->get('invoiceexpress.api_key', ''),
                timeout: (int) $config->get('invoiceexpress.timeout', 15),
                retryTimes: (int) $config->get('invoiceexpress.retry.times', 3),
                retryBackoffMs: (int) $config->get('invoiceexpress.retry.backoff_ms', 1000),
                rateLimitPerMinute: (int) $config->get('invoiceexpress.rate_limit', 780),
                cache: $cache,
                logger: $logger,
            );
        });

        $this->app->singleton(InvoiceExpress::class, static function (Application $app): InvoiceExpress {
            return new InvoiceExpress($app->make(InvoiceExpressClient::class));
        });

        $this->app->alias(InvoiceExpress::class, 'invoiceexpress');

        $this->app->singleton(WebhookSignatureVerifier::class, static function (Application $app): WebhookSignatureVerifier {
            /** @var ConfigRepository $config */
            $config = $app['config'];

            $logger = null;
            if ((bool) $config->get('invoiceexpress.log_requests', false)) {
                /** @var LoggerInterface $logger */
                $logger = $app['log']->channel((string) $config->get('invoiceexpress.log_channel', 'stack'));
            }

            return new WebhookSignatureVerifier($config, $logger);
        });

        $this->app->singleton(WebhookHandler::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'invoiceexpress');

        $this->registerWebhookRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/invoiceexpress.php' => config_path('invoiceexpress.php'),
            ], 'invoiceexpress-config');

            $this->publishes([
                __DIR__.'/../../database/migrations/' => database_path('migrations'),
            ], 'invoiceexpress-migrations');

            $this->publishes([
                __DIR__.'/../../resources/lang' => resource_path('lang/vendor/invoiceexpress'),
            ], 'invoiceexpress-translations');

            $this->commands([
                TestConnectionCommand::class,
                SyncSequencesCommand::class,
                GenerateSaftCommand::class,
            ]);
        }
    }

    private function registerWebhookRoutes(): void
    {
        /** @var ConfigRepository $config */
        $config = $this->app['config'];

        if (! (bool) $config->get('invoiceexpress.webhooks.enabled', true)) {
            return;
        }

        $middleware = $config->get('invoiceexpress.webhooks.route_middleware', ['api']);
        $prefix = (string) $config->get('invoiceexpress.webhooks.route_prefix', 'invoiceexpress/webhooks');

        Route::middleware(is_array($middleware) ? $middleware : ['api'])
            ->prefix($prefix)
            ->name('invoiceexpress.webhooks.')
            ->group(__DIR__.'/../../routes/webhooks.php');
    }
}
