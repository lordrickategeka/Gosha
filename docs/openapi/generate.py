"""Build the checked-in API references from the current controller/partner contracts.

Run: python docs/openapi/generate.py. Review contracts when endpoints change.
This script performs no network requests and reads no application credentials.
"""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent
TLS = "Before connecting, configure trusted HTTPS certificates in both web PHP and CLI workers. cURL error 60 can indicate missing/outdated CA trust or a server certificate-chain problem; Apache and CLI may load different php.ini files. Keep TLS verification enabled. See /user-guide#api-reference-for-integration-partners."


def ref(name):
    return {"$ref": f"#/components/schemas/{name}"}


def obj(properties, required=(), **extra):
    schema = {"type": "object", "properties": properties, **extra}
    if required:
        schema["required"] = list(required)
    return schema


def string(**extra):
    return {"type": "string", **extra}


def integer(**extra):
    return {"type": "integer", **extra}


def response(description, schema=None, **extra):
    return {"description": description, **({"content": {"application/json": {"schema": schema}}} if schema else {}), **extra}


def body(schema, example=None):
    return {"required": True, "content": {"application/json": {"schema": schema, **({"example": example} if example else {})}}}


def parameter(name, location="query", schema=None, required=False, description=""):
    return {"name": name, "in": location, "required": required, "description": description, "schema": schema or string()}


def operation(summary, operation_id, tags, responses, description="", **extra):
    return {"summary": summary, "operationId": operation_id, "tags": tags, "description": description, "responses": responses, **extra}


def base(title, description, servers, schemes, security):
    return {"openapi": "3.0.3", "info": {"title": title, "version": "1.0.0", "description": description + "\n\n" + TLS},
            "servers": servers, "security": security, "paths": {}, "components": {"securitySchemes": schemes, "schemas": {}}}


garage = base("GarageHQ application API", "Implemented application endpoints. These are separate from the Livewire web workflows. No general REST endpoints for garage/supplier registration, marketplace selling, plan selection or subscription checkout are exposed by this reference. Send Accept: application/json. Obtain a Sanctum token through an authorised administrator/workflow; this API does not expose a token-issuing endpoint. First-party browser sessions also depend on Sanctum stateful configuration and CSRF protection for writes.",
              [{"url": "/", "description": "Current GarageHQ instance"}, {"url": "https://garagehq.loganate.cc", "description": "Production"}],
              {"sanctumBearer": {"type": "http", "scheme": "bearer", "description": "GarageHQ Sanctum personal access token, not a OnePass JWT or Cream Theme key."},
               "legacyWebhookHash": {"type": "apiKey", "in": "header", "name": "verif-hash", "description": "Configured direct Flutterwave webhook secret; never a client login token."}}, [{"sanctumBearer": []}])
garage["components"]["schemas"] = {
    "User": obj({"id": integer(), "name": string(), "email": string(format="email"), "vendor_id": integer(nullable=True), "is_active": {"type": "boolean"}}, ["id", "name", "email"], additionalProperties=True),
    "Error": obj({"message": string(), "errors": {"type": "object", "additionalProperties": {"type": "array", "items": string()}}, "billing_url": string(format="uri")}),
    "CustomerSearchResult": obj({"id": integer(), "label": string(), "customer_name": string(nullable=True), "phone": string(nullable=True), "email": string(nullable=True), "company_name": string(nullable=True), "contact_person": string(nullable=True), "address": string(nullable=True)}),
    "VehicleSearchResult": obj({"id": integer(), "label": string(), "vehicle_name": string(nullable=True), "number_plate": string(nullable=True), "chasis_number": string(nullable=True), "color": string(nullable=True), "vehicle_type_id": integer(nullable=True), "customer_id": integer(nullable=True), "customer_name": string(nullable=True)}),
}
errors = {"401": response("Authentication required.", ref("Error")), "403": response("Inactive account or subscription access denied.", ref("Error"))}
garage["paths"]["/api/user"] = {"get": operation("Read the authenticated user", "getCurrentUser", ["Account"], {"200": response("User record; hidden credentials are excluded.", ref("User")), "401": errors["401"]})}
for path, schema, kind in [("search-customers", "CustomerSearchResult", "customers"), ("search-vehicles", "VehicleSearchResult", "vehicles")]:
    params = [parameter("query", schema=string(), description="Search text. Fewer than two bytes returns an empty list. Results are limited to ten.")]
    if kind == "vehicles":
        params.append(parameter("customer_id", schema=integer(minimum=1), description="Legacy query broadens results using OR customer_id; it is not a strict ownership filter."))
    garage["paths"][f"/api/job-cards/{path}"] = {"get": operation(f"Search legacy intake {kind}", "searchIntake" + kind.title(), ["Legacy intake"],
        {"200": response("Matching records, or an empty list.", {"type": "array", "items": ref(schema)}), **errors, "500": response("Legacy schema/query failure.", ref("Error"))},
        "Legacy intake field names may differ from current customer/vehicle screens. Validate schema compatibility in development.", parameters=params)}
