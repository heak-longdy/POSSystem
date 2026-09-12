@extends('admin::shared.layout')
@php
    $statusTitle = isset($data->status) && $data->status == 1
        ? __('stock_on_hand.status.active')
        : (isset($data->status) && $data->status == 2
            ? __('stock_on_hand.status.disabled')
            : '---');
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ __('stock_on_hand.form.title.view') }}
                </h3>
            </div>
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.product') }}</label>
                        <input type="text" value="{{ $data->product_title ?? '---' }}" readonly>
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.shop') }}</label>
                        <input type="text" value="{{ $data->shop_title ?? '---' }}" readonly>
                        <i class='bx bx-store-alt'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.category') }}</label>
                        <input type="text" value="{{ $data->category_title ?? '---' }}" readonly>
                        <i class='bx bx-category'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.uom') }}</label>
                        <input type="text" value="{{ $data->uom_title ?? '---' }}" readonly>
                        <i class='bx bx-ruler'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.current_stock') }}</label>
                        <input type="text" value="{{ $data->current_stock ?? 0 }}" readonly>
                        <i class='bx bx-layer'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.status') }}</label>
                        <input type="text" value="{{ $statusTitle }}" readonly>
                        <i class='bx bx-check-circle'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.date') }}</label>
                        <input type="text" value="{{ $data->created_date ?? '---' }}" readonly>
                        <i class='bx bx-calendar'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_on_hand.form.requested_by') }}</label>
                        <input type="text" value="{{ $data->request_by_title ?? '---' }}" readonly>
                        <i class='bx bx-user'></i>
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>{{ __('stock_on_hand.form.remark') }}</label>
                        <textarea rows="5" readonly>{{ $data->remark ?? '' }}</textarea>
                    </div>
                </div>
                <div class="form-button">
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('stock_on_hand.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop
