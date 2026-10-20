# Order Bulk Printing — Dedicated Module Implementation Plan

**Status:** Proposed — awaiting review and approval; development has not started.
**Date:** October 19, 2026
**Scope:** New standalone **Order Bulk Printing** module for batch Invoice and Delivery Slip printing.
**Review basis:** Repository inspection. No application code, database records, or configuration were changed. Browser and printer behavior described below must be verified during implementation.

> **Design change from original plan:** The original plan proposed adding checkboxes and bulk selection to the existing Order listing page (`orders.blade.php` / `listingData.blade.php`). That approach has been **replaced**. The new plan creates a completely **separate, dedicated module** with its own page, controller, routes, and UI. The existing Order module and its listing page remain **entirely unchanged**.

---

## 1. Recommended solution

Create a new **Order Bulk Printing** module accessible from the sidebar navigation. This module provides a dedicated, purpose-built page where users can:

1. **Search, filter, and browse** orders using the same filter criteria as the existing Order listing (status, payment status, shop, date range, keyword search).
2. **Select individual orders, multiple orders, or all matching orders** via checkboxes.
3. Choose **Print Invoices** or **Print Delivery Slips** with a Khmer/English language selector.
4. Open a **combined print preview** in a new browser tab with a single **Print All** action.

The preview renders the existing Order Blade templates (`invoice-template.blade.php` and `invoice-template-02.blade.php`) with the same data preparation and shared document styles. Each order begins on a new printed page. The existing **Print Invoice** and **Print Delivery Slip** on Order Details remain unchanged.

### Key advantages of the dedicated module approach

| Advantage | Description |
| --- | --- |
| **Zero risk to existing Order module** | No modifications to `orders.blade.php`, `listingData.blade.php`, or any shared listing component. |
| **Purpose-built UI** | The page is optimized for bulk selection/printing workflows rather than retrofitting checkboxes into a general listing. |
| **Simpler implementation** | No need for default-off shared-component hooks, session storage for selection persistence, or MDI tab state management. |
| **Independent maintenance** | Future enhancements to bulk printing do not affect the Order module and vice versa. |

### Alternatives considered

| Approach | Assessment |
| --- | --- |
| New dedicated module (this plan) | **Recommended.** Cleanly separated, purpose-built UI, no risk to existing Order module. |
| Checkboxes on existing Order listing | Original plan. Rejected by stakeholder preference — introduces shared component changes and coupling. |
| Modal with a printable iframe | Adds nested scrolling and frame-printing behavior to verify. Less suitable for reviewing many documents. |
| Generate a combined PDF on the server | Useful later for very large batches or downloadable archives. Requires additional infrastructure. Defer from this release. |

---

## 2. Findings from the current implementation

