<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources\Documents;

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Resources\Resource;

abstract class Document extends Resource
{
    /**
     * Endpoint root for this document type (e.g. "invoices", "estimates",
     * "guides", "purchase_orders"). Used by lifecycle concerns that need
     * to build paths dynamically from the document type.
     */
    abstract protected function endpointRoot(): string;

    /**
     * The default DocumentType for events dispatched by lifecycle concerns.
     */
    abstract protected function documentType(): DocumentType;
}
