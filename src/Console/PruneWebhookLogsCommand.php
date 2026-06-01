<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Console;

use DigitaldevLx\LaravelInvoiceExpress\Models\InvoiceExpressWebhookLog;
use Illuminate\Console\Command;

final class PruneWebhookLogsCommand extends Command
{
    protected $signature = 'invoiceexpress:prune-webhook-logs
        {--days= : Retention window in days (defaults to webhooks.prune_after_days)}';

    protected $description = 'Deletes InvoiceXpress webhook logs older than the retention window (GDPR data minimisation).';

    public function handle(): int
    {
        $daysOption = $this->option('days');

        $days = is_numeric($daysOption)
            ? (int) $daysOption
            : (int) config('invoiceexpress.webhooks.prune_after_days', 90);

        if ($days < 1) {
            $this->error('The retention window must be at least 1 day.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $deleted = InvoiceExpressWebhookLog::query()
            ->where('received_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} webhook log(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
