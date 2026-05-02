<?php

declare(strict_types=1);

return [
    'webhook' => [
        'invalid_signature' => 'Invalid InvoiceXpress webhook signature.',
        'received' => 'Webhook received.',
    ],
    'errors' => [
        'auth_failed' => 'Authentication failed for InvoiceXpress account ":account".',
        'not_found' => 'InvoiceXpress :resource not found.',
        'rate_limit' => 'InvoiceXpress rate limit exceeded — retry after :seconds seconds.',
        'validation_failed' => 'InvoiceXpress validation error on fields: :fields.',
        'server_error' => 'InvoiceXpress server error (HTTP :status).',
    ],
];
