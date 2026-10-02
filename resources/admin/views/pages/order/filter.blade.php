<div class="form-row shop-select" style="width: 150px; min-width: 150px;">
    <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
        <option value="">{{ __('order.select_shop') }}</option>
    </select>
</div>
<div class="form-row date-input" style="width: 145px; min-width: 145px;">
    <input type="text" name="from_date" placeholder="{{ __('order.filter.from_date') }}"
        value="{!! request('from_date') !!}" id="from_date" autocomplete="off">
    <i class='bx bx-calendar'></i>
</div>
<div class="form-row date-input" style="width: 145px; min-width: 145px;">
    <input type="text" name="to_date" placeholder="{{ __('order.filter.to_date') }}"
        value="{!! request('to_date') !!}" id="to_date" autocomplete="off">
    <i class='bx bx-calendar'></i>
</div>
<div class="form-row search-input" style="width: 180px; min-width: 180px;">
    <input type="text" name="search" placeholder="{{ __('order.filter.search') }}" value="{!! request('search') !!}">
</div>
@if(request()->filled('from_date') || request()->filled('to_date') || request()->filled('shop_id') || request()->filled('search'))
    <a href="{!! route('admin-' . ($routeName ?? 'order') . '-list', $status ?? 'Pending') !!}" class="btn-clear-filter" title="{{ __('global.button.reset') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
@endif
