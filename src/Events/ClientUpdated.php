<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class ClientUpdated
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
    ) {}
}
