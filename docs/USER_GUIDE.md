# GarageHQ user guide

This guide covers the screens and workflows currently available in GarageHQ. Start with the section for your role, then follow the numbered tasks. Menu items and actions depend on your account permissions, selected branch, business approval and subscription. Prices and limits shown in your own Billing screen take precedence over examples in this guide.

## Getting started and choosing an account
<!-- audience: all -->

GarageHQ supports garage and car-wash businesses, parts suppliers, their staff, and platform administrators. Use the registration cards on the [sign-in page](/login) to choose an account.

| Account | Main purpose | First destination |
| --- | --- | --- |
| Garage owner | Repairs, washing, customers, staff, stock and finance | Garage dashboard |
| Supplier administrator | Parts listings, quotes, orders and supplier team | Supplier dashboard |
| Staff member | Tasks and records allowed by the assigned role | Dashboard or assigned module |
| Platform administrator | Organisations, plans, supplier approval and integrations | Platform administration |

**Garage owner quick start:** register and verify your email, select a garage plan, confirm your main branch, review business settings, configure services, add staff, and create your first customer and work order.

**Supplier quick start:** register and verify your email, select a supplier plan, wait for business approval, and then prepare listings and respond to enquiries. A trial starts after approval; a paid plan requires verified payment.

**Staff quick start:** obtain an account from your business administrator, sign in using the standard email/password form, change any initial password when prompted, confirm the current branch, and open your assigned module. The garage and supplier registration cards create business accounts; staff use their administrator-provided login on the normal sign-in form.

## Signing in, passwords and account access
<!-- audience: all -->

1. Open [Sign in](/login) and enter your login email and password. The same form serves garages and suppliers.
2. Use **Remember me** only on a device you control. Sign out after using a shared device.
3. If prompted to change an initial password, complete that step before opening other modules. Use the initial password supplied by your administrator and choose a private replacement.
4. Suppliers normally land on the supplier dashboard. Garage users normally land on the garage dashboard; an intended destination may take you directly back to a protected screen.

The login email can differ from the business correspondence email. Verify which address was used when the account was created. For password recovery, use [Request a password reset](/forgot-password) where enabled, or contact your business administrator. The recovery process depends on configured email delivery.

An **inactive** user account needs a business administrator's help. A **suspended** organisation needs platform support. Repeatedly creating new accounts will not restore an existing business's access or records.

## Email and optional phone verification
<!-- audience: all -->

New garage and supplier owners must verify their exact login email before completing registration.

1. Enter or choose the login email in the account step.
2. Select **Send verification code**. Check the inbox and spam folder for the six-digit code.
3. Enter the code and select **Verify email**. Continue when **Login email verified** appears.
4. If a code expires, request a replacement after the resend countdown. Use the most recently requested code.

Changing the login email requires verification again. Verification proof also expires if registration is left unfinished for too long. A verified email confirms control of that address; it does not approve a supplier business or activate a paid subscription.

Garage signup can offer optional phone verification when the SMS service is enabled. Use international format such as `+256700000000`. Email and phone verification are separate. Supplier business phone entry is contact information and does not, by itself, verify ownership of the number.

If delivery is unavailable, keep the registration page open and retry when the service recovers. Do not share verification codes, passwords or partner API keys with anyone offering to troubleshoot the account.

## Registering a garage or car wash
<!-- audience: garage -->

Open [Register your garage](/register). The form has four steps.

1. **Your garage:** enter the business name, email, optional phone and location. GarageHQ creates a **Main Branch** using these details. Expand the branch option if its name or contact details should differ.
2. **Your account:** enter the owner's name. Reuse the business email for login or choose another address, verify that address, and enter a password and confirmation. The Show/Hide controls help check your password before submission.
3. **Choose a plan:** compare the trial, recurring price, currency, billing period, limits and any setup fee. A plan that cannot be purchased online may require assisted setup.
4. **Review:** check the garage, branch, account and plan details. Use **Edit** to correct a section, then select **Create my garage** once.

