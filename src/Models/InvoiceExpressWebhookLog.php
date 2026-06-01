<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $event
 * @property int|null $document_id
 * @property string|null $document_type
 * @property string|null $dedup_key
 * @property array<string, mixed> $payload
 * @property Carbon|null $received_at
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class InvoiceExpressWebhookLog extends Model
{
    public $timestamps = true;

    /** @var list<string> */
    protected $fillable = [
        'event',
        'document_id',
        'document_type',
        'dedup_key',
        'payload',
        'received_at',
        'processed_at',
    ];

    public function getTable(): string
    {
        $configured = config('invoiceexpress.persistence.tables.webhook_logs');

        return is_string($configured) && $configured !== ''
            ? $configured
            : 'invoice_express_webhook_logs';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => (bool) config('invoiceexpress.webhooks.encrypt_payloads', false)
                ? 'encrypted:array'
                : 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
