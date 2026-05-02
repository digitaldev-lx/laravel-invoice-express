<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

final class RateLimitException extends InvoiceExpressException
{
    public function __construct(
        string $message,
        public readonly int $retryAfter = 60,
        int $code = 429,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function secondsUntilRetry(): int
    {
        return $this->retryAfter;
    }
}
