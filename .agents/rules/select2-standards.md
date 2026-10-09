# Select2 Dropdown Standard Rules

Whenever creating a new Select2 field or converting an existing form field to Select2 across any UI forms in this project, you MUST strictly adhere to the project's established core Select2 implementation, standard configuration, and styling.

---

## 1. Core Principles

1. **Reuse Existing Standards:** Always review and follow the existing Select2 implementation in the project's core code (`resources/admin/sass/select2.scss` and `resources/admin/views/pages/order/createOrder.blade.php`) before making any changes.
2. **No Custom Styling:** Do NOT create new CSS, custom dropdown themes, or override the existing Select2 appearance (e.g., do NOT override `.select2-dropdown`, `.select2-search--dropdown`, `.select2-search__field`, or `.select2-results__option`).
3. **Consistent UI:** All Select2 dropdowns must follow the same standard layout, height (43px), border-radius (7px), border (#cbd5e1), typography (12.5px, 500, #334155), and standard arrow appearance already established in the project.
4. **Reuse Existing Configuration:** Use the existing Select2 initialization pattern and the global helper `window.$select2FocusInputSearch()` on `select2:open`.
5. **Avoid Duplicate Code:** Do NOT introduce unnecessary Select2 initialization scripts, duplicate CSS, or redundant configurations.
6. **Preserve Functionality:** Ensure all existing field behaviors, validations, events, data binding (Alpine/Blade), and business logic remain unchanged.
7. **Scope Control:** Only modify the requested fields. Do not change unrelated UI components, styles, or functionality.

---

## 2. Standard HTML & DOM Structure

Always wrap the `<select>` and custom `.select2Reset` in a `.select2Group` wrapper:

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

---

## 3. Standard Styling Rules

Follow the established core styles defined in `resources/admin/sass/select2.scss` and `resources/admin/views/pages/order/createOrder.blade.php`:

```css
.select2Group {
    width: 100% !important;
    position: relative !important;
}

.select2Group .select2.select2-container {
    width: 100% !important;
}

.select2Group .select2-container .select2-selection--single {
    height: 43px !important;
    border-radius: 7px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    box-shadow: none !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
}

.select2Group .select2-container--open .select2-selection--single,
.select2Group .select2-container--focus .select2-selection--single {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
}

.select2Group .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #334155 !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    line-height: normal !important;
    padding-left: 0 !important;
    padding-right: 28px !important;
}

.select2Group .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
    font-weight: 400 !important;
}

.select2Group .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    top: 0 !important;
    right: 8px !important;
}

/* Clear icon visibility & behavior */
.select2Group .select2Reset {
    position: absolute !important;
    top: 50% !important;
    right: 26px !important;
    transform: translateY(-50%) !important;
    cursor: pointer !important;
    z-index: 2 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.select2Group .select2Reset[style*="display: none"],
.select2Group .select2Reset[style*="display:none"],
.select2Group .select2Reset[x-cloak] {
    display: none !important;
}

.select2-container--default .select2-selection--single .select2-selection__clear {
    display: none !important;
}

.select2Group .select2Reset svg {
    fill: #94a3b8 !important;
    width: 18px !important;
    height: 18px !important;
    background: #ffffff !important;
    border-radius: 50% !important;
    transition: fill 0.15s ease !important;
}

.select2Group .select2Reset:hover svg {
    fill: #ef4444 !important;
}
```

---

## 4. Standard JavaScript Initialization

When initializing Select2 in JavaScript / jQuery:
1. Always set `width: '100%'`.
2. Do NOT enable `allowClear: true` to avoid unstyled native clear text duplicates; use the custom `.select2Reset` button instead.
3. Call `window.$select2FocusInputSearch()` on `select2:open`.
4. Ensure the clear button shows only when a non-empty value is selected, and hides when empty.

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
