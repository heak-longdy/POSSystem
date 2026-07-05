@extends('admin::shared.layout')
@section('layout')
<div class="content-wrapper">
    <div class="header">
        @include('admin::shared.header', ['header_name' => 'Booking History '.'Customer: '.$name->name . ' ' .$name->phone])
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