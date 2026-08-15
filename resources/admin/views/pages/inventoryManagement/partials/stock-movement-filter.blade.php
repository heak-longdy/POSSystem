<div class="form-row w160 custom-select">
    <select name="status_type" id="status_type" style="width: 100%;">
        <option value="">All Status</option>
        <option value="stock_in" {!! request('status_type') == 'stock_in' ? 'selected' : '' !!}>Stock In</option>
        <option value="stock_out" {!! request('status_type') == 'stock_out' ? 'selected' : '' !!}>Stock Out</option>
        <option value="stock_transfer" {!! request('status_type') == 'stock_transfer' ? 'selected' : '' !!}>Stock Transfer</option>
    </select>
</div>
<div class="form-row w180">
    <input type="text" name="search" placeholder="Search product..." value="{!! request('search') !!}">
</div>
<div class="form-row w180 custom-select">
    <select name="shop_id" class="SelectShop" id="shop_id" style="width: 100%;">
        <option value="">Select Shop</option>
        @if (isset($shop) && $shop)
            <option value="{{ $shop->id }}" selected>{{ $shop->name }}</option>
        @endif
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="from_date" placeholder="From date" value="{!! request('from_date') !!}" id="fromDate" autocomplete="off">
</div>
<div class="form-row w120">
    <input type="text" name="to_date" placeholder="To date" value="{!! request('to_date') !!}" id="toDate" autocomplete="off">
</div>