| Area | Current implementation | Implication |
| --- | --- | --- |
| Listing controller | `app/Http/Controllers/Admin/OrderController.php`, `index()` | Returns 50 orders per page, ordered by descending ID. The new module will reuse the same query predicates. |
| Listing view | `resources/admin/views/pages/order/orders.blade.php` | **Not modified.** The new module has its own independent view. |
| Shared listing component | `resources/admin/views/components/listingData.blade.php` | **Not modified.** The new module renders its own purpose-built order table. |
| Filters | `index()` and `pages/order/filter.blade.php` | Status tab, payment status, shop, search, From date, and To date. The new module replicates the same filter criteria. |
| Status semantics | `normalizeOrderStatusTab()` and `orderPaymentStatusForTab()` | Rejected maps to stored `Cancel`. The `all` tab without a payment filter excludes Cancel but includes Paid. Preserve this behavior. |
| Date defaults | `dateRange($req, false)` | The listing has no automatic current-month restriction. The new module must not introduce one. |
| Detail data | `OrderController::show()` | Loads customer, shop, barber, order details including soft-deleted detail rows, product/service data, and payments with creator. Also supplies `invoicePreviousBalance`. |
| Individual printing | `pages/order/detail.blade.php`, `printTemplate1()` / `printTemplate2()` | **Not modified.** Remains unchanged. |
| Invoice template | `pages/order/invoice-template.blade.php` | Uses `OrderPricing::line()` and the supplied previous balance. Minimum filler-row layout is part of the current format. |
| Delivery Slip template | `pages/order/invoice-template-02.blade.php` | Uses product codes, UOM, delivery date, discounts, net unit prices, and totals. Product UOM is accessed by the template and needs eager loading for a batch. |
| Document CSS | `pages/order/detail.blade.php`, approximately lines 1512–2263 | Document styles and print rules are embedded in the detail page. Existing paper setting is **A5 landscape**, with **4 mm top/bottom and 6 mm left/right margins**. |
| Template IDs | Both templates | IDs include language but not order ID. Repeating the templates without an optional ID override would create duplicate IDs. |
| Template context | Customer variables assigned in `detail.blade.php` | The templates inherit customer name, phone, and address from their parent view. Bulk rendering must explicitly reproduce the detail context per order. |
| Other consumers | `pages/remaining_amount/detail.blade.php` | Also includes both Order print templates. Template changes must preserve existing defaults and be regression-tested here. |
| Previous balance | `app/Services/InvoiceService.php`, `previousBalance()` | Sums current debt on the customer's other non-deleted Pending/Partial orders, including other shops and newer orders. Rounds each total/payment to cents and clamps each debt to zero. It is not a chronological running balance or a batch subtotal. |
| Workspace | `resources/admin/ts/workspace/mdi.js` | MDI form interception applies. Forms with `data-no-mdi` or `target="_blank"` bypass the form interceptor. |
| Permissions | `CheckAdminPermission` and `app/Support/AdminPermissions.php` | Detail access uses `order-view`. New bulk routes must be explicitly protected. |
| Menu config | `config/menu.php` | Sidebar navigation is driven by config array entries with `type`, `path`, `active`, `permission`, `name`, and `icon`. |
| Legacy multiple print component | `resources/admin/views/components/multiple_invoice_print.blade.php` | Uses a different store-based format; not the current Order invoice template and should not be used. |

### Existing behavior to preserve

No changes to order creation, invoice numbering, payment recording, rejection, stock effects, pricing, discounts, customer debt rules, exports, or existing status/filter semantics. Printing is read-only and must not mark orders as paid, delivered, or printed.

---

## 3. New module structure

### 3.1 Sidebar navigation

Add a new sidebar entry below "Orders" in `config/menu.php`:

```php
[
    'type'  => 'single',
    'active' => 'admin/order-bulk-print/*',
    'path' => 'admin/order-bulk-print',
    'permission' => ['order-view'],
    'name' => [
        'en' => 'Order Bulk Print',
        'km' => 'បោះពុម្ពការបញ្ជាទិញជាច្រើន',
    ],
    'icon' => 'bx-printer',
],
```

Uses the existing `order-view` permission — no new permission type is needed. Users who can view orders can use bulk printing.

### 3.2 Route group

Add a new route group in `routes/admin.php` after the existing Order route group:

```php
// Order Bulk Printing
Route::group([
    'prefix' => 'order-bulk-print',
    'as'     => 'order-bulk-print-'
], function () {
    Route::get('/', [OrderBulkPrintController::class, 'index'])->name('index');
    Route::post('selection', [OrderBulkPrintController::class, 'selection'])->name('selection');
    Route::post('preview', [OrderBulkPrintController::class, 'preview'])->name('preview');
});
```

| Route | Method | Purpose |
| --- | --- | --- |
| `admin-order-bulk-print-index` | GET | The dedicated bulk printing page with filters and order table. |
| `admin-order-bulk-print-selection` | POST | AJAX endpoint to fetch all matching order IDs for "Select All Matching". |
| `admin-order-bulk-print-preview` | POST | Standalone HTML preview page opened in a new tab via form POST. |

### 3.3 Controller

New `app/Http/Controllers/Admin/OrderBulkPrintController.php`:

- `index(Request $req)` — Renders the bulk printing page with paginated orders (50 per page), applying the same filter predicates as `OrderController::index()` via a shared query builder.
- `selection(Request $req)` — Returns matching order IDs (bounded by limit) for the current filters, as JSON.
- `preview(Request $req)` — Validates selected IDs, loads full print data, and renders the standalone preview page.

The controller reuses `OrderController`'s query-building logic by extracting a shared protected trait or helper, keeping `OrderController` completely unchanged.

### 3.4 Permission mapping

Add explicit mapping in `AdminPermissions.php` for the new route names:

```php
// In forRequest():
if (str_starts_with($name ?? '', 'admin-order-bulk-print-')) {
    return ['order-view'];
}
```

---

## 4. Proposed UI design

### 4.1 Page layout

The page follows the project's existing admin layout pattern (`@extends('admin::shared.layout')`) with a clean, modern, purpose-built interface:

```text
┌─────────────────────────────────────────────────────────────────────┐
│  Order Bulk Printing                                                │
│  ────────────────────────────────────────────────────────────────── │
│                                                                     │
│  ┌─ Filters ──────────────────────────────────────────────────────┐ │
│  │ [Status ▼]  [Payment Status ▼]  [Shop ▼]  [From]  [To]        │ │
│  │ [🔍 Search orders...]                         [Apply] [Reset]  │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌─ Selection Toolbar (visible when ≥1 selected) ────────────────┐ │
│  │ ✓ 12 orders selected                                          │ │
│  │ Language: [Khmer] [English]                                    │ │
│  │ [🖨 Print Selected Invoices]  [🖨 Print Selected Delivery Slips]│ │
│  │ [Clear Selection]                                              │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌─ Order Table ──────────────────────────────────────────────────┐ │
│  │ [☐] No.  Invoice#     Date       Shop    Customer    Status   │ │
│  │ [☐]  1   INV-0108    2026-10-18  Shop A  John Doe   Pending  │ │
│  │ [☑]  2   INV-0107    2026-10-18  Shop B  Jane Doe   Partial  │ │
│  │ [☑]  3   INV-0106    2026-10-17  Shop A  Walk-in    Paid     │ │
│  │ [☐]  4   INV-0105    2026-10-17  Shop C  Sam Smith  Pending  │ │
│  │ ...                                                            │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  All 50 orders on this page selected.                               │
│  [Select all 84 matching orders]                                    │
│                                                                     │
│  « 1 2 3 4 » (standard pagination)                                 │
└─────────────────────────────────────────────────────────────────────┘
```

### 4.2 Filter section

The filter section is **always visible** (not in a popover) for quick access:

- **Status tabs/dropdown:** All, Pending, Partial, Paid, Rejected (matching existing Order module semantics).
- **Payment status:** Optional filter, same as existing.
- **Shop:** Select2 dropdown following project standards.
- **Date range:** From/To date pickers following project date picker standards.
- **Search:** Free-text search across invoice number, shop name, customer name/phone, product name, service name (same as existing).
- **Apply / Reset buttons:** Apply triggers a page reload with filter parameters; Reset clears all filters.

### 4.3 Order table

A clean, purpose-built table (not using the shared `listingData` component):

- **Checkbox column** as the first column with a header checkbox for select/deselect all on the current page.
- **Row number** (sequential within the page).
- **Invoice number** — displayed prominently.
- **Order date** — formatted consistently.
- **Shop name** — from the related shop.
- **Customer name** — with walk-in fallback.
- **Total amount** — formatted as currency.
- **Payment status** — with colored badge (Pending/Partial/Paid/Rejected).

Each row is selectable by clicking the checkbox. The row itself is not clickable (no navigation to detail).

