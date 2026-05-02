<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\WebhookEvent;

it('flags lifecycle events', function (): void {
    expect(WebhookEvent::DocumentFinalized->isLifecycle())->toBeTrue();
    expect(WebhookEvent::DocumentPaid->isLifecycle())->toBeTrue();
    expect(WebhookEvent::DocumentCanceled->isLifecycle())->toBeTrue();
    expect(WebhookEvent::DocumentDeleted->isLifecycle())->toBeTrue();
    expect(WebhookEvent::DocumentCreated->isLifecycle())->toBeFalse();
});
