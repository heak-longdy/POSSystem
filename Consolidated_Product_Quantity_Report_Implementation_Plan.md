# Consolidated Product Quantity Report — Implementation Plan

**Status:** Proposed for review and approval. No module implementation or database changes have been made.

**Analysis date:** 22 October 2026 (Asia/Phnom_Penh).

**Evidence:** Existing application code, migrations, project standards, and a successful read-only inspection of the configured MySQL database's columns, indexes, and foreign-key metadata. The database transaction was rolled back. No business records were changed or exported. This verifies the configured database, not a separately verified production deployment.

## 1. Business requirements and expected results

Create a separate **Consolidated Product Quantity Report** under Report Management. Users explicitly select orders, review their selection, and generate one report containing each Product ID once, with its quantities added across all selected orders.

The product table contains **Product Code, Product Name, UOM, and Total Quantity**. Report metadata contains all selected internal Order IDs, invoice numbers where available, selected-order count, unique-product count, generation time, and overall quantity. Preview, print, and Excel must use the same generated dataset.

Expected example, using illustrative IDs and quantities:

| Selected order | Product ID | Product code | UOM | Quantity |
| --- | --- | --- | --- | ---: |
| 101 | 10 | 600817 | Case | 2 |
| 102 | 10 | 600817 | Case | 3 |
| 102 | 11 | 600818 | Case | 1 |

Result: Product 10 = 5 Case; Product 11 = 1 Case; 2 selected orders; 2 unique products; overall quantity = 6. An unselected order containing either product contributes nothing.

The supplied Load Report image is a layout/business reference. Its Units/Subunits, vehicle, driver, weight, stops, and load-number fields are not additional requirements for this module. There is no verified conversion model supporting its Units/Subunits split in this checkout.

Scope boundaries:

- Do not change existing Load Report, order processing, payments, financial calculations, stock movements, or inventory balances.
- Do not update existing orders, details, products, UOMs, or permission assignments.
- No saved-report/history business tables, delivery scheduling, accounting calculations, or conversion maintenance in the first version.
- The only deliverable at this stage is this plan. Implementation and permission-catalog additions require approval of the proposed work.

## 2. Current system analysis

### 2.1 Architecture and actual database structure

The project declares Laravel 9/PHP 8 in `composer.json`, uses Blade views in the `admin::` namespace, Alpine, jQuery/Select2, jQuery UI datepickers, Axios, and an MDI workspace. ExcelJS and FileSaver already exist locally. No new framework or package is required.

| Entity | Verified fields and relationships | Consequence for this report |
| --- | --- | --- |
| `orders` / `App\Models\Order` | `id`, `invoice_number`, `customer_id`, `shop_id`, `order_date`, `delivery_date`, `payment_status`, `deleted_at`; `orderDetails()`, `customer()`, `shop()` relationships | Select by internal `orders.id`; display invoice number separately. Order and delivery dates have different meanings. |
| `order_details` / `OrderDetail` | `id`, `order_id`, nullable `product_id` and `service_id`, nullable `type`, **integer** `qty`, `deleted_at` | This is the quantity source. Product rows and service rows share the table. |
| `products` / `Product` | `id`, nullable unique `code`, `name`, nullable `uom_id`, `status`, `deleted_at`; `uom()` | Product ID is the grouping identity. Code and name are display metadata, not grouping keys. |
| `uoms` / `UOM` | `id`, `name`, `status`, timestamps, `deleted_at` | One current UOM per product. There are no conversion factors in this table. |
| `shop_products` / `ShopProduct` | Shop/product association, pricing and quantities; `product_id` points to the global product | Do not group by `shop_products.id` or join this table into the sum. |
| `module_permissions`, `permissions` | Existing module catalog and permission records with `module_id`, `name`, `guard_name`, `display_name` | A new report requires new catalog entries and route authorization. |

`orders.id`, `order_details.id`, and product/UOM IDs are unsigned big integers; detail `order_id` and `product_id` are indexed integers. Existing indexes cover order date, shop, customer, payment status, invoice number, detail order/product/type, and product UOM. The inspected four main tables have **no declared foreign-key constraints**. Relationships exist in Eloquent, so missing related records must be handled explicitly.

Migration history confirms that `bookings`, `booking_details`, and `booking_payments` were renamed to the current order tables by `2026_09_27_000000_rename_bookings_to_orders_tables.php`. Several live index names still use the old booking names. Use current table/column names; do not introduce parallel booking queries. The product-code migration dated 18 October 2026 is reflected in the inspected schema.

Schema and code inspection do not establish that every existing record is valid. A bounded data-quality check against selected orders belongs in implementation; this analysis did not collect customer/order datasets or benchmark production volumes.

### 2.2 Order quantities and UOM behavior

`OrderController::createDetail()` stores the global Product ID for `type = product`, coerces quantities to integers, and requires at least one unit in the current admin creation path. `applyDetailStock()` also uses the stored integer quantity directly. The API order creation path likewise records product quantities as integers.

There is no `uom_id`, historical product name/code snapshot, base quantity, or conversion factor on `order_details`. `products.uom_id` supplies the current UOM. The legacy `order_details.rate` assignment comes from general settings in `Api/BookingController.php`; it is not evidence of a UOM conversion factor and must not be used as one.

The delivery-slip view displays a case/can layout, but `invoice-template-02.blade.php` puts the stored quantity in the case column; this is presentation, not a conversion service. Its placeholder product-code/UOM fallbacks must not be copied into the new report.

**Important limitation:** If a product's UOM was changed after an order was placed, the original UOM cannot be recovered from the inspected schema. This version can follow the current system's single-UOM convention, but cannot truthfully reconstruct historical conversions. Historical UOM snapshots/conversions would be a separately approved project involving order capture and schema changes.

### 2.3 Existing selection and report behavior

- `OrderBulkPrintController` uses `BuildsOrderListingQuery`, pagination of 50 orders, explicit IDs, and a separate matching-ID selection endpoint. Its selection UI is the closest reference for this module.
- `BuildsOrderListingQuery::buildOrderListingQuery()` supports order-date bounds, shop, payment status, and search across invoice number, shop, customer name/phone, product name, and service name. Its default `all` scope excludes `Cancel`. It does not implement exact Order ID or product-code search, customer-ID filtering, or shop-level user authorization.
- `OrderController::index()` maintains its own listing implementation. Reuse the trait in the **new** controller; do not refactor the existing Order controller during this work.
- Bulk-print selection state is component-local. Its `selectAllMatching()` currently starts from `window.location.search`; that is not a sufficient model for preserving a selected basket across MDI filtering/remounting.
- `OrderBulkPrintService::prepareForPreview()` renders separate invoices/slips and loads payments, financial context, and soft-deleted detail rows. Reuse its validation/limit ideas, not the service itself, for a quantity-only consolidated report.
- Several sales/order views load details with `withTrashed()`. The new report must explicitly select current, non-deleted product lines. `OrderController::Save()` force-deletes old details when editing, but that does not justify including every soft-deleted detail in a new quantity report.
- Searches of application code, routes, configuration, translations, and views did not identify a module literally named **Load Report**. The existing delivery-slip and bulk-print modules were found. Do not assume either is the photographed report or modify either implementation.

### 2.4 Navigation and permission architecture

The active sidebar is `resources/admin/views/shared/sidebar.blade.php`, which renders `AdminPermissions::visibleMenu(config('menu'))`. Add navigation in **`config/menu.php`**, not the legacy `app/Helper/Menu.php` or `app/Functions/Menu.php` lists.

Routes in `routes/admin.php` run behind `AdminGuard` and `CheckAdminPermission`. `AdminPermissions::forRequest()` maps named routes to permissions; report controllers also use view-permission middleware. Its permission arrays are alternatives (`canAny`), so requiring View **and** Excel must use separate checks, not one array interpreted as OR.

`PermissionModuleSelection::treeFromMenu()` builds the user-permission screen from the menu and permission catalog. Correct menu/catalog registration should expose the new module without rewriting the permission UI. `AuthServiceProvider` already provides the super-admin Gate bypass.

### 2.5 Concrete reuse map

| Existing source/function | Proposed reuse | Boundary |
| --- | --- | --- |
| `BuildsOrderListingQuery::buildOrderListingQuery()`, status normalization helpers | Shared candidate-order predicates for listing and Select All Matching | Add the new module's eligibility scope; leave the trait unchanged. |
| `Order::orderDetails()`, `OrderDetail::product()`, `Product::uom()` | Relationship definitions and eager-loading patterns | Select only needed fields; avoid serialization of unrelated model appends. |
| Bulk-print `toggleOrder()`, page-select/indeterminate-checkbox logic | Reference for the new selection component | These are embedded page methods, not an importable library. Adapt only necessary behavior locally; do not copy the whole page. |
| Bulk-print request/service ID validation and limits | FormRequest validation, exact-ID completeness checks, bounded requests | Do not reuse invoice preparation, payment loading, or deleted-detail behavior. |
| Sales report `getExportParams()` | MDI-aware filter harvesting | Correct precedence/cleared values in the new component; selected IDs remain authoritative. |
| Order/Sales `excel()` and `runExcelExport()` | Standard two-stage export and loading lifecycle | New four-column workbook; no financial workbook logic. |
| `report/partials/excel-assets.blade.php` | Include existing local ExcelJS/FileSaver assets directly | No CDN or duplicate imports. |
| `report/partials/excel-request.blade.php` | Reuse `requestReportExport()` and `showReportExportError()` directly | It performs GET and expects `{ rows: [...] }`; design the new endpoint accordingly. |
| Bulk-print `printAll()` and font-readiness handling | Reference for MDI print isolation and readiness | Implement only the new report's print behavior; restore exact prior styles rather than hard-coded values. |
| `admin::shared.header`, existing report/table/button/loading/pagination markup | Reuse layout components and established UI vocabulary | Any new layout/print styling stays local to the new report. |
| `App\Support\Language::translatedValue()` | Resolve array-based translated names after safe JSON decoding where necessary | Preserve plain-text product/UOM names and Khmer rendering. |
| `OrderBulkPrintPermissionSeeder` and its migration | Additive, idempotent module/permission registration pattern | No automatic grants, full reseeding, or existing-permission changes. |
| `Tests\TestCase`, `OrderBulkPrintTest`, `ReportExportTest`, browser export fixture | Isolated database tests and real workbook/browser verification patterns | Never exercise mutations against the configured business database. |

