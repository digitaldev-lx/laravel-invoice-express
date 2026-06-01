<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Support;

/**
 * Normalises InvoiceXpress monetary / decimal values to an exact string,
 * never routing them through PHP's float type — floats silently lose
 * precision (e.g. 0.1 + 0.2) and are unsafe for money. A string supplied
 * by the caller (e.g. "10.50") is kept verbatim so it survives the
 * round-trip to the API unchanged.
 */
final class Decimals
{
    public static function toString(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value === '' ? null : $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }
}
