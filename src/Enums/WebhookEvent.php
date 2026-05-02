<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum WebhookEvent: string
{
    case DocumentCreated = 'document.created';
    case DocumentFinalized = 'document.finalized';
    case DocumentPaid = 'document.paid';
    case DocumentCanceled = 'document.canceled';
    case DocumentDeleted = 'document.deleted';

    public function isLifecycle(): bool
    {
        return in_array($this, [
            self::DocumentFinalized,
            self::DocumentPaid,
            self::DocumentCanceled,
            self::DocumentDeleted,
        ], true);
    }
}