Reviewed standards: `AGENTS.md`, `.agents/rules/report-standards.md`, `.agents/rules/datepicker-standards.md`, `resources/admin/sass/select2.scss`, `createOrder.blade.php`, `orders.blade.php`, Sales Report, and `docs/report-export-production.md`.

## 3. Proposed new module structure

Use a report controller, dedicated request classes, a quantity-report service, and module-local Blade partials. No new Eloquent report model is necessary.

```text
Report Management
└── Consolidated Product Quantity Report
    ├── Find and select orders
    ├── Review selected orders
    └── Generated report preview
        ├── Print
        └── Export Excel
```

Proposed module key: `report-consolidated-product-quantity`.

Proposed menu path: `/admin/report/consolidated-product-quantity`; active pattern: `admin/report/consolidated-product-quantity*`; existing Boxicons report icon and bilingual menu labels. Keep the full requested title on the page even if the sidebar truncates long labels.

Permissions:

| Permission | Capability |
| --- | --- |
| `report-consolidated-product-quantity-view` | Open, search, select, review, generate, preview, and print |
| `report-consolidated-product-quantity-excel` | Export, additionally requiring the View permission |

Printing follows the existing preview/view permission model; a separate Print permission is not required in this proposal. Ordinary users receive no automatic access. Report View grants access to the candidate orders under the current module-level authorization architecture; it does not silently add order-edit privileges. No row-level shop policy was found in the inspected listing code. Apply any additional deployed policy discovered during implementation consistently to listing, matching, review, and generation.

## 4. Order selection workflow

1. Open a paginated candidate list with **nothing selected**. Default: non-deleted, non-cancelled orders across dates, following the reusable bulk-listing default. Offer Pending, Partial, and Paid filters; do not expose trash/rejected selection in this initial version.
2. Filter by Shop, Order Date From/To, Payment Status, and the existing search fields. Label the dates **Order Date**. Delivery-date filtering is not implied. Use an optional exact Order ID field if required during review; it would be a new module-local predicate, not an undocumented feature of the existing search trait.
3. Select individual rows. The header checkbox explicitly means **Select this page**. Selecting a page adds only visible eligible IDs; deselecting it removes only those IDs. Show its checked/indeterminate state accurately.
4. Offer a separate **Select all matching N orders** action. On explicit activation, the server resolves the current applied filters into concrete IDs. Merge/deduplicate these with the existing basket, enforce the union limit atomically, and show the resulting count. Never store a wildcard that can include newly created orders later.
5. Keep a sticky selection toolbar: selected count, Review Selected, Clear Selection, and Preview Report. Review lists selected Order IDs/invoice numbers, date, shop, customer, status, and product-line count, with individual removal and a selected-only search/page control.
6. Changing pages or discovery filters preserves the basket. Display how many selected orders are outside the current candidate filter. **Reset Filters** and **Clear Selection** remain separate actions.
7. Generate only after a nonempty selection. Revalidate all selected IDs, eligibility, quantities, and related metadata. If any order became unavailable, show a clear error and let the user review/remove it; do not generate a silently reduced report.
8. Create one immutable generated report and open its preview. Returning to selection preserves the basket. Changes to selection invalidate the association with that preview; a new preview requires generation again.

**Selection versus report filters:** Filters find candidates; the reviewed, explicit ID basket is the final report scope. An order selected before changing a search filter stays selected and must be visible in Review Selected. This is intentional for combining orders found by different searches. Export uses the generated selection and never reruns an unbounded candidate query or silently intersects it with a later discovery filter. All output formats therefore contain exactly the same selection.

Keep the component mounted during local AJAX filtering/pagination. Store only selected IDs and UI state in a session-storage key scoped by authenticated user and owning workspace tab for remount/reload recovery. Revalidate recovered IDs. Scope all DOM references to that component; switching tabs must not read another tab's filters or selection. Module-local pagination handlers must use existing pagination appearance without triggering competing MDI navigation handlers. Leave `mdi.js` unchanged.

Use request sequence/cancellation handling so old filter responses cannot replace newer results. “Select All Matching” uses the applied filter snapshot, not partially typed unapplied values. Disable selection-changing controls during generation to prevent a response being attached to a different basket.

## 5. Product grouping and quantity calculation logic

### 5.1 Authoritative calculation

Implement one `ConsolidatedProductQuantityReportService::buildReport()` calculation path:

1. Validate a bounded nonempty array of positive, distinct Order IDs. Reject malformed/duplicate submitted IDs with validation errors; also deduplicate defensively at the service boundary.
2. Resolve all IDs through the module's eligible-order scope. Compare requested and resolved ID sets. No partial success and no fallback to all orders.
3. Read current details for those orders, explicitly excluding `order_details.deleted_at != null`. Include only valid `type = product` rows with a Product ID. Exclude ordinary service rows. Flag inconsistent/unknown-type product-like rows instead of silently losing quantities.
4. Validate positive stored integer quantities. Do not convert missing/zero quantities into 1 or repair records while reporting. Invalid quantity/type/reference data produces an actionable validation response.
5. Aggregate only detail facts: **group by `order_details.product_id`; total = SUM(`order_details.qty`)**. Each detail ID contributes once. Two legitimate lines for the same product in one order both contribute. Never use `SUM(DISTINCT qty)`, which would incorrectly collapse equal quantities.
6. Load product/UOM metadata by the resulting IDs separately, or join the already-aggregated product totals to one-to-one metadata. Do not join order payments, shop-product assignments, stock history, or other one-to-many tables into the sum.
7. Resolve soft-deleted/inactive product and UOM master records for historical labels using explicitly scoped `withTrashed()` lookups. This does not include deleted orders/details or restore anything. Missing product/UOM records block generation rather than cause silent loss or guessed units. A missing product **code** displays `—` with a missing-code notice and the known Product ID; do not invent a code.
8. Sort consistently by Product Code, then Product Name, then Product ID. Resolve displayed metadata/localization once so every output uses identical values.
9. Produce rows and summaries server-side. Use an aggregate type capable of holding the sum, not a 32-bit line-value accumulator. Keep numeric Excel quantities; reject unrepresentable totals rather than allowing JavaScript precision loss.

Read validation, orders, details, and metadata within a consistent read transaction/snapshot. This prevents an order edit between validation and aggregation from producing a mixture of old and new lines. Do not lock business rows for updates or invoke any order mutation hooks.

### 5.2 UOM and summary rules

- Same Product ID across orders: one row in that product's current UOM; direct integer addition is consistent with current storage.
- Different Product IDs: separate rows even if their names/codes appear similar or they represent different packaging of the same item.
- Never merge Case and Piece products into a converted figure without an explicit verified conversion model. UOM labels such as “12” are labels, not conversion factors.
- Display **Overall Quantity = sum of all reported row quantities**. When UOMs differ, label it **Overall Quantity (mixed UOMs)** and explain that it is an arithmetic count in the displayed units, not a converted physical quantity. Also show totals by UOM ID/name, e.g. `Case: 12; Piece: 8`.
- `selected_order_count` is the number of validated distinct selected orders. `unique_product_count` is the number of aggregated Product IDs. Do not derive either from paginated UI rows.
- Selected orders containing only services contribute zero product quantity; keep them in selected-order metadata and identify them as having no product lines. If all selected orders are service-only, show a clear empty-product result with zero totals and disable Print/Excel until there are product rows.

The current-UOM convention and mixed-UOM overall total are explicit approval decisions. A physically normalized grand total or historical case/subunit report is outside this schema's capability.

## 6. Proposed UI/UX design

Use the existing report page shell, header actions, filter card, table styling, status badges, pagination vocabulary, translated alerts, and loading treatment. The selection list should display checkbox, Order ID/invoice, Order Date, Shop, Customer, Payment Status, and product-line count. Financial columns are unnecessary for this task.

```text
Consolidated Product Quantity Report
[Shop] [Order Date From] [To] [Status] [Search] [Apply] [Reset Filters]

12 selected · 3 outside current filters  [Review Selected] [Clear] [Preview Report]
[Select this page]                 [Select all matching 68 orders]
Order selection table
Existing pagination controls

Preview
Selected orders: 12   Unique products: 21   Overall quantity: 158
Selected Order IDs and invoice numbers · Generated at …
Product Code | Product Name | UOM | Total Quantity
Totals and totals by UOM
[Back to Selection] [Print] [Export Excel]
```

On small screens, stack filters, wrap action buttons, and keep tables horizontally scrollable within the content area. Use a selected-orders drawer/dialog or an inline review panel with its own scroll area, not hundreds of chips in the header. Display the complete order list in print/Excel even if the screen collapses it. Provide labeled checkboxes, keyboard operation, visible focus, live selection counts, and a focus-restoring review dialog if a modal is used.

### Select2 and datepicker compliance

