<div class="form-row w120 custom-select">
    <select name="payment_status">
        <option value="">All Status</option>
        <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}>Pending</option>
        <option value="Paid" {!! request('payment_status') == 'Paid' ? 'selected' : '' !!}>Paid</option>
        <option value="Cancel" {!! request('payment_status') == 'Cancel' ? 'selected' : '' !!}>Cancel</option>
    </select>
</div>
<div class="form-row w160">
    <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
        <option value="">Select Shop</option>
    </select>
</div>
<div class="form-row w160">
    <select name="barber_id" class="SelectBarber" id="barber_id" x-init="fetchSelectBarber()">
        <option value="">Select Barber</option>
    </select>
</div>
<div class="form-row w120">
    <input type="text" name="from_date" placeholder="From Date"
        value="{!! $firstMonthDay ?: request('from_date') !!}" id="from_date" autocomplete="off">
</div>
<div class="form-row w120">
    <input type="text" name="to_date" placeholder="To Date"
        value="{!! $lastMonthDay ?: request('to_date') !!}" id="to_date" autocomplete="off">
</div>
<div class="form-row w180">
    <input type="text" name="search" placeholder="Search..." value="{!! request('search') !!}">
</div>