### 4.4 Selection behavior

| User action | Result |
| --- | --- |
| Click row checkbox | Toggle selection for that order; update count immediately. |
| Click header checkbox | Select/deselect all orders on the current page. |
| Some rows on page selected | Header checkbox shows indeterminate state. |
| Change page (same filters) | Preserve selections across pages; reconcile visible checkboxes. |
| Click "Select all N matching" | AJAX request fetches all matching IDs; replaces selection. |
| Deselect after selecting all | Convert to explicit ID set minus the deselected ID. |
| Apply new filters or reset | Clear selection with a brief notice. |
| Click "Clear Selection" | Clear all selected IDs. |

### 4.5 Selection toolbar

Appears when at least one order is selected:

- **Selection count:** "12 orders selected"
- **Language toggle:** Khmer / English buttons (default based on UI locale).
- **Print Selected Invoices:** Submits a POST form to `admin-order-bulk-print-preview` with `document_type=invoice`, selected IDs, language, and current filter context. Opens in a new tab (`target="_blank"`, `data-no-mdi`).
- **Print Selected Delivery Slips:** Same as above with `document_type=delivery_slip`.
- **Clear Selection:** Clears all selected IDs.

### 4.6 "Select all matching" flow

After selecting the current page (header checkbox):

```text
All 50 orders on this page selected. [Select all 84 matching orders]
```

Clicking "Select all 84 matching orders" triggers a POST to `admin-order-bulk-print-selection`, which returns the bounded ID list. On success:

```text
All 84 matching orders selected. [Clear selection]
```

The all-matching label reflects the count at the time of selection. Newly created orders are not automatically included.

### 4.7 Batch limits

- **Maximum 100 orders** and **2,000 order-detail rows** per preview (configurable in `config/order_printing.php`).
- If a selection exceeds either limit, show a clear message explaining the limit and asking the user to narrow filters or select fewer orders. **Never silently truncate.**

### 4.8 Responsive behavior

- Filters stack vertically on narrow screens.
- Table scrolls horizontally with the checkbox column staying reachable.
- Selection toolbar stacks vertically; print buttons become full-width.
- Approximately 44 px touch targets for checkboxes and buttons.
- Accessible names including order references; keyboard-navigable checkboxes.
- Bilingual translations for all labels, counts, and error messages.

---

## 5. Print preview workflow

1. The user filters the list and selects orders on the bulk printing page.
2. The user chooses the batch language and clicks one of the two print actions.
3. A native CSRF-protected POST form submits to a new browser tab, using `target="_blank"` and `data-no-mdi`. Submit directly from the user action; do not wait for an asynchronous request before opening the tab.
4. The server validates the payload, permissions, filter context, selected records, and batch limits; it loads all required print data.
5. The response is a standalone combined preview containing only the selected document type and language for each order. It has no sidebar, workspace tabs, payment forms, or detail-page scripts.
6. While fonts/images are preparing, show "Preparing documents…" and disable Print All. Show the document count once available. Use a spinner/status, not a fabricated percentage.
7. The user reviews the document stack and clicks **Print All**. One `window.print()` call opens the browser print dialog for the combined document.
8. The preview stays open after printing or cancellation. The original bulk printing page and selection stay available in the original tab.

### Preview layout

```text
Invoice Preview · 12 documents · English          [Print All] [Close Preview]
Prepared: 2026-10-19 19:00                        Paper: A5 landscape

Document 1 of 12 · INV-0108                        ← screen-only separator
┌──────────────────────────────────────────────────────────────┐
│ Existing Invoice template and information                    │
└──────────────────────────────────────────────────────────────┘

Document 2 of 12 · INV-0106
┌──────────────────────────────────────────────────────────────┐
│ Existing Invoice template and information                    │
└──────────────────────────────────────────────────────────────┘
```