- Use `.select2Group`, `.SelectField.select2`, the standard reset SVG, `width: '100%'`, and `window.$select2FocusInputSearch()` on open. Initialize once after Alpine `$nextTick`, with component-local references and namespaced cleanup.
- Match the established 43px height, 7px radius, `#cbd5e1` border, 12.5px/500 text, `#334155` rendered color, standard arrow and reset positioning. No new dropdown theme or `.select2-dropdown`, search, or option overrides.
- **Reuse detail to resolve before UI implementation:** the base `select2.scss` contains older 14px/base-control values, while the exact requested control profile also appears in page-level rules in `createOrder.blade.php` and reports. Verify the compiled bundle and standalone new-page rendering; do not depend on a previously visited MDI tab injecting styles. If the exact profile cannot be reused as-is, propose a narrowly scoped extraction of existing canonical rules into a shared include for approval before touching an existing page. Do not silently copy new overrides or broaden global styles.
- Follow the datepicker standard exactly: text inputs, unique instance IDs, local refs, `yy-mm-dd`, month/year controls, `gotoCurrent: true`, `yearRange: '-50:+10'`, linked From/To limits on selection/typing/clearing, and existing calendar icon markup.
- Use the new report's popup class on `#ui-datepicker-div` at `z-index: 9999 !important`, added in `beforeShow` and removed in `onClose`. This is the explicitly required scoped popup rule, not a replacement calendar theme.
- Use separate listing/review/generation/export loading states. Export uses `.loadingFullSizeLayout` with the established spinner; clear loading in `finally`, including failures.

## 7. Database and API requirements

### 7.1 Database scope and temporary generated reports

No order/product/UOM schema changes or permanent report tables are required for the proposed current-UOM version. Existing indexes are sufficient for the initial bounded query design; assess an actual MySQL execution plan before proposing any new index.

To guarantee identical preview/print/export even if an order is subsequently edited, store the generated, immutable report DTO in the existing **file cache store**, using an opaque random token, authenticated-user/session ownership, generation time, locale, and expiry. Proposed lifetime: 30 minutes. The cache entry is temporary report output, not a business database record. Use the file store explicitly so a database-backed default cannot introduce report-table writes. Verify private cache storage is available; do not silently substitute database persistence.

Later print/export reads this exact snapshot, rechecks access, and does not recalculate quantities. A fresh generation intentionally produces a fresh snapshot. Expired/evicted tokens show “Generate the preview again” and never fall back to all orders. Avoid full IDs in URLs and do not use a session-wide “last report” value that multiple tabs could overwrite. Return private/no-store HTTP responses. This design is not saved report history.

New permission/module catalog rows are the sole proposed database additions after approval. A targeted idempotent seeder/migration creates the new module and two permissions only; it must preserve all existing records and assignments. Do not run the general `DatabaseSeeder` or rerun the broad `PermissionSeeder` in an existing environment, since those have unrelated effects. A fresh-install entry point may call the new targeted seeder.

### 7.2 Proposed endpoints

All routes are within the existing authenticated admin/web/CSRF architecture; no public API is required. Prefix: `/admin/report/consolidated-product-quantity`. Route-name prefix: `admin-report-consolidated-product-quantity-`.

| Method/path | Name suffix / controller method | Input and output |
| --- | --- | --- |
| GET `/` | `index` / `index()` | Selection shell, initial candidate page, filter options, limits |
| GET `/orders` | `orders` / `orders()` | Validated filters/page; paginated minimal order DTOs and pagination metadata |
| POST `/selection` | `selection` / `selection()` | Applied filters plus current basket for union-limit checking; concrete matching IDs, counts, review metadata |
| POST `/review` | `review` / `review()` | Selected IDs; validated selected-order metadata, selected-only pagination and issues |
| POST `/generate` | `generate` / `generate()` | Selected IDs, locale, discovery context; immutable snapshot token and named preview URL |
| GET `/preview/{token}` | `preview` / `preview()` | Owned snapshot; preview in the admin workspace/direct page |
| GET `/print/{token}` | `print` / `print()` | Owned snapshot; standalone print document using the same report partial |
| GET `/report?report_token=…` | `report` / `report()` | Owned snapshot; standard JSON with `rows` and summaries for the shared Excel helper |

All routes require View; `report()` additionally requires Excel. Add an explicit report mapping to `AdminPermissions::forRequest()` and View controller middleware. The new report is not a CRUD module: do not add it to the generic CRUD catalog merely to generate unwanted create/update/delete permissions. Server-render the small shop/status options under report authorization; no expansion of shared lookup permissions is necessary unless remote lookups are later justified.

Validate filter names/types, positive shop/page IDs, allowed status values, `Y-m-d` dates with From <= To, and a bounded search string. Proposed independent limits: **100 selected orders / 2,000 active detail lines**, initially matching existing bulk-print limits but held in new module configuration. Return an error for the entire over-limit operation; never truncate silently. Raise these only after measurements with realistic datasets. The limit is on source lines, not only the much smaller consolidated row count.

Proposed report payload fields:

| Field | Meaning |
| --- | --- |
| `report_token`, `generated_at`, `expires_at`, `locale` | Snapshot identity and display context |
| `selected_order_ids`, `selected_orders` | Exact ordered list and minimal display metadata for every selected order |
| `rows[]` | `product_id`, `product_code`, `product_name`, `uom_id`, `uom_name`, numeric `total_quantity` |
| `summary` | `selected_order_count`, `unique_product_count`, `overall_quantity`, `quantity_by_uom`, mixed-UOM flag |
| `notices` | Missing codes, current-UOM convention, selected orders without products, archived metadata notices |
| `selection_context` | Applied discovery filters at generation, explicitly labeled as discovery context |

