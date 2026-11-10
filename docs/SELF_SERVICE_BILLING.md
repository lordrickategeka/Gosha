# Verified registration and hosted billing

GarageHQ uses OnePass to verify the owner and Cream Theme to collect payments through Flutterwave and send subscription emails. Provider secrets stay in the existing encrypted API integrations store. No live credentials are seeded.

## Setup

GarageHQ development uses `APP_URL=http://garagehq.test:8080`. Production must use `APP_URL=https://garagehq.loganate.cc`. The payment return URLs are respectively `http://garagehq.test:8080/billing/pay/callback` and `https://garagehq.loganate.cc/billing/pay/callback`. These are GarageHQ URLs, not the Cream Theme or OnePass service base URLs. Set those separately in Platform Integrations after creating the partner accounts.

1. Run `php artisan migrate` on the GarageHQ deployment. The new migration creates a dedicated 14-day trial plan, payment attempts and the subscription email outbox. It does not replace existing pricing or restart existing subscriptions. The migration has already been applied to the local GarageHQ database.
2. Sign in as a platform administrator and open `/platform/integrations`.
3. Configure **Cream Theme** with its root API base URL (without `/api`), the GarageHQ partner key/secret, and `test` or `live`. The environment must match the partner setting in Cream Theme. Enable payments and mail for that partner. Production GarageHQ requires `live`; development/testing can use sandbox checkout. The connection test reads payment records without creating a charge or sending an email. Mail also requires an enabled module, a verified sender and sufficient quota.
   Create a dedicated GarageHQ partner through Cream Theme's `/partner` portal or its administrator. New portal accounts start in test mode; the operator approves go-live for real payments.
4. Configure **OnePass verification** with its root base URL and a dedicated GarageHQ partner key. Keep `sms_enabled` empty until SMS delivery works; use `true` to expose optional phone verification. Its connection test checks readiness and partner authentication using an empty verification request, without sending or consuming a code. Verify a signup using an authorized test address to check delivery.
   Create the GarageHQ key in OnePass's `/partners` administration page. Do not reuse the legacy/admin key.
5. Restart/redeploy the companion OnePass service to load its email-only readiness change. Its source now permits production startup with an email provider alone; SMS requests still fail explicitly while SMS is unconfigured. Its existing provider credentials were not changed.
6. Run the Laravel scheduler every minute and a queue worker. For example: `php artisan schedule:work` and `php artisan queue:work` for local development. Use the deployment's process manager or cron for production. `billing:self-service` is scheduled every minute with overlap prevention; legacy `billing:process` retains its daily schedule.

Both service URLs must use HTTPS in production. Configure OnePass's trusted proxies to include only the actual GarageHQ backend/reverse proxy addresses so the forwarded client IP is used for per-user rate limits. Do not trust arbitrary internet clients. GarageHQ also rate-limits sends and verification locally and honors OnePass `Retry-After`.

If connection tests report **cURL 60**, PHP cannot verify the service's certificate chain. Apache and command-line PHP can load different INI files. GarageHQ accepts an optional `INTEGRATIONS_CA_BUNDLE` pointing to a readable, trusted CA bundle. The local WAMP configuration uses `C:/wamp64/bin/php/php8.3.28/cacert.pem`; use the appropriate system CA configuration on production. Certificate verification stays enabled with or without this override. The connection test reports certificate, DNS, timeout and HTTP authentication failures without exposing credentials or upstream response bodies.

Partners should follow the [PHP HTTPS setup guide](PHP_HTTPS_SETUP.md) before testing API credentials. It covers runtime differences, CA configuration, service restarts and verification from both web requests and workers.

## Signup and trial

The login email must be verified before the wizard advances or creates a garage. Proof is bound to the current session, signup challenge and exact normalized destination; changing the email or waiting more than 30 minutes requires verification again. OTPs are never persisted by GarageHQ. OnePass's JWT is not a GarageHQ login session. Phone verification is separate and optional when enabled; neither channel grants proof for the other.

The trial starts at successful garage creation, lasts exactly 14 days, costs zero and has no setup fee, card collection or automatic charge. Paid cards do not silently grant another trial, even if their legacy `has_trial` field is set. Each garage gets one trial. Trial expiry does not create an invoice or activate a paid plan.

All core tools remain available subject to staff permissions and the displayed capacity limits. Cards do not advertise unenforced differences between module tiers. Capacity checks apply to new branches, users, customers, inventory items and monthly work/wash orders on self-service subscriptions.

Request-time middleware checks expiry independently of the scheduler. The Livewire hydration hook blocks stale operational actions after expiry. Billing remains available, including plan selection and checkout; operational job-card APIs also require authentication and subscription access. Customer data is preserved.

## Paid activation and renewal

Choosing a paid plan creates an invoice without changing operational access. One unpaid quote is allowed at a time and selecting the same plan reuses it. The quote includes subscription tax and an initial setup fee when configured. It snapshots the plan name and period length. Payment changes the plan only after server-side verification through Cream Theme.

Checkout uses a persisted partner reference for idempotency and stores the returned `txRef`, environment, amount and currency. Browser callback claims are not trusted. Verification checks every reference and expected amount/currency/environment. Invoice and attempt row locks prevent duplicate callbacks from adding another receipt or billing period. Conflicting duplicate charges enter `review_required` for an operator to inspect.

Cream Theme receives Flutterwave's webhooks. Its current partner contract does not forward those webhooks to GarageHQ. The minute scheduler therefore polls pending payments, including checkout attempts whose initial HTTP response was lost. No direct Flutterwave keys are needed for self-service payments. Existing direct integrations remain available to legacy invoices.

Early payment preserves remaining trial time. Payment after expiry starts the paid period at confirmation. Renewing the same plan preserves remaining paid time. Expired paid subscriptions require manual renewal. The partner API does not expose saved-card charging, so self-service automatic renewal is disabled.

## Subscription email

Trial start, three-day and one-day reminders, expiry and payment receipts create in-app notifications and durable email outbox rows. Dispatch happens after committed state is visible, independently of signup. The scheduler queues emails through Cream Theme's mail API, retries failures and deduplicates each lifecycle event. Platform Integrations shows the most recent delivery attempts and permits requeuing failed messages.

`sent_at` means the provider accepted the email, not that it reached the inbox. Cream Theme's mail contract has no idempotency key, so an ambiguous network timeout after provider acceptance can result in a duplicate email on retry. Payment reconciliation is independently idempotent. SMS trial notifications are not enabled: OnePass currently exposes OTP delivery, not a general SMS notification API.

## Validation

Run `php vendor/bin/phpunit --filter="RegistrationTest|SignupVerificationTest|SelfServiceBillingTest|PartnerIntegrationSetupTest"` and `npm run build` in GarageHQ. Run `npm test` in the OnePass repository. Automated tests fake external APIs; real OTP delivery, sandbox payment, live partner activation and production worker operation need the actual partner configuration.
