# Security deployment

1. Pull this repository, then reload the application in the browser to avoid cached old JavaScript.
2. Verify `.env`, `.git/config`, log/backup files return 403/404. Apache must allow the repository .htaccess rules.
3. Login, inspect the dashboard, test form/photo controls, and export a report. This release uses script nonces and POST/header CSRF tokens. Cached old pages can still submit legacy query tokens during rollout.
4. To reduce the Server response header, copy the directives in apache-security.conf into the GLOBAL Apache configuration, or Include the file there. They cannot be placed in .htaccess. Run the server Apache configuration check before reload. On Windows XAMPP use apache/bin/httpd.exe -t, then restart Apache in a maintenance window. On Linux ask the server administrator for the appropriate config-test/reload commands. Afterward the header should identify only Apache, without PHP/OpenSSL/version details.
5. The repository cannot change credentials already exposed in the server .env. Coordinate rotation of database/integration secrets. Do not rotate signature/HMAC keys blindly because old signatures must remain verifiable.

The CSP restricts scripts to the local origin and nonced trusted templates. Existing inline event handlers have a fixed hash allowlist. Form AM retains unsafe-eval for its existing Vue template compiler; this is an explicit compatibility exception, not a claim that all XSS is prevented. RTWT does not need unsafe-eval. Stronger style/resource restrictions and migrating Vue away from runtime compilation are separate work.

Dependencies: jQuery 3.7.1 in both applications; Chart.js 2.9.4 in Form AM, using the patched compatible 2.x API. This addresses the specific reported library issues and is not a claim that these legacy dependency lines receive indefinite security support.
