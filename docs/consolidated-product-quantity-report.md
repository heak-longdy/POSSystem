# Consolidated Product Quantity

Open **Report Management → Consolidated Product Quantity** at
`/admin/report/consolidated-product-quantity`.

The report sums product quantities from order lines, grouped by product. It shows
product code, product name, category, unit, distinct order count and total quantity.
Repeated lines for the same product are combined. The overall order count counts
each matching order once, even when it contains several products.

Filters cover order date, shop, category, product and product/code/invoice search.
Quick Presets provide Today, Yesterday, Last 7 Days, Last 30 Days, This Month and
Last Month, keeping the other applied filters. Presets use explicit date ranges
so manual date changes and Excel exports use the dates shown in the form.
The default period is today, matching the Sales report. Cancelled and deleted
orders, deleted order lines and services are excluded. Archived product names
remain available for historical orders. The existing order ownership scope applies.

The table paginates at 50 products; summary cards, footer totals and Excel exports
cover every matching product. Excel uses the same backend query and collects the
current workspace tab's filter values. The UI reuses the Customer Remaining report
styles and pagination, the standard Select2 and jQuery UI controls, and the shared
Excel assets and request helper. English and Khmer labels are included.

## Permissions and deployment

Deploy the controller, views, translations, routes, menu, permission mapping and
seeder together. Register the new permissions on each deployed environment:

```sh
php artisan db:seed --class=ConsolidatedProductQuantityPermissionSeeder --no-interaction
```

Grant `report-consolidated-product-quantity-view` to users who may open the report
and `report-consolidated-product-quantity-excel` to users who may export it. Export
requires both. The seeder is idempotent and does not change existing assignments.
It is also called by the main PermissionSeeder. No schema migration or asset build
is required. Refresh deployment route/config/view caches using the usual process.

## Verification

```sh
vendor/bin/phpunit tests/Feature/ConsolidatedProductQuantityReportTest.php
node tests/JavaScript/consolidated-product-quantity.browser.mjs
```

The PHP suite uses an in-memory database. The browser suite uses a disposable
Chromium endpoint at `BROWSER_DEBUG_URL` (default `http://127.0.0.1:9235`) and fixture
data. It exercises date selection, month/year changes, typed/cleared date limits,
Select2 reset, workspace tab isolation, mobile layout, native Excel downloads,
workbook values, active filters and export error recovery in English and Khmer.