Successful registration signs you in and selects your main branch. A free trial opens the dashboard; a paid signup opens Billing so you can review and pay the invoice. If a request fails, read the field errors and retry using the preserved form. An already registered login email should be used to sign in instead.

The dedicated garage trial lasts 14 days from successful creation. It requires no card and has no automatic charge. Selecting a paid plan does not silently grant another trial.

## Navigation, roles and branch selection
<!-- audience: garage,staff,supplier -->

Use **All modules** to find the areas available to you. Opening a module changes the sidebar to that module's tasks. The dashboard and top-bar shortcuts provide another route to common work. Use **User guide** whenever you need these instructions again.

Garage users should check the current branch before entering a job, invoice, expense or stock transaction. Switch to a branch you are authorised to access. A branch selection changes the context of branch-specific screens; search filters can further narrow the records displayed.

Typical garage roles include owner, branch manager, technician, cashier and wash attendant. Supplier accounts use supplier administrator and supplier staff roles. The administrator's actual permission assignments determine what each person can see or change; a job title alone does not guarantee access.

If a screen is missing or an action returns **403 / forbidden**, ask your administrator to check your role, branch access, supplier approval and plan state. Opening a direct URL does not bypass those checks.

## First-day garage setup
<!-- audience: garage -->

Before recording live jobs, complete a short setup pass:

1. Open [Business settings](/settings). Review the business details, currency, timezone, invoice presentation and applicable tax settings. Use settings appropriate to your business rather than relying on a default tax rate.
2. Open [Branches](/branches) and confirm the main branch name and location. Add further branches only if your plan allows them.
3. Open [Staff and users](/users). Create the accounts and roles needed for reception, technical work, washing and finance. Assign branch access where provided.
4. Configure [Service types](/service-types), [Service categories](/service-categories), [Service templates](/templates) and [Wash packages](/packages). Review descriptions and prices before using a template on a customer job.
5. Check [Bay status](/bays) and the bay configuration available to your role. Confirm the correct service or wash bay before assigning work.
6. Add stock records and suppliers before issuing parts to jobs.

The dashboard setup links are shortcuts into these screens. A plan limit can stop additional branches, users or records; review Billing before creating duplicate businesses as a workaround.

## Customers and vehicles
<!-- audience: garage,staff -->

Use [Customers](/customers) and [Vehicles](/vehicles) to maintain the records used by repair and wash orders.

1. Search by the details available on the screen before adding a record. Reuse an existing customer to preserve history.
2. Create or update the customer's name, phone, email and address. Check company details when dealing with an organisation.
3. Add or select the customer's vehicle. Check registration, make/model and other required vehicle fields before saving.
4. Open the customer or vehicle detail page to review linked activity before taking a new booking or starting a job.

Work-order intake also provides quick-add options for customers and vehicles. Avoid creating a second vehicle just because a returning customer uses a different phone number. Correct the underlying record with the permissions available to you.

## Appointments and the calendar
<!-- audience: garage,staff -->

Use [Appointments](/appointments) for planned visits and [Calendar](/calendar) to review scheduled activity.

1. Choose **New Appointment** and select the customer and vehicle.
2. Choose the appointment type: service, wash, combo, diagnostics or estimate.
3. Enter the date, time, expected duration and notes. New appointments require today or a future date and a duration of at least 15 minutes.
4. Save and review the appointment list or calendar. Confirm the selected branch and filters if a booking appears missing.

A booking records an expected visit. When the vehicle arrives, create or open the appropriate work/wash order; a saved appointment alone does not mean work has started or payment has been received.

## Repair work orders and intake
<!-- audience: garage,staff -->

Open [Work Orders](/work-orders) and select **New work order** when your role allows intake.

