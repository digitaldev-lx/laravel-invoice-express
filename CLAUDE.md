# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Laravel package for integrating with the InvoiceXpress API V2 (Portuguese invoicing platform). Provides a fluent interface via the `InvoiceExpress` facade to manage invoices, estimates, guides, purchase orders, clients, items, taxes, sequences, accounts, treasury and SAF-T. PHP 8.4+, Laravel 12/13.

## Commands

```bash
# Run all tests
vendor/bin/pest

# Run a single test file
vendor/bin/pest tests/Unit/Resources/InvoicesTest.php

# Run a specific test by name
vendor/bin/pest --filter="test name here"

# Static analysis (PHPStan level 6)
vendor/bin/phpstan analyse

# Code style (Pint)
vendor/bin/pint          # fix
vendor/bin/pint --test   # check only
```

## Architecture

### Request Flow

`InvoiceExpress` (manager) -> `Resource` subclass -> `InvoiceExpressClient` -> InvoiceXpress REST API

- **`InvoiceExpress`** (`src/InvoiceExpress.php`) - Main entry point, registered as singleton. Lazily resolves and caches Resource instances. Accessed via the `InvoiceExpress` facade.
- **`InvoiceExpressClient`** (`src/Http/InvoiceExpressClient.php`) - Stateless HTTP client. Builds URL `https://{accountName}.app.invoicexpress.com/{endpoint}.json?api_key={key}`. Handles GET/POST/PUT/DELETE, retry with exponential backoff for 429/5xx, and throws granular exceptions on errors. `useAccount(name, key)` returns a clone for runtime multi-account.
- **`Resource`** (`src/Resources/Resource.php`) - Abstract base. Subclasses use `#[InvoiceExpressEndpoint(method, path, binary, rootKey)]` PHP attributes on methods to declare API endpoints. The `call()` method reads the attribute via reflection and delegates to `InvoiceExpressClient::request()`.

### Key Patterns

- **Endpoint declaration via attributes**: Every API method uses `#[InvoiceExpressEndpoint(method: 'POST', path: 'invoices.json')]`. Path parameters use `{id}` placeholders replaced by `pathParameters`.
- **DTOs** (`src/DataTransferObjects/`): Readonly classes implementing `DataTransferObject` contract with `toArray()` / `fromArray()`. Resource methods accept both DTOs and raw arrays.
- **Concerns** (`src/Concerns/Resource/`): Reusable traits for CRUD + lifecycle operations (state changes, PDF, email, QR code, payment, related documents). Each Document type composes from these concerns.
- **Events** (`src/Events/`): Dispatched after mutation operations (creation, finalization, payment, cancellation, email, PDF generation, webhook reception).
- **Exceptions** (`src/Exceptions/`): `InvoiceExpressException` base, with `AuthenticationException`, `ValidationException`, `NotFoundException`, `RateLimitException`, `ServerException`, `WebhookException`, `BadRequestException`, `UnknownEndpointException` subtypes.
- **Enums** (`src/Enums/`): Typed enums for `DocumentType`, `DocumentState`, `PaymentMethod`, `VatExemptionCode`, `Country`, `Currency`, `Language`, etc.

### Document Resources

Document types (Invoices, Estimates, Guides, PurchaseOrders) live under `src/Resources/Documents/` and extend `Document` abstract which composes the lifecycle concerns. Each document type declares its `endpointRoot()` (e.g. `invoices`, `estimates`).

### Multi-account

`InvoiceExpress::useAccount('account-name', 'api-key')->invoices()->all()` returns a manager bound to a different InvoiceXpress account, without polluting the default singleton.

### Webhooks

`POST /{webhook-prefix}` is registered when `webhooks.enabled=true`. The `WebhookController` validates the signature via HMAC-SHA256 using `webhooks.signing_secret`, logs the payload to `invoice_express_webhook_logs`, and dispatches both a generic `WebhookReceived` event and a specific `Document*` event matching the InvoiceXpress event type.

### Persistence (opt-in)

Endpoint-only by default. Setting `persistence.enabled=true` activates Eloquent models that mirror remote state via event listeners — useful for dashboards but introduces sync responsibility. Apps that just need to track foreign keys to InvoiceXpress documents should use the `HasInvoiceExpressDocuments` trait on their own models instead.

### Testing

Uses Orchestra Testbench with a custom `TestCase` (`tests/TestCase.php`) that registers the service provider, sets test config values, and loads package migrations. Tests use Pest with `Http::fake()` and JSON fixtures in `tests/Fixtures/`.

### Package Registration

`InvoiceExpressServiceProvider` registers `InvoiceExpressClient` and `InvoiceExpress` as singletons, publishes config (`invoiceexpress-config` tag) and migrations (`invoiceexpress-migrations` tag), auto-loads migrations, and registers the webhook route via `RouteServiceProvider`.
