# Report Management Excel export investigation

Investigated on 18 October 2026. The fix covers Sales Report, Order Transaction Report, Inventory Movement Report, and Staff Expense Report. Customer Remaining Amount was reviewed and has no Excel export action in the current application; none was added.

## Findings and evidence

All four production JSON routes resolve. Read-only unauthenticated requests to the following paths returned HTTP 302 to the existing login page, rather than 404:

```text
/admin/report/sales/report
/admin/report/order-transaction/report
/admin/report/inventory-movement/report
/admin/report/staff-expense/report
```

This verifies route registration and successful forwarding to Laravel, not successful authorized report queries. The Inventory Management missing-route finding does not apply to these four endpoints.

Both production Excel assets returned HTTP 200 with a JavaScript content type:

```text
/admin-public/js/exceljs.min.js
/admin-public/js/FileSaver.min.js
```

Their SHA-256 hashes match the local files. The deployed `admin-public/js/app.js` also matches the local compiled bundle. The shared admin layout does not include the Excel assets, and the main bundle does not expose `ExcelJS` as a global: its ExcelJS import is commented out in `resources/admin/ts/app.js`.

**The reproduced defect:** Sales, Order Transaction, and Inventory Movement call `new ExcelJS.Workbook()` and `saveAs()` but did not include either library. Running each actual Blade export script without libraries inherited from another page produced `ReferenceError: ExcelJS is not defined`, caught by the existing generic “Excel export failed. Please try again.” alert. Inventory pages load these globals, and the workspace retains them across tabs, so visiting one first can mask this defect. This explains a working local session and a failing direct report visit without an Excel library or PHP version difference.

Staff Expense already included both assets. It receives the same consistent includes and diagnostics, but the missing declarations in the other three pages do not establish a separate Staff Expense production failure. A failing authenticated request there must be distinguished using its actual HTTP status and error.

Production source views, authenticated API responses, and server logs were not accessible through the available public HTTP requests. No configured Hostinger SSH connection or authenticated browser session was available. The confirmed code defect was fixed and tested; it is not evidence that every possible production error has been resolved. In particular, an account without the existing report Excel permission will still receive 403, and an expired session may redirect to HTML. Permissions remain unchanged.

## Implementation

All four report pages now include `report/partials/excel-assets.blade.php` before their component script. It loads the same existing local ExcelJS and FileSaver files used by Inventory Management. The existing workspace script loader deduplicates external script URLs when opening tabs; the shared admin layout and asset bundle were not changed.

The scoped `report/partials/excel-request.blade.php` checks that the libraries loaded, requests the existing named JSON route with exactly the existing filter query, and verifies the expected `rows` array before creating the workbook. English/Khmer messages distinguish library loading, route, permission, session, response, network, server, and workbook errors. Technical errors remain available in the browser console; raw server exception details are not shown in alerts.

All four workbook-building blocks were compared with the pre-change revision and are unchanged byte for byte, including columns, widths, headings, sheet names, filenames, calculations, deduction signs, data mapping, and workbook serialization. No controller, route, permission, query, database, date-picker, Select2, Inventory Management, or global asset changes were made for this fix.

## Targeted deployment

Upload these files together into the active Hostinger application:

```text
resources/admin/views/pages/report/sales/index.blade.php
resources/admin/views/pages/report/orderTransaction/index.blade.php
resources/admin/views/pages/report/inventoryMovement/index.blade.php
resources/admin/views/pages/report/staffExpense/index.blade.php
resources/admin/views/pages/report/partials/excel-assets.blade.php
resources/admin/views/pages/report/partials/excel-request.blade.php
lang/en/report_export.php
lang/km/report_export.php
```

The two existing assets must remain accessible at the exact case-sensitive URLs above. They already matched locally and remotely during the investigation, so dependency upgrades and asset rebuilding are unnecessary for this fix. Do not upload unrelated workspace changes, local `.env`, or generated local caches.

From the active application root on Hostinger, clear the compiled Blade views:

```bash
php artisan view:clear
```

The observed production stack in the preceding inventory investigation loaded `domains/forestgreen-donkey-748531.hostingersite.com/public_html/public/index.php`. Verify the application directory loaded by that entry point before running commands; do not assume the example `POSSystem/` directory in the general deployment guide is active. Keep production environment values and server-specific paths intact.

No route-cache rebuild is required by these view-only report changes. If a later probe returns 404, inspect `php artisan route:list --path=admin/report` in the active deployment and compare its route source/cache before changing it. Rebuilding caches cannot load missing browser libraries.

After deployment, close old workspace tabs and open each report directly in a fresh browser session. In Network, verify both library requests return 200 and the authenticated `/report` request returns 200 JSON containing `rows`. Download/open a file in each supported mode and compare it with the filtered report. Check Sales and Order Transaction daily grouped, daily ungrouped, and monthly grouped; also check Inventory Movement and Staff Expense monthly ungrouped. Keep the existing Excel permissions on the test account.

If a failure remains, record its Console error, request URL/status, and response type. For a 500, inspect the corresponding timestamp in `storage/logs/laravel.log` on Hostinger. For 403, verify the account's existing `report-sales-excel`, `report-transaction-excel`, `report-inventory-excel`, or `report-staff-expense-excel` permission and the controller's required view permission. For HTML/login responses, sign in again. Do not share cookies, credentials, customer data, or complete exception traces publicly.

## Verification

```bash
vendor/bin/phpunit tests/Feature/ReportExportTest.php
node tests/JavaScript/report-export.browser.mjs
vendor/bin/phpunit tests/Feature/InventoryExportTest.php
node --test tests/JavaScript/inventory-export.test.cjs
```

The browser test requires a disposable Chromium debugging endpoint, default `http://127.0.0.1:9235` or `BROWSER_DEBUG_URL`. It uses actual rendered Blade scripts, the existing compiled application, Alpine, Axios, ExcelJS and FileSaver, with a local fixture API. It opens each report with a fresh JavaScript scope, clicks Export, downloads native browser files, reopens them with ExcelJS, and verifies data, formats, modes, filters, empty results, error messages, and loading-state recovery. It never logs into or writes to production.

The PHP tests use an isolated in-memory SQLite database. They exercise all four real report endpoints and their existing date/month ranges, presets, individual/combined secondary filters, grouped/ungrouped responses, financial and quantity calculations, authentication, and permissions. This validates application behavior without modifying local or production business data; production MySQL data and query performance still require the authenticated deployment check.

Inventory regression checks passed: 11 PHP tests / 641 assertions and 11 JavaScript tests. Report endpoint checks passed: 12 PHP tests / 188 assertions. Native browser exports passed for all four report modules.

**Production status:** public routes and asset files verified; the source fix is ready to deploy. Uploading the changed views, clearing production view cache, reading production logs if needed, and verifying authenticated production downloads remain pending server access.
