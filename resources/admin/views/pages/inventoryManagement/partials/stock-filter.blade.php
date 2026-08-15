<div class="form-row w200">
    <input type="text" name="search" placeholder="Search product..." value="{!! request('search') !!}">
</div>
<div class="form-row w200 custom-select">
    <select name="shop_id" class="SelectShop" id="shop_id" style="width: 100%;">
        <option value="">Select Shop</option>
        @if (isset($shop) && $shop)
            <option value="{{ $shop->id }}" selected>{{ $shop->name }}</option>
        @endif
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="date" placeholder="Date" value="{!! request('date') !!}" id="date" autocomplete="off">
</div>
