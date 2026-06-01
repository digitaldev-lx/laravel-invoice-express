<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Http\Controllers;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\WebhookPayload;
use DigitaldevLx\LaravelInvoiceExpress\Events\WebhookSignatureFailed;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\WebhookException;
use DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks\WebhookHandler;
use DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks\WebhookSignatureVerifier;
use DigitaldevLx\LaravelInvoiceExpress\Models\InvoiceExpressWebhookLog;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookHandler $handler,
        private readonly WebhookSignatureVerifier $verifier,
        private readonly ConfigRepository $config,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $rawBody = (string) $request->getContent();
        $signature = $request->header('X-InvoiceXpress-Signature');
        $signatureString = is_string($signature) ? $signature : null;

        if (! $this->verifier->verify($rawBody, $signatureString)) {
            event(WebhookSignatureFailed::for($rawBody, $signatureString));

            throw new WebhookException('Invalid InvoiceXpress webhook signature.');
        }

        /** @var array<string, mixed> $body */
        $body = $request->json()->all();
        $payload = WebhookPayload::fromArray($body);

        $logPayloads = $this->config->get('invoiceexpress.webhooks.log_payloads', true) === true;

        // Idempotency: dedupe on the raw body digest. firstOrCreate + the unique
        // index on dedup_key make this race-safe (a concurrent duplicate hits the
        // constraint and re-reads the existing row, so wasRecentlyCreated stays
        // false and we never dispatch the same callback twice).
        $log = InvoiceExpressWebhookLog::query()->firstOrCreate(
            ['dedup_key' => hash('sha256', $rawBody)],
            [
                'event' => $payload->event->value,
                'document_id' => $payload->documentId,
                'document_type' => $payload->documentType?->value,
                'payload' => $logPayloads ? $body : [],
                'received_at' => now(),
            ],
        );

        if (! $log->wasRecentlyCreated) {
            return response()->json(['status' => 'duplicate']);
        }

        $this->handler->dispatch($payload);

        return response()->json(['status' => 'ok']);
    }
}
