<div class="form-row w200">
    <input type="text" name="search" placeholder="{{ __('stock_in.filter.search_product') }}" value="{!! request('search') !!}">
</div>
<div class="form-row w200 custom-select">
    <select name="shop_id" class="SelectShop" id="shop_id" style="width: 100%;">
        <option value="">{{ __('stock_in.filter.select_shop') }}</option>
        @if (isset($shop) && $shop)
            <option value="{{ $shop->id }}" selected>{{ $shop->name }}</option>
        @endif
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="date" placeholder="{{ __('stock_in.filter.date') }}" value="{!! request('date') !!}" id="date" autocomplete="off">
</div>
