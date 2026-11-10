# GarageHQ documentation

The public help centre is available at `/user-guide`, with a separate Swagger UI reference at `/api-docs`. Login and the application sidebar link to the guide. These pages contain static documentation and stay accessible when account access is restricted.

## User guide

Edit `USER_GUIDE.md` to update both the web guide and its Markdown download. Each level-two heading starts a chapter. Follow it with an audience comment, for example `<!-- audience: garage,staff -->`. Supported audiences are `all`, `garage`, `supplier`, `staff` and `admin`. Heading slugs become chapter anchors; preserve them when other pages link to them. Search and audience filters run on the server. Printing uses the current filtered view; downloading includes the complete guide.

Check instructions against the actual screen, permission checks and service behaviour. Describe incomplete workflows explicitly. Keep credentials, customer data and operational secrets out of examples.

## OpenAPI and Swagger UI

The checked-in documents in `openapi/` describe GarageHQ application endpoints, Cream Theme partner payments/mail, and OnePass OTP. They are reference contracts, separate from Livewire page actions. Swagger UI is bundled locally and configured without live request submission or persisted authorization.

Update the contract definitions in `openapi/generate.py`, regenerate the JSON, then validate:

```sh
python docs/openapi/generate.py
npm run docs:validate
php artisan test tests/Feature/DocumentationTest.php
npm run build
```

Review endpoints, authentication, request validation, response status codes and schemas against the owning application's source whenever an integration changes. Add new services to the controller allowlist, route constraint and API page links. The generator does not contact services or read credentials.

`resources/js/api-docs.js` loads Swagger only on the API reference page. `resources/css/documentation.css` styles the help centre, responsive navigation and print output.

## Deployment and integration guides

- [PHP HTTPS certificate setup](PHP_HTTPS_SETUP.md)
- [Self-service billing](SELF_SERVICE_BILLING.md)
- [Supplier registration](SUPPLIER_REGISTRATION.md)
- [Supplier plans](SUPPLIER_PLANS.md)

Deploy the Markdown and OpenAPI files with the application and include the generated Vite assets. Certificate trust, queues, scheduled tasks and partner credentials still require environment-specific setup; the documentation does not provision them.
