# Supplier registration

The public supplier form is at `/become-a-supplier`. It uses four steps: business details, verified login, supplier plan, and review. The supplier page has marketplace-specific branding and explains the platform approval process before submission. See [Supplier plans](SUPPLIER_PLANS.md) for prices and billing rules.

The business email can also be the login email, or the owner can provide a different login address. Login email verification uses the existing GarageHQ OnePass partner integration and the same certificate configuration documented in [PHP HTTPS setup](PHP_HTTPS_SETUP.md). Supplier signup requires proof of ownership of the exact login email. Changing that email or allowing its verification proof to expire requires verification again.

Successful submission atomically creates a supplier vendor, its `supplier_admin` owner, and the selected supplier subscription in a paused state. The owner's `email_verified_at` is set only after successful verification; `is_verified_supplier` stays false. No garage branch or garage settings are created. Platform approval is a separate administrative action and is required for selling. Approval starts the 14-day trial or creates the selected paid plan's invoice. Paid access starts only after verified payment. The supplier can browse the catalog while awaiting approval.

Creation validates all steps again, normalizes names and email addresses, rejects existing business/login emails, rate-limits attempts and shares the garage registration lock for a given login email. Failed creation rolls back records and preserves the form for retry. Generated business slugs allow distinct suppliers to share a business name. Successful registration clears any stale branch/vendor selection, rotates the session and opens the supplier dashboard. Subsequent supplier logins default to that dashboard; the generic `/dashboard` URL also redirects pure supplier accounts there.

## Email and queue setup

Configure OnePass and Cream Theme in Platform → Integrations. The marketplace's existing `MarketplaceRolesSeeder` provisions supplier roles and permissions; it must have been run on the deployment. Signup never grants platform approval automatically.

A welcome notification is queued after successful database commit. It explains that the email is verified and business approval remains pending. It includes the supplier dashboard link and never includes a password or OTP. Its in-app and Cream Theme email channels use Laravel's database queue; run `php artisan queue:work database` under the deployment's process manager. Email failures retry with backoff and remain visible through Laravel's failed-job tooling after attempts are exhausted. Cream Theme's mail module, sender and quota must be configured. An email outage does not trigger an upstream mail request during the registration transaction.

## Validation

Run `php vendor/bin/phpunit tests/Feature/Marketplace/SupplierRegistrationTest.php` for supplier signup, verified/separate email, proof expiry, validation, role assignment, duplicate submissions, concurrent requests, rollback, supplier login destination, approval restrictions and welcome email payload checks. Tests fake external delivery and do not send live OTPs or email.
