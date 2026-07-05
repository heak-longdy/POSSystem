@extends('admin::shared.layout')
@section('layout')
<div class="content-wrapper">
    <div class="header">
        @include('admin::shared.header', ['header_name' => 'Commission History '.'Barber: '.$name->name . ' ' .$name->phone])
        <div class="header-tab">
            <div class="header-tab-wrapper">
                <div class="menu-row">
                    <div class="menu-item active" s-click-link="{!! route('admin-barber-commission-history', $name->id) !!}">
                       All</div>
                </div>
            </div>
            <div class="header-action-button">
                <form class="filter" action="{!! url()->current() !!}" method="GET">
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
    <div class="table">
        @if ($data->count() > 0)
            <div class="table-wrapper">
                <div class="table-header">
                    <div class="row table-row-5">
                        <span>Nº</span>
                    </div>
                    <div class="row table-row-20">
                        <span>Receipt</span>
                    </div>
                    <div class="row table-row-35">
                        <span>Amount</span>
                    </div>
                    <div class="row table-row-20">
                        <span>Status</span>
                    </div>
                    <div class="row table-row-20">
                        <span>Request Date</span>
                    </div>
                </div>
                <div class="table-body">
                    @foreach ($data as $index => $item)
                        <div class="column">
                            <div class="row table-row-5">
                                <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                            </div>
                            <div class="row table-row-20">
                                <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image)  }}">
                                    <img src="{!! $item->image != null ? asset('file_manager' . $item->image) : asset('images/logo/default.png') !!}"
                                        onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                                </div>
                            </div>
                            <div class="row table-row-35">
                                <span>{!! isset($item->amount) ? $item->amount : '---' !!}</span>
                            </div>
                            <div class="row table-row-20">
                                <span>{!! isset($item->type) ? $item->type : '---' !!}</span>
                            </div>
                            <div class="row table-row-20">
                                <span>{!! isset($item->created_at) ? $item->created_at : '---' !!}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="table-footer">
                    @include('admin::components.pagination', ['paginate' => $data])
                </div>
            </div>
        @else
            @component('admin::components.empty',
                [
                    'name' => __('No data'),
                   // 'msg' => __('adminGlobal.empty.descriptionSlide'),
                   // 'permission' => 'customer-create',
                ])
            @endcomponent
        @endif
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