1. **Customer and vehicle:** select existing records or use the quick-add controls. Record the customer's concerns and any items left with the vehicle.
2. **Job details:** select the work type, priority, bay and technician where available. Record mileage, expected completion and whether the job includes washing.
3. **Line items:** use a service template or add the required labour, parts and other items. Confirm quantities, prices and any stock selection.
4. **Review and save:** check the customer, vehicle and commercial details before creating the order.

Keep the work-order number for follow-up. The detail page brings together assignments, line items, quotations, quality checks and related records. Opening a work order does not collect money automatically.

For a service-and-wash job, check the related wash workflow and queue before promising that both activities are complete.

## Moving repairs through work, quality and delivery
<!-- audience: garage,staff -->

The usual repair progression is **Open → In progress → Quality check → Ready → Delivered**, with quotation-related states where applicable. The screen only offers transitions available to your role and the current order state.

1. Assign the correct bay and technician on the order detail screen.
2. Select **Start work** when the job begins.
3. Move the job to **Quality check** after technical work is ready for inspection. Review the checklist and record the results in the available quality-check screen.
4. Mark the order **Ready** after completing the required checks.
5. Confirm the customer handover before selecting **Deliver**. Use cancellation only when the job should stop; review the consequences for linked records first.

Technicians should keep notes and job items accurate as work changes. Cashiers should check the invoice and balance separately from the operational status. An order being ready or delivered is not proof that an invoice has been paid.

## Quotations and additional-work approval
<!-- audience: garage,staff -->

Use [Quotations](/quotations) or create a quotation from the relevant work order.

1. Confirm the customer, vehicle, proposed work and line prices.
2. Review totals, discounts, tax and any expiry or notes shown by the form.
3. Save the quotation and use its available PDF or customer approval link when sharing it.
4. Review the customer's approval or rejection before proceeding with work that requires consent.

Customers can open a quotation approval link without a staff login. Treat the link as private: anyone with access to it may be able to view the quotation and respond while it is valid.

If extra work is identified during a job, use the order's additional-work/debit-note request where available. Add the proposed items and send the approval request. The customer can approve or reject items; approved items are applied to the related job/invoice by the workflow. Do not interpret a sent request as approval. Review the resulting records before continuing.

## Wash orders, packages and the queue
<!-- audience: garage,staff -->

Open [Wash Orders](/wash-orders) to review the queue or record a new wash.

1. Confirm the current branch, customer and vehicle.
2. Choose the wash type and, if appropriate, an active wash package. Check the included items and prices.
3. Add any permitted custom items and notes, then save. New washes enter the queue.
4. When a bay is available, start the wash using the correct bay assignment.
5. Mark the wash complete after the service is finished. Use cancellation or return-to-queue only when the screen offers the action and the change reflects what happened.

Check [Bay status](/bays) when the queue cannot move. A wash order's operational status and its invoice/payment status are separate records.

## Invoices, payments and balances
<!-- audience: garage,staff -->

[Customer invoices](/invoices) are bills issued by your garage. [Subscription Billing](/billing) contains platform charges for using GarageHQ. Keep the two workflows separate when checking a balance.

To create a customer invoice:

1. Select the customer and the relevant work or wash order where applicable.
2. Review the imported items or add invoice lines with description, quantity and unit price.
3. Check the due date, discount and tax settings, then save.
4. Open the invoice to review its total, payments and outstanding balance.

To record an actual customer payment, open the invoice, choose **Record payment**, enter the amount, method and reference, then confirm. Available methods include cash, mobile money, card, bank transfer and credit. The amount must be positive and cannot exceed the recorded balance in this form.

Recording a payment is bookkeeping; selecting “card” or “mobile money” in this form does not, by itself, charge the customer's account. Confirm that funds have actually been received before recording them. Use [Payments](/payments) and the debtor/creditor reports to follow up balances when permitted.

## Inventory, stock movements and purchasing
<!-- audience: garage,staff -->

