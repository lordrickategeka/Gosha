# Supplier plans

These tiers apply to supplier-only businesses. An existing garage adding the supplier module keeps its garage subscription and expiry; it does not start a supplier trial or buy a second subscription. Hybrid supplier sales use the normal marketplace commission rate and require business approval. See [Existing garages](SUPPLIER_REGISTRATION.md#existing-garages-adding-supplier-sales).

Supplier plans use `pricing_plans.audience = supplier`; existing garage plans default to `garage`. The registration, billing and payment services validate the audience. Platform pricing administration can edit audience, active listing capacity, team size, price and commission. A subscribed plan cannot be moved to a different audience.

| Plan | Monthly subscription | Active listings | Active team members, including administrators | Marketplace commission |
| --- | ---: | ---: | ---: | ---: |
| Supplier free trial | UGX 0 for 14 days | 50 | 2 | Waived |
| Supplier Starter | UGX 30,000 | 100 | 2 | 5% |
| Supplier Growth | UGX 75,000 | 500 | 5 | 3% |
| Supplier Pro | UGX 150,000 | 2,000 | 15 | 2% |

All paid tiers include catalog, pricing and stock, quotation and order management. Listed fees exclude configured platform tax. Gateway processing fees, when applicable, are separate. There is no automatic charge or saved-card renewal.

## Approval, trial and payment

The supplier registration form includes plan selection. An unapproved supplier's subscription stays paused with no trial end date or paid invoice. The existing platform supplier-approval action starts a selected trial for exactly 14 days; approval of a paid signup creates its first invoice. The billing scheduler recovers approval transitions if processing was interrupted. Revoking and restoring approval never restarts a trial.

The existing `billing:self-service` schedule sends trial-start, reminder, expiry and payment notices through the durable subscription email outbox and in-app notifications. Cream Theme handles hosted checkout and payment verification. Paying before trial expiry preserves the remaining trial time, including the commission waiver. Paying after expiry starts the paid period at confirmation. Paying cannot grant supplier approval.

Existing suppliers without a subscription can choose one from Billing, including their initial free trial. The migration adds plans without charging or silently enrolling existing suppliers. The supplier dashboard links to Billing and Team. Garage plans are never offered to supplier accounts.

## Expiry and capacity

At expiry, public listing queries exclude the supplier immediately, even before the scheduler runs. Listing records remain intact and can become visible again after verified renewal. New listings, activation, quotes, orders and product submissions require approval and valid selling access. Existing orders and their fulfilment pages remain available; billing, catalog browsing and the dashboard stay accessible. Read-only access does not grant a write bypass: selling actions recheck plan access server-side, including stale Livewire pages.

Active listing capacity is checked when a listing is created or activated. Inactive listings do not consume active capacity. Team management is available to supplier administrators at `/supplier/team`. New members receive only `supplier_staff`, with a required password change at first sign-in. Removing access deactivates the member and preserves their historical records; deactivated accounts cannot access the app and do not consume active seats.

A smaller plan cannot be selected until usage fits. While its invoice awaits payment, the lower pending limits also apply to new listings and staff so usage cannot grow beyond that quote before activation. Expired suppliers may deactivate listings and remove staff to fit a smaller plan.

Listing writes, team creation, plan quoting and verified subscription payment share a business-level database lock. Capacity checks run within that transaction so simultaneous listing activation and plan downgrade requests cannot independently consume the same capacity.

## Commission and cancellations

The order acceptance event snapshots the supplier's commission rate into a provisional marketplace transaction. Orders accepted during a valid trial have a zero rate, including the remaining trial time after early subscription payment. Later plan changes do not rewrite an accepted order's rate.

Only completed orders generate a commission invoice. An order that is both received and paid is marked completed, regardless of which event arrives first. The final order total, including order tax, is multiplied by the snapshotted rate. Applicable platform tax is then added to the commission fee. The invoice is due in seven days and appears alongside subscription invoices in Billing. Completion is idempotent. Paying a commission invoice settles its transaction and sends a receipt without activating, renewing or extending a subscription.

Cancelling an order waives provisional commission and cancels an unpaid commission invoice. If any commission payment has already been recorded, its transaction is marked `refund_due` for operator reconciliation. Partial refunds also require operator review: there is no automatic gateway refund integration or customer-refund workflow in this release. No record claims that money has been returned automatically.

## Deployment and checks

Run `php artisan migrate`; the supplier-plan migration has already been applied locally. Keep the scheduler and queue workers configured as described in [self-service billing setup](SELF_SERVICE_BILLING.md). No live payment or message is needed to run automated tests.

Run `php vendor/bin/phpunit --filter="SupplierPlansTest|SupplierRegistrationTest|SelfServiceBillingTest|RegistrationTest|SignupVerificationTest|PartnerIntegrationSetupTest|AuthenticationTest"`. Tests cover approval timing, audience boundaries, expiry, listing visibility and limits, team seats, payment activation, provisional/final commission, cancellation and existing-order access.

The payment-flow test simulates hosted checkout, server verification and duplicate callbacks after trial expiry, and confirms that paid activation restores listing visibility. Additional checks preserve the remaining trial's commission waiver after early payment and prevent suppliers from accessing another supplier's order. External responses are faked; these tests do not charge a card or send real messages.
