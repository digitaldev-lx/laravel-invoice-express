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
 * @property array<string, mixed> $payload
 * @property Carbon|null $received_at
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class InvoiceExpressWebhookLog extends Model
{
    public $timestamps = true;

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        $configured = config('invoiceexpress.persistence.tables.webhook_logs');

        return is_string($configured) && $configured !== ''
            ? $configured
            : 'invoice_express_webhook_logs';
    }
}
