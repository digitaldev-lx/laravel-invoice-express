<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

/**
 * The temporary PDF link could not be downloaded (timeout, HTTP error or an
 * invalid URL). The link itself is never included in the message.
 */
class PdfDownloadException extends InvoiceExpressException {}