intake = obj({
    "staff_id": integer(minimum=1), "service_type_id": integer(nullable=True), "service_types": {"type": "array", "items": integer()},
    "phone": string(maxLength=20), "customer_name": string(maxLength=255), "company_name": string(maxLength=255, nullable=True),
    "email": string(format="email", maxLength=255, nullable=True), "contact_person": string(maxLength=255, nullable=True), "address": string(nullable=True),
    "vehicle_type_id": integer(minimum=1), "vehicle_name": string(maxLength=255), "number_plate": string(maxLength=20),
    "chasis_number": string(maxLength=50, nullable=True, description="Legacy spelling required by this controller."), "color": string(maxLength=50, nullable=True),
    "image_attachment": string(nullable=True), "status": string(enum=["pending", "in_progress", "completed", "collected"], default="pending"),
    "notes": string(nullable=True), "intake_datetime": string(format="date-time", nullable=True),
}, ["staff_id", "phone", "customer_name", "vehicle_type_id", "vehicle_name", "number_plate"])
garage["components"]["schemas"]["LegacyIntakeRequest"] = intake
garage["paths"]["/api/job-cards"] = {"post": operation("Create a legacy intake job card", "createLegacyJobCard", ["Legacy intake"],
    {"201": response("Job card created.", obj({"success": {"type": "boolean"}, "message": string(), "data": {"type": "object", "additionalProperties": True}}, ["success", "message", "data"])),
     **errors, "422": response("Request validation failed.", ref("Error")), "500": response("Creation failed; current database may not support the legacy fields.", obj({"success": {"type": "boolean"}, "message": string(), "error": string()}))},
    "Legacy interface, not the current work-order wizard. The legacy service's customer_name, number_plate and related fields require schema compatibility. Existing-record IDs must be appropriate for the caller's business. Use the operational web screens for normal repair intake.",
    requestBody=body(ref("LegacyIntakeRequest"), {"staff_id": 1, "phone": "+256700000000", "customer_name": "Example customer", "vehicle_type_id": 1, "vehicle_name": "Example vehicle", "number_plate": "TEST001"}))}
garage["paths"]["/api/webhooks/flutterwave"] = {"post": operation("Receive a legacy direct Flutterwave event", "receiveLegacyFlutterwaveWebhook", ["Legacy payment webhook"],
    {"200": {"description": "OK, or Webhook not configured when the legacy integration is disabled.", "content": {"text/plain": {"schema": string(enum=["OK", "Webhook not configured"])}}},
     "401": {"description": "Missing or incorrect verif-hash.", "content": {"text/plain": {"schema": string(example="Invalid signature")}}}},
    "Provider-only receiver for the direct Flutterwave integration. New self-service payments use Cream Theme's own webhook receiver and partner verification API instead. The receiver verifies successful charge.completed transactions server-to-server before recording payment.",
    security=[{"legacyWebhookHash": []}], requestBody=body(obj({"event": string(example="charge.completed"), "data": obj({"id": integer(), "tx_ref": string(), "status": string(example="successful")})})))}

cream = base("Cream Theme partner API", "Backend-to-backend hosted payments and transactional mail used by GarageHQ. Both partner headers are required together. Partners use one centrally managed Flutterwave account and do not receive Flutterwave keys. Configure test/live mode in the Cream Theme partner account; every payment retains its original environment. This reference describes the partner payment/mail contracts, not all Cream Theme products. Never trust browser redirect claims as payment proof.",
             [{"url": "https://comhub.loganate.cc", "description": "Cream Theme service; use the operator-confirmed root URL for your environment"}],
             {"partnerKey": {"type": "apiKey", "in": "header", "name": "X-Api-Key"}, "partnerSecret": {"type": "apiKey", "in": "header", "name": "X-Api-Secret"}}, [{"partnerKey": [], "partnerSecret": []}])
cream["components"]["schemas"] = {
    "PartnerError": obj({"error": string(description="Stable error code, e.g. invalid_api_credentials, partnership_expired, payment_quota_exceeded, daily_send_quota_exceeded, mail_not_configured."), "messageId": integer(description="May be returned for a logged failed mail send.")}, ["error"]),
    "Payment": obj({"paymentId": integer(), "txRef": string(), "reference": string(nullable=True), "amount": {"type": "number"}, "currency": string(minLength=3, maxLength=3),
        "status": string(enum=["pending", "successful", "failed", "cancelled"]), "environment": string(enum=["test", "live"]),
        "createdAt": string(format="date-time"), "confirmedAt": string(format="date-time", nullable=True), "checkoutUrl": string(format="uri", description="Returned on checkout initiation when applicable.")}),
    "MailResult": obj({"messageId": integer(), "status": string(enum=["sent", "failed"]), "providerMessageId": string(nullable=True)}, ["messageId", "status"]),
}
partner_errors = {str(status): response(desc, ref("PartnerError")) for status, desc in [
    (400, "Invalid request; inspect the stable error code."), (401, "Missing/invalid partner credentials."), (403, "Inactive or expired partner."),
    (404, "Requested partner record/template was not found."), (429, "Partner quota reached."), (500, "Internal or mail-provider error."), (503, "Payment or mail provider is not configured.")]}
payment_request = obj({"amount": {"type": "number", "exclusiveMinimum": True, "minimum": 0}, "currency": string(pattern="^[A-Za-z]{3}$"),
    "customerEmail": string(format="email"), "customerName": string(), "redirectUrl": string(format="uri"), "narration": string(), "reference": string(), "meta": {"type": "object", "additionalProperties": True}},
    ["amount", "currency", "customerEmail", "redirectUrl"])
cream["paths"]["/api/partner-keys/payments/initiate"] = {"post": operation("Start hosted checkout", "initiatePartnerPayment", ["Hosted payments"],
    {"201": response("Payment record and checkout URL when pending.", ref("Payment")), **partner_errors},
    "Use a stable reference for idempotency. A retry while pending reuses the transaction with its original amount/fields. A resolved reference returns the final record. Redirect the user to checkoutUrl; verify the returned txRef before granting access. Amount must be positive; a zero-cost trial does not need this endpoint.",
    requestBody=body(payment_request, {"amount": 30000, "currency": "UGX", "customerEmail": "buyer@example.com", "customerName": "Example buyer", "redirectUrl": "https://partner.example.com/payments/return", "reference": "invoice-example-001", "meta": {"invoiceId": "example-001"}}))}
for path, name, summary in [("/{txRef}/verify", "verifyPartnerPayment", "Verify a payment with Flutterwave"), ("/{txRef}", "getPartnerPayment", "Read a stored payment")]:
    cream["paths"]["/api/partner-keys/payments" + path] = {"get": operation(summary, name, ["Hosted payments"],
        {"200": response("Partner-scoped payment record.", ref("Payment")), **partner_errors, "502": response("upstream_verify_failed: Flutterwave verification failed.", ref("PartnerError"))},
        "Use /verify for authoritative confirmation. Stored status alone and browser callback query parameters are insufficient.",
        parameters=[parameter("txRef", "path", required=True, description="The txRef returned by initiation, not the optional partner reference.")])}
cream["paths"]["/api/partner-keys/payments"] = {"get": operation("List this partner's payments", "listPartnerPayments", ["Hosted payments"], {"200": response("Newest first.", {"type": "array", "items": ref("Payment")}), **partner_errors})}
recipient = {"oneOf": [string(), {"type": "array", "items": string()}]}
shared_mail = {"to": recipient, "from": string(description="Optional verified sender; defaults to configured sender."), "cc": {"type": "array", "items": string()}, "bcc": {"type": "array", "items": string()}, "replyTo": string()}
for path, name, template in [("send", "sendPartnerMail", False), ("send-template", "sendPartnerTemplateMail", True)]:
    fields = {**shared_mail, **({"templateKey": string(), "variables": {"type": "object", "additionalProperties": True}} if template else {"subject": string(), "html": string(), "text": string()})}
    cream["paths"]["/api/mail/" + path] = {"post": operation("Send a template email" if template else "Send a transactional email", name, ["Transactional mail"],
        {"201": response("Provider accepted the email; this is not an inbox-delivery guarantee.", ref("MailResult")), **partner_errors},
        "Every send attempt is logged and counts toward quota. No mail idempotency key is documented; an ambiguous timeout may require operator checking before resending. Template placeholders use {{variableName}}; omitted values render empty.",
        requestBody=body(obj(fields, ["to", "templateKey"] if template else ["to", "subject", "html"])))}
cream["paths"]["/api/mail/messages/{id}"] = {"get": operation("Read a sent/failed mail record", "getPartnerMailMessage", ["Transactional mail"],
    {"200": response("Record scoped to the calling partner.", obj({**shared_mail, **cream["components"]["schemas"]["MailResult"]["properties"], "templateKey": string(nullable=True), "subject": string(), "error": string(nullable=True), "createdAt": string(format="date-time")})), **partner_errors}, parameters=[parameter("id", "path", integer(minimum=1), True)])}

otp = base("OnePass OTP partner API", "Backend-to-backend email/phone ownership verification. Use a dedicated GarageHQ partner bearer key. Returned OnePass JWTs and otp_session cookies are not GarageHQ application sessions. Email is available when configured; phone delivery requires a configured SMS provider. Defaults: code lifetime five minutes, three code attempts, resend cooldown one minute; administrator settings can differ. Do not blindly retry OTP sends. Honor Retry-After and keep codes/keys/tokens out of logs.",
           [{"url": "https://onepass.loganate.cc", "description": "Current integration host; confirm the service root with the OnePass operator"}],
           {"onepassPartnerKey": {"type": "http", "scheme": "bearer", "description": "Partner API key, not an OTP response JWT or GarageHQ access token."}}, [{"onepassPartnerKey": []}])
otp["components"]["schemas"] = {
    "Identifier": {"oneOf": [string(format="email"), string(pattern="^\\+[1-9][0-9]{7,14}$")], "description": "Email is normalized to lowercase; phones must be E.164."},
    "OtpError": obj({"error": string(), "code": string(enum=["invalid_request", "unauthorized", "otp_expired", "invalid_code", "too_many_attempts", "rate_limited", "delivery_failed", "delivery_unconfigured", "unavailable"])}, ["error", "code"]),
}
otp_errors = {str(status): response(desc, ref("OtpError")) for status, desc in [(400, "Missing or invalid identifier/code."), (401, "Invalid API key, expired code, wrong code or exhausted code attempts; inspect code."), (502, "OTP delivery failed."), (503, "Service or selected delivery channel is unavailable/unconfigured.")]}
otp_errors["429"] = response("Request or verification rate limit reached.", ref("OtpError"), headers={"Retry-After": {"description": "Seconds before retrying when supplied.", "schema": integer()}})
otp["paths"]["/auth/otp/request"] = {"post": operation("Send an email or phone OTP", "requestOnePassOtp", ["OTP"],
    {"200": response("Code dispatched through the configured provider.", obj({"message": string(example="OTP dispatched")})), **otp_errors},
    "Resending replaces the pending challenge for that identifier. Default request limits include one per identifier per minute, five per identifier per 15 minutes, and 20 per client IP per 15 minutes. Trusted proxy configuration matters for backend callers.",
    requestBody=body(obj({"identifier": ref("Identifier")}, ["identifier"]), {"identifier": "person@example.com"}))}
otp["paths"]["/auth/otp/verify"] = {"post": operation("Verify and consume an OTP", "verifyOnePassOtp", ["OTP"],
    {"200": response("Code accepted and consumed. Establish the calling application's own session separately.", obj({"message": string(example="Authentication successful"), "token": string(description="Short-lived OnePass JWT, 15-minute default.")}, ["message", "token"])), **otp_errors},
    "Use the same identifier as the request. Verification is rate-limited separately. An empty request with a valid partner key returns 400 invalid_request before OTP/rate-limit processing; GarageHQ uses that non-delivery probe alongside health readiness for its connection test.",
    requestBody=body(obj({"identifier": ref("Identifier"), "code": string(pattern="^[0-9]{6}$", minLength=6, maxLength=6)}, ["identifier", "code"]), {"identifier": "person@example.com", "code": "123456"}))}
for path, name, readiness in [("ready", "getOnePassReadiness", True), ("live", "getOnePassLiveness", False)]:
    otp["paths"]["/health/" + path] = {"get": operation("Check readiness" if readiness else "Check liveness", name, ["Health"],
        {"200": response("Service ready." if readiness else "Process running.", obj({"status": string(example="ready" if readiness else "ok")})), **({"503": response("Redis/service is unavailable.", obj({"status": string()}))} if readiness else {})},
        "Open endpoint; readiness checks Redis, not partner credentials or end-to-end message delivery.", security=[])}

for name, document in [("garagehq", garage), ("cream-theme", cream), ("onepass", otp)]:
    (ROOT / f"{name}.json").write_text(json.dumps(document, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"Built {name}.json ({len(document['paths'])} paths)")