Use [Inventory](/inventory) for parts and consumables, [Low stock](/inventory/low-stock) for replenishment, and [Movements](/inventory/movements) to review stock changes.

1. Search for the item before creating another record. Check its SKU, unit, stock location, price and reorder information.
2. Open the item detail screen to record purchases, stock adjustments or transfers if authorised.
3. For an adjustment, enter the actual change and a meaningful reason. For a transfer, confirm both branches and quantity.
4. Review the movement history after saving. If a change appears wrong, investigate the source transaction rather than repeatedly adjusting the total.

Maintain business supplier contacts in [Suppliers](/suppliers). These purchasing contacts are separate from marketplace supplier accounts that register to sell parts online.

When buying through the marketplace, receive the purchase order using its goods-receipt workflow so stock is credited through the linked order. Avoid adding the same purchase manually and through goods receipt.

## Expenses and staff commissions
<!-- audience: garage,staff -->

Use [Expenses](/expenses) to record business costs. Choose the applicable category, amount, currency, date, payment details and notes; attach supporting documents where the form allows them. Save a draft while details are incomplete, or submit the expense for approval.

An expense awaiting approval is not automatically approved or paid. The deployed expense screens may not expose a full approval-management interface to every role; ask the responsible administrator if no approval action is available.

[Staff commissions](/commissions) concern employee earnings. Filter by user, date and status to review records. Authorised staff can approve commissions and mark actual payouts as paid. Individual staff may have access to [My commissions](/commissions/my). Check configured commission rules before relying on a calculated amount.

Staff commissions are separate from the marketplace commission a parts supplier pays to GarageHQ. A staff payout does not settle a supplier's platform invoice.

## Reports and daily closing
<!-- audience: garage,staff -->

Open the report relevant to your role: [Sales](/reports/sales), [Operations](/reports/operations), [Inventory](/reports/inventory), [Staff performance](/reports/staff), [Debtors](/reports/debtors) or [Creditors](/reports/creditors).

1. Set the intended date range and branch context before comparing totals.
2. Check whether the report measures orders, invoices, payments, expenses or balances. These are different events and may fall on different dates.
3. Compare unusual totals against the underlying records rather than assuming every difference is an error.
4. Use any export option shown on that report; export formats vary by screen.

A useful end-of-day review is: unfinished jobs and washes, unrecorded customer payments, unpaid invoices, pending expenses, stock exceptions, and tomorrow's appointments. The dashboard period filters provide an operational summary, not a substitute for checking financial records.

## Adding supplier sales to an existing garage
<!-- audience: garage,supplier,admin -->

Garage owners can add supplier selling to the same business and login. Do not create a duplicate supplier registration or move staff to another business.

1. Open **Modules → Add supplier module**, or **Marketplace → Supplier module** at [/supplier/module](/supplier/module). Opening a supplier workspace link without access also takes you to this page instead of a generic permissions error.
2. Check the business contact details in Settings. Explain what products the garage supplies, accept the confirmation, and select **Request supplier access**. Only the business owner can apply. Other staff see instructions to contact that owner.
3. The business becomes a combined garage/supplier account. The requesting owner retains their garage role and receives supplier administrator access. Existing garage records, branches, staff and subscription are retained. Applying again does not restart a trial or create another subscription.
4. While approval is pending, browse the shared catalog and track the review on Supplier module. Publishing listings, proposing catalog products and submitting quotes require platform approval.
5. Platform reviewers receive an in-app notification. In **Platform → Vendors**, filter **Pending supplier approval**, open the business, review its application and select **Verify supplier**. Do not replace its existing garage subscription with a supplier-only subscription. The owner receives an in-app approval notification.
6. After approval, open the supplier workspace. Use **Back to garage** to return to garage operations. Under **Supplier team**, use **Give supplier access** for existing garage staff; **Remove access** removes their supplier staff role while keeping their garage login and other roles.

