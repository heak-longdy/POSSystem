# Inventory export 404 investigation

Investigated on 18 October 2026. Production was probed using read-only, unauthenticated GET requests. No server files, caches, configuration, or inventory data were changed.

## Confirmed cause

Hostinger's active compiled Laravel route collection does not contain the new inventory export routes. The production JSON response to `GET /admin/stock-transfer/report?status=1` states:

```text
The route admin/stock-transfer/report could not be found.
Symfony\Component\HttpKernel\Exception\NotFoundHttpException
Illuminate\Routing\CompiledRouteCollection -> match
Illuminate\Routing\AbstractRouteCollection -> handleMatchedRoute
```

The exception occurs during route matching, before the controller or Excel generation runs. All eight missing endpoints were added in local commit `696e4758d296069efc194c96dc8b9e147eb9599b` on 16 October 2026. The two report routes that existed before that commit still resolve on production. Both the current local `routes/admin.php` and local `bootstrap/cache/routes-v7.php` contain all ten endpoints.

This confirms a mismatch between production's effective route table and the local source. Remote HTTP responses cannot establish whether production has an old `routes/admin.php`, an old compiled cache, or both. Compare the active source and cache on the server before clearing anything; no SSH connection was available during this investigation.

| Inventory module | `/report` (Excel data) | `/export` (CSV) |
| --- | --- | --- |
| Stock In | 404 | 404 |
| Stock Out | 404 | 404 |
| Stock Transfer | 404 | 404 |
| Stock On Hand | 302 to login | 404 |
| Stock Movement | 302 to login | 404 |

A 302 to login demonstrates that a protected route resolves; it does not prove an authenticated export succeeds. The Stock Transfer list also returned 302 to login. Stock Transfer report returned 404 both without query parameters and with `status=1`.

## Environment comparison

| Check | Local | Hostinger evidence |
| --- | --- | --- |
| PHP | CLI 8.2.28 | HTTP `X-Powered-By: PHP/8.3.35` |
| Routing | All ten routes resolve | Eight absent from compiled collection |
| Admin prefix | `RouteServiceProvider` loads `routes/admin.php` under `/admin`, name prefix `admin-` | Existing `/admin` list and report URLs resolve |
| Front controller / rewrite | Standard `public/.htaccess` forwards non-files to `index.php` | Exception reaches Laravel through `public_html/public/index.php`; direct `/index.php/admin/stock-transfer/report?status=1` also returns 404 |
| Active application root | This workspace | Stack trace references `domains/forestgreen-donkey-748531.hostingersite.com/public_html/vendor/...` |
| Route source revision | Contains 16 October additions | Effective route availability matches the earlier inventory registrations; on-disk source requires server inspection |

The remote rewrite configuration itself has not been read, but the failing request reaches Laravel's router. Changing `.htaccess`, PHP file permissions, Excel dependencies, or database structures does not address this observed exception. The deployed application root differs from the example `POSSystem/` layout in `HOSTINGER_DEPLOYMENT.md`; run commands in the directory actually loaded by the website, not another checkout.

## Targeted server recovery

1. Connect through the Hostinger SSH connection shown in hPanel. Locate the active application directory containing `artisan`, `routes`, and `bootstrap`; verify it against the `bootstrap/app.php` loaded by the site's `index.php`. Based on the observed trace, start by checking:

   ```bash
   cd /home/u138795580/domains/forestgreen-donkey-748531.hostingersite.com/public_html
   php artisan route:list --path=stock-transfer
   ```

   Preserve this output before changing caches. Inspect `routes/admin.php` for `report` and `export` in each inventory route group. The new read-only command can be uploaded independently as `app/Console/Commands/CheckInventoryExportRoutes.php` and then run:

   ```bash
   php artisan inventory:check-export-routes
   ```

   It prints the application root, active cache path, and resolution of all ten routes. It does not dispatch controllers, read inventory data, or change caches. This is also useful if `route:list` fails because another controller accesses a database in its constructor.

2. Ensure the production inventory route groups contain these registrations, each exactly once. These already exist in the local source; do not add duplicates locally or move routes outside their existing authentication/permission middleware.

   | Existing group prefix / name prefix | Controller | Required GET registrations |
   | --- | --- | --- |
   | `stock-in` / `stock-in-` | `StockInController` | `report` → `report`, name `report`; `export` → `export`, name `export` |
   | `stock-out` / `stock-out-` | `StockOutController` | Same |
   | `stock-transfer` / `stock-transfer-` | `StockTransferController` | Same |
   | `stock-on-hand` / `stock-on-hand-` | `StockOnHandController` | Same; `report` already resolves remotely |
   | `stock-movement` / `stock-movement-` | `StockMovementController` | Same; `report` already resolves remotely |

   For example, inside the existing Stock Transfer group:

   ```php
   Route::get('report', [StockTransferController::class, 'report'])->name('report');
   Route::get('export', [StockTransferController::class, 'export'])->name('export');
   ```

   Verify the matching inventory controllers contain those public methods. Deploy matching inventory files if needed, preserving existing queries, middleware, server paths, production `.env`, and application key. Do not upload unrelated uncommitted workspace changes or generated local cache files.

