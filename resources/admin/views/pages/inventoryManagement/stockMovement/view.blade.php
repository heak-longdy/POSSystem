@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ __('stock_movement.form.title.view') }}
                </h3>
            </div>
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.product') }}</label>
                        <input type="text" value="{{ $data->product_title ?? '---' }}" readonly>
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.status') }}</label>
                        <input type="text" value="{{ $data->stock_status_title ?? '---' }}" readonly>
                        <i class='bx bx-transfer'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.category') }}</label>
                        <input type="text" value="{{ $data->category_title ?? '---' }}" readonly>
                        <i class='bx bx-category'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.uom') }}</label>
                        <input type="text" value="{{ $data->uom_title ?? '---' }}" readonly>
                        <i class='bx bx-ruler'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.qty') }}</label>
                        <input type="text" value="{{ $data->qty ?? 0 }}" readonly>
                        <i class='bx bx-hash'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.current_stock') }}</label>
                        <input type="text" value="{{ $data->current_stock ?? 0 }}" readonly>
                        <i class='bx bx-layer'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.stock_in') }}</label>
                        <input type="text" value="{{ $data->stock_in ?? 0 }}" readonly>
                        <i class='bx bx-plus-circle'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.stock_out') }}</label>
                        <input type="text" value="{{ $data->stock_out ?? 0 }}" readonly>
                        <i class='bx bx-minus-circle'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.from') }}</label>
                        <input type="text" value="{{ $data->from_title ?? '---' }}" readonly>
                        <i class='bx bx-log-out'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.to') }}</label>
                        <input type="text" value="{{ $data->to_title ?? '---' }}" readonly>
                        <i class='bx bx-log-in'></i>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.date') }}</label>
                        <input type="text" value="{{ $data->created_date ?? '---' }}" readonly>
                        <i class='bx bx-calendar'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_movement.form.requested_by') }}</label>
                        <input type="text" value="{{ $data->request_by_title ?? '---' }}" readonly>
                        <i class='bx bx-user'></i>
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>{{ __('stock_movement.form.remark') }}</label>
                        <textarea rows="5" readonly>{{ $data->remark ?? '' }}</textarea>
                    </div>
                </div>
                <div class="form-button">
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('stock_movement.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop
