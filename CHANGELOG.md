# Changelog

All notable changes to `digitaldev-lx/laravel-invoice-express` are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.1.0] - 2026-07-20

### Fixed

Five document endpoints were built from the per-type resource root (`invoices/{id}/…`, `estimates/{id}/…`, …) but the InvoiceXpress API does not expose them there — every call returned `404`, even for documents that were issued and visible in the account. Verified against a live account.

- **`pdfUrl()` / `pdf()`** (`GeneratesPdf`) — now hit `api/pdf/{id}.json`, a single, document-type agnostic endpoint. `pdf()` never returned bytes before; it silently returned an empty string.
- **`qrCode()`** (`GetsQrCode`) — now hits `api/qr_codes/{id}.json`. The response envelope is `{ "qr_code": { "url": … } }` (the previous test asserted a fabricated `output.url` shape).
- **`relatedDocuments()`** (`HandlesRelatedDocuments`) — now hits `document/{id}/related_documents.json` (literal singular `document`). The response envelope is `{ "documents": [ … ] }` (was asserted as `related_documents`).
- **`payment()` / `cancelPayment()`** (`GeneratesPayment`) — partial payments live under the generic `documents/` resource, so these now target `documents/{id}/partial_payments.json` and `documents/{id}/partial_payments/{paymentId}/change-state.json`. Corrected from the API reference; not yet exercised against a live account (financial writes).

The unit tests for all five mocked the wrong (non-existent) paths, so they passed against fabricated endpoints and hid the bug. They now assert the real paths and response shapes.

## [3.0.1] - 2026-07-02

### Fixed
- **Document `items` are now serialised as a plain JSON array**, matching what the InvoiceXpress V2 API expects. Previously `Invoice`, `Estimate`, `Guide` and `PurchaseOrder` wrapped their line items in an `{ "item": [ … ] }` envelope, which the JSON API rejected with `422 — "Items element should be of type array"` — so no document could be created through the API. `toArray()` now emits `"items": [ … ]`; `fromArray()` stays tolerant, accepting both the plain array and the legacy `{ "item": [ … ] }` envelope some responses still return.

## [3.0.0] - 2026-06-01

Decimal-integrity release. **Contains breaking changes** — see [UPGRADE.md](UPGRADE.md).

### Changed
- **BREAKING:** every monetary / decimal DTO property is now typed `string` (was `float`), so money never round-trips through PHP's lossy `float` type (`0.1 + 0.2 !== 0.3`):
  - `Payment::$amount`
  - `DocumentItem::$quantity`, `DocumentItem::$unitPrice`, `DocumentItem::$discount`
  - `Item::$unitPrice`, `Item::$taxRate`
  - `Tax::$value`
  - `Account::$openingBalance`
  - `Client::$discount`
  - `TreasuryMovement::$amount`

  Construct with strings (`amount: '492.00'`), read them as strings, and `toArray()` now emits strings. A value supplied as a string is preserved exactly — `'10.50'` no longer collapses to `10.5`.

### Added
- `DigitaldevLx\LaravelInvoiceExpress\Support\Decimals::toString()` — normalises mixed numeric input to an exact decimal string without float coercion.

## [2.1.0] - 2026-06-01

Follow-up security hardening from the audit. No breaking changes.

### Security
- **`WebhookSignatureFailed` no longer propagates the unbounded raw body.** The raw body is truncated to `MAX_BODY_PREVIEW_BYTES` (2048) and the full body's SHA-256 hash and length are exposed via new `bodyHash` / `bodyLength` properties — this bounds how much attacker-controlled data can flow into a consumer's logs/queues if a listener persists the event.
- **Opt-in encryption at rest for webhook payloads** via `webhooks.encrypt_payloads` (default `false`). Webhook payloads carry personal data (names, emails, NIF, addresses).
- **Upstream response bodies embedded in client exception messages are now truncated** to 500 characters, reducing information disclosure when `APP_DEBUG` is enabled.
- **Dependencies verified** — the test suite and `composer audit` run clean against the latest Laravel 13.x / Symfony releases. The package does not commit `composer.lock` (consumers resolve their own versions), so no vulnerable versions were ever pinned downstream.

### Added
- `invoiceexpress:prune-webhook-logs` command and `webhooks.prune_after_days` config (default `90`) for GDPR data-retention pruning of the webhook log table.
- `webhooks.encrypt_payloads` config flag.
- `WebhookSignatureFailed::for()` factory plus `bodyHash` and `bodyLength` properties on the event.

### Changed
- `InvoiceExpressWebhookLog` now declares an explicit `$fillable` whitelist instead of `$guarded = []`.

### Fixed
- `Client::$preferredContactName` was typed `?float`, coercing every contact name to `0.0`. It is now correctly typed `?string`.

## [2.0.0] - 2026-06-01

Security hardening of the webhook receiver. Contains breaking changes — see [UPGRADE.md](UPGRADE.md).

### Security
- **Webhook signature verification is now fail-closed.** Previously an unset `webhooks.signing_secret` made the verifier accept *every* callback as valid (a warning was logged), so an unauthenticated request could forge `document.paid` / `document.canceled` events. Verification now **rejects** all callbacks when no secret is configured, unless `webhooks.allow_unsigned=true` is set explicitly (intended for local development only).
- **Replay / idempotency protection.** Callbacks are deduplicated on a unique `dedup_key` (SHA-256 of the raw body) stored on `invoice_express_webhook_logs`; a replayed delivery is recorded and dispatched only once. The dedup is race-safe (`firstOrCreate` + unique index).
- **The state-changing webhook endpoint is now rate-limited by default** (`throttle:60,1`).

### Added
- `webhooks.allow_unsigned` config flag (default `false`) — explicit opt-in to accept unsigned callbacks during local development. **Never enable in production.**
- Migration `add_dedup_key_to_invoice_express_webhook_logs_table` — adds a unique `dedup_key` column to the webhook log table.
- `UPGRADE.md` with step-by-step migration guidance.

### Changed
- **BREAKING:** `WebhookSignatureVerifier::verify()` now returns `false` (instead of `true`) when no signing secret is configured, unless `webhooks.allow_unsigned=true`.
- **BREAKING:** default `webhooks.route_middleware` is now `['api', 'throttle:60,1']` (was `['api']`).
- **BREAKING:** a new migration must be run on upgrade; the webhook endpoint returns `{"status":"duplicate"}` (HTTP 200) for replayed deliveries and no longer re-dispatches their events.

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
