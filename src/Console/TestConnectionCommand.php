<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Console;

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\InvoiceExpressException;
use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;
use Illuminate\Console\Command;

final class TestConnectionCommand extends Command
{
    protected $signature = 'invoiceexpress:test-connection
        {--account= : Account name to use (otherwise the default config account)}
        {--key= : API key to use (when overriding the account)}';

    protected $description = 'Pings the InvoiceXpress API to confirm credentials.';

    public function handle(InvoiceExpress $manager): int
    {
        $account = $this->option('account');
        $key = $this->option('key');

        if (is_string($account) && $account !== '' && is_string($key) && $key !== '') {
            $manager = $manager->useAccount($account, $key);
        }

        $accountName = $manager->client()->accountName();

        $this->info("Testing InvoiceXpress connection for account: {$accountName}");

        try {
            $manager->accounts()->all();
        } catch (InvoiceExpressException $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Connection OK.');

        return self::SUCCESS;
    }
}