Use 422 for validation/limit problems; 401/419 for authentication/CSRF problems; 403 for permission/ownership failures; 410 for expired snapshots; and standard server errors for unexpected failures. Do not reveal information about inaccessible orders in validation messages. Server-calculated metadata/quantities are authoritative; never accept client-supplied product totals or labels.

## 8. Print and Excel export functionality

### 8.1 Preview and print

Build a new `document.blade.php` partial from the report DTO and reuse it in preview and the standalone print view. Use A4 portrait, repeat table headers on page breaks, right-align quantities, wrap Khmer/long product names, and avoid splitting a product row where practical. Put the full selected-order list after the product table when it would overwhelm the header.

Keep report title, generation time, IDs, counts, totals, UOM notes, and row ordering identical. Hide controls during print, remove scroll-height constraints, and wait for fonts/images before enabling Print. Print the standalone report document so the sidebar and unrelated MDI tabs cannot appear. If the preview itself is printed, adapt the existing bulk-print isolation pattern with exact style restoration and cleanup after printing/cancelling. Do not reuse invoice/slip data builders or their sample/fallback data.

### 8.2 Excel

Follow the report standard:

1. Export button calls `excel()` and is disabled while `exportLoading`.
2. Show the full-screen standard spinner.
3. Include `admin::pages.report.partials.excel-assets` and `admin::pages.report.partials.excel-request` with `$routeName = 'report-consolidated-product-quantity'`.
4. `getExportParams()` resolves the owning tab's URL, initial state, current filter form `FormData`, tracked Select2 values, and date refs, with cleared values deleting stale parameters. An unrelated top-level URL never overrides the owning component. Always call `params.delete('page')`.
5. Supply the **generated report token**. Discovery filters are context only; the token's reviewed ID set defines the report. A preview without a live filter form uses its frozen selection context. Require regeneration if the selection changes. This preserves the user's explicit selection requirement while retaining MDI filter fidelity.
6. Call the existing `requestReportExport(params)` against the named GET `admin-report-consolidated-product-quantity-report` route. The endpoint returns the complete snapshot, irrespective of candidate/review pagination.
7. `runExcelExport(reportData)` builds the workbook and downloads it; `exportExcel()` remains an alias returning `this.excel()`.
8. Handle errors with the shared helper. Add module-local handling for snapshot expiry and actionable 422 messages without modifying other reports' helper behavior. Reset loading in `finally`.

Proposed workbook: a primary **Product Quantities** sheet with the four required columns, title/generation metadata, totals and UOM breakdown; a **Selected Orders** sheet listing every selected ID/invoice and the same selected-order count. Names stay within Excel's 31-character worksheet limit, with suitable translated alternatives. Suggested column widths: Code 18, Name 50, UOM 18, Quantity 18. Codes and invoice numbers are strings to retain leading zeros; quantities are numbers with `#,##0`; metadata remains text, never interpreted as spreadsheet formulas. Use existing bold/subtle header styling and sensible print settings.

Do not recalculate or round quantities independently in JavaScript, print markup, or spreadsheet formulas. A later order edit changes a newly generated report, not the already previewed snapshot.

## 9. Files, functions, and components to create or update

Paths below are proposed, not yet created (except this plan).

| Action | File/component | Responsibility |
| --- | --- | --- |
| Create | `app/Http/Controllers/Admin/Report/ConsolidatedProductQuantityReportController.php` | `index`, `orders`, `selection`, `review`, `generate`, `preview`, `print`, `report`; use existing listing trait; explicit authorization |
| Create | `app/Http/Requests/Admin/ConsolidatedProductQuantityFilterRequest.php` | Candidate/search/select-matching filter validation |
| Create | `app/Http/Requests/Admin/ConsolidatedProductQuantityReportRequest.php` | Selected-ID, limit, locale, and review/generation validation |
| Create | `app/Services/ConsolidatedProductQuantityReportService.php` | Eligibility/review checks, `buildReport`, metadata lookup, `createSnapshot`, `getSnapshot`; read-only business-data access |
| Create | `config/consolidated_product_quantity_report.php` | Selection/detail limits, snapshot TTL and explicit file cache store |
| Create | `resources/admin/views/pages/report/consolidatedProductQuantity/index.blade.php` | Selection shell and Alpine component |
| Create | Same directory: `filter.blade.php`, `order-table.blade.php`, `selected-orders.blade.php`, `pagination.blade.php` | Small module-local selection/review pieces following existing component markup |
| Create | Same directory: `preview.blade.php`, `document.blade.php`, `print.blade.php` | Shared report data presentation and isolated printable document |
| Create | Same directory: `styles.blade.php` | Scoped layout/print rules and required calendar popup layer only; no custom Select2 theme |
| Create | `lang/en/consolidated_product_quantity_report.php`, `lang/km/consolidated_product_quantity_report.php` | All new labels, messages, notices, and export labels |
| Update | `routes/admin.php` | Add the isolated report route group and controller import |
| Update | `config/menu.php` | Add one Report Management item and bilingual title |
| Update | `app/Support/AdminPermissions.php` | Map new module route actions; Excel additional check |
| Create | `database/seeders/ConsolidatedProductQuantityReportPermissionSeeder.php` | Add only the new catalog module and View/Excel permissions |
| Create after approval | A new timestamped `add_consolidated_product_quantity_report_permissions` migration | Invoke targeted additive permission registration for existing installations; no order schema changes |
| Update | `database/seeders/PermissionSeeder.php` | Invoke targeted seeder during normal fresh-install permission provisioning; do not rerun the broad seeder on existing databases |
| Create | `tests/Feature/ConsolidatedProductQuantityReportTest.php` | Routes, authorization, aggregation, selection, expiry and non-mutation coverage |
| Create | `tests/Fixtures/consolidated-product-quantity-report.php`, `tests/JavaScript/consolidated-product-quantity-report.browser.mjs` | Actual UI interaction, print document, downloaded workbook and MDI checks |
| Extend if needed | `tests/Feature/UserPermissionTest.php` | New menu/catalog exposure and View/Excel combinations |

Expected unchanged: `OrderController`, existing Order models/business logic, existing bulk-print controller/service/views, invoice/slip templates, existing report controllers/views, shared Excel helpers, global calendar/Select2 styles, `mdi.js`, and financial/inventory services. Any canonical Select2 style extraction identified in section 6 is a separate, explicit scope decision before implementation, not an assumed authorization to edit these files.

## 10. Impact analysis and backward compatibility

| Area | Expected effect and control |
| --- | --- |
| Load Report and current printing | No changes. The photographed report's source is not identified; no guessed replacement or refactoring. |
| Orders, payments, inventory | Read queries only. Never call `Save`, `applyOrderEffects`, `reverseOrderEffects`, payment methods, stock mutations, invoice numbering, or order-pricing calculations. |
| Database | No business schema/data changes. Only approved new permission catalog records; generated report snapshots use temporary files. |
| Existing authorization | Existing permission names, role/user grants, and super-admin behavior remain unchanged. New module-specific permissions only. |
| Existing UI | New route/menu and scoped views. No global style override or MDI interception changes. |
| Historical reporting | Current master UOM/code/name semantics are explicit; archived metadata can be read, but missing or untraceable historical UOM conversion is not fabricated. |
| Concurrency | Consistent read during generation plus immutable snapshots prevent preview/export differences. UI identifies generation time and offers regeneration for current data. |
| Performance | Pagination, bounded ID sets/detail counts, aggregate-first queries, small metadata lookups, and no N+1 payments/product serialization. Measure before raising limits. |
| Local/Hostinger | Existing local assets, named routes, file-cache availability, schema parity, private storage and cache-expiry behavior must be verified in the deployed environment. |

Rollback disables/removes only the new route/menu/code and clears only this module's temporary cache entries. Preserve newly assigned permission catalog records rather than deleting grants during rollback, matching the existing additive permission migration practice. No order rollback or data restoration should be necessary.

## 11. Step-by-step implementation tasks after approval

1. **Approve behavior and scope.** Confirm the defaults listed below, particularly current-UOM interpretation, status eligibility, mixed-unit totals, temporary snapshots, and new permission entries. Resolve the canonical Select2 style reuse issue without unauthorized shared-page edits.
2. **Confirm target environment.** Read-only verification of schema parity, product-code availability, private file-cache access, existing permissions, and candidate-query performance. Do not run unrelated pending migrations or seeders.
3. **Register the module.** Add route group, explicit View/Excel enforcement, menu entry, translations, configuration, and targeted permission catalog migration/seeder code. Test denial paths before exposing data.
4. **Implement service and requests.** Centralize eligibility, validate complete selected sets, aggregate active product lines, load metadata, calculate totals, and produce one DTO. Keep all business-data access read-only.
5. **Implement selection/review.** Build the filterable 50-row list, page and matching selection, persistent basket, selected-only review/removal, limit handling, request-race protection and user/tab isolation.
6. **Implement generation/snapshots.** Use a consistent read, immutable private cache payload, owner/session checks, expiry, and independent tokens for different reports.
7. **Implement preview/print.** Shared report partial, full selected-order metadata, multipage print layout, Khmer/English support, document readiness, and workspace isolation.
8. **Implement Excel.** Reuse local assets/helper, standard loading flow, token-based GET payload, workbook formatting, and complete selected-order sheet.
9. **Validate the module and regressions.** Run targeted PHP/JS/browser checks below, including actual datepicker selection/navigation, downloaded workbook re-reading, and print-layout inspection. Confirm only intended files changed.
10. **Prepare reviewable delivery.** Supply code diff, test results, sample preview/print/Excel based on synthetic data, remaining limitations, and the exact targeted deployment steps. Permission registration or deployment happens only in the approved scope; do not run a general business-data seeder.