The existing garage subscription and expiry continue to govern the combined business. There is no new supplier trial or automatic subscription charge when enabling the module. Normal marketplace commission rules still apply to sales. Renew the garage plan in Billing if it expires; adding the supplier module cannot bypass that expiry. If verification is revoked, supplier selling pauses while garage records and roles remain intact. Contact platform support to review a paused application.

## Registering and approving a supplier business
<!-- audience: supplier -->

Open [Register as a supplier](/become-a-supplier). Complete **Business → Account → Plan → Review**.

1. Enter your business name, correspondence email, phone and warehouse/location details.
2. Enter the account administrator's name. Reuse the business email or choose another login address, verify it, and create a password.
3. Choose a supplier trial or paid plan. Review both the recurring subscription price and the sales commission.
4. Check the review and create the supplier account. You will be signed in with business approval pending.

You can browse the catalog while a platform administrator reviews the business. Email verification does not grant business approval, and paying does not bypass approval. The chosen trial clock has not started yet; paid invoices are issued after approval.

Once the administrator approves the business, a trial starts for 14 days or a selected paid plan produces its invoice. Paid selling access starts after payment is verified. Existing suppliers without a plan can choose their first plan in Billing.

## Supplier plans, renewal and expiry
<!-- audience: supplier -->

Use [Plans and Billing](/billing) from the supplier dashboard. Current launch plans are:

| Plan | Subscription | Active listings | Active team members | Sales commission |
| --- | --- | ---: | ---: | ---: |
| Supplier free trial | UGX 0 for 14 days after approval | 50 | 2 | Waived |
| Supplier Starter | UGX 30,000 / month | 100 | 2 | 5% |
| Supplier Growth | UGX 75,000 / month | 500 | 5 | 3% |
| Supplier Pro | UGX 150,000 / month | 2,000 | 15 | 2% |

Your administrator can change published plans, so review the live card and invoice before paying. Prices exclude applicable platform tax; any gateway processing fees are separate. There is no automatic charge.

Choosing a paid plan creates an invoice. Selling permissions change only after confirmed payment. Early payment preserves unused trial time and its commission waiver. On expiry, public listings pause and new selling activity requires renewal. Your data and existing orders remain accessible.

If a smaller plan does not fit your active listings or team, deactivate listings or remove staff access first. While its invoice awaits payment, the lower pending limits also apply to new records. Do not create another supplier account to restart a trial.

## Supplier catalog and listings
<!-- audience: supplier -->

The [Supplier catalog](/supplier/catalog) contains shared product definitions. A **listing** is your own offer for one of those products: price, stock, condition and delivery information.

1. Find the product in the shared catalog. Confirm brand, part number and unit before using it.
2. If the product is missing, an approved supplier with active selling access may **Propose product** when submissions are enabled. Enter the product details and submit it for platform moderation.
3. Open [Listings](/supplier/listings) and create an offer against the correct product. Enter supplier SKU, price, stock, minimum order quantity, lead time and condition.
4. Save and check the listing's active state. Only active, in-stock offers against approved active products from suppliers with selling access appear in public browsing.

Supplier business approval and product moderation are separate. A proposed product does not become publicly approved merely because its supplier is approved. Inactive listings do not use active-listing capacity. Expired suppliers can deactivate offers to prepare for a smaller plan; reactivation requires valid selling access.

## Supplier quotes, orders and commission
<!-- audience: supplier -->

Open [Quote inbox](/supplier/quotes/inbox) to review enquiries available to your business. A request for quotation is often abbreviated **RFQ**.

1. Open an available RFQ and review each requested product and quantity.
2. Enter the quantities you can supply, unit prices, tax, validity date and notes.
3. Save a draft while preparing the offer, or submit the quote when ready. Submitted quotes become read-only in the quote composer.
4. Follow incoming [Supplier orders](/supplier/orders). Check the buyer, products, quantities and agreed terms before arranging fulfilment.

