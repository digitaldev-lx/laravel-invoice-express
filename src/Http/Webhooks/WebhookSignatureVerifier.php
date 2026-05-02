<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Psr\Log\LoggerInterface;

final class WebhookSignatureVerifier
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    public function verify(string $rawBody, ?string $signature): bool
    {
        $secret = $this->config->get('invoiceexpress.webhooks.signing_secret');

        if (! is_string($secret) || $secret === '') {
            $this->logger?->warning(
                'InvoiceXpress webhook signature verification skipped: no signing secret configured.',
            );

            return true;
        }

        if ($signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
