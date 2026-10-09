# Project Coding Standards & Guidelines

## Select2 Dropdown Standard Rules

Whenever creating a new Select2 field or converting an existing form field to Select2 across any UI forms in this project, you MUST strictly adhere to the project's established core Select2 implementation, standard configuration, and styling.

### 1. Core Principles
1. **Reuse Existing Standards:** Always review and follow the existing Select2 implementation in the project's core code (`resources/admin/sass/select2.scss` and `resources/admin/views/pages/order/createOrder.blade.php`) before making any changes.
2. **No Custom Styling:** Do NOT create new CSS, custom dropdown themes, or override the existing Select2 appearance (e.g., do NOT override `.select2-dropdown`, `.select2-search--dropdown`, `.select2-search__field`, or `.select2-results__option`).
3. **Consistent UI:** All Select2 dropdowns must follow the same standard layout, height (43px), border-radius (7px), border (#cbd5e1), typography (12.5px, 500, #334155), and standard arrow appearance already established in the project.
4. **Reuse Existing Configuration:** Use the existing Select2 initialization pattern and the global helper `window.$select2FocusInputSearch()` on `select2:open`.
5. **Avoid Duplicate Code:** Do NOT introduce unnecessary Select2 initialization scripts, duplicate CSS, or redundant configurations.
6. **Preserve Functionality:** Ensure all existing field behaviors, validations, events, data binding (Alpine/Blade), and business logic remain unchanged.
7. **Scope Control:** Only modify the requested fields. Do not change unrelated UI components, styles, or functionality.

### 2. Standard HTML & DOM Structure
```html
<div class="select2Group">
    <select name="field_name" id="field_id" class="SelectField select2">
        <option value="">{{ __('placeholder_text') }}</option>
        @foreach ($items as $item)
            <option value="{{ $item->id }}">{{ $item->name }}</option>
        @endforeach
    </select>
    <div class="select2Reset" x-show="fieldValue" @click.stop="resetField()" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
        </svg>
    </div>
</div>
```

### 3. Standard Styling Rules
Defined in `resources/admin/sass/select2.scss` and `resources/admin/views/pages/order/createOrder.blade.php`:
- Input height: `43px`
- Border-radius: `7px`
- Border: `1px solid #cbd5e1`
- Focus: `border-color: #2563eb !important; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;`
- Font size: `12.5px`, font weight: `500`, rendered color: `#334155`
- Arrow: `top: 0; right: 8px; height: 100%` (standard arrow; no custom border triangle overrides)
- Reset clear icon (`.select2Reset`):
  - 18x18px SVG (`#94a3b8`, hover `#ef4444`)
  - `position: absolute; top: 50%; right: 26px; transform: translateY(-50%);`
  - Suppress native clear (`.select2-selection__clear { display: none !important; }`)
  - Hidden when empty via `.select2Reset[style*="display: none"] { display: none !important; }`
- Dropdown: Use the standard core styles from `select2.scss` / `app.css`. Do NOT create custom overrides for `.select2-dropdown`.

### 4. Standard JavaScript Initialization
```javascript
$select.select2({
    placeholder: '{{ __("placeholder") }}',
    width: '100%'
}).on('select2:open', function () {
    if (typeof window.$select2FocusInputSearch === 'function') {
        window.$select2FocusInputSearch();
    }
}).on('change select2:select select2:clear select2:unselect', function () {
    // Sync clear icon visibility and state
});
```

## Date Picker Standard Rules

Whenever creating, converting, or fixing date picker fields, you MUST read and follow [Date Picker Standard Rules](.agents/rules/datepicker-standards.md).

- Reuse the existing jQuery UI date picker and the working implementations in `resources/admin/views/pages/report/sales/index.blade.php` and `resources/admin/views/pages/order/orders.blade.php`.
- Use `yy-mm-dd`, month/year selection, `gotoCurrent: true`, and `yearRange: '-50:+10'`, while preserving field-specific business constraints.
- Initialize within the current Alpine component after `$nextTick`; use local references and unique input IDs so workspace tabs do not interfere with one another.
- Preserve submitted field names, existing values, translations, bindings, validation, and report behavior. Link From/To limits and update them when dates are selected, typed, or cleared.
- Reuse existing input styling and the calendar icon pattern. Keep the report calendar above report content with a scoped popup class and `z-index: 9999 !important`; add the class in `beforeShow` and remove it in `onClose`.
- Verify actual calendar interaction, including date selection and month/year navigation. Syntax checks alone do not confirm that the popup is visible or clickable.
- Apply changes only to the requested fields and pages.
