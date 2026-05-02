<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Console;

use DigitaldevLx\LaravelInvoiceExpress\InvoiceExpress;
use Illuminate\Console\Command;

final class SyncSequencesCommand extends Command
{
    protected $signature = 'invoiceexpress:sync-sequences
        {--account= : Account name to use (otherwise the default config account)}
        {--key= : API key to use (when overriding the account)}';

    protected $description = 'Lists all InvoiceXpress sequences for the active account.';

    public function handle(InvoiceExpress $manager): int
    {
        $account = $this->option('account');
        $key = $this->option('key');

        if (is_string($account) && $account !== '' && is_string($key) && $key !== '') {
            $manager = $manager->useAccount($account, $key);
        }

        $payload = $manager->sequences()->all();

        $rows = [];
        $sequences = $payload['sequences'] ?? $payload;

        if (is_array($sequences)) {
            foreach ($sequences as $sequence) {
                if (! is_array($sequence)) {
                    continue;
                }
                $rows[] = [
                    'id' => $sequence['id'] ?? '',
                    'serie' => $sequence['serie'] ?? '',
                    'document_type' => $sequence['document_type'] ?? '',
                    'current_sequence_number' => $sequence['current_sequence_number'] ?? '',
                    'default' => isset($sequence['default_sequence']) && $sequence['default_sequence'] ? 'yes' : 'no',
                ];
            }
        }

        $this->table(['ID', 'Serie', 'Document Type', 'Current #', 'Default'], $rows);

        return self::SUCCESS;
    }
}
