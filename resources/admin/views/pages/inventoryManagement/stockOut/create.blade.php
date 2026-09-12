@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $data = $data ?? null;
    $readonly = $readonly ?? false;
    $formTitle = $readonly ? __('stock_out.form.title.view') : ($id ? __('stock_out.form.title.update') : __('stock_out.form.title.create'));
    $selectedType = old('to_id', $data->to_id ?? $stockTypes->first()?->key);
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xStockOutForm">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', $id) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $formTitle }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('stock_out.form.shop') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="shop_id" id="shop_id" class="SelectShop" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">{{ __('stock_out.form.select_shop') }}</option>
                                @if (isset($selectedShop) && $selectedShop)
                                    <option value="{{ $selectedShop->id }}" selected>{{ $selectedShop->name }}</option>
                                @endif
                            </select>
                        </div>
                        @error('shop_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>{{ __('stock_out.form.product') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="product_id" id="product_id" class="SelectProduct" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">{{ __('stock_out.form.select_product') }}</option>
                                @if (isset($selectedProduct) && $selectedProduct)
                                    <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->name }}</option>
                                @endif
                            </select>
                        </div>
                        @error('product_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_out.form.current_stock') }}</label>
                        <input type="text" id="current_stock" value="{{ $currentStock ?? 0 }}" readonly>
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_out.form.qty') }}<span>*</span></label>
                        <input type="number" name="qty" min="1" value="{{ old('qty', $data->qty ?? '') }}" placeholder="{{ __('stock_out.form.placeholder_qty') }}" {!! $readonly ? 'readonly' : '' !!}>
                        <i class='bx bx-minus-circle'></i>
                        @error('qty')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('stock_out.form.stock_type') }}<span>*</span></label>
                        <select name="to_id" id="to_id" {!! $readonly ? 'disabled' : '' !!}>
                            <option value="">{{ __('stock_out.form.select_stock_type') }}</option>
                            @foreach ($stockTypes as $stockType)
                                @php
                                    $stockTypeName = __('stock_out.stock_types.' . $stockType->key);
                                    $stockTypeDisplay = $stockTypeName !== 'stock_out.stock_types.' . $stockType->key ? $stockTypeName : $stockType->name;
                                @endphp
                                <option value="{{ $stockType->key }}" {!! (string) $selectedType === (string) $stockType->key ? 'selected' : '' !!}>
                                    {{ $stockTypeDisplay }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_out.form.status') }}</label>
                        <input type="text" value="{{ $data ? $data->stock_status_title : __('stock_out.status.confirmed') }}" readonly>
                        <i class='bx bx-check-circle'></i>
                    </div>
                </div>
                <div class="row">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_out.form.remark') }}</label>
                        <input type="text" name="remark" value="{{ old('remark', $data->remark ?? '') }}" placeholder="{{ __('stock_out.form.placeholder_remark') }}" {!! $readonly ? 'readonly' : '' !!}>
                        <i class='bx bx-note'></i>
                        @error('remark')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    @if ($readonly)
                        @if ($id && !$data->trashed())
                            <button color="primary" type="button" s-click-link="{!! route('admin-' . $routeName . '-edit', $id) !!}">
                                <i data-feather="edit"></i>
                                <span>{{ __('global.action.edit') }}</span>
                            </button>
                        @endif
                    @else
                        <button type="submit" color="primary">
                            <i data-feather="save"></i>
                            <span>{{ __('global.button.submit') }}</span>
                        </button>
                        @if (!$id)
                            <button type="submit" name="save_opt" value="save_new" color="success">
                                <i data-feather="save"></i>
                                <span>{{ __('global.button.save_new') }}</span>
                            </button>
                        @endif
                    @endif
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xStockOutForm', () => ({
                isReadonly: @json($readonly),
                init() {
                    this.initShop();
                    this.initProduct();
                    $('#to_id').select2({ placeholder: @json(__('stock_out.form.select_stock_type')) });

                    if (this.isReadonly) {
                        return;
                    }

                    $('#shop_id').on('change', () => {
                        $('#product_id').val(null).trigger('change');
                        $('#current_stock').val(0);
                    });
                    $('#product_id').on('change', () => this.fetchCurrentStock());
                },
                initShop() {
                    $('#shop_id').select2({
                        placeholder: @json(__('stock_out.form.select_shop')),
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    id: item.id,
                                    text: item?.name ?? '',
                                }))
                            })
                        }
                    });
                },
                initProduct() {
                    $('#product_id').select2({
                        placeholder: @json(__('stock_out.form.select_product')),
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-shop-product') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term,
                                shop_id: $('#shop_id').val(),
                                product_id: JSON.stringify([]),
                            }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    id: item?.product?.id,
                                    text: item?.product?.name ?? '',
                                }))
                            })
                        }
                    });
                },
                async fetchCurrentStock() {
                    const productId = $('#product_id').val();
                    const shopId = $('#shop_id').val();
                    if (!productId || !shopId) {
                        $('#current_stock').val(0);
                        return;
                    }

                    await fetch(`/admin/stock-on-hand/find/${productId}/${shopId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        }
                    })
                        .then((response) => response.json())
                        .then((response) => $('#current_stock').val(response?.current_stock ?? 0))
                        .catch(() => $('#current_stock').val(0));
                },
            }));
        });
    </script>
@stop
