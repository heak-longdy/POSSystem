---
name: report-standards
description: Standard rules and requirements for Report Management and Excel Export functionality across all report modules.
activation: manual
trigger: manual
always_on: false
---

# Report Management & Excel Export Standard Rules

> **Activation Mode:** `Manual`  
> **Trigger Condition:** Manually activated when working on report modules, or when the user explicitly mentions "report", "update report", or references report Excel export standards.

Apply these rules whenever creating, converting, updating, or debugging report modules and **Export to Excel** functionality across the application. When working on any report (e.g., Sales Report, Order Transaction Report, Inventory Movement Report, Staff Expense Report), you MUST strictly adhere to these standards.

---

## 1. Reference Implementations

Review these files before making any report changes:

- **Order Module Reference:** `resources/admin/views/pages/order/orders.blade.php`: Canonical pattern for Excel export workflow, button triggers (`excel()`), state management (`exportLoading`), full-screen spinner overlay (`.loadingFullSizeLayout`), and dedicated workbook generator (`runExcelExport`).
- **Sales Report Reference:** `resources/admin/views/pages/report/sales/index.blade.php`: Reference implementation for reports, including multi-mode grouping, live MDI tab filter harvesting (`getExportParams`), and ExcelJS workbook creation.
- **Shared Excel Assets:** `resources/admin/views/pages/report/partials/excel-assets.blade.php`: Local imports for `exceljs.min.js` and `FileSaver.min.js`.
- **Shared Request Helper:** `resources/admin/views/pages/report/partials/excel-request.blade.php`: Resilient Axios export fetcher (`requestReportExport`) and localized error handler (`showReportExportError`).
- **Production Guidelines:** `docs/report-export-production.md`: Hostinger production deployment and diagnostic notes.

---

## 2. Core Principles

1. **Adopt Order Module Export Workflow:** Every report module must follow the Order module's two-stage export architecture:
   - `excel()`: Initiates export, activates loading state, gathers active filters, fetches data via Axios, invokes the export builder, handles errors, and resets loading in a `finally` block.
   - `runExcelExport(reportData)`: Pure workbook builder that configures columns, maps formatted rows, applies styling, generates spreadsheet buffer, and triggers browser download via FileSaver `saveAs()`.
2. **Strict Filter Fidelity:** Exported files must strictly contain **only records matching the user's currently applied filters**. If no filters are applied, the export must follow the default report listing behavior.
3. **MDI Tab Filter Synchronization:** In the MDI tab workspace, filter submissions do not update the top-level browser address bar (`window.location.search`). Never rely solely on `window.location.search`. Always harvest active filters from the active MDI tab URL, DOM form inputs, tracked Select2 fields, and datepickers.
4. **Remove Pagination from Export Queries:** Always remove the `page` parameter before requesting export data (`params.delete('page')`) so the backend exports the complete matching dataset rather than only the current page (e.g. 50 records).
5. **Preserve Business Logic & Formatting:** Do NOT change existing report columns, calculations, mathematical totals, header labels, widths, number formatting, or sheet names when updating export workflows.
6. **Local Development & Production Consistency:** Ensure the implementation works reliably in both local development and the Hostinger production server without requiring CDN internet access or custom server modifications.
7. **Scope Control:** Modify only the requested report pages and components. Never alter unrelated modules or global styles.

---

## 3. Standard UI & DOM Structure

### 3.1. Export Button
Use the standard export button in the page header actions, bound to `excel()` with `:disabled="exportLoading"`:

```blade
@can('report-sales-excel')
<button type="button" @click="excel()" class="btn-excel-export" :disabled="exportLoading">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
        <path d="M252.31-180Q222-180 201-201q-21-21-21-51.31v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46Q780-222 759-201q-21 21-51.31 21H252.31ZM450-664.46l-76.92 76.92q-8.93 8.92-21.19 8.81-12.27-.12-21.58-9.43-8.69-9.3-9-21.07-.31-11.77 9-21.08l124.38-124.38q5.62-5.62 11.85-7.92 6.23-2.31 13.46-2.31t13.46 2.31q6.23 2.3 11.85 7.92l124.38 124.38q8.92 8.92 8.81 20.89-.12 11.96-8.81 21.26-9.31 9.31-21.38 9.62-12.08.31-21.39-9L510-664.46v306q0 12.77-8.62 21.38-8.61 8.62-21.38 8.62t-21.38-8.62q-8.62-8.61-8.62-21.38v-306Z"/>
    </svg>
    <span x-text="exportLoading ? '{{ __('sales_report.excel.exporting') }}' : '{{ __('global.button.export') }}'">
        {{ __('global.button.export') }}
    </span>
</button>
@endcan
```

