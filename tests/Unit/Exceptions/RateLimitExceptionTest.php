<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\RateLimitException;

it('exposes the retry-after value', function (): void {
    $exception = new RateLimitException('throttled', retryAfter: 90);

    expect($exception->retryAfter)->toBe(90);
    expect($exception->secondsUntilRetry())->toBe(90);
    expect($exception->getCode())->toBe(429);
});

it('defaults retry-after to 60 seconds', function (): void {
    $exception = new RateLimitException('throttled');

    expect($exception->retryAfter)->toBe(60);
});
