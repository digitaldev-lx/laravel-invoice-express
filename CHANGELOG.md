# Changelog

All notable changes to `digitaldev-lx/laravel-invoice-express` are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-05-02

### Added
- **Webhook support** — `POST /{prefix}` route registered automatically when `webhooks.enabled=true`.
  - `WebhookController` validates the `X-InvoiceXpress-Signature` HMAC-SHA256 against `webhooks.signing_secret` and dispatches both a generic `WebhookReceived` event and a specific `Document*` event matching `WebhookEvent`.
  - `WebhookHandler` performs the fan-out via a typed `match` against `WebhookEvent`.
  - `WebhookSignatureVerifier` (singleton) — HMAC-SHA256 verification with safe fall-through when no secret is configured (logs a warning).
  - `InvoiceExpressWebhookLog` Eloquent model + migration for auditing every payload received.
  - `WebhookPayload` DTO and `WebhookEvent` enum (`document.created/finalized/paid/canceled/deleted`).
  - Events: `WebhookReceived`, `WebhookSignatureFailed`.
- **Retry & backoff** — `InvoiceExpressClient` now retries `429`/`5xx`/connection errors with exponential backoff via `Http::retry()`. Configurable via `invoiceexpress.retry.times` and `invoiceexpress.retry.backoff_ms`.
- **Preventive rate limiting** — when a cache store is configured (`invoiceexpress.cache_store`) the client throws `RateLimitException` locally once 95% of the 780 req/min/account limit is reached, so jobs/queues can back off before InvoiceXpress responds 429.
- **`HasInvoiceExpressDocuments` trait** — first-class shortcuts for Eloquent models (`createInvoiceXpressInvoice()`, `finalizeInvoiceXpress()`, `settleInvoiceXpress()`, `cancelInvoiceXpress()`, `emailInvoiceXpress()`, `downloadInvoiceXpressPdf()` and state predicates).
- Translations EN/PT (`resources/lang/{en,pt}/invoiceexpress.php`) — webhook + error labels.
- Persistence opt-in scaffolding (`config('invoiceexpress.persistence.enabled')`); webhook log table is always created.

### Changed
- `InvoiceExpressServiceProvider` now loads migrations + translations, registers the webhook route conditionally, and binds `WebhookSignatureVerifier`/`WebhookHandler` as singletons.

## [0.9.0] - 2026-05-02

### Added
- Resources: `Documents/PurchaseOrders` (full lifecycle), `Accounts`, `Treasury` (with `accounts()` and `categories()`), `Saft`.
- DTOs: `PurchaseOrder`, `Account`, `TreasuryMovement`.
- Manager / Facade now expose `purchaseOrders()`, `accounts()`, `treasury()`, `saft()`.
- Console commands:
  - `invoiceexpress:test-connection` — pings `accounts.json` to validate credentials, supports `--account` / `--key` overrides.
  - `invoiceexpress:sync-sequences` — lists sequences in a table.
  - `invoiceexpress:saft` — generates SAF-T XML for a given year/month, optionally writes it to `--out`.
- All resources covered for the V2 API documentation sidebar (Invoices, Estimates, Guides, Purchase Orders, Clients, Items, Sequences, Taxes, Accounts, SAF-T, Treasury).

## [0.5.0] - 2026-05-02

### Added
- Resources: `Estimates`, `Guides`, `Sequences`, `Taxes`.
- DTOs: `Estimate`, `Guide`, `Sequence`, `Payment`, `EmailMessage`, `EmailRecipient`.
- Enums: `DocumentState`, `EstimateType`, `GuideType`, `PaymentMethod`, `VatExemptionCode` (M01..M99), `TaxRegion` (PT mainland / Açores / Madeira).
- Lifecycle concerns shared by every Document type: `ChangesState` (with `finalize()` / `cancel()` / `settle()` shortcuts), `GeneratesPdf` (`pdfUrl()` / `pdf()`), `SendsByEmail`, `GetsQrCode`, `GeneratesPayment` (+ `cancelPayment()`), `HandlesRelatedDocuments`.
- Events: `DocumentFinalized`, `DocumentPaid`, `DocumentCanceled`, `DocumentDeleted`, `EmailSent`, `PdfGenerated`, `PaymentReceived`, `PaymentCanceled`.
- `Document` abstract now declares `endpointRoot()` and `documentType()` so concerns can route requests dynamically.
- Estimates and Guides route to type-specific endpoints (`quotes.json`, `proformas.json`, `fees_notes.json`, `transports.json`, `shippings.json`, `devolutions.json`, `globals.json`).
- `Sequences::setCurrent()` and `Sequences::register()` for AT validation flow.

## [0.1.0] - 2026-05-02

### Added
- Initial MVP release.
- `InvoiceExpressClient` HTTP client with API key auth via query string.
- `InvoiceExpress` manager + Facade with lazy resource resolution.
- `Resource` abstract using `#[InvoiceExpressEndpoint]` PHP attributes + reflection.
- Resources: `Clients`, `Items`, `Documents/Invoices` (CRUD).
- DTOs: `Address`, `Client`, `Item`, `Tax`, `DocumentItem`, `Invoice`.
- Enums: `DocumentType`, `Country`, `Currency`, `Language`.
- Exception hierarchy: `InvoiceExpressException`, `AuthenticationException`, `BadRequestException`, `NotFoundException`, `RateLimitException`, `ServerException`, `UnknownEndpointException`, `ValidationException`, `WebhookException`.
- Events: `ClientCreated`, `ClientUpdated`, `ItemCreated`, `ItemUpdated`, `DocumentCreated`.
- `InvoiceExpressServiceProvider` registers the manager and HTTP client as singletons; publishes config under the `invoiceexpress-config` tag.
- Multi-account runtime via `InvoiceExpress::useAccount(name, key)`.
- Pest 4 test suite + GitHub Actions workflow for PHP 8.4 / Laravel 12 & 13 (Pint + PHPStan + Pest).
