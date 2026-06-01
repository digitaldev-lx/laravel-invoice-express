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
            // Fail closed: an unconfigured secret must never grant blanket trust.
            // Operators that genuinely run without a secret (e.g. local tunnels)
            // must opt in explicitly via webhooks.allow_unsigned.
            if ((bool) $this->config->get('invoiceexpress.webhooks.allow_unsigned', false)) {
                $this->logger?->warning(
                    'InvoiceXpress webhook signature verification DISABLED (allow_unsigned=true). Do not use in production.',
                );

                return true;
            }

            $this->logger?->error(
                'InvoiceXpress webhook rejected: no signing secret configured and allow_unsigned is false.',
            );

            return false;
        }

        if ($signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
