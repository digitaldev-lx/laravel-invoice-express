<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Console;

use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;
use Illuminate\Console\Command;

final class GenerateSaftCommand extends Command
{
    protected $signature = 'invoiceexpress:saft
        {--year= : Year of the SAF-T export (defaults to current year)}
        {--month= : Month of the SAF-T export (defaults to current month)}
        {--out= : Output path for the XML (otherwise prints to stdout)}
        {--account= : Account name to use (otherwise the default config account)}
        {--key= : API key to use (when overriding the account)}';

    protected $description = 'Generates and downloads a SAF-T XML export for the requested period.';

    public function handle(InvoiceExpress $manager): int
    {
        $year = $this->option('year');
        $month = $this->option('month');
        $out = $this->option('out');

        $resolvedYear = is_numeric($year) ? (int) $year : (int) date('Y');
        $resolvedMonth = is_numeric($month) ? (int) $month : (int) date('n');

        if ($resolvedMonth < 1 || $resolvedMonth > 12) {
            $this->error("Invalid month: {$resolvedMonth}");

            return self::FAILURE;
        }

        $account = $this->option('account');
        $key = $this->option('key');

        if (is_string($account) && $account !== '' && is_string($key) && $key !== '') {
            $manager = $manager->useAccount($account, $key);
        }

        $xml = $manager->saft()->generate($resolvedYear, $resolvedMonth);

        if (is_string($out) && $out !== '') {
            file_put_contents($out, $xml);
            $this->info("SAF-T XML saved to {$out} ({$resolvedYear}-{$resolvedMonth}).");

            return self::SUCCESS;
        }

        $this->line($xml);

        return self::SUCCESS;
    }
}
