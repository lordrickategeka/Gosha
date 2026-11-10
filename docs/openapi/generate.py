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

garage["components"]["securitySchemes"].update({
    "webSession": {"type": "apiKey", "in": "cookie", "name": "laravel_session", "description": "Authenticated browser session. Default cookie name shown; replace with the deployment's SESSION_COOKIE. This endpoint does not accept Sanctum bearer tokens."},
    "csrfHeader": {"type": "apiKey", "in": "header", "name": "X-CSRF-TOKEN", "description": "CSRF token for the same authenticated browser session. Both session and CSRF are required."},
})
chat_result = obj({"answer": string(maxLength=6000), "sources": {"type": "array", "items": obj({"title": string(), "path": string()})}}, ["answer", "sources"])
garage["paths"]["/assistant/chat"] = {"post": operation("Ask the GarageHQ documentation assistant", "askGarageHqAssistant", ["Documentation assistant"],
    {"200": response("Plain-text answer and approved guide links.", chat_result),
     **{str(code): response(description, ref("Error")) for code, description in [(401,"Sign in required."),(403,"Inactive user."),(419,"Session expired or CSRF mismatch."),(422,"Question or consent validation failed."),(503,"Assistant unavailable; use the guide.")]},
     "429": response("Per-user, global or shared-service limit reached.", ref("Error"), headers={"Retry-After": {"schema": integer()}})},
    "First-party same-origin browser endpoint. Requires web-session authentication, CSRF and explicit consent to send the question and selected guide excerpts to the shared assistant and the configured AI provider. Role and pseudonymous actor are derived on the server; client-supplied actor, role, docs and history are ignored. No operational records are accessed and no actions are performed. Limits: six requests per user per minute, 100 per day, 60 globally per minute. Help remains available during subscription expiry. Responses are not cached.",
    security=[{"webSession": [], "csrfHeader": []}], requestBody=body(obj({"question": string(minLength=1,maxLength=1200), "consent": {"type":"boolean","enum":[True]}}, ["question","consent"]), {"question":"How does the free trial work?","consent":True}))}

assistant = base("Shared System Assistant API", "Backend contract for the shared Salord System Assistant. The calling product authenticates its users and supplies approved documentation for each request. This is document retrieval, not model fine-tuning or access to operational databases. Matching excerpts and the question are sent to the configured AI provider. Use a dedicated system-specific bearer key on the backend only. A configured tenant mapping selects the active tenant and its model settings.",
    [{"url":"https://cot.loganate.cc"}], {"systemBearer":{"type":"http","scheme":"bearer","description":"Dedicated key paired with system in SUPPORT_AGENT_API_KEYS_JSON."}}, [{"systemBearer":[]}])
assistant["components"]["schemas"] = {
    "Guide": obj({"id":string(),"title":string(),"content":string(),"source":string(),"primaryPath":string(description="Local product path beginning with a single slash, required for API retrieval.")},["title","content","primaryPath"]),
    "AssistantError":obj({"error":string()},["error"]),
}
assistant["paths"]["/health"] = {"get":operation("Check service liveness","getAssistantHealth",["Health"],{"200":response("Service running; does not validate keys or model readiness.",obj({"status":string()}))},security=[])}
assistant["paths"]["/v1/chat"] = {"post":operation("Explain approved product documentation","askSharedSystemAssistant",["Chat"],
    {"200":response("Answer and sources. No matching guides also returns 200, without a model call.",chat_result),
     **{str(code):response(description,ref("AssistantError")) for code,description in [(400,"Invalid question, actor, JSON or docs."),(401,"System/key pair rejected."),(413,"Request body exceeds two MiB."),(429,"Actor rate limit reached."),(500,"Internal error."),(502,"Model unavailable or returned invalid output."),(503,"Mapped tenant or model is not configured/active.")]}},
    "Question limit: 1,200 characters. Documentation limit: 220,000 UTF-8 bytes of JSON. Retrieves up to five matching guides with valid local paths and returns up to eight links. Actor is a product-local pseudonymous user identifier. No chat history, operational actions, live records or browser tools are supported. Do not retry timeouts automatically: the model may already have processed the request.",
    requestBody=body(obj({"system":string(pattern="^[a-z0-9][a-z0-9-]{1,63}$",example="garagehq"),"actor":string(pattern="^[a-zA-Z0-9:_-]+$",maxLength=80),"role":string(maxLength=40),"question":string(minLength=1,maxLength=1200),
        "docs":obj({"overview":obj({"intro":string()}),"supportGuides":{"type":"array","items":ref("Guide")},"moduleGuides":{"type":"array","items":ref("Guide")}})},["system","actor","question","docs"]))) }

assistant["components"]["securitySchemes"]["adminSession"] = {"type":"apiKey","in":"cookie","name":"__Host-agent_session","description":"Cot production session for an active platform administrator. Local development uses agent_session. Partner bearer keys do not authorize these administration endpoints."}
tenant_header = parameter("X-Tenant-Slug", location="header", description="Active tenant slug; defaults to default. The same administrator session can configure authorized tenants.")
origin_header = parameter("Origin", location="header", required=True, description="Same origin as Cot, required for writes, e.g. https://cot.loganate.cc.")
provider_enum = string(enum=["ollama_cloud","groq","ollama_local"])
admin_errors = {"401":response("Sign in required.",ref("AssistantError")),"403":response("Platform administrator required, or write origin rejected.",ref("AssistantError"))}
assistant["paths"]["/api/admin/providers"] = {"get":operation("List selectable AI providers","listAssistantProviders",["Provider administration"],
    {"200":response("Provider labels, descriptions, default model suggestions and configuration flags. Contains no keys or server addresses.",obj({"providers":{"type":"array","items":obj({"id":provider_enum,"label":string(),"description":string(),"defaultModel":string(),"configured":{"type":"boolean"}})}})),**admin_errors},
    "A configured flag does not prove inference readiness or account balance. Self-hosted localhost refers to the machine running Cot. Model keys may be saved using the separate credential endpoints. Server environment keys remain defaults; model-server URLs are operator-managed. No keys or ciphertext are returned.",security=[{"adminSession":[]}],parameters=[tenant_header])}
assistant["paths"]["/api/admin/models"] = {"get":operation("List models from a provider","listAssistantProviderModels",["Provider administration"],
    {"200":response("Available model identifiers.",obj({"models":{"type":"array","items":string()}})),**admin_errors,"400":response("Unknown provider.",ref("AssistantError")),"502":response("Provider catalog unavailable or invalid.",ref("AssistantError")),"503":response("Provider not configured.",ref("AssistantError"))},
    "Omitting provider selects the current tenant's saved provider. Groq identifiers can contain a namespace slash. Known cloud aliases are excluded from the self-hosted list. Model listing does not establish available credits or inference readiness.",security=[{"adminSession":[]}],parameters=[tenant_header,parameter("provider",schema=provider_enum)])}
assistant["paths"]["/api/admin/model-test"] = {"post":operation("Test an unsaved provider and model","testAssistantModel",["Provider administration"],
    {"200":response("A short completion succeeded; no tenant settings were changed.",obj({"message":string()})),**admin_errors,"400":response("Invalid provider or model.",ref("AssistantError")),"429":response("Administrative test limit or provider token/request limit reached.",ref("AssistantError")),"502":response("Inference failed.",ref("AssistantError")),"503":response("Provider configuration, access or billing/credit failure.",ref("AssistantError"))},
    "Sends one fixed short prompt to the selected provider. May consume quota or incur charges under the operator's provider plan. Does not save settings or silently switch providers. Provider response bodies and credentials are not returned.",security=[{"adminSession":[]}],parameters=[tenant_header,origin_header],requestBody=body(obj({"provider":provider_enum,"model":string(maxLength=150)},["provider","model"]))) }
assistant["paths"]["/api/admin/settings"] = {
    "get":operation("Read tenant assistant settings","getAssistantTenantSettings",["Provider administration"],{"200":response("Current branding, provider/model and other tenant settings.",obj({"settings":obj({"provider":provider_enum,"model":string()},additionalProperties=True),"tenant":{"type":"object"}})),**admin_errors},security=[{"adminSession":[]}],parameters=[tenant_header]),
    "post":operation("Save tenant provider/model selection","saveAssistantTenantSettings",["Provider administration"],{"200":response("Updated tenant settings.",obj({"settings":{"type":"object"}})),**admin_errors,"400":response("Invalid settings, unsupported fields or selected provider not configured.",ref("AssistantError"))},
        "Accepts partial updates of recognized tenant settings. Selection applies to that tenant's chat, streaming answers, code explanations and mapped partner systems. Other tenants are unaffected. Existing tenants retain their saved model and default to Ollama Cloud. Provider keys and URLs cannot be saved here. There is no automatic paid-provider fallback.",security=[{"adminSession":[]}],parameters=[tenant_header,origin_header],requestBody=body(obj({"provider":provider_enum,"model":string(maxLength=150)},additionalProperties=True),{"provider":"groq","model":"openai/gpt-oss-120b"}))}

