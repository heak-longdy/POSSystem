<div class="form-row w120 custom-select">
    <select name="payment_status">
        <option value="">{{ __('order.filter.all_status') }}</option>
        <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}>{{ __('order.status.pending') }}</option>
        <option value="Paid" {!! request('payment_status') == 'Paid' ? 'selected' : '' !!}>{{ __('order.status.paid') }}</option>
        <option value="Cancel" {!! request('payment_status') == 'Cancel' ? 'selected' : '' !!}>{{ __('order.status.canceled') }}</option>
    </select>
</div>
<div class="form-row w160">
    <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
        <option value="">{{ __('order.form.select_shop') }}</option>
    </select>
</div>
<div class="form-row w160">
    <select name="barber_id" class="SelectBarber" id="barber_id" x-init="fetchSelectBarber()">
        <option value="">{{ __('order.form.select_barber') }}</option>
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="from_date" placeholder="{{ __('order.placeholder.from_date') }}"
        value="{!! $firstMonthDay ?: request('from_date') !!}" id="from_date" autocomplete="off">
</div>
<div class="form-row w120">
    <input type="text" name="to_date" placeholder="{{ __('order.placeholder.to_date') }}"
        value="{!! $lastMonthDay ?: request('to_date') !!}" id="to_date" autocomplete="off">
</div>
<div class="form-row w180">
    <input type="text" name="search" placeholder="{{ __('order.placeholder.search') }}" value="{!! request('search') !!}">
</div>