Use a sticky, wrapping preview toolbar and a vertical stack of paper-shaped documents. At narrow widths, fit the preview visually to the viewport with a readable full-size/zoom option. Keep the printable dimensions unchanged. Remove screen scaling, shadows, spacing, and controls in print media.

"12 documents" is an order/document count, not a guaranteed physical page count: long orders can occupy multiple pages. The browser print dialog remains the final pagination preview.

### Failure and recovery states

| Condition | User experience |
| --- | --- |
| No selection | Print actions disabled; explain that orders must be selected. |
| All-matching request loading/fails | Disable only the relevant action during loading; show an inline retry message on failure and preserve existing selection. |
| Batch over limit | Show the order/detail-row limit and instruct the user to reduce the batch. No partial preview. |
| Selected order deleted or no longer matches applied scope | Reject the complete preview with a clear "Selection changed; refresh and review" message. Do not silently omit a document. |
| Lost permission or expired session | Explain that access/sign-in is required; preserve selection in the original tab. |
| Network/server error | Keep the page intact and provide a retry/back path. Error preview page has no Print All control. |
| New tab prevented by browser | Show guidance to allow/open the preview from a direct user action. |
| Font/image asset failure | Show a visible retry state and keep Print All disabled until required assets are ready. |
| Print cancelled | Keep the preview and selection. Do not display "Printed successfully" or infer completion from a browser event. |

---

## 6. Technical implementation

### 6.1 New controller — `OrderBulkPrintController`

```
app/Http/Controllers/Admin/OrderBulkPrintController.php
```

