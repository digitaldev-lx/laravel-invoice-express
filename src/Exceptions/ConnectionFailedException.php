<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

/**
 * Transport-level failure (DNS, connect, timeout). Messages and the previous
 * exception are scrubbed of the api_key.
 */
class ConnectionFailedException extends InvoiceExpressException {}