3. Clear only the route cache, then check the source registrations before rebuilding:

   ```bash
   php artisan route:clear
   php artisan inventory:check-export-routes
   ```

   Stop if the check fails. If all ten routes appear only after clearing, stale cache was the cause. If they remain missing, correct the active route source/application root first. A missing controller method requires the matching inventory controller deployment.

4. Once all ten checks pass, rebuild on Hostinger and clear compiled views after uploading the frontend changes:

   ```bash
   php artisan route:cache
   php artisan view:clear
   php artisan inventory:check-export-routes
   ```

   Stop at any failed command. If CLI now passes but web requests still show the old routes, verify CLI and web load the same application and cache paths, then refresh PHP OPcache through hosting controls. Do not use public cache-clearing URLs. No database migration, key generation, dependency update, or permission change is required for this repair.

   For File Manager-only access: inspect/update the active inventory route/controller files and remove only its generated route cache (`bootstrap/cache/routes-v7.php` by default; respect an `APP_ROUTES_CACHE` override). Laravel can serve uncached routes. Deploy the changed Blade views, their new shared partial, and both translation files together. Clear compiled views through Artisan when SSH becomes available.

5. Repeat unauthenticated GET probes: all ten URLs should resolve and redirect to login. Then sign in with the existing required permissions and perform the authenticated checks below. A login redirect alone is not completion.

The production 404 JSON exposed a full exception trace during this investigation. Set the effective production `APP_DEBUG=false` and rebuild configuration cache on the server after verifying its `.env`; this is a separate observed configuration issue, not the cause of the 404.

## Frontend changes

All five Excel actions now share a small Blade request/error helper. It uses the registered named report route, reports a missing registration without guessing a URL, requests JSON, and rejects unexpected responses such as an expired-session login page. Users receive English/Khmer messages for missing routes, denied access, session expiry, network errors, server errors, and workbook creation failures. Loading state is released on failure.

The five workbook builders, column definitions, fonts, widths, sheet names, filenames, values, calculations, controller queries, CSV formats, filters, and permissions are unchanged. The `/export` endpoints are CSV alternatives; switching Excel buttons to those endpoints would change the output format and is not this fix.

Upload these frontend files together:

- The five `resources/admin/views/pages/inventoryManagement/*/index.blade.php` files.
- `resources/admin/views/pages/inventoryManagement/partials/excel-request.blade.php`.
- `lang/en/inventory_export.php` and `lang/km/inventory_export.php`.

These are Blade changes; no JavaScript asset rebuild is required.

## Verification

Local automated checks:

```bash
php artisan inventory:check-export-routes
vendor/bin/phpunit tests/Feature/InventoryExportTest.php
node --test tests/JavaScript/inventory-export.test.cjs
```

The PHP tests use an isolated in-memory database. They exercise all ten endpoints, authentication and authorization, empty results, blank filters, special characters in search, quantities, CSV BOM/column counts, and existing filters. They also reproduce the missing-route 404 and verify the diagnostic exits unsuccessfully. The JavaScript tests render the actual Blade scripts, exercise their request/error paths, generate XLSX files with ExcelJS, reopen them, and check columns, values, layout, filenames, and empty exports. HTTP calls in the JavaScript tests use fixtures; they are not live browser or production authentication tests.

Results: 11 PHP tests / 641 assertions passed with uncached routes and again with a freshly generated route cache at a temporary path. All 11 JavaScript tests passed. All five workbook builder implementations were also compared against the existing revision and are unchanged byte for byte. The developer's existing route cache was preserved.

After deployment, check each applicable filter individually and together with an authorized account:

| Module | Filters to verify |
| --- | --- |
| Stock In / Stock Out | `status=1`, `status=2`, `status=trash`, `search`, `shop_id`; endpoint `date` |
| Stock Transfer | Same, including `shop_id` matching either source or destination |
| Stock On Hand | `search`, `shop_id`; endpoint `date` |
| Stock Movement | `search`, `shop_id`, `status_type`; endpoint `from_date` and `to_date`, including inclusive boundaries |

Preserve the current UI's submitted filter set; date parameters above also cover existing endpoint behavior. In the browser Network panel, `/report` must return HTTP 200 and a JSON array. Open each downloaded `.xlsx`, compare rows and quantities to the filtered report, and check each CSV endpoint separately. Also test empty results, signed-out sessions, a user without export permissions, and multiple workspace tabs with different filters. Confirm no database records change during export.

**Production status:** root cause and route availability verified by remote HTTP probes; server source/cache repair and authenticated downloads remain pending Hostinger access. Do not treat the local passing tests as proof of a production repair.
