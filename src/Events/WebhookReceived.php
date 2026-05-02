<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\WebhookPayload;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class WebhookReceived
{
    use Dispatchable;

    public function __construct(
        public WebhookPayload $payload,
    ) {}
}
