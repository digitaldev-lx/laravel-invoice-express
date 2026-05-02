<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Events;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class EmailSent
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
        public DocumentType $type,
        public int $documentId,
        public EmailMessage $message,
    ) {}
}
