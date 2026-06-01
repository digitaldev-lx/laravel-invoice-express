<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Models\InvoiceExpressWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prunes webhook logs older than the retention window', function (): void {
    InvoiceExpressWebhookLog::query()->create([
        'event' => 'document.created',
        'dedup_key' => 'old',
        'payload' => [],
        'received_at' => now()->subDays(100),
    ]);
    InvoiceExpressWebhookLog::query()->create([
        'event' => 'document.created',
        'dedup_key' => 'recent',
        'payload' => [],
        'received_at' => now()->subDays(10),
    ]);

    $this->artisan('invoiceexpress:prune-webhook-logs', ['--days' => 90])
        ->assertExitCode(0);

    expect(InvoiceExpressWebhookLog::query()->count())->toBe(1);
    expect(InvoiceExpressWebhookLog::query()->first()->dedup_key)->toBe('recent');
});

it('falls back to the configured retention window when --days is omitted', function (): void {
    config()->set('invoiceexpress.webhooks.prune_after_days', 30);

    InvoiceExpressWebhookLog::query()->create([
        'event' => 'document.created',
        'dedup_key' => 'old',
        'payload' => [],
        'received_at' => now()->subDays(45),
    ]);

    $this->artisan('invoiceexpress:prune-webhook-logs')->assertExitCode(0);

    expect(InvoiceExpressWebhookLog::query()->count())->toBe(0);
});

it('rejects a non-positive retention window', function (): void {
    $this->artisan('invoiceexpress:prune-webhook-logs', ['--days' => 0])
        ->assertExitCode(1);
});