The current supplier fulfilment page shows the ordered and received items; it does not yet provide a complete shipping/dispatch workflow. Agree delivery details with the buyer through your normal business process. The buyer's goods-receipt action is what records receipt and credits their stock.

Commission rates are recorded at order acceptance. Orders accepted during the trial have commission waived even if they finish later. A commission invoice is raised when the order is completed; receipt and full payment can complete the order. Check that invoice separately from the recurring subscription.

Cancelling an order waives provisional commission and cancels an unpaid commission invoice. If commission has already been paid, the transaction is flagged for refund review. Contact platform support for refunds or partial refunds; the system does not automatically return funds through the gateway. Paying a commission invoice does not extend the subscription.

## Managing a supplier team
<!-- audience: supplier -->

Supplier administrators can open [Manage team](/supplier/team) from their dashboard. The seat limit includes active administrators and staff.

1. Choose **Add a team member** and enter the person's name, email and initial password.
2. Confirm the password and save. The account receives supplier staff permissions, not supplier administrator permissions.
3. Share the initial credentials securely. The person must change the initial password at first sign-in.
4. To remove access, choose **Remove access** on the team page and confirm. The account is deactivated while historical records are preserved.

Adding members needs approved selling access and available seats. You can remove access after expiry to fit a smaller plan. Removing a staff member does not remove the supplier business or its historical orders.

## Buying parts through the marketplace
<!-- audience: garage,staff -->

Browse the public [Storefront](/storefront) or use the authenticated [Marketplace](/marketplace) when your role has buying permissions.

For a direct listing purchase, inspect the product, supplier, condition, unit price, stock, minimum quantity and lead time before creating an order. A stale or expired listing can become unavailable before the purchase is saved.

For competitive quotations:

1. Create an [RFQ](/marketplace/rfqs) with the required products, quantities and delivery requirements.
2. Publish or invite suppliers using the available controls.
3. Compare submitted quotes for price, quantity, tax, validity and delivery terms.
4. Award the selected quote and review the resulting purchase order.

Open [Purchase orders](/marketplace/purchase-orders) to follow orders. When goods arrive, open the receipt screen and enter the quantity actually received for each item. Partial receipts leave outstanding quantities. Review the linked stock change before recording another receipt.

Marketplace customer-order payment recording and GarageHQ subscription checkout are separate workflows. Confirm the agreed payment and delivery arrangements for a parts order; do not assume creating a purchase order collects money automatically.

## Paying GarageHQ subscriptions and platform invoices
<!-- audience: garage,supplier -->

The business owner or supplier administrator can use [Billing](/billing) to choose a plan, inspect invoices and start checkout.

1. Review the invoice type, currency, amount and due date. Subscription, setup and marketplace commission charges may differ.
2. Select **Pay Now**. GarageHQ opens Flutterwave-hosted checkout through its payment integration.
3. Complete or cancel checkout on the provider's page. Return to GarageHQ and read the resulting status.
4. If confirmation is pending, allow time for the background check and review Billing again. A browser success message alone does not activate a subscription until the payment is verified.

Closing checkout does not necessarily mean the payment failed. Before paying a second time, inspect the invoice and any pending confirmation. Contact support with the invoice number and payment reference if money was taken but the invoice remains unpaid. Do not send card details, passwords or OTPs.

Subscriptions renew manually. A card used in checkout does not enable automatic renewal for self-service accounts. If an unpaid plan invoice already exists, selecting that plan again reuses it; the screen may require completing that invoice before choosing another plan.

## Notifications and getting help
<!-- audience: all -->

Use the notification bell or [Notifications](/notifications) for in-app messages. Some events also send email, including trial reminders and payment receipts when delivery is configured. Email acceptance by a provider does not guarantee that the message reached your inbox; check spam and any mailbox rules.

When asking for help, provide your business name, affected branch if relevant, the page you were using, the record/invoice number, the action taken and the visible error. Include an approximate time. Share screenshots only after removing customer, payment and credential information that the recipient does not need.