**`index(Request $req)`** — Renders the bulk printing page:
- Apply the same filter predicates as `OrderController::index()` (status, payment status, shop, date range, search).
- Query builder is shared via a protected trait `BuildsOrderListingQuery` (extracted from common predicates, not modifying `OrderController`'s method signatures).
- Paginate at 50 per page, ordered by `id DESC`.
- Pass filter values back to the view for form re-population.
- Include shop list for the Shop filter dropdown.

**`selection(Request $req)`** — AJAX endpoint returning matching IDs:
- Same query predicates, no pagination.
- Fetch at most `limit + 1` IDs. If too many match, return a limit response rather than the first 100.
- Response: JSON with `ids[]`, `count`, and `over_limit` boolean.

**`preview(Request $req)`** — Print preview:
- Validate via `OrderBulkPrintRequest`.
- Load all selected orders with full print relationships.
- Build per-order context (customer variables, previous balance for invoices).
- Render standalone preview Blade view.

### 6.2 Shared query builder trait

```
app/Http/Controllers/Admin/Traits/BuildsOrderListingQuery.php
```

Extract the common order listing query predicates (status, payment status, shop, dates, search) into a trait. Both `OrderController::index()` and `OrderBulkPrintController` can use this trait. The trait provides a method like `buildOrderListingQuery(Request $req, string $status): Builder` that returns the base query before pagination/eager-loading.

**Critical:** `OrderController::index()` continues to call its own method chain exactly as-is. The trait extraction must not change any of its behavior or method signatures.

### 6.3 Request validation

```
app/Http/Requests/Admin/OrderBulkPrintRequest.php
```

Validates:
- `document_type`: exactly `invoice` or `delivery_slip`. Map to a fixed Blade view server-side. Never accept a client-supplied template path.
- `print_locale`: exactly `en` or `km`.
- `order_ids`: nonempty array of distinct positive integers within the configured limit.
- Filter context keys: explicitly allowlisted types/dates.
- IDs resolve to permitted, non-deleted records in the submitted applied listing scope.
- Every requested ID is present after validation. If any is unavailable, fail the complete request.
- Total detail rows within the configured limit.

Use 422 for invalid/oversized input, 403 for permission failures.

### 6.4 Bulk print service

```
app/Services/OrderBulkPrintService.php
```

- Bound batch size checks (order count and detail row count).
- Eager-load print data for selected orders in descending ID order:
  - `customer`, `shop`, `barber`
  - `orderDetails` using `withTrashed()`, with `service` and `product.uom`
  - `payments.createdBy` (preserving existing payment ordering)
- Build separate render context per order: `order`, `printLocale`, `invoicePreviousBalance`, `customerName`, `customerPhone`, `customerAddress`, and a unique print-area ID.
- Reproduce the Order Details parent values, including walk-in label behavior.
- Keep per-order context isolated so one customer's fallback values cannot leak into the next document.

### 6.5 Batch previous balance

For Invoice batches, add a batch helper in `InvoiceService` while retaining the existing `previousBalance()` method unchanged:

1. Collect the selected orders' non-null customer IDs.
2. Stream those customers' non-deleted Pending/Partial orders, selecting only IDs, customer IDs, totals, and paid amounts.
3. Use the exact existing per-order rounding-to-cents and `max(0, total - paid)` rule.
4. For each selected invoice, subtract its own contribution if it contributed.
5. Walk-in invoices have zero previous balance.
6. Do not restrict by shop, date, or selection filters.

Prove equivalence against `previousBalance()` for each fixture.

### 6.6 Template reuse

Continue to include `invoice-template.blade.php` and `invoice-template-02.blade.php`. Add only an optional print-area ID parameter with the current IDs as defaults. Bulk IDs include order ID, template, and locale; existing individual and Remaining Amount consumers keep their current IDs and output.

Extract document appearance CSS from Order Details into a shared print-style partial:
```
resources/admin/views/pages/order/partials/print-document-styles.blade.php
```
Include at the same cascade position in the detail page and in the new preview. Preserve property values and ordering. Keep detail-only shell hiding and template switching in the detail page.

### 6.7 Page breaks and pagination

Preserve `@page { size: A5 landscape; margin: 4mm 6mm; }`. Wrap each order in a bulk document section with `break-before: page` (except the first). This avoids trailing blank pages.

Within the bulk wrapper:
- Allow long order tables to flow onto additional pages.
- Keep item rows and signature blocks together where they fit.
- Hide screen-only labels, controls, shadows, and scaling during printing.
- Single-page documents retain existing layout. Multi-page documents retain all content.

Wait for fonts before enabling Print All, using `document.fonts.ready` with image load completion.

### 6.8 Configuration

```
config/order_printing.php
```

```php
return [
    'max_orders' => 100,
    'max_detail_rows' => 2000,
];
```

---

## 7. Performance approach

- No document HTML is added to the normal Order listing. Documents are prepared only after a print action.
- One bulk query with eager-loaded relationships instead of fetching each detail page.
- Order-count and detail-row checks before rendering.
- Batch previous-balance reads by customer; preserve existing cents arithmetic.
- Render only the selected template and language, not all four variants per order.
- Load shared assets once; no iframes or per-order HTTP requests.

Measure batches of 1, 10, 50, and 100 orders, plus batches near 2,000 detail rows. Record server time, query count, peak memory, HTML size, font readiness time, and preview responsiveness. Provisional target: usable 50-order preview within 5 seconds.

---

## 8. Affected files

Paths below describe proposed implementation changes. Only this Markdown plan is created at the review stage.

### New files

| File | Purpose |
| --- | --- |
| `app/Http/Controllers/Admin/OrderBulkPrintController.php` | Index, selection, and preview actions for the new module. |
| `app/Http/Controllers/Admin/Traits/BuildsOrderListingQuery.php` | Shared order listing query predicates trait. |
| `app/Http/Requests/Admin/OrderBulkPrintRequest.php` | Input validation for selection and preview. |
| `app/Services/OrderBulkPrintService.php` | Batch size checks, data loading, and per-order context preparation. |
| `resources/admin/views/pages/order-bulk-print/index.blade.php` | Main bulk printing page with filters, table, selection UI. |
| `resources/admin/views/pages/order-bulk-print/preview.blade.php` | Standalone combined preview page with Print All action. |
| `resources/admin/views/pages/order/partials/print-document-styles.blade.php` | Shared document CSS extracted from detail page. |
| `config/order_printing.php` | Configurable batch limits. |
| `lang/en/order_bulk_print.php` | English translations for the new module. |
| `lang/km/order_bulk_print.php` | Khmer translations for the new module. |
| `tests/Feature/OrderBulkPrintTest.php` | Backend tests for request validation, permissions, selection, rendering, and financial parity. |

### Modified files (minimal, targeted changes only)

| File | Proposed change |
| --- | --- |
| `config/menu.php` | Add new sidebar entry for Order Bulk Printing after the Orders entry. |
| `routes/admin.php` | Add the new route group. |
| `app/Support/AdminPermissions.php` | Add explicit `order-view` mapping for `admin-order-bulk-print-*` routes. |
| `app/Services/InvoiceService.php` | Add batch `previousBalanceForMany()` helper; existing `previousBalance()` unchanged. |
| `resources/admin/views/pages/order/invoice-template.blade.php` | Optional unique print-area ID parameter; preserve default ID and content. |
| `resources/admin/views/pages/order/invoice-template-02.blade.php` | Same optional-ID support for delivery slips. |
| `resources/admin/views/pages/order/detail.blade.php` | Replace extracted CSS block with `@include` of the new shared partial at the same position. Keep all other content unchanged. |

### Files explicitly NOT modified

| File | Reason |
| --- | --- |
| `resources/admin/views/pages/order/orders.blade.php` | Order listing page remains unchanged per stakeholder requirement. |
| `resources/admin/views/components/listingData.blade.php` | Shared listing component remains unchanged — no checkbox hooks needed. |
| `resources/admin/ts/workspace/mdi.js` | Workspace behavior unchanged. |
| `resources/admin/views/pages/remaining_amount/detail.blade.php` | Remaining Amount printing is unchanged; template contract preserved. |
| Report/export modules, global styles, database schema | Not in scope. |

---

## 9. Potential impacts and controls

| Risk or impact | Control |
| --- | --- |
| CSS extraction alters individual printing | Keep cascade order and declarations; compare both templates and languages before/after using print media and saved PDFs. |
| Shared templates affect Remaining Amount printing | Preserve default IDs/context contract and run regression tests. |
| Incorrect previous balance for multiple invoices of one customer | Per-customer debt accumulation with per-invoice exclusion; compare directly to existing `previousBalance()`. |
| Missing/repeated pages or clipped totals | Forced page breaks between documents, bulk-only flow rules, long-order fixtures, and real print preview inspection. |
| Browser memory/server load | Bounded counts and detail rows, eager loading, one language/template per preview. |
| Missing locale fonts/logos | Reuse local assets, wait for readiness, and test failed/slow assets. |
| Permissions omitted on new routes | Explicit route permission mapping and real middleware tests. |
| Reprint after data changed | Timestamped preview; regenerate to refresh. No database updates or locks from printing. |

No schema migration or data backfill required. Deployment should refresh Laravel route/view/config caches. Rollback removes the new controller, views, routes, and config entry.

---

## 10. Testing scenarios and acceptance criteria

### 10.1 Backend and data correctness

| Scenario | Expected result |
| --- | --- |
| One ID; several IDs; all 50 IDs on a page | Exactly one chosen document per selected order, in descending ID order. |
| 0, duplicate, malformed, negative, nonexistent, or excessive IDs | Clear validation failure; no partial batch. |
| 100 orders / 101 orders; 2,000 rows / 2,001 rows | Boundary accepted/rejected per configured limits. |
| Unauthenticated user; user without `order-view`; permitted user; super-admin | Existing auth/permission semantics enforced on all three endpoints. |
| Invalid template/locale, unexpected filter fields | Reject input; never resolve a client-supplied view path. |
| All status tabs, payment filters, shop, search, date ranges | Matching IDs equal the normal Order listing across all pages. |
| Order deleted or changed between selection and preview | Reject the stale batch and require review. |
| Multiple customers, same customer, walk-ins, missing data | Context remains isolated per order. |
| Products, services, UOM, discounts, paid/partial/unpaid | Printed values equal individual output. |
| Previous balance equivalence | Bulk method equals `previousBalance()` for every fixture. |
| No data mutations | Database snapshot before/after preview is identical. |

### 10.2 UI and interaction checks

- Filter application, reset, and persistence across pagination.
- Select/deselect by mouse, keyboard, and touch.
- Header checkbox select-all/deselect-all with correct indeterminate state.
- Cross-page selection preservation.
- "Select all matching" success, over-limit, and error scenarios.
- Clear selection behavior.
- Filter change clears selection with notice.
- Print button disabled when nothing selected.
- Language toggle state reflected in preview.
- Responsive behavior at 360 px, 768 px, and desktop widths.
- Khmer labels and long text handling.

### 10.3 Document and printing checks

- Invoice and Delivery Slip, each in Khmer and English, with 1, 2, 10, 50, and maximum-size batches.
- Compare bulk documents against individual documents for the same data; customer fallbacks and previous balance must match.
- Every order begins on a fresh printed page; no trailing blank page, mixed-language content, or duplicated documents.
- Verify invoice footer rowspans, totals at page boundaries, delivery-slip signatures, logo dimensions, and Khmer fonts.
- Print All invokes one print dialog; cancel/retry retains selection and preview.
- Test actual print previews on Chrome, Firefox, Safari, and mobile browsers.

---

## 11. Implementation sequence after approval

1. **Extract shared query trait** — Create `BuildsOrderListingQuery` trait from `OrderController::index()` predicates. Verify Order listing is unchanged.
2. **Create controller, routes, permissions** — `OrderBulkPrintController`, route group, sidebar entry, permission mapping. Verify navigation and access control.
3. **Build the bulk printing page** — Filters, order table with checkboxes, selection logic, toolbar, language toggle, Alpine.js component. All self-contained in `order-bulk-print/index.blade.php`.
4. **Implement selection endpoint** — Bounded ID resolution with the same query predicates.
5. **Extract document CSS** — Shared print-style partial from `detail.blade.php`. Verify individual printing is unchanged.
6. **Add optional template IDs** — Update both templates with optional unique ID parameter. Verify existing consumers.
7. **Build preview page** — Standalone preview with document loop, asset readiness, page breaks, Print All action.
8. **Implement bulk print service** — Data loading, context preparation, batch previous balance.
9. **Add translations** — English and Khmer for all new labels, counts, and errors.
10. **Testing** — Backend tests, UI interaction tests, real print-preview checks, mobile review, performance measurements. Adjust limits based on evidence.
11. **Present for review** — Completed enhancement with test results and screenshots for review before deployment.

---

## 12. Approval scope

Approval of this plan authorizes development of the solution above, with these defaults:

- **New dedicated Order Bulk Printing module** — separate from the existing Order listing.
- Separate browser-tab preview with one batch print dialog.
- Current-page and cross-page selection; all-matching selection within batch limits.
- Initial limits of **100 orders / 2,000 detail rows**, configurable.
- **A5 landscape** with existing margins; Khmer or English per batch; descending order ID sequence.
- Selection reset when filters change; in-memory selection during the page session.
- Existing `order-view` permission; no new permission type or database schema.
- Bulk actions on All, Pending, Partial, Paid, and Rejected statuses. Trash excluded.
- Minimal shared template changes (optional ID parameter and CSS extraction only).
- **No modifications to the Order listing page, shared listing component, or unrelated modules.**

Development and deployment have not been performed. The next step is the user's review and approval of this plan, as requested.
