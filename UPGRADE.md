# Upgrade Guide

## 2.1.0 → 3.0.0 (decimal integrity)

**Breaking:** monetary and decimal DTO fields changed from `float` to `string` so
money is never routed through PHP's lossy `float` type.

Affected properties — now `string` (or `?string`):

- `Payment::$amount`
- `DocumentItem::$quantity`, `$unitPrice`, `$discount`
- `Item::$unitPrice`, `$taxRate`
- `Tax::$value`
- `Account::$openingBalance`
- `Client::$discount`
- `TreasuryMovement::$amount`

**Action required:**

1. Construct DTOs with string values:

   ```php
   // before
   new Payment(paymentMechanism: PaymentMethod::BankTransfer, amount: 492.00);
   new DocumentItem(name: 'Hour', quantity: 4, unitPrice: 100.00);

   // after
   new Payment(paymentMechanism: PaymentMethod::BankTransfer, amount: '492.00');
   new DocumentItem(name: 'Hour', quantity: '4', unitPrice: '100.00');
   ```

2. When you read these properties they are now strings. Cast to `float` only at
   the edge where you actually compute, and prefer `bcmath` for money:

   ```php
   $total = bcmul($item->unitPrice, $item->quantity, 2); // exact
   ```

3. `toArray()` emits these fields as strings now. Update any test snapshots or
   comparisons that asserted the old float output.

**Why:** PHP `float` cannot represent decimal money exactly (`0.1 + 0.2 !== 0.3`).
Strings preserve the exact value end-to-end and InvoiceXpress accepts decimal
strings, so `'10.50'` survives the round-trip unchanged.

## 2.0.0 → 2.1.0

No breaking changes. Two new **opt-in** security features worth enabling:

- **Encrypt webhook payloads at rest.** Set `INVOICEEXPRESS_WEBHOOKS_ENCRYPT=true`
  (or `webhooks.encrypt_payloads`). Applies to newly-written rows only — existing
  plaintext rows must be pruned first, and a stable `APP_KEY` is required.
- **Prune old webhook logs (GDPR).** Schedule
  `php artisan invoiceexpress:prune-webhook-logs` (retention via
  `webhooks.prune_after_days`, default 90).

If you read `WebhookSignatureFailed::$rawBody` in a listener, note it is now
truncated to 2048 bytes; use the new `$bodyHash` / `$bodyLength` for correlation.

Re-publish the config to pick up the new keys if you keep a local copy:

```bash
php artisan vendor:publish --tag=invoiceexpress-config --force
```

## 1.0.0 → 2.0.0 (security hardening of the webhook receiver)

This release closes three webhook-receiver security issues. Two introduce
behaviour changes you must account for before deploying.

### 1. Webhook signature verification is now FAIL-CLOSED (breaking)

**Before:** if `webhooks.signing_secret` was unset, every callback was accepted
as valid (a warning was logged). An unauthenticated request could forge
`document.paid` / `document.canceled` events.

**Now:** with no secret configured the endpoint **rejects** every callback.

**Action required:**

- Production: set `INVOICEEXPRESS_WEBHOOK_SECRET` to the shared secret your
  InvoiceXpress account (or signing reverse proxy) uses. Webhooks already
  configured with a secret are unaffected.
- Local development without signing (e.g. `ngrok`/`expose`): opt in explicitly
  with `INVOICEEXPRESS_WEBHOOKS_ALLOW_UNSIGNED=true`. **Never set this in
  production.**

### 2. The webhook route is now throttled (minor)

The default `webhooks.route_middleware` changed from `['api']` to
`['api', 'throttle:60,1']` (60 requests/minute). If you need a different limit,
or your application already throttles globally, override
`config('invoiceexpress.webhooks.route_middleware')`. Consider prepending an IP
allowlist middleware for the InvoiceXpress source ranges.

### 3. Webhook delivery is now idempotent — run the new migration (action required)

A replayed callback (identical raw body) is now recorded once and dispatched
once, deduplicated via a unique `dedup_key` column on
`invoice_express_webhook_logs`. Duplicate deliveries return
`{"status":"duplicate"}` with HTTP 200 and do **not** re-dispatch events.

**Action required:** publish/run the new migration:

```bash
php artisan vendor:publish --tag=invoiceexpress-migrations   # if you publish migrations
php artisan migrate
```

If your event listeners previously relied on being invoked for every (possibly
duplicate) delivery, they will now fire only once per unique payload — which is
almost certainly what you want for payment/cancellation handling.

### New configuration keys

| Key | Default | Purpose |
| --- | --- | --- |
| `webhooks.allow_unsigned` | `false` | Accept callbacks when no secret is set (local dev only) |
| `webhooks.route_middleware` | `['api', 'throttle:60,1']` | Now includes a throttle |

Re-publish the config if you keep a local copy:

```bash
php artisan vendor:publish --tag=invoiceexpress-config --force
```