| Problem | What to check first |
| --- | --- |
| Cannot sign in | Correct login email; current password; inactive user or suspended business |
| Verification code missing | Correct address; spam folder; resend countdown; delivery service availability |
| Supplier cannot sell | Email verified, business approved, plan active, product approved, listing active and in stock |
| Record not visible | Current branch, filters, ownership and assigned permissions |
| Plan limit reached | Active listing/member count and any smaller plan invoice awaiting payment |
| Payment still pending | Invoice status and payment reference; allow verification before retrying |
| Missing menu or forbidden action | Role and permissions; ask the administrator instead of trying another account |
| No active branch | Select an authorised branch before entering garage transactions |

Use the platform's [Billing support](/billing/support) page where available. This guide remains accessible even when subscription access is restricted.

## Platform administration
<!-- audience: admin -->

These tasks require platform roles and permissions. Business owners and supplier administrators cannot grant themselves platform access.

**Organisations:** use [Platform vendors](/platform/vendors) to find the business, inspect its state and manage permitted administrative actions. Review the business details before approving a supplier. Supplier approval starts a selected trial or prepares its paid invoice; removing and restoring approval does not restart an existing trial.

**Products:** use [Catalog moderation](/platform/catalog) to review supplier-proposed products. Check identifiers, naming, category and units before marking a product verified. Business approval alone does not moderate individual products.

**Pricing:** use [Platform pricing](/platform-pricing). Choose the correct audience, price, billing period, commission and limits. Garage and supplier plans are separate. A subscribed plan cannot be moved to another audience. Prices and capacity should be checked before publishing a plan change.

**Billing:** use [Platform billing](/platform/billing) to review invoices and financial exceptions available to your role. Supplier commission refund flags require operator review. Do not mark a payment received without supporting evidence.

**Integrations:** use [API Integrations](/platform/integrations) to configure Cream Theme, OnePass and other enabled services. Use a dedicated partner account and the correct service base URL. Match test/live payment settings to the provider; production payment collection requires live configuration. Run the connection checks and read the error details. Certificate, authentication and quota failures need different fixes. Credentials belong only in the protected integration settings.

Subscription email delivery status and retry controls are available for the configured mail integration. Background reminders and reconciliation depend on the scheduler and queue workers running. If queued messages or payment checks stop progressing, ask the deployment operator to inspect these services.

## Asking the GarageHQ assistant
<!-- audience: all -->

When enabled, signed-in users can select **Ask GarageHQ** in the lower-right corner of the app or help centre. It explains the approved user guide and links to relevant chapters. Supplier questions use supplier guidance; garage users receive garage guidance. The assistant cannot inspect records, confirm an actual payment, change a plan or perform work on your behalf.

1. Open **Ask GarageHQ** and review the data-sharing notice. The shared assistant runs on the platform server and sends the question and matching documentation excerpts to its configured AI provider for an answer. Platform administrators can select Ollama Cloud, Groq or self-hosted Ollama per tenant in the Cot assistant settings. Cloud providers process requests outside GarageHQ; a self-hosted provider uses the configured model host.
2. Confirm the notice, then enter a complete question such as “How do I receive parts against a purchase order?” Do not include customer details, vehicle identifiers, passwords, verification codes or API keys.
3. Read the explanation and follow its guide links. Check the app for your actual permissions, plan, prices, records and payment state; AI answers can contain mistakes.
4. Ask each new question in full. Earlier messages remain visible only on the current page and are not sent as conversation history. **Clear chat** removes them from the widget; leaving the page clears the conversation too.

The assistant is available to active signed-in users, including when a subscription has expired. Requests have per-user and service-wide limits. If the service is busy or unavailable, use the ordinary user guide and retry later. A business administrator can help with missing permissions or operational questions beyond the guide.

## API reference for integration partners
<!-- audience: admin -->

