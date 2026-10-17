@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $data = $data ?? null;
    $readonly = $readonly ?? false;
    $formTitle = $readonly ? __('stock_transfer.form.title.view') : ($id ? __('stock_transfer.form.title.update') : __('stock_transfer.form.title.create'));
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xStockTransferForm">
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
                        <label>{{ __('stock_transfer.form.from_shop') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="shop_id" id="shop_id" class="SelectShop" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">{{ __('stock_transfer.form.select_from_shop') }}</option>
                                @if (isset($selectedShop) && $selectedShop)
                                    <option value="{{ $selectedShop->id }}" selected>{{ $selectedShop->name }}</option>
                                @endif
                            </select>
                            <div class="select2Reset" x-show="!isReadonly && selectedValues.shop_id" @click.stop="resetSelect('shop_id')" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('shop_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>{{ __('stock_transfer.form.product') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="product_id" id="product_id" class="SelectProduct" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">{{ __('stock_transfer.form.select_product') }}</option>
                                @if (isset($selectedProduct) && $selectedProduct)
                                    <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->name }}</option>
                                @endif
                            </select>
                            <div class="select2Reset" x-show="!isReadonly && selectedValues.product_id" @click.stop="resetSelect('product_id')" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('product_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_transfer.form.current_stock') }}</label>
                        <input type="text" id="current_stock" value="{{ $currentStock ?? 0 }}" readonly>
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_transfer.form.qty') }}<span>*</span></label>
                        <input type="number" name="qty" min="1" value="{{ old('qty', $data->qty ?? '') }}" placeholder="{{ __('stock_transfer.form.placeholder_qty') }}" {!! $readonly ? 'readonly' : '' !!}>
                        <i class='bx bx-transfer'></i>
                        @error('qty')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('stock_transfer.form.to_shop') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="to_shop_id" id="to_shop_id" class="SelectToShop" {!! $readonly ? 'disabled' : '' !!}>
                                <option value="">{{ __('stock_transfer.form.select_to_shop') }}</option>
                                @if (isset($selectedToShop) && $selectedToShop)
                                    <option value="{{ $selectedToShop->id }}" selected>{{ $selectedToShop->name }}</option>
                                @endif
                            </select>
                            <div class="select2Reset" x-show="!isReadonly && selectedValues.to_shop_id" @click.stop="resetSelect('to_shop_id')" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('to_shop_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('stock_transfer.form.status') }}</label>
                        <input type="text" value="{{ $data ? $data->stock_status_title : __('stock_transfer.status.confirmed') }}" readonly>
                        <i class='bx bx-check-circle'></i>
                    </div>
                </div>
                <div class="row">
                    <div class="form-row iconInput">
                        <label>{{ __('stock_transfer.form.remark') }}</label>
                        <input type="text" name="remark" value="{{ old('remark', $data->remark ?? '') }}" placeholder="{{ __('stock_transfer.form.placeholder_remark') }}" {!! $readonly ? 'readonly' : '' !!}>
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
            Alpine.data('xStockTransferForm', () => ({
                isReadonly: @json($readonly),
                selectedValues: { shop_id: '', product_id: '', to_shop_id: '' },
                init() {
                    this.initShop('#shop_id', @json(__('stock_transfer.form.select_from_shop')));
                    this.initShop('#to_shop_id', @json(__('stock_transfer.form.select_to_shop')));
                    this.initProduct();

                    const $selects = $(this.$root).find('#shop_id, #product_id, #to_shop_id');
                    const syncValue = (select) => {
                        this.selectedValues[select.name] = $(select).val() || '';
                    };
                    $selects.each((index, select) => syncValue(select))
                        .on('change select2:select select2:clear select2:unselect', (event) => syncValue(event.target));

                    if (this.isReadonly) {
                        return;
                    }

                    $(this.$root).find('#shop_id').on('change', () => {
                        $(this.$root).find('#product_id').val(null).trigger('change');
                        $(this.$root).find('#current_stock').val(0);
                    });
                    $(this.$root).find('#product_id').on('change', () => this.fetchCurrentStock());
                },
                resetSelect(field) {
                    if (this.isReadonly) return;
                    $(this.$root).find(`#${field}`).val(null).trigger('change');
                },
                initShop(selector, placeholder) {
                    $(this.$root).find(selector).select2({
                        placeholder: placeholder,
                        width: '100%',
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
                    $(this.$root).find('#product_id').select2({
                        placeholder: @json(__('stock_transfer.form.select_product')),
                        width: '100%',
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-shop-product') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term,
                                shop_id: $(this.$root).find('#shop_id').val(),
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
                    const productId = $(this.$root).find('#product_id').val();
                    const shopId = $(this.$root).find('#shop_id').val();
                    if (!productId || !shopId) {
                        $(this.$root).find('#current_stock').val(0);
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
                        .then((response) => $(this.$root).find('#current_stock').val(response?.current_stock ?? 0))
                        .catch(() => $(this.$root).find('#current_stock').val(0));
                },
            }));
        });
    </script>
@stop
