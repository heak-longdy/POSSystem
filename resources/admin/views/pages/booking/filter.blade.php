<div class="form-row w120 custom-select">
    <select name="payment_status">
        <option value="">{{ __('booking.filter.all_status') }}</option>
        <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}>{{ __('booking.status.pending') }}</option>
        <option value="Paid" {!! request('payment_status') == 'Paid' ? 'selected' : '' !!}>{{ __('booking.status.paid') }}</option>
        <option value="Cancel" {!! request('payment_status') == 'Cancel' ? 'selected' : '' !!}>{{ __('booking.status.canceled') }}</option>
    </select>
</div>
<div class="form-row w160">
    <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
        <option value="">{{ __('booking.form.select_shop') }}</option>
    </select>
</div>
<div class="form-row w160">
    <select name="barber_id" class="SelectBarber" id="barber_id" x-init="fetchSelectBarber()">
        <option value="">{{ __('booking.form.select_barber') }}</option>
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="from_date" placeholder="{{ __('booking.placeholder.from_date') }}"
        value="{!! $firstMonthDay ?: request('from_date') !!}" id="from_date" autocomplete="off">
</div>
<div class="form-row w120">
    <input type="text" name="to_date" placeholder="{{ __('booking.placeholder.to_date') }}"
        value="{!! $lastMonthDay ?: request('to_date') !!}" id="to_date" autocomplete="off">
</div>
<div class="form-row w180">
    <input type="text" name="search" placeholder="{{ __('booking.placeholder.search') }}" value="{!! request('search') !!}">
</div>