credential_schema = obj({"id":provider_enum,"configured":{"type":"boolean"},"hasSavedKey":{"type":"boolean"},"hasApiKey":{"type":"boolean"},"credentialSource":string(enum=["saved","environment","none"]),"locked":{"type":"boolean"}},additionalProperties=True)
credential_errors = {**admin_errors,"400":response("Invalid provider, key or unsupported field.",ref("AssistantError")),"429":response("Credential operation rate limit reached.",ref("AssistantError")),"503":response("Encrypted credential cannot be unlocked; restore the matching encryption key/database backup.",ref("AssistantError"))}
assistant["paths"]["/api/admin/provider-credentials"] = {
    "get":operation("Read masked key status for a tenant/provider","getAssistantCredentialStatus",["Provider credentials"],{"200":response("Status only; no plaintext key or ciphertext.",obj({"credential":credential_schema})),**admin_errors,"400":credential_errors["400"]},security=[{"adminSession":[]}],parameters=[tenant_header,parameter("provider",schema=provider_enum,required=True)]),
    "post":operation("Save or replace an encrypted tenant provider key","saveAssistantProviderKey",["Provider credentials"],{"200":response("Key saved. Response contains only status and a safe message.",obj({"credential":credential_schema,"message":string()})),**credential_errors},
        "Dedicated model-provider credential endpoint, separate from branding/model settings and system integration bearer keys. AES-256-GCM encrypts the key at rest and binds it to the tenant/provider. The encryption key is kept in a private file beside the database. Saved UI keys take precedence over environment defaults for this tenant only and apply immediately to requests using that provider. Responses never echo the key. Back up the database and its .credentials.key file together.",security=[{"adminSession":[]}],parameters=[tenant_header,origin_header],requestBody=body(obj({"provider":provider_enum,"apiKey":string(minLength=8,maxLength=4096,writeOnly=True,description="New key; printable ASCII, without spaces or line breaks. Stored keys are never returned. Omit/blank is rejected, not a request to clear the key.")},["provider","apiKey"],additionalProperties=False))),
    "delete":operation("Remove a tenant key override and use server defaults","deleteAssistantProviderKey",["Provider credentials"],{"200":response("Override removed; any existing environment default is active again.",obj({"credential":credential_schema,"message":string()})),**credential_errors},
        "Removes only this tenant/provider's encrypted override. It does not delete environment keys or affect other tenants. Do not send an API key with deletion.",security=[{"adminSession":[]}],parameters=[tenant_header,origin_header],requestBody=body(obj({"provider":provider_enum},["provider"],additionalProperties=False))) }
assistant["paths"]["/api/admin/provider-credentials/test"] = {"post":operation("Test an entered or saved provider key without saving","testAssistantProviderKey",["Provider credentials"],{"200":response("Provider model catalog reachable. Does not prove inference quota or billing readiness.",obj({"message":string()})),**credential_errors,"502":response("Provider catalog/authentication failed.",ref("AssistantError"))},
    "Uses an entered apiKey transiently; if omitted, uses the saved tenant key or server default. Calls the provider's model catalog without generating a completion or persisting the entered key. For direct self-hosted Ollama the catalog may be public, so success does not establish that a proxy token was checked. Use Test model for inference readiness.",security=[{"adminSession":[]}],parameters=[tenant_header,origin_header],requestBody=body(obj({"provider":provider_enum,"apiKey":string(minLength=8,maxLength=4096,writeOnly=True)},["provider"],additionalProperties=False))) }

branding = obj({"assistantName": string(maxLength=80), "welcomeMessage": string(maxLength=500)}, ["assistantName", "welcomeMessage"])
garage["paths"]["/assistant/config"] = {"get": operation("Read GQ Cot name and greeting", "getGarageAssistantConfig", ["Documentation assistant"],
    {"200": response("Tenant branding, cached for 30 seconds, or local defaults if Cot is unavailable.", branding),
     "401": response("Sign in required.", ref("Error")), "403": response("Inactive user.", ref("Error")), "429": response("Too many requests.", ref("Error"))},
    "First-party authenticated browser endpoint. Provider and system keys remain on the backend. Configure the mapped tenant's Assistant name and Assistant greeting in Cot Settings.", security=[{"webSession": []}])}
assistant["paths"]["/v1/config"] = {"get": operation("Read the mapped tenant's widget identity", "getSystemAssistantConfig", ["Integration setup"],
    {"200": response("Only the assistant name and greeting; no provider configuration or keys.", branding), "401": response("System key rejected.", ref("AssistantError")), "503": response("Mapped tenant missing or inactive.", ref("AssistantError"))},
    "Load through the product backend using its existing system-specific bearer key. This performs no model inference.", parameters=[parameter("system", required=True, description="System identifier assigned to this integration key.")])}
assistant["paths"]["/api/admin/integration"] = {"get": operation("Inspect selected tenant integration setup", "getTenantIntegrationReadiness", ["Integration setup"],
    {"200": response("Configured system identifiers, hosted knowledge count and credential readiness; no secrets or live inference check.", obj({"tenant": string(), "systems": {"type": "array", "items": string()}, "knowledgeCount": integer(), "providerConfigured": {"type": "boolean"}, "assistantName": string(), "limits": obj({"questionCharacters": integer(), "documentationBytes": integer()})})), **admin_errors},
    "Creating a tenant does not import product knowledge. Hosted tenant knowledge and role-filtered docs supplied to /v1/chat are separate sources. Backend keys and mappings currently require operator setup.", security=[{"adminSession": []}], parameters=[tenant_header])}

for name, document in [("garagehq", garage), ("cream-theme", cream), ("onepass", otp), ("assistant", assistant)]:
    (ROOT / f"{name}.json").write_text(json.dumps(document, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"Built {name}.json ({len(document['paths'])} paths)")
