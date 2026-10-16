<div class="form-row w200">
    <input type="text" name="search" placeholder="{{ __('stock_in.filter.search_product') }}" value="{!! request('search') !!}">
</div>
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

