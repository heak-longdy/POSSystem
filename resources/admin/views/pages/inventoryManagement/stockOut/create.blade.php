@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $data = $data ?? null;
    $readonly = $readonly ?? false;
    $formTitle = $readonly ? 'View Stock Out' : ($id ? 'Update Stock Out' : 'Create Stock Out');
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
                        <label>Shop<span>*</span></label>
                        <div class="select2Group">
                            <select name="shop_id" id="shop_id" class="SelectShop" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">Select Shop</option>
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
                        <label>Product<span>*</span></label>
                        <div class="select2Group">
                            <select name="product_id" id="product_id" class="SelectProduct" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">Select Product</option>
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
                        <label>Current Stock</label>
                        <input type="text" id="current_stock" value="{{ $currentStock ?? 0 }}" readonly>
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>Qty<span>*</span></label>
                        <input type="number" name="qty" min="1" value="{{ old('qty', $data->qty ?? '') }}" placeholder="Enter qty ..." {!! $readonly ? 'readonly' : '' !!}>
                        <i class='bx bx-minus-circle'></i>
                        @error('qty')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Stock Type<span>*</span></label>
                        <select name="to_id" id="to_id" {!! $readonly ? 'disabled' : '' !!}>
                            <option value="">Select Stock Type</option>
                            @foreach ($stockTypes as $stockType)
                                <option value="{{ $stockType->key }}" {!! (string) $selectedType === (string) $stockType->key ? 'selected' : '' !!}>
                                    {{ $stockType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>Status</label>
                        <input type="text" value="{{ $data->stock_status_title ?? 'Confirmed' }}" readonly>
                        <i class='bx bx-check-circle'></i>
                    </div>
                </div>
                <div class="row">
                    <div class="form-row iconInput">
                        <label>Remark</label>
                        <input type="text" name="remark" value="{{ old('remark', $data->remark ?? '') }}" placeholder="Enter remark ..." {!! $readonly ? 'readonly' : '' !!}>
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
                                <span>Edit</span>
                            </button>
                        @endif
                    @else
                        <button type="submit" color="primary">
                            <i data-feather="save"></i>
                            <span>Submit</span>
                        </button>
                        @if (!$id)
                            <button type="submit" name="save_opt" value="save_new" color="success">
                                <i data-feather="save"></i>
                                <span>Save & New</span>
                            </button>
                        @endif
                    @endif
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
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
                    $('#to_id').select2();

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
                        placeholder: 'Select Shop',
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
                        placeholder: 'Select Product',
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
