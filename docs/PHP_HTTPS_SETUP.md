# PHP HTTPS setup for API partners

Configure HTTPS certificate trust before testing API credentials. Validate requests from the PHP runtime serving your application and from any command-line workers or scheduled tasks.

## Recognizing cURL error 60

`cURL error 60: SSL certificate problem: unable to get local issuer certificate` means PHP could not verify the API server's certificate chain. A missing, outdated or inaccessible CA certificate bundle can cause this error. An incomplete or invalid certificate chain on the API server can also cause it.

Apache, PHP-FPM and command-line PHP can load different configuration files or use different certificate stores. Consequently, an API request may succeed from a terminal while the same request fails from the website. A successful command-line check alone does not confirm that the web application's HTTPS configuration works.

## Check each PHP runtime

Use `php --ini` to identify the configuration loaded by command-line PHP. For the web application, inspect these values through a protected diagnostic route or your hosting control panel:

```php
[
    'php_version' => PHP_VERSION,
    'loaded_ini' => php_ini_loaded_file(),
    'curl_ca' => ini_get('curl.cainfo'),
    'openssl_ca' => ini_get('openssl.cafile'),
];
```

Keep diagnostics private and remove temporary routes after checking. Empty CA settings do not automatically indicate a fault: some installations use a working system trust store.

## Configure a trusted CA bundle when needed

1. Use the operating system's maintained CA store, or obtain an up-to-date PEM bundle from a trusted source such as [curl's CA Extract](https://curl.se/docs/caextract.html).
2. Place the bundle in a stable location readable by the account running PHP.
3. Update the configuration loaded by each affected runtime. For example, on Windows:

   ```ini
   curl.cainfo = "C:/certificates/cacert.pem"
   openssl.cafile = "C:/certificates/cacert.pem"
   ```

   Replace the example with your actual absolute path. `curl.cainfo` configures PHP cURL; `openssl.cafile` configures PHP's OpenSSL streams. Linux paths and system trust-store behavior depend on the distribution.

4. Restart the affected Apache or PHP-FPM service and any long-running PHP workers. Restart services only after saving the configuration.
5. Repeat the API connection check from the website and from any workers that make API calls.

Keep certificate and hostname verification enabled. Do not resolve the error by using `verify => false`, `withoutVerifying()`, or `CURLOPT_SSL_VERIFYPEER = false`.

If verification still fails with a current, readable bundle, ask the API operator to check the server's certificate expiry, hostname and intermediate certificate chain. Provide the HTTP client error and PHP runtime details, with credentials removed.

## References

- [PHP cURL configuration](https://www.php.net/manual/en/curl.configuration.php)
- [PHP OpenSSL configuration](https://www.php.net/manual/en/openssl.configuration.php)
- [curl TLS certificate verification](https://curl.se/docs/sslcerts.html)
