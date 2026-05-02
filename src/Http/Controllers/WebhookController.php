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
            WebhookSignatureFailed::dispatch($rawBody, $signatureString);

            throw new WebhookException('Invalid InvoiceXpress webhook signature.');
        }

        /** @var array<string, mixed> $body */
        $body = $request->json()->all();
        $payload = WebhookPayload::fromArray($body);

        if ($this->config->get('invoiceexpress.webhooks.log_payloads', true) === true) {
            InvoiceExpressWebhookLog::query()->create([
                'event' => $payload->event->value,
                'document_id' => $payload->documentId,
                'document_type' => $payload->documentType?->value,
                'payload' => $body,
                'received_at' => now(),
            ]);
        }

        $this->handler->dispatch($payload);

        return response()->json(['status' => 'ok']);
    }
}
