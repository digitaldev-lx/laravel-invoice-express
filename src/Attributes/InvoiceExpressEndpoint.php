<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class InvoiceExpressEndpoint
{
    public function __construct(
        public string $method,
        public string $path,
        public bool $binary = false,
        public ?string $rootKey = null,
        public string $description = '',
    ) {}
}
