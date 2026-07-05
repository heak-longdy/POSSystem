@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper">
        <div class="header">
            @include('admin::shared.header', ['header_name' => __('Barber Management')])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/report/barber/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-report-barber-list', 1) !!}">
                           All</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <select name="barber_id">
                                <option value=""> All Barber</option>
                                @foreach ($barbers as $barber)
                                    <option value="{{ $barber->id }}">  {{ $barber->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <input type="text" name="from_date" placeholder="From Date"    value="{!! request('from_date') !!}" id="from_date" autocomplete="off">
                        </div>
                        <div class="form-row">
                            <input type="text" name="to_date" placeholder="To Date"    value="{!! request('to_date') !!}" id="to_date" autocomplete="off">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                    </form>
                   
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('user.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.barber.table')
        </div>
    </div>
@stop
@section('script')
    <script lang="ts">
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
    </script>
@stop