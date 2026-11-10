# GarageHQ assistant

The floating **Ask GarageHQ** widget connects signed-in active users to the shared Salord System Assistant. It appears in both app layouts and the help centre when configured. It is documentation help: it cannot read tenant records, confirm actual payments, query customer data or execute actions.

## Knowledge and privacy

`AssistantKnowledge` turns `USER_GUIDE.md` chapters into the shared service's approved guide format. Each request supplies the current guide, filtered for garage, supplier or platform users; updates take effect on the next question without retraining model weights. Source links are restricted to those exact guide chapters. Public/common chapters still describe the system's account types; audience filtering is for relevance, not confidential documentation access control.

The backend sends only `system=garagehq`, a stable pseudonymous user identifier, a server-derived role, the question and static documentation. Client-supplied identities, documentation and previous messages are ignored. There is no access to business records or operational tools. Questions are not stored by GarageHQ. The widget keeps messages only in page memory and does not use local/session storage. Logging excludes question text, answers, credentials and upstream response bodies.

The shared service runs on the platform host; its Cot tenant settings choose Ollama Cloud, Groq or self-hosted Ollama for inference. Users must acknowledge this before submitting. Questions and matching guide excerpts are sent to that provider. Do not include personal/customer data, vehicle identifiers, secrets or codes. The integration does not enable public-web search or code analysis. AI output is displayed as plain text, and users should check permissions and current billing details in the application.

## Configuration

```dotenv
ASSISTANT_ENABLED=true
ASSISTANT_BASE_URL=https://cot.loganate.cc
ASSISTANT_API_KEY=<dedicated server-held GarageHQ key>
```

Keep the key out of source control, browser bundles and screenshots. The URL must use HTTPS and certificate verification remains enabled. The optional shared `INTEGRATIONS_CA_BUNDLE` also applies. `false` disables the widget and makes its endpoint return 503. The assistant service must map this dedicated key to `garagehq` in `SUPPORT_AGENT_API_KEYS_JSON`, and map that system to the isolated `garagehq` tenant in `SUPPORT_AGENT_API_TENANT_SLUGS_JSON`. Preserve every existing system entry.

## Shared-host provisioning and knowledge refresh

After reviewing the guide, on the production host:

```sh
cd /var/www/garagehq
php artisan assistant:export-knowledge
node scripts/provision-assistant.mjs /home/salord/fotex-support-agent /var/www/garagehq /home/salord/backups/garagehq-assistant-UNIQUE-TIMESTAMP
pm2 restart salord-system-assistant --update-env
php artisan config:cache
php artisan queue:restart
```

The provisioning script requires Node 22.13+ with its built-in SQLite support. It backs up both environment files and the shared SQLite database in a fresh private directory before making changes. It creates/reuses a dedicated GarageHQ tenant, imports chapter text as tenant-only knowledge attributed to an existing active platform administrator, replaces only its prior managed imports, and preserves other systems' keys, tenants and documents. A new tenant inherits the currently configured default tenant model; an existing tenant keeps its selected model. No new admin user/password is created. Tenant knowledge makes the guide available in the standalone assistant too; access there requires an account permitted for the GarageHQ tenant. The API continues using the role-filtered documents supplied by GarageHQ.

Use a new backup directory for each run. A partial import can be safely rerun with another directory: previous managed chapters are replaced. Review before repointing an existing mapping or reactivating an inactive tenant; the script refuses those cases. If activation fails, restore the protected environment backups and restart the same service; restore the SQLite backup only after considering other users' changes since backup.

## Access and reliability

`POST /assistant/chat` uses the normal web session, CSRF protection and explicit consent. It remains available during subscription expiry and forced password changes; inactive users are blocked. Limits are six requests per user per minute, 100 per day, and 60 per minute across GarageHQ. The shared service applies its own limits too. Network/model errors return a safe message with a guide fallback; no automatic retry can duplicate a model request. Previous messages are not forwarded, so each question should stand alone.

The shared API selects at most five relevant guide chapters. An unmatched-question fallback is relabelled for GarageHQ because the existing service still names Fotex in that response. A model change or framework migration requires checking that service's current contract.

A healthy shared service and valid system key do not prove model readiness. If Ollama Cloud returns HTTP 402, the account needs usage credits or a subscription; a service restart or new GarageHQ key will not fix billing. The shared API deliberately hides provider details behind a 502 response, and GarageHQ returns a safe 503 with guide fallback. An operator should check the authenticated provider response without exposing keys or prompts, restore account availability, and then test a harmless documentation question. Do not purchase credits or change billing automatically. Use an available model from the authenticated model catalog; cloud-hosted model identifiers can differ from older local `-cloud` aliases.

The Cot administrator can switch the `garagehq` tenant?s provider/model in Settings without changing GarageHQ?s integration key. Provider credentials and any private connection to a PC-hosted Ollama instance belong to the shared service.

Swagger UI at `/api-docs` documents both the GarageHQ browser endpoint and the shared service contract. Validate with `npm run docs:validate`; test with `php vendor/bin/phpunit tests/Feature/AssistantTest.php tests/Feature/DocumentationTest.php`; build with `npm run build`.
