<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class PdfGenerated
{
    use Dispatchable;

    public function __construct(
        public DocumentType $type,
        public int $documentId,
        public int $bytes,
    ) {}
}
