<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class WebhookSignatureFailed
{
    use Dispatchable;

    public function __construct(
        public string $rawBody,
        public ?string $signature = null,
    ) {}
}
