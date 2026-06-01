<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | InvoiceXpress Account
    |--------------------------------------------------------------------------
    |
    | Your InvoiceXpress account subdomain. Requests are sent to
    | https://{account_name}.app.invoicexpress.com/.
    |
    */

    'account_name' => env('INVOICEEXPRESS_ACCOUNT_NAME'),

    /*
    |--------------------------------------------------------------------------
    | InvoiceXpress API Key
    |--------------------------------------------------------------------------
    |
    | Your personal API key. Generate one at
    | https://www.app.invoicexpress.com/users/api.
    |
    */

    'api_key' => env('INVOICEEXPRESS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'timeout' => env('INVOICEEXPRESS_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Retry Strategy
    |--------------------------------------------------------------------------
    |
    | Number of retries and base backoff (milliseconds) when InvoiceXpress
    | responds with 429 or 5xx. Backoff is exponential.
    |
    */

    'retry' => [
        'times' => env('INVOICEEXPRESS_RETRY_TIMES', 3),
        'backoff_ms' => env('INVOICEEXPRESS_RETRY_BACKOFF_MS', 1000),
        'on_status' => [429, 500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (preventive, client-side)
    |--------------------------------------------------------------------------
    |
    | InvoiceXpress allows 780 requests/minute per account. The client throws
    | a RateLimitException locally when reaching 95% of this threshold so the
    | app can queue/delay before getting an HTTP 429.
    |
    */

    'rate_limit' => env('INVOICEEXPRESS_RATE_LIMIT', 780),
    'cache_store' => env('INVOICEEXPRESS_CACHE'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'log_requests' => env('INVOICEEXPRESS_LOG', false),
    'log_channel' => env('INVOICEEXPRESS_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | If enabled, a route at /{route_prefix} accepts POST callbacks. The
    | signing_secret is used to verify the HMAC-SHA256 signature passed via
    | the X-InvoiceXpress-Signature header.
    |
    | Security: verification is FAIL-CLOSED. If no signing_secret is set, the
    | endpoint rejects every callback unless you explicitly set allow_unsigned
    | to true (intended for local development only — never in production).
    |
    | The endpoint changes application state, so it ships throttled by default.
    | Tighten the limit or add an IP allowlist via route_middleware as needed.
    |
    */

    'webhooks' => [
        'enabled' => env('INVOICEEXPRESS_WEBHOOKS_ENABLED', true),
        'route_prefix' => env('INVOICEEXPRESS_WEBHOOKS_PREFIX', 'invoiceexpress/webhooks'),
        'route_middleware' => ['api', 'throttle:60,1'],
        'signing_secret' => env('INVOICEEXPRESS_WEBHOOK_SECRET'),
        'allow_unsigned' => env('INVOICEEXPRESS_WEBHOOKS_ALLOW_UNSIGNED', false),
        'log_payloads' => env('INVOICEEXPRESS_WEBHOOKS_LOG', true),

        // Webhook payloads carry personal data (names, emails, NIF, addresses).
        // Set to true to store the `payload` column encrypted at rest. Applies
        // to newly-written rows only — pre-existing plaintext rows will fail to
        // decrypt, so prune them first. Requires a stable APP_KEY.
        'encrypt_payloads' => env('INVOICEEXPRESS_WEBHOOKS_ENCRYPT', false),

        // Retention window (days) used by `invoiceexpress:prune-webhook-logs`.
        'prune_after_days' => env('INVOICEEXPRESS_WEBHOOKS_PRUNE_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local Persistence (opt-in)
    |--------------------------------------------------------------------------
    |
    | Two-layer mode. When enabled, Eloquent models mirror documents/clients
    | /items/sequences locally and stay synced via event listeners.
    | Webhook log table is always created.
    |
    */

    'persistence' => [
        'enabled' => env('INVOICEEXPRESS_PERSIST', false),
        'tables' => [
            'documents' => 'invoice_express_documents',
            'clients' => 'invoice_express_clients',
            'items' => 'invoice_express_items',
            'sequences' => 'invoice_express_sequences',
            'webhook_logs' => 'invoice_express_webhook_logs',
        ],
    ],

];
