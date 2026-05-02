<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

final class NotFoundException extends InvoiceExpressException
{
    public function __construct(
        string $message,
        public readonly ?string $resource = null,
        public readonly ?int $resourceId = null,
        int $code = 404,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
