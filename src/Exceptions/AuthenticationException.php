<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

final class AuthenticationException extends InvoiceExpressException
{
    public function __construct(
        string $message,
        public readonly ?string $accountName = null,
        int $code = 401,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
