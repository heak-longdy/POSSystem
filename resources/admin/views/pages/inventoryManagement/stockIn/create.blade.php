@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $data = $data ?? null;
    $readonly = $readonly ?? false;
    $isCreate = empty($id) && !$readonly;
    $formTitle = $readonly ? __('stock_in.form.title.view') : ($id ? __('stock_in.form.title.update') : __('stock_in.form.title.create'));
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xStockInForm">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper {{ $isCreate ? 'stock-in-form' : '' }}" action="{!! route('admin-' . $routeName . '-save', $id) !!}" method="POST" @submit="handleFormSubmit($event)">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $formTitle }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                {{-- Header Information: Supplier & Shop --}}
                <div class="stock-in-header-card">
                    <div class="stock-in-card-title">
                        <i class='bx bx-building-house'></i>
                        <span>{{ __('stock_in.table.supplier') }} & {{ __('stock_in.table.shop') }}</span>
                    </div>

                    <div class="row-2">
                        <div class="form-row">
                            <label>{{ __('stock_in.form.supplier') }}<span>*</span></label>
                            <div class="select2Group">
                                <select name="supplier_id" class="SelectSupplier" id="supplier_id" x-init="fetchSelectSupplier()" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value=""> {{ __('stock_in.form.select_supplier') }}</option>
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="supplier?.id" @click="$select2Data('#supplier_id'); supplier = { id: '', text: '' }">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" x-model="supplier.text" name="supplier_text">
                            @error('supplier_id')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                        <div class="form-row">
                            <label>{{ __('stock_in.form.shop') }}<span>*</span></label>
                            <div class="select2Group">
                                <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value=""> {{ __('stock_in.form.select_shop') }}</option>
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="shop?.id" @click="$select2Data('#shop_id'); shop = { id: '', text: '' }">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" x-model="shop.text" name="shop_text">
                            @error('shop_id')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>

                    @if ($isCreate)
                        <div class="row">
                            <div class="form-row iconInput" style="margin-bottom: 0;">
                                <label>{{ __('stock_in.form.general_remark') }}</label>
                                <input type="text" name="remark" value="{{ old('remark') }}" placeholder="{{ __('stock_in.form.placeholder_remark') }}">
                                <i class='bx bx-note'></i>
                                @error('remark')
                                    <label class="error">{{ $message }}</label>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                @if ($isCreate)
                    {{-- Multi-Record Items Section (Create Mode) --}}
                    <div class="stock-in-items-section">
                        @error('items')
                            <div class="stock-in-alert stock-in-alert--error">
                                <i class='bx bx-error-circle'></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        <div class="stock-in-items-toolbar">
                            <div class="stock-in-toolbar-left">
                                <div class="stock-in-section-heading">
                                    <i class='bx bx-list-plus'></i>
                                    <h4>{{ __('stock_in.form.records_title') }}</h4>
                                </div>
                                <span class="stock-in-section-subtitle">{{ __('stock_in.form.records_desc') }}</span>
                            </div>

                            <div class="stock-in-toolbar-right">
                                <div class="stock-in-badge">
                                    <i class='bx bx-file'></i>
                                    <span class="badge-label">{{ __('stock_in.form.total_records') }}:</span>
                                    <strong class="badge-num" x-text="items.length"></strong>
                                </div>
                                <div class="stock-in-badge badge-primary">
                                    <i class='bx bx-layer'></i>
                                    <span class="badge-label">{{ __('stock_in.form.total_qty') }}:</span>
                                    <strong class="badge-num" x-text="totalQty()"></strong>
                                </div>
                                <button type="button" class="btn-stock-add" @click="addRow()">
                                    <i class='bx bx-plus'></i>
                                    <span>{{ __('stock_in.button.add_row') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Table of Multiple Records (Always Visible) --}}
                        <div class="stock-in-table-container">
                            <div class="stock-in-loading-shimmer" x-show="loadingProducts" style="display: none;">
                                <i class='bx bx-loader-alt bx-spin'></i>
                                <span>Loading shop products...</span>
                            </div>

                            <table class="stock-in-table">
                                <thead>
                                    <tr>
                                        <th class="th-num">#</th>
                                        <th class="th-product">{{ __('stock_in.table.product') }} <span class="req">*</span></th>
                                        <th class="th-stock">{{ __('stock_in.form.current_stock') }}</th>
                                        <th class="th-qty">{{ __('stock_in.table.qty') }} <span class="req">*</span></th>
                                        <th class="th-remark">{{ __('stock_in.table.remark') }}</th>
                                        <th class="th-action">{{ __('stock_in.form.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in items" :key="row.uid">
                                        <tr class="stock-in-row" :class="{'dropdown-open': row.dropdownOpen}">
                                            <td class="td-num">
                                                <span class="row-num" x-text="index + 1"></span>
                                            </td>

                                            <td class="td-product">
                                                <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id">

                                                {{-- When Product is Selected --}}
                                                <div class="selected-product-card" x-show="row.product_id && !row.dropdownOpen">
                                                    <div class="selected-product-img">
                                                        <img :src="row.product_image || '{{ asset('images/logo/default.png') }}'" onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                                    </div>
                                                    <div class="selected-product-info">
                                                        <div class="selected-product-name" x-text="row.product_name"></div>
                                                        <div class="selected-product-meta">
                                                            <span class="meta-tag meta-category" x-text="row.product_category" x-show="row.product_category"></span>
                                                            <span class="meta-tag meta-uom" x-text="row.product_uom" x-show="row.product_uom"></span>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-change-product" @click="openDropdown(row, $event)" title="{{ __('stock_in.form.select_product') }}">
                                                        <i class='bx bx-sync'></i>
                                                        <span>Change</span>
                                                    </button>
                                                </div>

                                                {{-- When Product is NOT Selected or Changing --}}
                                                <div class="product-dropdown-wrapper" x-show="!row.product_id || row.dropdownOpen" @click.outside="closeDropdown(row)">
                                                    <div class="product-dropdown-trigger" :class="{'is-active': row.dropdownOpen}" @click="toggleDropdown(row, $event)">
                                                        <i class='bx bx-search'></i>
                                                        <span x-text="row.product_name || '{{ __('stock_in.form.select_product') }}'" :class="{'placeholder-text': !row.product_id}"></span>
                                                        <i class='bx bx-chevron-down' :class="{'is-rotated': row.dropdownOpen}"></i>
                                                    </div>

                                                    <div class="product-dropdown-menu" :class="{'dropup-menu': row.isDropup}" x-show="row.dropdownOpen" style="display: none;">
                                                        <div class="product-dropdown-search">
                                                            <i class='bx bx-search'></i>
                                                            <input type="text"
                                                                :data-dropdown-input="row.uid"
                                                                x-model="row.searchQuery"
                                                                placeholder="{{ __('stock_in.form.search_product') }}"
                                                                @click.stop>
                                                            <button type="button" class="btn-clear-search" x-show="row.searchQuery" @click.stop="row.searchQuery = ''">
                                                                <i class='bx bx-x'></i>
                                                            </button>
                                                        </div>

                                                        <div class="product-dropdown-list">
                                                            <template x-for="product in availableProducts(row)" :key="product.id">
                                                                <div class="product-option-item" @click="selectProduct(row, product)">
                                                                    <div class="product-option-img">
                                                                        <img :src="product.image || '{{ asset('images/logo/default.png') }}'" onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                                                    </div>
                                                                    <div class="product-option-detail">
                                                                        <div class="product-option-name" x-text="product.name"></div>
                                                                        <div class="product-option-sub">
                                                                            <span class="meta-tag meta-category" x-text="product.category" x-show="product.category"></span>
                                                                            <span class="meta-tag meta-uom" x-text="product.uom" x-show="product.uom"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="product-option-stock" x-show="shopId">
                                                                        <span class="stock-pill" :class="{'stock-zero': !product.current_stock || product.current_stock == 0}">
                                                                            <i class='bx bx-archive'></i>
                                                                            <span x-text="`Stock: ${product.current_stock ?? 0}`"></span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <div class="product-option-empty" x-show="!shopId">
                                                                <i class='bx bx-store-alt'></i>
                                                                <span>Please select a shop first</span>
                                                            </div>
                                                            <div class="product-option-empty" x-show="shopId && availableProducts(row).length === 0">
                                                                <i class='bx bx-search-alt'></i>
                                                                <span>{{ __('stock_in.form.no_products_found') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <template x-if="fieldError(index, 'product_id')">
                                                    <label class="error" x-text="fieldError(index, 'product_id')"></label>
                                                </template>
                                            </td>

                                            <td class="td-stock">
                                                <div class="current-stock-badge" :class="{'is-zero': !row.current_stock || row.current_stock === 0}">
                                                    <i class='bx bx-archive'></i>
                                                    <span x-text="shopId ? (row.current_stock ?? 0) : '-'"></span>
                                                </div>
                                            </td>

                                            <td class="td-qty">
                                                <div class="qty-control-wrapper">
                                                    <button type="button" class="btn-qty-step" @click="row.qty = Math.max(1, (parseInt(row.qty, 10) || 1) - 1)" title="Decrease">
                                                        <i class='bx bx-minus'></i>
                                                    </button>
                                                    <input type="number"
                                                        :name="`items[${index}][qty]`"
                                                        x-model.number="row.qty"
                                                        min="1"
                                                        class="input-qty"
                                                        placeholder="1">
                                                    <button type="button" class="btn-qty-step" @click="row.qty = (parseInt(row.qty, 10) || 0) + 1" title="Increase">
                                                        <i class='bx bx-plus'></i>
                                                    </button>
                                                </div>
                                                <template x-if="fieldError(index, 'qty')">
                                                    <label class="error" x-text="fieldError(index, 'qty')"></label>
                                                </template>
                                            </td>

                                            <td class="td-remark">
                                                <div class="remark-input-wrapper">
                                                    <i class='bx bx-note'></i>
                                                    <input type="text"
                                                        :name="`items[${index}][remark]`"
                                                        x-model="row.remark"
                                                        class="input-remark"
                                                        placeholder="{{ __('stock_in.form.item_remark_placeholder') }}">
                                                </div>
                                                <template x-if="fieldError(index, 'remark')">
                                                    <label class="error" x-text="fieldError(index, 'remark')"></label>
                                                </template>
                                            </td>

                                            <td class="td-action">
                                                <button type="button"
                                                    class="btn-row-remove"
                                                    @click="removeRow(index)"
                                                    title="{{ __('stock_in.button.remove_row') }}">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <div class="stock-in-table-footer">
                                <button type="button" class="btn-stock-add-bottom" @click="addRow()">
                                    <i class='bx bx-plus-circle'></i>
                                    <span>{{ __('stock_in.button.add_row') }}</span>
                                </button>

                                <div class="stock-in-footer-summary">
                                    <div class="footer-stat">
                                        <i class='bx bx-file'></i>
                                        <span class="stat-title">{{ __('stock_in.form.total_records') }}:</span>
                                        <strong class="stat-number" x-text="items.length"></strong>
                                    </div>
                                    <div class="footer-stat stat-primary">
                                        <i class='bx bx-layer'></i>
                                        <span class="stat-title">{{ __('stock_in.form.total_qty') }}:</span>
                                        <strong class="stat-number" x-text="totalQty()"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Single Record Mode (Edit & View) --}}
                    <div class="row-2">
                        <div class="form-row">
                            <label>{{ __('stock_in.form.product') }}<span>*</span></label>
                            <div class="select2Group">
                                <select name="product_id" id="product_id" class="SelectProduct" x-init="fetchSelectProduct()" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value="">{{ __('stock_in.form.select_product') }}</option>
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="product?.id" @click="$select2Data('#product_id'); product = { id: '', text: '' }">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" x-model="product.text" name="product_text">
                            @error('product_id')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.current_stock') }}</label>
                            <input type="text" id="current_stock" value="{{ $currentStock ?? 0 }}" readonly>
                            <i class='bx bx-package'></i>
                        </div>
                    </div>
                    <div class="row-2">
                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.qty') }}<span>*</span></label>
                            <input type="number" name="qty" min="1" value="{{ old('qty', $data->qty ?? '') }}" placeholder="{{ __('stock_in.form.placeholder_qty') }}" {!! $readonly ? 'readonly' : '' !!}>
                            <i class='bx bx-plus-circle'></i>
                            @error('qty')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.status') }}</label>
                            <input type="text" value="{{ $data ? $data->stock_status_title : __('stock_in.status.confirmed') }}" readonly>
                            <i class='bx bx-check-circle'></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.remark') }}</label>
                            <input type="text" name="remark" value="{{ old('remark', $data->remark ?? '') }}" placeholder="{{ __('stock_in.form.placeholder_remark') }}" {!! $readonly ? 'readonly' : '' !!}>
                            <i class='bx bx-note'></i>
                            @error('remark')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>
                @endif

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
    <style>
        /* Form wrapper sizing */
        .form-wrapper.stock-in-form {
            max-width: 82rem;
            padding-left: 45px;
            padding-right: 45px;
        }

        /* Supplier & Shop Header Card */
        .stock-in-header-card {
            background: #ffffff;
            border: 1px solid rgba(152, 152, 152, 0.22);
            border-radius: 10px;
            padding: 20px 22px 14px 22px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .stock-in-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14.5px;
            font-weight: 600;
            color: #231f20;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(152, 152, 152, 0.15);
        }

        .stock-in-card-title i {
            font-size: 19px;
            color: #024de3;
        }

        /* Items Section */
        .stock-in-items-section {
            margin-bottom: 22px;
        }

        .stock-in-items-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .stock-in-section-heading {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stock-in-section-heading i {
            font-size: 20px;
            color: #024de3;
        }

        .stock-in-section-heading h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #231f20;
        }

        .stock-in-section-subtitle {
            font-size: 12.5px;
            color: #7a7f89;
            margin-top: 3px;
            display: block;
        }

        .stock-in-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stock-in-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 500;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all 0.15s ease;
        }

        .stock-in-badge i {
            font-size: 16px;
            color: #64748b;
        }

        .stock-in-badge.badge-primary {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #024de3;
        }

        .stock-in-badge.badge-primary i {
            color: #024de3;
        }

        .stock-in-badge .badge-label {
            color: inherit;
        }

        .stock-in-badge .badge-num {
            font-weight: 700;
            color: #0f172a;
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 12.5px;
        }

        .stock-in-badge.badge-primary .badge-num {
            color: #024de3;
            background: rgba(2, 77, 227, 0.1);
        }

        .btn-stock-add {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            background: linear-gradient(180deg, #0b57ea 0%, #024de3 100%) !important;
            color: #ffffff !important;
            border: 1px solid #0244cb !important;
            border-radius: 8px !important;
            padding: 0 16px !important;
            height: 38px !important;
            min-height: 38px !important;
            min-width: auto !important;
            line-height: 38px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(2, 77, 227, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .btn-stock-add:hover {
            background: linear-gradient(180deg, #024de3 0%, #003dbd 100%) !important;
            box-shadow: 0 4px 14px rgba(2, 77, 227, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            transform: translateY(-1px) !important;
        }

        .btn-stock-add:active {
            transform: translateY(0) !important;
            box-shadow: 0 1px 3px rgba(2, 77, 227, 0.2) !important;
        }

        .btn-stock-add i {
            font-size: 17px !important;
            color: #ffffff !important;
            line-height: 1 !important;
        }

        .btn-stock-add span {
            color: #ffffff !important;
            line-height: 1 !important;
            font-weight: 600 !important;
        }

        /* Alert Box */
        .stock-in-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 14px;
        }

        .stock-in-alert i {
            font-size: 19px;
            flex-shrink: 0;
        }

        .stock-in-alert--info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .stock-in-alert--error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .stock-in-loading-shimmer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13.5px;
        }

        .stock-in-loading-shimmer i {
            font-size: 20px;
            color: #024de3;
        }

        /* Table Container & Table */
        .stock-in-table-container {
            border: 1px solid rgba(152, 152, 152, 0.22);
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            overflow: visible !important;
            position: relative;
        }

        .stock-in-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }

        .stock-in-table thead tr {
            background: #f8f9fb;
        }

        .stock-in-table th {
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            background: #f8fafc;
            white-space: nowrap !important;
        }

        .stock-in-table th:first-child {
            border-top-left-radius: 9px;
        }

        .stock-in-table th:last-child {
            border-top-right-radius: 9px;
        }

        .stock-in-table th.th-num,
        .stock-in-table th.th-stock,
        .stock-in-table th.th-qty,
        .stock-in-table th.th-action {
            text-align: center;
        }

        .stock-in-table th .req {
            color: #ef4444;
            font-weight: 700;
        }

        .stock-in-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
            background: #ffffff;
        }

        .stock-in-row {
            position: relative;
            transition: background 0.15s ease;
        }

        .stock-in-row:hover td {
            background: #fafbfc;
        }

        .stock-in-row.dropdown-open {
            position: relative;
            z-index: 100 !important;
        }

        .stock-in-row.dropdown-open td {
            background: #f8faff;
        }

        .th-num, .td-num {
            width: 48px;
            text-align: center;
        }

        .row-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e9ecef;
            color: #5a5e66;
            font-weight: 600;
            font-size: 11.5px;
        }

        .th-product, .td-product {
            min-width: 330px;
            position: relative;
        }

        .th-stock, .td-stock {
            width: 135px;
            white-space: nowrap;
            text-align: center;
        }

        .th-qty, .td-qty {
            width: 140px;
            text-align: center;
        }

        .th-remark, .td-remark {
            min-width: 200px;
        }

        .th-action, .td-action {
            width: 64px;
            text-align: center;
        }

        /* Selected Product Card */
        .selected-product-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #d8dce5;
            border-radius: 8px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .selected-product-card:hover {
            border-color: #cbd5e1;
        }

        .selected-product-img {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(152, 152, 152, 0.2);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .selected-product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .selected-product-info {
            flex: 1;
            min-width: 0;
        }

        .selected-product-name {
            font-weight: 600;
            color: #231f20;
            font-size: 13.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .selected-product-meta {
            display: flex;
            gap: 6px;
            margin-top: 2px;
            flex-wrap: wrap;
        }

        .meta-tag {
            font-size: 11px;
            font-weight: 500;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.3;
        }

        .meta-category {
            background: #eff6ff;
            color: #024de3;
            border: 1px solid #dbeafe;
        }

        .meta-uom {
            background: #f5f3ff;
            color: #7c3aed;
            border: 1px solid #ede9fe;
        }

        /* Change Product Button (Overrides global button styles) */
        .btn-change-product {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
            height: 30px !important;
            min-height: 30px !important;
            padding: 0 11px !important;
            line-height: 30px !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 7px !important;
            color: #024de3 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            margin: 0 0 0 auto !important;
            box-shadow: 0 1px 2px rgba(2, 77, 227, 0.05) !important;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .btn-change-product:hover {
            color: #ffffff !important;
            border-color: #024de3 !important;
            background: #024de3 !important;
            box-shadow: 0 3px 8px rgba(2, 77, 227, 0.28) !important;
            transform: translateY(-1px) !important;
        }

        .btn-change-product:hover span {
            color: #ffffff !important;
        }

        .btn-change-product i {
            font-size: 15px !important;
            color: #024de3 !important;
            line-height: 1 !important;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.15s ease !important;
        }

        .btn-change-product:hover i {
            color: #ffffff !important;
            transform: rotate(180deg) !important;
        }

        .btn-change-product span {
            color: inherit !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
        }

        /* Product Dropdown Trigger & Menu */
        .product-dropdown-wrapper {
            position: relative;
            width: 100%;
        }

        .product-dropdown-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #d8dce5;
            border-radius: 7px;
            background: #ffffff;
            color: #231f20;
            cursor: pointer;
            transition: all 0.15s ease;
            height: 40px;
            box-sizing: border-box;
        }

        .product-dropdown-trigger:hover {
            border-color: #024de3;
            background: #fafcff;
        }

        .product-dropdown-trigger.is-active {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
        }

        .product-dropdown-trigger span {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13.5px;
            font-weight: 500;
        }

        .product-dropdown-trigger span.placeholder-text {
            color: #94a3b8;
            font-weight: 400;
        }

        .product-dropdown-trigger i.bx-search {
            font-size: 17px;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .product-dropdown-trigger i.bx-chevron-down {
            font-size: 18px;
            color: #64748b;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .product-dropdown-trigger i.bx-chevron-down.is-rotated {
            transform: rotate(180deg);
        }

        .product-dropdown-menu {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            width: 100%;
            min-width: 360px;
            max-width: 500px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16), 0 4px 10px rgba(15, 23, 42, 0.06);
            z-index: 1050;
            overflow: hidden;
            animation: dropdownSlideDown 0.15s ease-out;
        }

        .product-dropdown-menu.dropup-menu {
            top: auto;
            bottom: calc(100% + 5px);
            box-shadow: 0 -12px 32px rgba(15, 23, 42, 0.16), 0 -4px 10px rgba(15, 23, 42, 0.06);
            animation: dropdownSlideUp 0.15s ease-out;
        }

        @keyframes dropdownSlideDown {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes dropdownSlideUp {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-dropdown-search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .product-dropdown-search i.bx-search {
            color: #024de3;
            font-size: 17px;
            flex-shrink: 0;
        }

        .product-dropdown-search input {
            border: none !important;
            background: transparent !important;
            outline: none !important;
            box-shadow: none !important;
            padding: 3px 0 !important;
            font-size: 13px !important;
            font-family: inherit !important;
            width: 100% !important;
            color: #1e293b !important;
            height: auto !important;
            min-height: auto !important;
        }

        .product-dropdown-search input::placeholder {
            color: #94a3b8;
            font-size: 12.5px;
        }

        .btn-clear-search {
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            padding: 3px !important;
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            transition: all 0.15s ease !important;
            flex-shrink: 0 !important;
        }

        .btn-clear-search:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .btn-clear-search i {
            font-size: 14px !important;
            line-height: 1 !important;
        }

        .product-dropdown-list {
            max-height: 210px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        /* Custom sleek scrollbar for dropdown list */
        .product-dropdown-list::-webkit-scrollbar {
            width: 5px;
        }
        .product-dropdown-list::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .product-dropdown-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .product-dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .product-option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.12s ease;
        }

        .product-option-item:last-child {
            border-bottom: none;
        }

        .product-option-item:hover {
            background: #eff6ff;
        }

        .product-option-img {
            width: 34px;
            height: 34px;
            border-radius: 5px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-option-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-option-detail {
            flex: 1;
            min-width: 0;
        }

        .product-option-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .product-option-sub {
            display: flex;
            gap: 5px;
            margin-top: 2px;
        }

        .product-option-stock {
            flex-shrink: 0;
        }

        .stock-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .stock-pill.stock-zero {
            background: #f1f5f9;
            color: #64748b;
            border-color: #e2e8f0;
        }

        .stock-pill i {
            font-size: 12px;
        }

        .product-option-empty {
            padding: 24px 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .product-option-empty i {
            font-size: 26px;
            color: #94a3b8;
        }

        /* Current Stock Badge */
        .current-stock-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            min-width: 64px;
            letter-spacing: 0.01em;
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.06);
        }

        .current-stock-badge i {
            font-size: 15px;
            color: #059669;
        }

        .current-stock-badge.is-zero {
            background: #f1f5f9;
            color: #64748b;
            border-color: #e2e8f0;
            box-shadow: none;
        }

        .current-stock-badge.is-zero i {
            color: #94a3b8;
        }

        /* QTY Stepper Control */
        .qty-control-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: all 0.18s ease;
            overflow: hidden;
            height: 38px;
            width: 116px;
            margin: 0 auto;
        }

        .qty-control-wrapper:hover {
            border-color: #9ca3af;
        }

        .qty-control-wrapper:focus-within {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
        }

        .btn-qty-step {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            min-width: 32px !important;
            max-width: 32px !important;
            height: 38px !important;
            min-height: 38px !important;
            padding: 0 !important;
            line-height: 1 !important;
            background: #f8fafc !important;
            border: none !important;
            color: #64748b !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
            outline: none !important;
            user-select: none !important;
        }

        .btn-qty-step:first-child {
            border-right: 1px solid #e5e7eb !important;
        }

        .btn-qty-step:last-child {
            border-left: 1px solid #e5e7eb !important;
        }

        .btn-qty-step:hover {
            background: #eff6ff !important;
            color: #024de3 !important;
        }

        .btn-qty-step:active {
            background: #dbeafe !important;
            color: #003dbd !important;
        }

        .btn-qty-step i {
            font-size: 15px !important;
            color: inherit !important;
            line-height: 1 !important;
            pointer-events: none !important;
        }

        .input-qty {
            width: 52px !important;
            min-width: 52px !important;
            height: 38px !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            text-align: center !important;
            box-sizing: border-box !important;
            outline: none !important;
            box-shadow: none !important;
            -moz-appearance: textfield;
        }

        .input-qty::-webkit-outer-spin-button,
        .input-qty::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Remark Input */
        .remark-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: all 0.18s ease;
            height: 38px;
        }

        .remark-input-wrapper:hover {
            border-color: #9ca3af;
        }

        .remark-input-wrapper:focus-within {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
            background: #ffffff;
        }

        .remark-input-wrapper i {
            position: absolute;
            left: 11px;
            font-size: 16px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.18s ease;
            line-height: 1;
        }

        .remark-input-wrapper:focus-within i {
            color: #024de3;
        }

        .input-remark {
            width: 100% !important;
            height: 100% !important;
            padding: 0 12px 0 34px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 13.5px !important;
            color: #1e293b !important;
            box-sizing: border-box !important;
            background: transparent !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .input-remark::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
            font-size: 13px !important;
        }

        /* Remove Row Button */
        .btn-row-remove {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            max-width: 34px !important;
            min-height: 34px !important;
            padding: 0 !important;
            line-height: 34px !important;
            border-radius: 8px !important;
            border: 1px solid #fee2e2 !important;
            background: #fef2f2 !important;
            color: #ef4444 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 1px 2px rgba(239, 68, 68, 0.05) !important;
            outline: none !important;
        }

        .btn-row-remove:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
            transform: translateY(-1px) scale(1.06) !important;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3) !important;
        }

        .btn-row-remove i {
            font-size: 17px !important;
            color: #ef4444 !important;
            line-height: 1 !important;
            transition: color 0.15s ease !important;
        }

        .btn-row-remove:hover i {
            color: #ffffff !important;
        }

        /* Table Footer */
        .stock-in-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 9px 9px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-stock-add-bottom {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            background: #f8faff !important;
            border: 1.5px dashed #93c5fd !important;
            color: #024de3 !important;
            border-radius: 8px !important;
            padding: 0 18px !important;
            height: 38px !important;
            min-height: 38px !important;
            min-width: auto !important;
            line-height: 38px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 1px 2px rgba(2, 77, 227, 0.04) !important;
            outline: none !important;
        }

        .btn-stock-add-bottom:hover {
            background: #024de3 !important;
            border-color: #024de3 !important;
            border-style: solid !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(2, 77, 227, 0.28) !important;
        }

        .btn-stock-add-bottom i {
            font-size: 18px !important;
            color: #024de3 !important;
            line-height: 1 !important;
            transition: color 0.15s ease !important;
        }

        .btn-stock-add-bottom:hover i {
            color: #ffffff !important;
        }

        .btn-stock-add-bottom span {
            color: inherit !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
        }

        .stock-in-footer-summary {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .footer-stat {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #475569;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 6px 14px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all 0.15s ease;
        }

        .footer-stat i {
            font-size: 16px;
            color: #64748b;
        }

        .footer-stat .stat-number {
            font-weight: 700;
            color: #0f172a;
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 12.5px;
        }

        .footer-stat.stat-primary {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #024de3;
        }

        .footer-stat.stat-primary i {
            color: #024de3;
        }

        .footer-stat.stat-primary .stat-number {
            color: #024de3;
            background: rgba(2, 77, 227, 0.1);
            font-size: 13px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .form-wrapper.stock-in-form {
                padding-left: 25px;
                padding-right: 25px;
            }
        }

        @media (max-width: 900px) {
            .stock-in-table-container {
                overflow-x: auto;
            }
            .stock-in-table {
                min-width: 780px;
            }
        }

        @media (max-width: 768px) {
            .form-wrapper.stock-in-form {
                padding-left: 15px;
                padding-right: 15px;
            }
            .stock-in-items-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .stock-in-toolbar-right {
                justify-content: space-between;
                flex-wrap: wrap;
            }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xStockInForm', () => ({
                isCreate: @json($isCreate),
                isReadonly: @json($readonly),
                supplier: {
                    id: '',
                    text: '',
                },
                shop: {
                    id: '',
                    text: '',
                },
                product: {
                    id: '',
                    text: '',
                },
                shopId: @json(old('shop_id', $selectedShop->id ?? '')),
                products: @json($preloadedProducts ?? []),
                loadingProducts: false,
                oldItems: @json(old('items')),
                items: [],
                errors: @json($errors->toArray()),

                init() {
                    const data = @json($data ?? '');
                    this.supplier.id = data?.supplier_id ?? `{{ old('supplier_id', $selectedSupplier->id ?? '') }}`;
                    this.supplier.text = data?.supplier?.name ?? `{{ old('supplier_text', $selectedSupplier->name ?? '') }}`;

                    this.shop.id = data?.shop_id ?? `{{ old('shop_id', $selectedShop->id ?? '') }}`;
                    this.shop.text = data?.shop?.name ?? `{{ old('shop_text', $selectedShop->name ?? '') }}`;
                    this.shopId = this.shop.id;

                    this.fetchSelectSupplier();
                    this.fetchSelectShop();

                    if (this.supplier.id) {
                        $select2Data('#supplier_id', this.supplier.id, this.supplier.text);
                    }

                    if (this.shop.id) {
                        $select2Data('#shop_id', this.shop.id, this.shop.text);
                    }

                    if (this.isCreate) {
                        this.initMultiRecords();
                    } else {
                        this.fetchSelectProduct();
                        this.product.id = data?.product_id ?? `{{ old('product_id', $selectedProduct->id ?? '') }}`;
                        this.product.text = data?.product?.name ?? `{{ old('product_text', $selectedProduct->name ?? '') }}`;
                        if (this.product.id) {
                            $select2Data('#product_id', this.product.id, this.product.text);
                        }
                        if (!this.isReadonly) {
                            this.fetchCurrentStock();
                        }
                    }
                },

                fetchSelectSupplier() {
                    if ($('#supplier_id').hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $('#supplier_id').select2({
                        placeholder: `{{ __('stock_in.form.select_supplier') }}`,
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-supplier') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: (data) => {
                                return {
                                    results: $.map(data.data, (item) => {
                                        return {
                                            text: item?.name || '',
                                            id: item.id
                                        };
                                    })
                                };
                            },
                            error: (xhr, status, error) => {
                                console.error('Error fetching suppliers:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = event.params.data.id;
                        const selectedText = event.params.data.text;
                        const Obj = {
                            "id": selectedId,
                            "text": selectedText
                        };
                        this.supplier = Obj;
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });

                    $('#supplier_id').on('change', () => {
                        const val = $('#supplier_id').val();
                        if (!val) {
                            this.supplier = { id: '', text: '' };
                        }
                    });
                },

                fetchSelectShop() {
                    if ($('#shop_id').hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $('#shop_id').select2({
                        placeholder: `{{ __('stock_in.form.select_shop') }}`,
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: (data) => {
                                return {
                                    results: $.map(data.data, (item) => {
                                        return {
                                            text: item?.name || '',
                                            id: item.id
                                        };
                                    })
                                };
                            },
                            error: (xhr, status, error) => {
                                console.error('Error fetching shops:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = event.params.data.id;
                        const selectedText = event.params.data.text;
                        const Obj = {
                            "id": selectedId,
                            "text": selectedText
                        };
                        this.shop = Obj;
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });

                    $('#shop_id').on('change', () => {
                        const newShopId = $('#shop_id').val();
                        this.shopId = newShopId;
                        if (!newShopId) {
                            this.shop = { id: '', text: '' };
                        }

                        if (this.isCreate) {
                            if (newShopId) {
                                this.fetchShopProducts(newShopId);
                            } else {
                                this.items.forEach(row => {
                                    row.current_stock = 0;
                                });
                            }
                        } else {
                            if (typeof $select2Data === 'function') {
                                $select2Data('#product_id');
                            } else {
                                $('#product_id').val(null).trigger('change');
                            }
                            this.product = { id: '', text: '' };
                            $('#current_stock').val(0);
                        }
                    });
                },

                fetchSelectProduct() {
                    if ($('#product_id').hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $('#product_id').select2({
                        placeholder: `{{ __('stock_in.form.select_product') }}`,
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-shop-product') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => {
                                return {
                                    search: param.term,
                                    shop_id: $('#shop_id').val() || this.shop.id,
                                    product_id: JSON.stringify([]),
                                };
                            },
                            processResults: (data) => {
                                return {
                                    results: $.map(data.data, (item) => {
                                        return {
                                            text: item?.product?.name || '',
                                            id: item?.product?.id
                                        };
                                    })
                                };
                            },
                            error: (xhr, status, error) => {
                                console.error('Error fetching products:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = event.params.data.id;
                        const selectedText = event.params.data.text;
                        const Obj = {
                            "id": selectedId,
                            "text": selectedText
                        };
                        this.product = Obj;
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });

                    if (!this.isReadonly) {
                        $('#product_id').on('change', () => {
                            const val = $('#product_id').val();
                            if (!val) {
                                this.product = { id: '', text: '' };
                            }
                            this.fetchCurrentStock();
                        });
                    }
                },

                initMultiRecords() {
                    if (this.oldItems && Array.isArray(this.oldItems) && this.oldItems.length > 0) {
                        this.items = this.oldItems.map(item => this.normalizeOldItem(item));
                    } else {
                        this.items = [this.newRow()];
                    }

                    if (this.shopId) {
                        this.fetchShopProducts(this.shopId);
                    }
                },

                newRow() {
                    return {
                        uid: `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                        product_id: '',
                        product_name: '',
                        product_category: '',
                        product_uom: '',
                        product_image: '',
                        current_stock: 0,
                        qty: 1,
                        remark: '',
                        searchQuery: '',
                        dropdownOpen: false,
                        isDropup: false,
                    };
                },

                normalizeOldItem(item) {
                    const productId = item?.product_id ? String(item.product_id) : '';
                    const matchedProduct = this.products.find(p => String(p.id) === productId);

                    return {
                        uid: `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                        product_id: productId,
                        product_name: matchedProduct?.name ?? '',
                        product_category: matchedProduct?.category ?? '',
                        product_uom: matchedProduct?.uom ?? '',
                        product_image: matchedProduct?.image ?? '',
                        current_stock: matchedProduct?.current_stock ?? 0,
                        qty: item?.qty ? parseInt(item.qty, 10) : 1,
                        remark: item?.remark ?? '',
                        searchQuery: '',
                        dropdownOpen: false,
                        isDropup: false,
                    };
                },

                async fetchShopProducts(shopId) {
                    if (!shopId) return;
                    this.loadingProducts = true;
                    try {
                        const baseUrl = @json(url('admin/stock-in/products'));
                        const url = `${baseUrl}/${shopId}`;
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const res = await response.json();
                        this.products = res?.data ?? [];

                        // Re-sync any row that already selected a product
                        this.items.forEach(row => {
                            if (row.product_id) {
                                const found = this.products.find(p => String(p.id) === String(row.product_id));
                                if (found) {
                                    row.product_name = found.name;
                                    row.product_category = found.category;
                                    row.product_uom = found.uom;
                                    row.product_image = found.image;
                                    row.current_stock = found.current_stock;
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Failed to load shop products:', e);
                    } finally {
                        this.loadingProducts = false;
                    }
                },

                selectedProductIds(exceptUid = null) {
                    return this.items
                        .filter(row => row.uid !== exceptUid && row.product_id)
                        .map(row => String(row.product_id));
                },

                availableProducts(row) {
                    const selectedIds = this.selectedProductIds(row.uid);
                    const query = (row.searchQuery || '').toLowerCase().trim();

                    return this.products.filter(product => {
                        // Do not show products already chosen in other rows
                        if (selectedIds.includes(String(product.id))) {
                            return false;
                        }

                        if (!query) {
                            return true;
                        }

                        const name = (product.name || '').toLowerCase();
                        const cat = (product.category || '').toLowerCase();
                        const uom = (product.uom || '').toLowerCase();

                        return name.includes(query) || cat.includes(query) || uom.includes(query);
                    });
                },

                toggleDropdown(row, $event) {
                    const willOpen = !row.dropdownOpen;
                    this.items.forEach(r => {
                        if (r.uid !== row.uid) r.dropdownOpen = false;
                    });

                    if (willOpen) {
                        this.computeDropDirection(row, $event);
                        row.dropdownOpen = true;
                        row.searchQuery = '';
                        this.$nextTick(() => {
                            const input = document.querySelector(`[data-dropdown-input="${row.uid}"]`);
                            if (input) input.focus();
                        });
                    } else {
                        row.dropdownOpen = false;
                    }
                },

                openDropdown(row, $event) {
                    this.items.forEach(r => {
                        if (r.uid !== row.uid) r.dropdownOpen = false;
                    });

                    this.computeDropDirection(row, $event);
                    row.dropdownOpen = true;
                    row.searchQuery = '';
                    this.$nextTick(() => {
                        const input = document.querySelector(`[data-dropdown-input="${row.uid}"]`);
                        if (input) input.focus();
                    });
                },

                computeDropDirection(row, $event) {
                    const trigger = $event ? ($event.currentTarget || $event.target) : null;
                    if (trigger && typeof trigger.getBoundingClientRect === 'function') {
                        const rect = trigger.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom;
                        const spaceAbove = rect.top;
                        // If less than 280px below and more room above than below, drop up
                        row.isDropup = spaceBelow < 280 && spaceAbove > spaceBelow;
                    } else {
                        const idx = this.items.indexOf(row);
                        row.isDropup = this.items.length > 2 && idx >= Math.floor(this.items.length / 2);
                    }
                },

                closeDropdown(row) {
                    row.dropdownOpen = false;
                },

                selectProduct(row, product) {
                    row.product_id = String(product.id);
                    row.product_name = product.name;
                    row.product_category = product.category;
                    row.product_uom = product.uom;
                    row.product_image = product.image;
                    row.current_stock = product.current_stock ?? 0;
                    row.dropdownOpen = false;
                    row.searchQuery = '';
                },

                addRow() {
                    this.items.push(this.newRow());
                },

                removeRow(index) {
                    if (this.items.length <= 1) {
                        this.items = [this.newRow()];
                    } else {
                        this.items.splice(index, 1);
                    }
                },

                totalQty() {
                    return this.items.reduce((sum, item) => {
                        const val = parseInt(item.qty, 10);
                        return sum + (isNaN(val) || val < 0 ? 0 : val);
                    }, 0);
                },

                fieldError(index, field) {
                    const key = `items.${index}.${field}`;
                    return this.errors?.[key]?.[0] ?? '';
                },

                handleFormSubmit(e) {
                    if (!this.isCreate) {
                        return true;
                    }

                    const supplierId = $('#supplier_id').val();
                    const shopId = $('#shop_id').val();

                    if (!supplierId) {
                        alert(@json(__('stock_in.validation.supplier_required')));
                        e.preventDefault();
                        return false;
                    }

                    if (!shopId) {
                        alert(@json(__('stock_in.validation.shop_required')));
                        e.preventDefault();
                        return false;
                    }

                    const validItems = this.items.filter(item => item.product_id);
                    if (validItems.length === 0) {
                        alert(@json(__('stock_in.validation.items_required')));
                        e.preventDefault();
                        return false;
                    }

                    for (let i = 0; i < this.items.length; i++) {
                        const item = this.items[i];
                        if (!item.product_id) {
                            alert(`Row ${i + 1}: ${@json(__('stock_in.validation.product_required'))}`);
                            e.preventDefault();
                            return false;
                        }
                        if (!item.qty || parseInt(item.qty, 10) < 1) {
                            alert(`Row ${i + 1}: ${@json(__('stock_in.validation.qty_min'))}`);
                            e.preventDefault();
                            return false;
                        }
                    }

                    return true;
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