Approval defaults proposed by this plan:

- Active Pending/Partial/Paid orders are selectable; cancelled/deleted orders are excluded.
- Discovery filters preserve selected orders outside the current filter, with explicit review/notice.
- Quantities use the current product UOM with no conversion; mixed-UOM overall quantity is labeled and accompanied by UOM subtotals.
- Active product detail lines count; service lines do not; archived product/UOM labels may be read.
- Initial limits are 100 selected orders and 2,000 active source lines; file snapshots expire after 30 minutes.
- View includes Print; Excel requires View plus Excel. Only new permission/module catalog rows are added after approval.

## 12. Testing scenarios and acceptance criteria

Use the project's `Tests\TestCase` isolated in-memory SQLite database. It explicitly switches database/cache/session configuration; still assert database isolation in new tests. Do not run migrations or mutation tests against the configured business database. Use a separate MySQL fixture environment for aggregate/query-plan compatibility where needed.

| Scenario | Required result |
| --- | --- |
| Same product in two selected orders | One product row; exact sum of both quantities |
| Same product on two legitimate lines in one order | Both details counted once; no quantity-based deduplication |
| Repeated click/overlapping Select All operations | Basket contains each Order ID once |
| Duplicate/malformed IDs submitted directly | 422 or defensive internal deduplication; never double-count |
| Large unselected order with matching products | No contribution to rows or summaries |
| Product appears across multiple shops/payment records | One Product ID row; payments/shop-product joins cannot multiply quantity |
| Products with different UOMs | Separate Product IDs stay separate; no inferred conversions; labeled mixed total and correct subtotals |
| Product code is null or starts with zero | Placeholder/notice for null; leading zeros retained in preview, print and workbook |
| Service rows mixed with products | Services excluded; product quantities unchanged |
| Service-only selected order / all service-only | Accurate selected-order metadata; explicit zero-product state; no misleading printable/exported product report |
| Soft-deleted details | Excluded; legitimate active details still counted |
| Deleted/cancelled/missing order after selection | Entire generation rejected for review; no silent dropping |
| Inactive/soft-deleted product or UOM | Existing archived metadata resolved without restoration; quantity retained |
| Missing product/UOM, invalid quantity or inconsistent type | Clear validation issue; no guessed conversion/quantity or omitted product |
| Page selection and deselection | Header indeterminate state and basket exactly match actions |
| Select All Matching with date/shop/status/search filters | Concrete IDs match the applied filter snapshot across every page |
| Selection limit or detail-line limit exceeded | Entire action rejected with limits shown; no truncation |
| Filters reset/changed after selection | Basket retained, outside-filter count shown, selected review complete |
| Candidate/review page > 1 during export | Entire generated selection exported; `page` absent from export query |
| Direct page visit and MDI-loaded page | Identical filters, correct styles, working local assets and export |
| Two workspace/browser tabs | No shared selection/token/filter leakage; responses update only their owning component |
| Rapid searches or filter changes | Stale responses cannot replace the latest list or select unintended matches |
| Order changed after preview | Existing snapshot print/Excel unchanged; regeneration intentionally reflects new data |
| Consistency during generation | Orders/details/metadata come from one consistent read |
| Expired snapshot or another user's token | Friendly expiry or denied access; no fallback query or data exposure |
| Unauthorized / View-only / Excel-only / both / super-admin | Correct route and button permissions; Excel-only does not bypass View |
| Session expiry, 403, 404, 419, 422, 500, missing assets, network failure | Clear translated message; loading always stops; no corrupt download |
| Datepicker interaction | Input/icon open, visible/clickable popup, date selection, month/year navigation, linked limits, typed/cleared values, MDI isolation |
| Select2 interaction | Search focus, standard reset/state sync, exact approved appearance, no duplicate initialization |
| Long Khmer names and many selected orders/products | Readable responsive UI; all rows/IDs present across printed pages and Excel sheets |
| Actual downloaded Excel file | Reopened with ExcelJS; rows, quantities, IDs, summary totals, text codes and number formats equal the preview DTO |
| Print preview/cancel | Only the report prints; repeated headers and totals intact; other tabs/layout restored |
| Non-mutation/regression | No order/payment/stock/product/UOM writes; existing order payment, bulk-print, report-export and permission checks still pass |

Suggested focused verification after implementation: new report feature tests, relevant existing `OrderBulkPrintTest`, `ReportExportTest`, `OrderPaymentTest`, `UserPermissionTest`, and the new real-browser script using the existing report-export fixture approach. Run PHP/Blade/JavaScript syntax checks as supporting checks; they do not substitute for calendar, print, or workbook interaction.

Acceptance requires all four requested product columns, complete selected-order metadata, correct unique-product and quantity totals, exact selection fidelity, identical snapshot data across all output formats, correct permissions, usable cross-page selection, and no change to existing business behavior. Historical UOM conversion is explicitly not claimed. This plan itself has not run implementation tests because no implementation has been authorized.
