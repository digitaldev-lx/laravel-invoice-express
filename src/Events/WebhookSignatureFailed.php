<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class WebhookSignatureFailed
{
    use Dispatchable;

    /**
     * Upper bound on the bytes of the (untrusted, attacker-controlled) raw
     * body retained on the event. Caps how much arbitrary data can flow into
     * a consumer's logs/queues if a listener naively persists `$rawBody`.
     * The full body's hash and length are preserved separately for
     * correlation.
     */
    public const int MAX_BODY_PREVIEW_BYTES = 2048;

    public function __construct(
        public string $rawBody,
        public ?string $signature = null,
        public string $bodyHash = '',
        public int $bodyLength = 0,
    ) {}

    /**
     * Build the event from a rejected request, truncating the raw body to a
     * safe preview while keeping the full body's SHA-256 hash and length.
     */
    public static function for(string $rawBody, ?string $signature): self
    {
        return new self(
            rawBody: substr($rawBody, 0, self::MAX_BODY_PREVIEW_BYTES),
            signature: $signature,
            bodyHash: hash('sha256', $rawBody),
            bodyLength: strlen($rawBody),
        );
    }
}