Open the [Swagger UI API reference](/api-docs) for the implemented GarageHQ API and the connected Cream Theme and OnePass contracts. Select the service before reading its server URL, authentication, request body and response schemas. Download its OpenAPI document when preparing a client integration.

GarageHQ's application API uses its own authentication. Cream Theme partner headers and OnePass bearer keys are different credentials and belong on backend requests to their respective services. Livewire form actions and ordinary authenticated web pages are not a general REST API for signup, subscriptions or marketplace operations.

The job-card API is a legacy intake interface and uses field names that differ from the current work-order screens. Validate compatibility in development before relying on it; the work-order interface is the normal operational workflow. The API reference documents the existing contract rather than adding new endpoints.

Prepare PHP HTTPS certificate trust for both the web runtime and workers. A request can succeed in command-line PHP and fail under Apache when their CA configuration differs. The reference's integration notes cover cURL error 60. A partner credential cannot fix a TLS verification error, which occurs before API authentication.

**Partner HTTPS setup checklist:**

1. Identify the `php.ini` loaded by Apache/PHP-FPM and the one loaded by command-line PHP (`php --ini`). They may be different files or PHP versions. Do not leave a public `phpinfo()` page available after checking the web runtime.
2. Install a current CA certificate bundle from a trusted source. On installations that need an explicit bundle, set `curl.cainfo` and `openssl.cafile` to its absolute path in the relevant PHP configuration. Ensure the web-server and worker users can read it. For example, a Windows installation might use `C:/php/certs/cacert.pem`; use your actual installed path.
3. Restart Apache/PHP-FPM and long-running queue workers after changing their configuration. Clear/rebuild application configuration if application-level certificate settings changed.
4. Test HTTPS from the running web application as well as from the command line. A successful command-line check alone does not prove Apache has working certificate trust.
5. If cURL error 60 persists, check the destination server's certificate chain, certificate expiry and the machine clock. Keep TLS verification enabled; disabling it exposes credentials and payment traffic.

In GarageHQ, the optional `INTEGRATIONS_CA_BUNDLE` environment setting can point the integration HTTP client to a readable CA bundle. This setting applies to the configured partner integrations; it does not replace certificate setup for other PHP applications. Keep paths specific to the deployment and never copy a developer's machine path into production.

The embedded Swagger UI is a read-only reference: it does not submit live signup, payment or operational requests. Use an authorised test client and test environment for integration execution. Never paste production credentials into a public screenshot or support conversation.

## Daily checklists and glossary
<!-- audience: all -->

**Garage opening:** confirm branch, staff availability, bay status, expected appointments, carried-over jobs and needed stock. **Garage closing:** review unfinished work, handovers, actual payments, invoice balances, expenses and stock exceptions.

**Supplier opening:** check plan/approval state, stock and prices, RFQs, submitted quotes and existing orders. **Supplier closing:** confirm deliveries with buyers, review receipt discrepancies, outstanding invoices and any trial/renewal reminders.

**Administrator review:** check supplier/product approval queues, published plans, integration status, queued delivery failures, payment exceptions and refund-review flags.

| Term | Meaning |
| --- | --- |
| Branch | An authorised location within a garage business |
| Work order | A garage repair/service job and its operational history |
| Wash order | A wash/detailing job and its queue state |
| Quotation | Proposed work or goods and prices awaiting a decision |
| Debit note / additional-work request | Extra proposed job items with customer decisions |
| Catalog product | A shared parts definition, separate from a supplier's offer |
| Listing | One supplier's price, stock and terms for a catalog product |
| RFQ | Request for quotation sent or made available to suppliers |
| Purchase order / PO | A buyer's order for supplied goods |
| Goods receipt | A record of quantities actually received against a PO |
| Subscription invoice | A platform bill for access to GarageHQ |
| Marketplace commission | A supplier's platform fee linked to completed marketplace sales |
| Staff commission | Employee earnings under configured business rules |
