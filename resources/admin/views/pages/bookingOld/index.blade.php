@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper">
        <div class="header">
            @include('admin::shared.header', ['header_name' => 'Product Order'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/booking/list/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-booking-list', 1) !!}">
                            Service</div>
                        <div class="menu-item {!! Request::is('admin/booking/list-product/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-booking-list-product', 2) !!}">Product</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <select name="shop_id">
                                <option value="">All Shop</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}"> {{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <select name="barber_id">
                                <option value=""> All Barberdd</option>
                                @foreach ($barbers as $barber)
                                    <option value="{{ $barber->id }}"> {{ $barber->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <input type="text" name="from_date" placeholder="From Date" value="{!! $firstMonthDay ? $firstMonthDay : request('from_date') !!}"
                                id="from_date" autocomplete="off">
                        </div>
                        <div class="form-row">
                            <input type="text" name="to_date" placeholder="To Date" value="{!! $lastMonthDay ? $lastMonthDay : request('to_date') !!}"
                                id="to_date" autocomplete="off">
                        </div>
                        <div class="form-row">
                            <input type="text" name="timepicker" placeholder="From Date" value="" id="timepicker" autocomplete="off">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                    </form>

                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('adminGlobal.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.booking.table')
            {{-- <div class="table">
                @if ($data->count() > 0)
                    <div class="table-wrapper">
                        <div class="table-header">
                            <div class="row table-row-5">
                                <span>Nº</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Shop</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Barbor</span>
                            </div>
                            <div class="row table-row-30">
                                <span>Product</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Total Price $</span>
                            </div>
                            <div class="row table-row-10">
                                <span>Commission $</span>
                            </div>
                            <div class="row table-row-10">
                                <span>Order Date</span>
                            </div>
                        </div>
                        <div class="table-body">
                            @foreach ($data as $index => $item)
                                <div class="column">
                                    <div class="row table-row-5">
                                        <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->shop) ? $item->shop->name : '---' !!}</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->shop) ? $item->shop->name : '---' !!}</span>
                                    </div>
                                    <div class="row table-row-30">
                                        @foreach ($item->orders as $order)
                                            <span>
                                                {!! isset($order->product) ? $order->product->name : '---' !!} (Price : {!! isset($order) ? $order->price : '---' !!} $ , QTY
                                                {!! isset($order) ? $order->qty : '---' !!})
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->total_price) ? $item->total_price : '---' !!}</span>
                                    </div>
                                    <div class="row table-row-10">
                                        <span>{!! isset($item->commission) ? $item->commission : '---' !!}</span>
                                    </div>
                                    <div class="row table-row-10">
                                        <span>{!! isset($item->order_date) ? $item->order_date : '---' !!}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="table-footer">
                            @include('admin::components.pagination', ['paginate' => $data])
                        </div>
                    </div>
                @else
                    @component('admin::components.empty', [
                        'name' => __('No data'),
                        'msg' => __('adminGlobal.empty.descriptionSlide'),
                        'permission' => 'Customer-create',
                    ])
                    @endcomponent
                @endif
            </div> --}}
        </div>
    @stop
    @section('script')
        {{-- <script lang="ts">
        $(document).ready(function() {
            $("#from_date,#to_date").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
            $("#from_date").change(function() {
                let str = $(this).val();
                $("#to_date").datepicker("option", "minDate", new Date(str));
            });
            const date = $('#from_date').val();
            if(date){
                $("#to_date").datepicker("option", "minDate", new Date(date));
            }
        });
    </script> --}}
        <script>
            $(document).ready(function() {
                $("#from_date").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#to_date").datepicker("option", "minDate", selected)
                    }
                });
                $("#to_date").datepicker({
                    minDate: `{{ $startDate }}`,
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#from_date").datepicker("option", "maxDate", selected)
                    }
                });
                $('#timepicker').timepicker({
                    'timeFormat': 'H:i',
                    'interval': 30, // Interval between selectable times (in minutes)
                    'minTime': '0:00am',
                    'maxTime': '11:59pm',
                    'defaultTime': 'now',
                    'startTime': '00:00',
                    'dynamic': false,
                    'dropdown': true,
                    'scrollbar': true
                });
            });
        </script>
    @stop
