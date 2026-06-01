<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Http\Webhooks\WebhookSignatureVerifier;

it('passes verification with a valid HMAC-SHA256 signature', function (): void {
    config()->set('invoiceexpress.webhooks.signing_secret', 'whsec_secret');

    /** @var WebhookSignatureVerifier $verifier */
    $verifier = app(WebhookSignatureVerifier::class);

    $body = '{"event":"document.paid"}';
    $signature = hash_hmac('sha256', $body, 'whsec_secret');

    expect($verifier->verify($body, $signature))->toBeTrue();
});

it('rejects a tampered signature', function (): void {
    config()->set('invoiceexpress.webhooks.signing_secret', 'whsec_secret');

    /** @var WebhookSignatureVerifier $verifier */
    $verifier = app(WebhookSignatureVerifier::class);

    expect($verifier->verify('body', 'wrong'))->toBeFalse();
});

it('rejects an empty signature when a secret is configured', function (): void {
    config()->set('invoiceexpress.webhooks.signing_secret', 'whsec_secret');

    /** @var WebhookSignatureVerifier $verifier */
    $verifier = app(WebhookSignatureVerifier::class);

    expect($verifier->verify('body', null))->toBeFalse();
});

it('rejects verification when no secret is configured (fail-closed)', function (): void {
    config()->set('invoiceexpress.webhooks.signing_secret', null);
    config()->set('invoiceexpress.webhooks.allow_unsigned', false);

    /** @var WebhookSignatureVerifier $verifier */
    $verifier = app(WebhookSignatureVerifier::class);

    expect($verifier->verify('body', null))->toBeFalse();
    expect($verifier->verify('body', 'anything'))->toBeFalse();
});

it('allows unsigned webhooks only when allow_unsigned is explicitly enabled', function (): void {
    config()->set('invoiceexpress.webhooks.signing_secret', null);
    config()->set('invoiceexpress.webhooks.allow_unsigned', true);

    /** @var WebhookSignatureVerifier $verifier */
    $verifier = app(WebhookSignatureVerifier::class);

    expect($verifier->verify('body', null))->toBeTrue();
});
