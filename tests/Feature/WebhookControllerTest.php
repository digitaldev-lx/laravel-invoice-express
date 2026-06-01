<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\WebhookEvent;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentFinalized;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentPaid;
use DigitaldevLx\LaravelInvoiceExpress\Events\WebhookReceived;
use DigitaldevLx\LaravelInvoiceExpress\Events\WebhookSignatureFailed;
use DigitaldevLx\LaravelInvoiceExpress\Models\InvoiceExpressWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function signWebhookPayload(array $payload, string $secret = 'whsec_test'): array
{
    $body = (string) json_encode($payload);

    return [
        'body' => $payload,
        'header' => hash_hmac('sha256', $body, $secret),
    ];
}

it('accepts a properly-signed webhook and dispatches WebhookReceived + specific event', function (): void {
    Event::fake();

    $payload = [
        'event' => WebhookEvent::DocumentPaid->value,
        'document_id' => 99,
        'document_type' => DocumentType::Invoice->value,
        'occurred_at' => '2026-05-01T10:00:00Z',
        'payload' => ['id' => 99, 'status' => 'settled'],
    ];

    $signed = signWebhookPayload($payload);

    $response = $this->postJson('/invoiceexpress/webhooks', $signed['body'], [
        'X-InvoiceXpress-Signature' => $signed['header'],
    ]);

    $response->assertOk()->assertJson(['status' => 'ok']);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(
        DocumentPaid::class,
        static fn (DocumentPaid $event): bool => $event->documentId === 99,
    );
});

it('rejects a webhook with an invalid signature', function (): void {
    Event::fake();

    $payload = ['event' => WebhookEvent::DocumentCreated->value];

    $response = $this->postJson('/invoiceexpress/webhooks', $payload, [
        'X-InvoiceXpress-Signature' => 'wrong-signature',
    ]);

    expect($response->status())->toBeGreaterThanOrEqual(400);
    Event::assertDispatched(WebhookSignatureFailed::class);
    Event::assertNotDispatched(WebhookReceived::class);
});

it('logs the payload in invoice_express_webhook_logs when log_payloads=true', function (): void {
    config()->set('invoiceexpress.webhooks.log_payloads', true);

    $payload = [
        'event' => WebhookEvent::DocumentCreated->value,
        'document_id' => 7,
        'document_type' => DocumentType::Invoice->value,
        'payload' => ['id' => 7],
    ];
    $signed = signWebhookPayload($payload);

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], [
        'X-InvoiceXpress-Signature' => $signed['header'],
    ])->assertOk();

    expect(InvoiceExpressWebhookLog::query()->count())->toBe(1);
    $log = InvoiceExpressWebhookLog::query()->first();
    expect($log->event)->toBe(WebhookEvent::DocumentCreated->value);
    expect($log->document_id)->toBe(7);
});

it('dispatches DocumentFinalized for a finalized webhook', function (): void {
    Event::fake();

    $payload = [
        'event' => WebhookEvent::DocumentFinalized->value,
        'document_id' => 50,
        'document_type' => DocumentType::Invoice->value,
        'payload' => ['id' => 50],
    ];
    $signed = signWebhookPayload($payload);

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], [
        'X-InvoiceXpress-Signature' => $signed['header'],
    ])->assertOk();

    Event::assertDispatched(
        DocumentFinalized::class,
        static fn (DocumentFinalized $event): bool => $event->documentId === 50,
    );
});

it('dispatches DocumentCreated when the event matches', function (): void {
    Event::fake();

    $payload = [
        'event' => WebhookEvent::DocumentCreated->value,
        'document_id' => 1,
        'document_type' => DocumentType::CreditNote->value,
        'payload' => ['id' => 1],
    ];
    $signed = signWebhookPayload($payload);

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], [
        'X-InvoiceXpress-Signature' => $signed['header'],
    ])->assertOk();

    Event::assertDispatched(
        DocumentCreated::class,
        static fn (DocumentCreated $event): bool => $event->type === DocumentType::CreditNote,
    );
});

it('processes a replayed webhook only once (idempotency)', function (): void {
    Event::fake();

    $payload = [
        'event' => WebhookEvent::DocumentPaid->value,
        'document_id' => 99,
        'document_type' => DocumentType::Invoice->value,
        'occurred_at' => '2026-05-01T10:00:00Z',
        'payload' => ['id' => 99, 'status' => 'settled'],
    ];
    $signed = signWebhookPayload($payload);
    $headers = ['X-InvoiceXpress-Signature' => $signed['header']];

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], $headers)
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], $headers)
        ->assertOk()
        ->assertJson(['status' => 'duplicate']);

    expect(InvoiceExpressWebhookLog::query()->count())->toBe(1);
    Event::assertDispatchedTimes(DocumentPaid::class, 1);
    Event::assertDispatchedTimes(WebhookReceived::class, 1);
});

it('rejects an unsigned webhook when no secret is configured (fail-closed)', function (): void {
    Event::fake();
    config()->set('invoiceexpress.webhooks.signing_secret', null);
    config()->set('invoiceexpress.webhooks.allow_unsigned', false);

    $payload = ['event' => WebhookEvent::DocumentPaid->value, 'document_id' => 5];

    $response = $this->postJson('/invoiceexpress/webhooks', $payload);

    expect($response->status())->toBeGreaterThanOrEqual(400);
    Event::assertDispatched(WebhookSignatureFailed::class);
    Event::assertNotDispatched(WebhookReceived::class);
    expect(InvoiceExpressWebhookLog::query()->count())->toBe(0);
});

it('protects the state-changing webhook route with throttle middleware', function (): void {
    $route = collect(app('router')->getRoutes()->getRoutes())
        ->first(static fn ($r): bool => $r->getName() === 'invoiceexpress.webhooks.handle');

    expect($route)->not->toBeNull();
    expect($route->gatherMiddleware())->toContain('throttle:60,1');
});

it('caps the raw body on the signature-failure event and exposes hash + length', function (): void {
    Event::fake();

    $big = str_repeat('x', 5000);

    $response = $this->call(
        'POST',
        '/invoiceexpress/webhooks',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_X_INVOICEXPRESS_SIGNATURE' => 'wrong'],
        $big,
    );

    expect($response->getStatusCode())->toBeGreaterThanOrEqual(400);

    Event::assertDispatched(
        WebhookSignatureFailed::class,
        static fn (WebhookSignatureFailed $e): bool => $e->bodyLength === 5000
            && strlen($e->rawBody) === WebhookSignatureFailed::MAX_BODY_PREVIEW_BYTES
            && $e->bodyHash === hash('sha256', $big),
    );
});

it('encrypts the stored webhook payload when encrypt_payloads is enabled', function (): void {
    config()->set('invoiceexpress.webhooks.encrypt_payloads', true);

    $payload = [
        'event' => WebhookEvent::DocumentCreated->value,
        'document_id' => 7,
        'document_type' => DocumentType::Invoice->value,
        'payload' => ['client_email' => 'pii@example.pt'],
    ];
    $signed = signWebhookPayload($payload);

    $this->postJson('/invoiceexpress/webhooks', $signed['body'], [
        'X-InvoiceXpress-Signature' => $signed['header'],
    ])->assertOk();

    // At rest: the raw column is ciphertext, not the plaintext PII.
    $raw = DB::table('invoice_express_webhook_logs')->value('payload');
    expect($raw)->not->toContain('pii@example.pt');

    // Through the model cast: transparently decrypted back to an array.
    $log = InvoiceExpressWebhookLog::query()->first();
    expect($log->payload)->toBeArray();
    expect($log->payload['payload']['client_email'])->toBe('pii@example.pt');
});
