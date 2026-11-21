<div class="form-row w200">
    <input type="text" name="search" placeholder="{{ __('stock_in.filter.search') }}" value="{!! request('search') !!}">
</div>

{{-- Supplier Select2 Filter --}}
<div class="select2-filter-wrap w220">
    <div class="select2Group">
        <select name="supplier_id" id="stock_in_filter_supplier_id" class="SelectField select2">
            <option value="">{{ __('stock_in.filter.select_supplier') }}</option>
            @if (isset($supplier) && $supplier)
                <option value="{{ $supplier->id }}" selected>{{ $supplier->name }}</option>
            @endif
        </select>
        <div class="select2Reset" x-show="supplierId" @click.stop="resetSupplierFilter()" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
            </svg>
        </div>
    </div>
</div>

{{-- Shop Select2 Filter --}}
<div class="select2-filter-wrap w220">
    <div class="select2Group">
        <select name="shop_id" id="stock_in_filter_shop_id" class="SelectField select2">
            <option value="">{{ __('stock_in.filter.select_shop') }}</option>
            @if (isset($shop) && $shop)
                <option value="{{ $shop->id }}" selected>{{ $shop->name }}</option>
            @endif
        </select>
        <div class="select2Reset" x-show="shopId" @click.stop="resetShopFilter()" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
            </svg>
        </div>
    </div>
</div>

{{-- Payment Status Filter --}}
<div class="form-row w160">
    <select name="payment_status" class="filter-select" onchange="$(this).closest('form').submit()">
        <option value="">{{ __('stock_in.filter.payment_status') }}</option>
        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('stock_in.payment_status.unpaid') }}</option>
        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>{{ __('stock_in.payment_status.partial') }}</option>
        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('stock_in.payment_status.paid') }}</option>
    </select>
</div>

{{-- Date Range (From & To) --}}
<div class="filter-date-input w140" @click="$('#filter_from_date').focus()">
    <input type="text" name="from_date" id="filter_from_date" class="filter-input datepicker-input"
        value="{!! request('from_date') !!}" autocomplete="off" placeholder="{{ __('stock_in.filter.from_date') }}">
    <i class='bx bx-calendar' aria-hidden="true"></i>
</div>

<div class="filter-date-input w140" @click="$('#filter_to_date').focus()">
    <input type="text" name="to_date" id="filter_to_date" class="filter-input datepicker-input"
        value="{!! request('to_date') !!}" autocomplete="off" placeholder="{{ __('stock_in.filter.to_date') }}">
    <i class='bx bx-calendar' aria-hidden="true"></i>
</div>