### 3.2. Full-Screen Loading Spinner Overlay
Include the standard full-screen loading overlay inside the root Alpine component just before the closing tag of `@section('layout')`:

```blade
<template x-if="exportLoading">
    <div class="loadingFullSizeLayout">
        <div class="loading loadingSubmit">
            <span id="spinner"></span>
            <label>{{ __('order.export_excel') }}</label>
        </div>
    </div>
</template>
```

### 3.3. Asset Inclusions
Always include the local Excel assets before the component script in `@section('script')`:

```blade
@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script>
        // Alpine component registration...
    </script>
@stop
```

---

## 4. Parameter & Filter Harvesting (`getExportParams`)

To guarantee that the exported Excel file respects all active filters, every report Alpine component must implement `getExportParams()` following this exact hierarchy:

```javascript
getExportParams() {
    // 1. Start with top-level browser query (for direct navigation and automated tests)
    const params = new URLSearchParams(window.location.search);

    // 2. Capture active MDI workspace tab query if running inside the MDI tab manager
    if (window.MDI && Array.isArray(window.MDI.tabs) && window.MDI.activeTabKey) {
        const currentTab = window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);
        if (currentTab && currentTab.url && currentTab.url.includes('?')) {
            const tabParams = new URLSearchParams(currentTab.url.split('?')[1]);
            for (const [key, value] of tabParams.entries()) {
                if (value !== '' && !params.has(key)) {
                    params.set(key, value);
                }
            }
        }
    }

    // 3. Merge server-rendered initial query parameters
    const initialQuery = @json(request()->query());
    if (initialQuery && typeof initialQuery === 'object') {
        for (const [key, value] of Object.entries(initialQuery)) {
            if (value !== null && value !== undefined && value !== '' && !params.has(key)) {
                params.set(key, value);
            }
        }
    }

    // 4. Capture current values from the tab's active filter form DOM controls
    const form = (this.$root ? this.$root.querySelector('#salesFilterForm') : null)
        || document.getElementById('salesFilterForm');
    if (form) {
        const formData = new FormData(form);
        for (const [key, value] of formData.entries()) {
            const trimmed = typeof value === 'string' ? value.trim() : value;
            if (trimmed !== null && trimmed !== undefined && trimmed !== '') {
                params.set(key, trimmed);
            } else if (params.has(key)) {
                params.delete(key);
            }
        }
    }

    // 5. Sync Select2 filter values tracked in Alpine component state
    if (this.filterValues && typeof this.filterValues === 'object') {
        for (const [key, value] of Object.entries(this.filterValues)) {
            const trimmed = typeof value === 'string' ? value.trim() : value;
            if (trimmed) {
                params.set(key, trimmed);
            } else if (value === '' && params.has(key)) {
                params.delete(key);
            }
        }
    }

    // 6. Ensure date inputs are captured if present in DOM
    if (this.viewMode === 'daily') {
        const fromDateInput = this.$refs.fromDate || (form ? form.querySelector('input[name="from_date"]') : null);
        const toDateInput = this.$refs.toDate || (form ? form.querySelector('input[name="to_date"]') : null);
        if (fromDateInput && fromDateInput.value && fromDateInput.value.trim()) {
            params.set('from_date', fromDateInput.value.trim());
        }
        if (toDateInput && toDateInput.value && toDateInput.value.trim()) {
            params.set('to_date', toDateInput.value.trim());
        }
    }

    // 7. Ensure view_mode and grouping reflect active component state
    params.set('view_mode', this.viewMode);
    params.set('grouping', this.grouping);

    // 8. CRITICAL: Remove pagination parameter so export retrieves the complete matching dataset
    params.delete('page');

    return params;
}
```

---

## 5. Standard Export Execution Flow

Inside the Alpine component:

```javascript
Alpine.data('xReportComponent', () => ({
    exportLoading: false,
    viewMode: '{{ $viewMode }}',
    grouping: '{{ $grouping }}',
    filterValues: {},

    // Include shared request helper methods
    @include('admin::pages.report.partials.excel-request')

    // Standard Order-style excel trigger
    async excel() {
        this.exportLoading = true;
        try {
            const currentParams = this.getExportParams();
            const reportData = await this.requestReportExport(currentParams);
            await this.runExcelExport(reportData);
        } catch (err) {
            this.showReportExportError(err);
        } finally {
            this.exportLoading = false;
        }
    },

    // Backward-compatibility alias
    async exportExcel() {
        return this.excel();
    },

    // Dedicated ExcelJS workbook builder
    async runExcelExport(reportData) {
        const workbook = new ExcelJS.Workbook();
        const sheetName = '{{ __('report.sheet_name') }}';
        const worksheet = workbook.addWorksheet(sheetName);

        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

        // Define columns
        worksheet.columns = [
            { header: '{{ __('report.excel.no') }}', key: 'index', width: 8 },
            // ... module-specific columns with defined widths ...
        ];

        // Populate rows
        rows.forEach((r, idx) => {
            worksheet.addRow({
                index: idx + 1,
                // ... map row properties ...
            });
        });

        // Standard header styling: bold with subtle background
        worksheet.getRow(1).font = { bold: true };
        worksheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE2E8F0' }
        };

        // Write buffer and trigger browser download
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        const filename = 'Report_' + moment().format('YYYY_MM_DD_HHmmss');
        saveAs(blob, filename);
    }
}));
```

---

## 6. Backend Controller Standards (`report` endpoint)

1. **Route Naming Convention:** The export endpoint must follow `admin-report-{module}-report` (e.g., `admin-report-sales-report`).
2. **Accept All Listing Filters:** The controller's `report(Request $req)` method must construct the exact same query as the index/daily/monthly listing methods:
   - Date range (`from_date` / `to_date`, `year` / `from_month` / `to_month`).
   - Secondary filters via `applySecondaryFilters($query, $req)`: `shop_id`, `barber_id` / `staff_id`, `customer_id`, `payment_status`, `pay_way`, `item_type`, `search` keyword.
3. **No Pagination on Export:** Export queries must call `->get()` (e.g. `getUngroupedAll(clone $baseQuery)`) rather than `->paginate()`.
4. **Standard JSON Response:** The endpoint must return a JSON response with status 200 and a `rows` array:
   ```json
   {
       "viewMode": "daily",
       "grouping": "ungrouped",
       "summary": { ... },
       "rows": [ ... ]
   }
   ```

---

## 7. Error Handling & Diagnostics

All report exports must use `showReportExportError(error)` from `excel-request.blade.php`:
- Detect missing client libraries (`REPORT_EXPORT_ASSETS_MISSING`).
- Detect missing or unrouted endpoints (`REPORT_EXPORT_ROUTE_MISSING` / `404`).
- Detect expired sessions / CSRF mismatch (`401` / `419`) and login redirects (`REPORT_EXPORT_RESPONSE_INVALID`).
- Detect permission denials (`403`).
- Detect server exceptions (`500+`) and network drops.
- Display translated, user-friendly alerts (`report_export.*`) while logging technical details to `console.error`.

---

## 8. Checklist for Adding or Updating Report Modules

Before finalizing any changes to a report module:

- [ ] Does the export button use `@click="excel()"` and `:disabled="exportLoading"`?
- [ ] Is the full-screen `<div class="loadingFullSizeLayout">` overlay present with spinner and label?
- [ ] Are local Excel assets included via `@include('admin::pages.report.partials.excel-assets')`?
- [ ] Does `getExportParams()` properly harvest active filters from MDI tabs, DOM form, Select2 state, and datepickers?
- [ ] Is `params.delete('page')` executed so all filtered records are exported without pagination cuts?
- [ ] Does the backend `report()` method apply the identical query filters as the UI table?
- [ ] Are all Excel column headers, widths, calculations, and data formatting completely preserved?
- [ ] Is `async exportExcel()` provided as an alias for backwards compatibility?
- [ ] Does the export function cleanly in both local environment and Hostinger production server?
