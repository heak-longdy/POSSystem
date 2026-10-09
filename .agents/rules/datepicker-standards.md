# Date Picker Standard Rules

Apply these rules when creating, converting, or fixing date picker fields in this project. Only update the fields and pages requested by the user.

## 1. Reference Implementations

Review these files before making changes:

- `resources/admin/views/pages/report/sales/index.blade.php`: working report date fields, Alpine initialization, range constraints, and the calendar popup layering fix.
- `resources/admin/views/pages/order/orders.blade.php`: order listing date picker configuration and linked From/To limits.
- `resources/admin/views/pages/order/filter.blade.php`: calendar icon and clickable date field pattern.
- `resources/admin/sass/component.scss`: core jQuery UI calendar styling, compiled into `public/admin-public/css/app.css`.
- `resources/admin/views/index.blade.php`: shared jQuery UI asset loading.

Reuse the existing jQuery UI `datepicker()` plugin. Do not add another date picker library, duplicate asset imports, or substitute native `type="date"` fields unless the user requests it.

## 2. Fields and Appearance

- Use `type="text"`, `autocomplete="off"`, and translated labels/placeholders. Preserve manual entry unless the field already forbids it.
- Preserve each input's `name`, server-rendered value, validation, Alpine bindings, and submission behavior.
- Use input IDs unique among mounted workspace tabs and matching label `for` attributes. Use page prefixes, and instance prefixes where multiple copies of a page can coexist.
- Use component-local `x-ref` values for initialization and focus handling.
- Follow the order listing's calendar icon pattern: clicking the field or icon area focuses the input and opens the calendar. Keep keyboard focus working.
- Mark a decorative calendar icon with `aria-hidden="true"`.
- Reuse existing input styles. For report fields, follow the Sales Report: height `43px`, radius `7px`, border `1px solid #cbd5e1`, font size `12.5px`, weight `500`, and focus border `#2563eb` with a `2px` blue focus shadow.
- Preserve the existing order listing's pill layout when working there; do not restyle unrelated controls to match a report.
- For report icon wrappers, reuse `.filter-date-input`: relative positioning, `36px` right input padding, and an `18px` calendar icon positioned `12px` from the right and vertically centered. Use `#64748b` and `pointer-events: none` on the icon.
- Scope any necessary wrapper CSS to the requested page. Reuse the core calendar theme; do not create new calendar colors, navigation icons, or day-cell styling.

Example from the Sales Report, inside its existing Alpine component:

```html
<div class="filter-field-wrap">
    <label for="sales-from-date">{{ __('sales_report.filter.from_date') }}</label>
    <div class="filter-date-input" @click="$refs.fromDate.focus()">
        <input type="text" name="from_date" id="sales-from-date"
            x-ref="fromDate" class="filter-input" value="{{ $from_date }}"
            autocomplete="off" placeholder="{{ __('sales_report.filter.placeholder_date') }}">
        <i class="bx bx-calendar" aria-hidden="true"></i>
    </div>
</div>
```

Use the corresponding `toDate` reference for the end date. Adapt prefixes and translation keys for the requested page rather than copying Sales-specific names unchanged.

## 3. Initialization and Workspace Tabs

- Add initialization to the existing Alpine component's `init()` through `this.$nextTick(() => this.initDatepickers())`. If `init()` already schedules work, add the call to that callback; do not replace existing initialization or define a second `init()` method.
- Check `window.jQuery`, `$.fn.datepicker`, and the relevant field references before initializing. Views without date inputs, such as monthly report views, must remain valid.
- Select inputs through `this.$refs` or the current component root. Do not use global selectors such as `$('#from_date')` or initialize every `.datepicker-input` across the workspace.
- Do not rely solely on `$(document).ready()` for content loaded into workspace tabs.
- Initialize each input once. If a lifecycle can run initialization again, check for an existing date picker and avoid duplicate event handlers.
- Preserve existing callbacks and reactive state synchronization. If a field uses `x-model` or other input/change listeners, ensure date selections update those bindings too.

Use the following standard options. Keep existing business-specific date limits in addition to these settings:

```javascript
const options = {
    dateFormat: 'yy-mm-dd',
    changeYear: true,
    changeMonth: true,
    gotoCurrent: true,
    yearRange: '-50:+10',
    beforeShow: function(input, instance) {
        instance.dpDiv.addClass('sales-report-datepicker');
    },
    onClose: function(dateText, instance) {
        instance.dpDiv.removeClass('sales-report-datepicker');
    },
};
```

`yy-mm-dd` is jQuery UI's format for submitted `YYYY-MM-DD` values. `yearRange` controls the year selector; it does not replace `minDate` or `maxDate` validation.

## 4. Linked Date Ranges

For From/To pairs:

- Selecting From sets To's `minDate`.
- Selecting To sets From's `maxDate`.
- Apply the limits from prefilled values after both date pickers have initialized.
- Handle `change` as well as `onSelect`, so typed and cleared values update the opposite field's limit.
- Use `datepicker('getDate')` to read dates with the configured format instead of parsing date strings with `new Date(...)`.
- Clearing a field removes its corresponding range constraint using `null`. Retain any independent business limit already required by the form.
- Preserve existing presets, reset behavior, GET parameter names, and date defaults. Do not add automatic form submission unless it already exists or is requested.

Example range synchronization, after creating `$from` and `$to` from the component's references:

```javascript
$from.datepicker({
    ...options,
    onSelect: function(selected) {
        $to.datepicker('option', 'minDate', selected);
    }
}).on('change', function() {
    $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
});

$to.datepicker({
    ...options,
    onSelect: function(selected) {
        $from.datepicker('option', 'maxDate', selected);
    }
}).on('change', function() {
    $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
});

if ($from.val()) {
    $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
}
if ($to.val()) {
    $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
}
```

This example assumes no additional business limits or existing callbacks. Merge those requirements when applying it to another form.

## 5. Calendar Visibility and Clickability

The report content uses a higher stacking level than jQuery UI's default popup. A date picker can initialize successfully while its calendar is hidden behind the report table and cannot receive clicks.

For report date pickers, use the proven popup level from the order listing and Sales Report:

```css
#ui-datepicker-div.sales-report-datepicker {
    z-index: 9999 !important;
}
```

- Adapt the popup class to the requested page and use the same class in CSS, `beforeShow`, and `onClose`.
- Add it only while that page's calendar is open, and remove it on close. jQuery UI shares `#ui-datepicker-div` across inputs and workspace tabs.
- Keep this popup selector separate from the page wrapper selector: the calendar is appended outside the report wrapper.
- Do not depend on jQuery UI's computed inline `z-index` or add a global override affecting every calendar.
- If the requested field is inside a modal, check its actual stacking context and use the appropriate popup level for that modal. Verify it rather than assuming the report level applies.

## 6. Verification

After changing behavior, verify it in a browser or a representative browser test using the project's actual scripts and styles:

1. Open the calendar from both the input and the icon area, including keyboard focus.
2. Select dates in both fields and reopen them to select again.
3. Change month and year through the calendar controls.
4. Confirm initial values, linked date limits, and manual entry/clearing behavior.
5. Confirm the submitted values retain the expected field names and `YYYY-MM-DD` format.
6. Check the calendar against the full report content, including the table below the filters, so stacking and click interception are exercised.
7. Verify initialization after workspace tab loading and confirm another tab's fields are unaffected.

Run applicable Blade/JavaScript syntax checks as well. Do not claim working date selection based only on syntax checks. If browser verification is unavailable, state that limitation.
