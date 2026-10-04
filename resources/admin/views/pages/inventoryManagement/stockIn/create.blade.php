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
                        <span class="stock-in-card-icon"><i class='bx bx-building-house'></i></span>
                        <span>{{ __('stock_in.table.supplier') }} & {{ __('stock_in.table.shop') }}</span>
                    </div>

                    <div class="row-2">
                        <div class="form-row">
                            <label>{{ __('stock_in.form.supplier') }}<span>*</span></label>
                            <div class="select2Group">
                                <select name="supplier_id" class="SelectSupplier" id="supplier_id" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value=""> {{ __('stock_in.form.select_supplier') }}</option>
                                    @if (isset($selectedSupplier) && $selectedSupplier)
                                        <option value="{{ $selectedSupplier->id }}" selected>{{ $selectedSupplier->name }}</option>
                                    @endif
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="supplier?.id" @click="resetSupplier()">
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
                            <template x-if="errors?.supplier_id?.[0]">
                                <label class="error" x-text="errors.supplier_id[0]"></label>
                            </template>
                        </div>
                        <div class="form-row">
                            <label>{{ __('stock_in.form.shop') }}<span>*</span></label>
                            <div class="select2Group">
                                <select name="shop_id" class="SelectShop" id="shop_id" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value=""> {{ __('stock_in.form.select_shop') }}</option>
                                    @if (isset($selectedShop) && $selectedShop)
                                        <option value="{{ $selectedShop->id }}" selected>{{ $selectedShop->name }}</option>
                                    @endif
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="shop?.id" @click="resetShop()">
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
                            <template x-if="errors?.shop_id?.[0]">
                                <label class="error" x-text="errors.shop_id[0]"></label>
                            </template>
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
                        <template x-if="errors?.items?.[0]">
                            <div class="stock-in-alert stock-in-alert--error">
                                <i class='bx bx-error-circle'></i>
                                <span x-text="errors.items[0]"></span>
                            </div>
                        </template>

                        <div class="stock-in-items-toolbar">
                            <div class="stock-in-toolbar-left">
                                <div class="stock-in-heading-icon">
                                    <i class='bx bx-layer-plus'></i>
                                </div>
                                <div class="stock-in-heading-content">
                                    <h4>{{ __('stock_in.form.records_title') }}</h4>
                                    <span class="stock-in-section-subtitle">{{ __('stock_in.form.records_desc') }}</span>
                                </div>
                            </div>

                            <div class="stock-in-toolbar-right">
                                <div class="stock-in-stat-pill">
                                    <i class='bx bx-list-ol'></i>
                                    <span class="stat-pill-label">{{ __('stock_in.form.total_records') }}:</span>
                                    <strong class="stat-pill-value" x-text="items.length"></strong>
                                </div>
                                <div class="stock-in-stat-pill stat-pill--primary">
                                    <i class='bx bx-package'></i>
                                    <span class="stat-pill-label">{{ __('stock_in.form.total_qty') }}:</span>
                                    <strong class="stat-pill-value" x-text="totalQty()"></strong>
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
                                                <div class="row-num-wrapper">
                                                    <span class="row-num" x-text="index + 1"></span>
                                                    <span class="mobile-row-title">Item #<span x-text="index + 1"></span></span>
                                                </div>
                                            </td>

                                            <td class="td-product">
                                                <label class="mobile-field-label">
                                                    <i class='bx bx-purchase-tag-alt'></i>
                                                    <span>{{ __('stock_in.table.product') }}</span>
                                                    <span class="req">*</span>
                                                </label>
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
                                                <label class="mobile-field-label">
                                                    <i class='bx bx-archive'></i>
                                                    <span>{{ __('stock_in.form.current_stock') }}</span>
                                                </label>
                                                <div class="current-stock-badge" :class="{'is-zero': !row.current_stock || row.current_stock === 0}">
                                                    <i class='bx bx-archive'></i>
                                                    <span x-text="shopId ? (row.current_stock ?? 0) : '-'"></span>
                                                </div>
                                            </td>

                                            <td class="td-qty">
                                                <label class="mobile-field-label">
                                                    <i class='bx bx-calculator'></i>
                                                    <span>{{ __('stock_in.table.qty') }}</span>
                                                    <span class="req">*</span>
                                                </label>
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
                                                <label class="mobile-field-label">
                                                    <i class='bx bx-note'></i>
                                                    <span>{{ __('stock_in.table.remark') }}</span>
                                                </label>
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
                                <div class="stock-in-footer-hint">
                                    <i class='bx bx-info-circle'></i>
                                    <span>{{ __('stock_in.form.records_desc') }}</span>
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
                                <select name="product_id" id="product_id" class="SelectProduct" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value="">{{ __('stock_in.form.select_product') }}</option>
                                    @if (isset($selectedProduct) && $selectedProduct)
                                        <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->name }}</option>
                                    @endif
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="product?.id" @click="resetProduct()">
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
            max-width: 73rem !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding-left: 32px !important;
            padding-right: 32px !important;
            box-sizing: border-box !important;
        }

        /* Supplier & Shop Header Card */
        .stock-in-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px 16px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .stock-in-header-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .stock-in-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .stock-in-card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: #024de3;
            font-size: 17px;
            flex-shrink: 0;
            border: 1px solid #dbeafe;
        }

        .stock-in-card-title i {
            font-size: 18px;
            color: #024de3;
        }

        /* Items Section */
        .stock-in-items-section {
            margin-bottom: 24px;
        }

        /* Toolbar */
        .stock-in-items-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .stock-in-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stock-in-heading-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #024de3;
            font-size: 22px;
            flex-shrink: 0;
            border: 1px solid #bfdbfe;
            box-shadow: 0 2px 5px rgba(2, 77, 227, 0.08);
        }

        .stock-in-heading-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .stock-in-section-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin: 2px 0 0 0;
            display: block;
            line-height: 1.35;
        }

        .stock-in-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Modern Metric Stat Pills */
        .stock-in-stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 6px 12px;
            font-size: 12.5px;
            color: #475569;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            height: 38px;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }

        .stock-in-stat-pill:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .stock-in-stat-pill i {
            font-size: 16px;
            color: #64748b;
            flex-shrink: 0;
        }

        .stock-in-stat-pill .stat-pill-label {
            font-weight: 500;
            color: #64748b;
            font-size: 12px;
        }

        .stock-in-stat-pill .stat-pill-value {
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12.5px;
            min-width: 20px;
            text-align: center;
        }

        .stock-in-stat-pill.stat-pill--primary {
            background: #f8faff;
            border-color: #bfdbfe;
            color: #024de3;
        }

        .stock-in-stat-pill.stat-pill--primary:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .stock-in-stat-pill.stat-pill--primary i {
            color: #024de3;
        }

        .stock-in-stat-pill.stat-pill--primary .stat-pill-label {
            color: #024de3;
            font-weight: 600;
        }

        .stock-in-stat-pill.stat-pill--primary .stat-pill-value {
            color: #024de3;
            background: rgba(2, 77, 227, 0.1);
        }

        /* Primary Add Button */
        .btn-stock-add {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            background: linear-gradient(180deg, #0d5df5 0%, #024de3 100%) !important;
            color: #ffffff !important;
            border: 1px solid #0245cb !important;
            border-radius: 8px !important;
            padding: 0 16px !important;
            height: 38px !important;
            min-height: 38px !important;
            line-height: 38px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(2, 77, 227, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
            white-space: nowrap !important;
        }

        .btn-stock-add:hover {
            background: linear-gradient(180deg, #024de3 0%, #003dbd 100%) !important;
            box-shadow: 0 4px 12px rgba(2, 77, 227, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.22) !important;
            transform: translateY(-1px) !important;
        }

        .btn-stock-add:active {
            transform: translateY(0) !important;
            box-shadow: 0 1px 3px rgba(2, 77, 227, 0.2) !important;
        }

        .btn-stock-add i {
            font-size: 16px !important;
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
            padding: 12px 16px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 14px;
            font-weight: 500;
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
            background: #f8fafc;
            color: #64748b;
            font-size: 13.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .stock-in-loading-shimmer i {
            font-size: 20px;
            color: #024de3;
        }

        /* Table Container & Table */
        .stock-in-table-container {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
            overflow: visible !important;
            position: relative;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .stock-in-table {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 13px;
            box-sizing: border-box !important;
        }

        .stock-in-table thead tr {
            background: #f8fafc;
        }

        .stock-in-table th {
            padding: 10px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }

        .stock-in-table th:first-child {
            border-top-left-radius: 11px;
        }

        .stock-in-table th:last-child {
            border-top-right-radius: 11px;
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
            margin-left: 2px;
        }

        .stock-in-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            background: #ffffff;
            box-sizing: border-box !important;
        }

        .stock-in-row {
            position: relative;
            transition: background 0.15s ease;
        }

        .stock-in-row:hover td {
            background: #f8faff;
        }

        .stock-in-row.dropdown-open {
            position: relative;
            z-index: 100 !important;
        }

        .stock-in-row.dropdown-open td {
            background: #f8faff;
        }

        /* Fixed Column Width Distribution (Strictly fits 100% container) */
        .th-num, .td-num {
            width: 44px !important;
            min-width: 44px !important;
            max-width: 44px !important;
            text-align: center !important;
            padding: 8px 4px !important;
        }

        .row-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            font-size: 11px;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }

        .stock-in-row:hover .row-num {
            background: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }

        .th-product, .td-product {
            width: 33% !important;
            min-width: 160px !important;
            position: relative;
            padding: 8px 8px !important;
        }

        .th-stock, .td-stock {
            width: 100px !important;
            min-width: 100px !important;
            max-width: 100px !important;
            white-space: nowrap !important;
            text-align: center !important;
            padding: 8px 4px !important;
        }

        .th-qty, .td-qty {
            width: 114px !important;
            min-width: 114px !important;
            max-width: 114px !important;
            text-align: center !important;
            padding: 8px 4px !important;
        }

        .th-remark, .td-remark {
            width: auto !important;
            min-width: 110px !important;
            padding: 8px 8px !important;
        }

        .th-action, .td-action {
            width: 68px !important;
            min-width: 68px !important;
            max-width: 68px !important;
            text-align: center !important;
            white-space: nowrap !important;
            padding: 8px 4px !important;
        }

        /* Selected Product Card */
        .selected-product-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .selected-product-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.05);
        }

        .selected-product-img {
            width: 38px;
            height: 38px;
            border-radius: 7px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
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
            color: #0f172a;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .selected-product-meta {
            display: flex;
            gap: 6px;
            margin-top: 3px;
            flex-wrap: wrap;
        }

        .meta-tag {
            font-size: 10.5px;
            font-weight: 600;
            padding: 1.5px 7px;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.35;
            letter-spacing: 0.01em;
        }

        .meta-category {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
        }

        .meta-uom {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ede9fe;
        }

        /* Change Product Button */
        .btn-change-product {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
            height: 28px !important;
            min-height: 28px !important;
            padding: 0 10px !important;
            line-height: 28px !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 6px !important;
            color: #024de3 !important;
            font-size: 11.5px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            margin: 0 0 0 auto !important;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .btn-change-product:hover {
            color: #ffffff !important;
            border-color: #024de3 !important;
            background: #024de3 !important;
            box-shadow: 0 2px 7px rgba(2, 77, 227, 0.25) !important;
            transform: translateY(-1px) !important;
        }

        .btn-change-product:hover span {
            color: #ffffff !important;
        }

        .btn-change-product i {
            font-size: 14px !important;
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
            font-size: 11.5px !important;
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
            padding: 0 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            cursor: pointer;
            transition: all 0.15s ease;
            height: 38px;
            box-sizing: border-box;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .product-dropdown-trigger:hover {
            border-color: #024de3;
            background: #fafbff;
        }

        .product-dropdown-trigger.is-active {
            border-color: #024de3;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
        }

        .product-dropdown-trigger span {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13px;
            font-weight: 500;
        }

        .product-dropdown-trigger span.placeholder-text {
            color: #94a3b8;
            font-weight: 400;
        }

        .product-dropdown-trigger i.bx-search {
            font-size: 16px;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .product-dropdown-trigger i.bx-chevron-down {
            font-size: 18px;
            color: #94a3b8;
            transition: transform 0.2s ease, color 0.15s ease;
            flex-shrink: 0;
        }

        .product-dropdown-trigger.is-active i.bx-chevron-down,
        .product-dropdown-trigger:hover i.bx-chevron-down {
            color: #024de3;
        }

        .product-dropdown-trigger i.bx-chevron-down.is-rotated {
            transform: rotate(180deg);
        }

        .product-dropdown-menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            min-width: 360px;
            max-width: 500px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.14), 0 4px 12px rgba(15, 23, 42, 0.06);
            z-index: 1050;
            overflow: hidden;
            animation: dropdownSlideDown 0.15s ease-out;
        }

        .product-dropdown-menu.dropup-menu {
            top: auto;
            bottom: calc(100% + 6px);
            box-shadow: 0 -14px 34px rgba(15, 23, 42, 0.14), 0 -4px 12px rgba(15, 23, 42, 0.06);
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
            padding: 4px 0 !important;
            font-size: 13px !important;
            font-family: inherit !important;
            width: 100% !important;
            color: #0f172a !important;
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
            padding: 2px !important;
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
            max-height: 220px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

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
            border-radius: 6px;
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
            color: #0f172a;
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

        /* Current Stock Badge (Clean Read-Only Indicator) */
        .current-stock-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 0 8px;
            height: 32px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            min-width: 58px;
            max-width: 86px;
            margin: 0 auto;
            letter-spacing: 0.01em;
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.05);
            user-select: none;
            box-sizing: border-box;
        }

        .current-stock-badge i {
            font-size: 13px;
            color: #059669;
        }

        .current-stock-badge.is-zero {
            background: #f8fafc;
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
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
            overflow: hidden;
            height: 34px;
            width: 104px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .qty-control-wrapper:hover {
            border-color: #cbd5e1;
        }

        .qty-control-wrapper:focus-within {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
        }

        .btn-qty-step {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 29px !important;
            min-width: 29px !important;
            max-width: 29px !important;
            height: 34px !important;
            min-height: 34px !important;
            padding: 0 !important;
            line-height: 1 !important;
            background: #f8fafc !important;
            border: none !important;
            color: #64748b !important;
            cursor: pointer !important;
            transition: all 0.12s ease !important;
            outline: none !important;
            user-select: none !important;
        }

        .btn-qty-step:first-child {
            border-right: 1px solid #e2e8f0 !important;
        }

        .btn-qty-step:last-child {
            border-left: 1px solid #e2e8f0 !important;
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
            font-size: 14px !important;
            color: inherit !important;
            line-height: 1 !important;
            pointer-events: none !important;
        }

        .input-qty {
            width: 44px !important;
            min-width: 32px !important;
            height: 34px !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            font-size: 13.5px !important;
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
            min-width: 0 !important;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
            height: 34px;
            box-sizing: border-box;
        }

        .remark-input-wrapper:hover {
            border-color: #cbd5e1;
        }

        .remark-input-wrapper:focus-within {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
            background: #ffffff;
        }

        .remark-input-wrapper i {
            position: absolute;
            left: 10px;
            font-size: 14px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.15s ease;
            line-height: 1;
        }

        .remark-input-wrapper:focus-within i {
            color: #024de3;
        }

        .input-remark {
            width: 100% !important;
            min-width: 0 !important;
            height: 100% !important;
            padding: 0 10px 0 28px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 12.5px !important;
            color: #0f172a !important;
            box-sizing: border-box !important;
            background: transparent !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .input-remark::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
            font-size: 12px !important;
        }

        /* Remove Row Button */
        .btn-row-remove {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            max-width: 32px !important;
            min-height: 32px !important;
            padding: 0 !important;
            line-height: 32px !important;
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
            margin: 0 auto !important;
        }

        .btn-row-remove:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
            transform: translateY(-1px) scale(1.05) !important;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25) !important;
        }

        .btn-row-remove i {
            font-size: 16px !important;
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
            border-radius: 0 0 11px 11px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-stock-add-bottom {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            background: #ffffff !important;
            border: 1.5px dashed #93c5fd !important;
            color: #024de3 !important;
            border-radius: 8px !important;
            padding: 0 18px !important;
            height: 36px !important;
            min-height: 36px !important;
            min-width: auto !important;
            line-height: 36px !important;
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
            box-shadow: 0 4px 12px rgba(2, 77, 227, 0.25) !important;
        }

        .btn-stock-add-bottom i {
            font-size: 17px !important;
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

        .stock-in-footer-hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #64748b;
        }

        .stock-in-footer-hint i {
            font-size: 16px;
            color: #94a3b8;
        }

        /* Mobile Field Labels & Row Title (Hidden on Desktop) */
        .mobile-field-label {
            display: none;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .mobile-field-label i {
            font-size: 14px;
            color: #64748b;
        }

        .mobile-field-label .req {
            color: #ef4444;
            font-weight: 700;
            margin-left: 1px;
        }

        .row-num-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .mobile-row-title {
            display: none;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .form-wrapper.stock-in-form {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }

        /* ==========================================================
           Responsive Tablet & Mobile Transformations (<= 768px)
           ========================================================== */
        @media (max-width: 768px) {
            .mobile-field-label {
                display: flex !important;
            }

            .mobile-row-title {
                display: inline-block !important;
            }

            .form-wrapper.stock-in-form {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            /* Toolbar Stacking */
            .stock-in-items-toolbar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
            }

            .stock-in-toolbar-left {
                width: 100% !important;
            }

            .stock-in-toolbar-right {
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
            }

            .stock-in-stat-pill {
                flex: 1 1 auto !important;
                justify-content: center !important;
                height: 36px !important;
                padding: 4px 10px !important;
                font-size: 12px !important;
            }

            .btn-stock-add {
                flex-shrink: 0 !important;
                height: 36px !important;
                line-height: 36px !important;
                padding: 0 14px !important;
                font-size: 12.5px !important;
            }

            /* Container and Table as Modern Card Stack */
            .stock-in-table-container {
                border: none !important;
                background: transparent !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .stock-in-table {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }

            .stock-in-table thead {
                display: none !important;
            }

            .stock-in-table tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 16px !important;
                width: 100% !important;
            }

            /* Each table row transforms into a clean, modern card */
            .stock-in-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                grid-template-areas:
                    "num action"
                    "product product"
                    "stock qty"
                    "remark remark" !important;
                gap: 12px 14px !important;
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px !important;
                padding: 16px !important;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02) !important;
                position: relative !important;
                box-sizing: border-box !important;
                transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
            }

            .stock-in-row:hover {
                border-color: #cbd5e1 !important;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06) !important;
            }

            .stock-in-row.dropdown-open {
                z-index: 1050 !important;
                border-color: #93c5fd !important;
                box-shadow: 0 8px 24px rgba(2, 77, 227, 0.12) !important;
            }

            .stock-in-row td {
                display: block !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
                width: auto !important;
                min-width: 0 !important;
                max-width: none !important;
            }

            .td-num {
                grid-area: num !important;
                display: flex !important;
                align-items: center !important;
                text-align: left !important;
            }

            .td-action {
                grid-area: action !important;
                display: flex !important;
                align-items: center !important;
                justify-content: flex-end !important;
                text-align: right !important;
            }

            .btn-row-remove {
                margin: 0 0 0 auto !important;
            }

            .td-product {
                grid-area: product !important;
                width: 100% !important;
            }

            .td-stock {
                grid-area: stock !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                text-align: left !important;
            }

            .td-stock .current-stock-badge {
                width: 100% !important;
                max-width: 140px !important;
                box-sizing: border-box !important;
                height: 38px !important;
                justify-content: flex-start !important;
                padding: 0 12px !important;
            }

            .td-qty {
                grid-area: qty !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .td-qty .qty-control-wrapper {
                margin: 0 !important;
                width: 100% !important;
                max-width: 130px !important;
            }

            .td-remark {
                grid-area: remark !important;
                width: 100% !important;
            }

            /* Product Dropdown on Mobile: Full width without clipping */
            .product-dropdown-menu {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                box-shadow: 0 14px 34px rgba(15, 23, 42, 0.18), 0 4px 12px rgba(15, 23, 42, 0.08) !important;
            }

            /* Table Footer on Mobile */
            .stock-in-table-footer {
                background: #ffffff !important;
                border: 1.5px dashed #bfdbfe !important;
                border-radius: 12px !important;
                padding: 16px !important;
                margin-top: 16px !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
            }

            .btn-stock-add-bottom {
                width: 100% !important;
                height: 40px !important;
                line-height: 40px !important;
                font-size: 13.5px !important;
                justify-content: center !important;
            }

            .stock-in-footer-hint {
                font-size: 12px !important;
                color: #64748b !important;
                text-align: center !important;
            }
        }

        /* Responsive adjustments for phones (<= 480px) */
        @media (max-width: 480px) {
            .form-wrapper.stock-in-form {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .stock-in-header-card {
                padding: 14px !important;
                border-radius: 10px !important;
                margin-bottom: 16px !important;
            }

            .stock-in-toolbar-right {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
            }

            .stock-in-stat-pill {
                width: 100% !important;
                padding: 4px 8px !important;
                font-size: 11.5px !important;
            }

            .btn-stock-add {
                grid-column: 1 / -1 !important;
                width: 100% !important;
                height: 38px !important;
                line-height: 38px !important;
            }

            .stock-in-row {
                padding: 14px 12px !important;
                gap: 12px !important;
            }
        }

        /* Error styles */
        .stock-in-table td label.error {
            display: block;
            color: #ef4444 !important;
            font-size: 11.5px;
            margin-top: 4px;
            line-height: 1.2;
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

                getScopeEl(selector) {
                    if (this.$el && $(this.$el).is(selector)) {
                        return $(this.$el);
                    }
                    if (this.$root && $(this.$root).find(selector).length) {
                        return $(this.$root).find(selector);
                    }
                    const $active = $('.workspace-tab-pane.active ' + selector);
                    if ($active.length) {
                        return $active;
                    }
                    return $(selector);
                },

                setSelect2Value(selector, value, text) {
                    if (!value) return;
                    const $select = this.getScopeEl(selector);
                    if (!$select.length || !$select.is('select')) return;
                    $select.find('option').filter((index, option) => String(option.value) === String(value)).remove();
                    $select.append(new Option(text || value, value, true, true)).trigger('change');
                },

                resetShop() {
                    const $shop = this.getScopeEl('#shop_id');
                    $shop.val(null).trigger('change');
                    this.shop = { id: '', text: '' };
                    this.shopId = '';
                    this.products = [];
                    this.items.forEach(row => {
                        row.product_id = '';
                        row.product_name = '';
                        row.product_category = '';
                        row.product_uom = '';
                        row.product_image = '';
                        row.current_stock = 0;
                    });
                },

                resetSupplier() {
                    const $supplier = this.getScopeEl('#supplier_id');
                    $supplier.val(null).trigger('change');
                    this.supplier = { id: '', text: '' };
                },

                resetProduct() {
                    const $prod = this.getScopeEl('#product_id');
                    $prod.empty().append(new Option(@json(__('stock_in.form.select_product')), '', true, true)).trigger('change');
                    this.product = { id: '', text: '' };
                    this.getScopeEl('#current_stock').val(0);
                },

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
                        this.setSelect2Value('#supplier_id', this.supplier.id, this.supplier.text);
                    }

                    if (this.shop.id) {
                        this.setSelect2Value('#shop_id', this.shop.id, this.shop.text);
                    }

                    if (this.isCreate) {
                        this.initMultiRecords();
                    } else {
                        this.fetchSelectProduct();
                        this.product.id = data?.product_id ?? `{{ old('product_id', $selectedProduct->id ?? '') }}`;
                        this.product.text = data?.product?.name ?? `{{ old('product_text', $selectedProduct->name ?? '') }}`;
                        if (this.product.id) {
                            this.setSelect2Value('#product_id', this.product.id, this.product.text);
                        }
                        if (!this.shop.id && !this.getScopeEl('#shop_id').val()) {
                            this.getScopeEl('#product_id').prop('disabled', true);
                        }
                        if (!this.isReadonly) {
                            this.fetchCurrentStock();
                        }
                    }
                },

                fetchSelectSupplier() {
                    const $supplier = this.getScopeEl('#supplier_id');
                    if (!$supplier.length) return;
                    if ($supplier.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $supplier.select2({
                        placeholder: `{{ __('stock_in.form.select_supplier') }}`,
                        width: '100%',
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-supplier', [], false) }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term
                            }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name || item?.phone || '',
                                    id: item.id
                                }))
                            }),
                            error: (xhr, status, error) => {
                                console.error('Error fetching suppliers:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = String(event.params.data.id);
                        const selectedText = event.params.data.text;
                        this.supplier = {
                            id: selectedId,
                            text: selectedText
                        };
                        if (this.errors?.supplier_id) {
                            delete this.errors.supplier_id;
                            this.errors = { ...this.errors };
                        }
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });

                    $supplier.on('change', () => {
                        const val = $supplier.val();
                        if (!val) {
                            this.supplier = { id: '', text: '' };
                        } else if (this.errors?.supplier_id) {
                            delete this.errors.supplier_id;
                            this.errors = { ...this.errors };
                        }
                    });
                },

                fetchSelectShop() {
                    const $shop = this.getScopeEl('#shop_id');
                    if (!$shop.length) return;
                    if ($shop.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $shop.select2({
                        placeholder: `{{ __('stock_in.form.select_shop') }}`,
                        width: '100%',
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-stock-shop', [], false) }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term
                            }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name || item?.phone || '',
                                    id: item.id
                                }))
                            }),
                            error: (xhr, status, error) => {
                                console.error('Error fetching shops:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = String(event.params.data.id);
                        const selectedText = event.params.data.text;
                        this.shop = {
                            id: selectedId,
                            text: selectedText
                        };
                        this.shopId = selectedId;
                        if (this.errors?.shop_id) {
                            delete this.errors.shop_id;
                            this.errors = { ...this.errors };
                        }
                        if (this.isCreate) {
                            this.fetchShopProducts(selectedId);
                        }
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });

                    $shop.on('change', () => {
                        const newShopId = $shop.val() ? String($shop.val()) : '';
                        this.shopId = newShopId;
                        if (!newShopId) {
                            this.shop = { id: '', text: '' };
                        } else if (this.errors?.shop_id) {
                            delete this.errors.shop_id;
                            this.errors = { ...this.errors };
                        }

                        if (this.isCreate) {
                            if (newShopId) {
                                this.fetchShopProducts(newShopId);
                            } else {
                                this.products = [];
                                this.items.forEach(row => {
                                    row.product_id = '';
                                    row.product_name = '';
                                    row.product_category = '';
                                    row.product_uom = '';
                                    row.product_image = '';
                                    row.current_stock = 0;
                                });
                            }
                        } else {
                            const $prod = this.getScopeEl('#product_id');
                            $prod.empty().append(new Option(@json(__('stock_in.form.select_product')), '', true, true)).trigger('change');
                            this.product = { id: '', text: '' };
                            this.getScopeEl('#current_stock').val(0);
                            if (!newShopId) {
                                $prod.prop('disabled', true);
                            } else if (!this.isReadonly) {
                                $prod.prop('disabled', false);
                            }
                        }
                    });
                },

                fetchSelectProduct() {
                    const $prod = this.getScopeEl('#product_id');
                    if (!$prod.length) return;
                    if ($prod.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $prod.select2({
                        placeholder: `{{ __('stock_in.form.select_product') }}`,
                        width: '100%',
                        ajax: this.isReadonly ? null : {
                            url: '{{ route('admin-select-shop-product', [], false) }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term,
                                shop_id: this.getScopeEl('#shop_id').val() || this.shop.id,
                                product_id: JSON.stringify([]),
                            }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.product?.name || '',
                                    id: item?.product?.id
                                }))
                            }),
                            error: (xhr, status, error) => {
                                console.error('Error fetching products:', error);
                            }
                        }
                    }).on('select2:select', (event) => {
                        const selectedId = event.params.data.id;
                        const selectedText = event.params.data.text;
                        this.product = {
                            id: selectedId,
                            text: selectedText
                        };
                    }).on('select2:open', (e) => {
                        const currentShopId = this.getScopeEl('#shop_id').val() || this.shop.id;
                        if (!currentShopId) {
                            $prod.select2('close');
                            return;
                        }
                        $select2FocusInputSearch();
                    });

                    if (!this.isReadonly) {
                        $prod.on('change', () => {
                            const val = $prod.val();
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
                        const url = `/admin/stock-in/products/${shopId}`;
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
                                } else {
                                    row.product_id = '';
                                    row.product_name = '';
                                    row.product_category = '';
                                    row.product_uom = '';
                                    row.product_image = '';
                                    row.current_stock = 0;
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
                    const idx = this.items.indexOf(row);
                    if (idx !== -1 && this.errors && this.errors[`items.${idx}.product_id`]) {
                        delete this.errors[`items.${idx}.product_id`];
                        this.errors = { ...this.errors };
                    }
                    if (this.errors && this.errors.items) {
                        delete this.errors.items;
                        this.errors = { ...this.errors };
                    }
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

                    const supplierId = this.getScopeEl('#supplier_id').val() || this.supplier.id;
                    const shopId = this.getScopeEl('#shop_id').val() || this.shopId || this.shop.id;
                    const newErrors = {};
                    let isValid = true;
                    let firstErrorTarget = null;

                    if (!supplierId) {
                        newErrors['supplier_id'] = [@json(__('stock_in.validation.supplier_required'))];
                        isValid = false;
                        if (!firstErrorTarget) {
                            firstErrorTarget = this.getScopeEl('#supplier_id')[0] || this.getScopeEl('.SelectSupplier')[0];
                        }
                    }

                    if (!shopId) {
                        newErrors['shop_id'] = [@json(__('stock_in.validation.shop_required'))];
                        isValid = false;
                        if (!firstErrorTarget) {
                            firstErrorTarget = this.getScopeEl('#shop_id')[0] || this.getScopeEl('.SelectShop')[0];
                        }
                    }

                    const validItems = this.items.filter(item => item.product_id);
                    if (validItems.length === 0) {
                        newErrors['items'] = [@json(__('stock_in.validation.items_required'))];
                        isValid = false;
                        if (!firstErrorTarget) {
                            firstErrorTarget = document.querySelector('.stock-in-items-section');
                        }
                    }

                    for (let i = 0; i < this.items.length; i++) {
                        const item = this.items[i];
                        if (!item.product_id) {
                            newErrors[`items.${i}.product_id`] = [@json(__('stock_in.validation.product_required'))];
                            isValid = false;
                            if (!firstErrorTarget) {
                                firstErrorTarget = document.querySelector(`input[name="items[${i}][qty]"]`);
                            }
                        }
                        if (!item.qty || parseInt(item.qty, 10) < 1) {
                            newErrors[`items.${i}.qty`] = [@json(__('stock_in.validation.qty_min'))];
                            isValid = false;
                            if (!firstErrorTarget) {
                                firstErrorTarget = document.querySelector(`input[name="items[${i}][qty]"]`);
                            }
                        }
                    }

                    this.errors = newErrors;

                    if (!isValid) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof e.stopImmediatePropagation === 'function') {
                            e.stopImmediatePropagation();
                        }
                        if (firstErrorTarget) {
                            firstErrorTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return false;
                    }

                    return true;
                },

                async fetchCurrentStock() {
                    const productId = this.getScopeEl('#product_id').val() || this.product.id;
                    const shopId = this.getScopeEl('#shop_id').val() || this.shop.id;
                    const $currentStock = this.getScopeEl('#current_stock');
                    if (!productId || !shopId) {
                        $currentStock.val(0);
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
                        .then((response) => $currentStock.val(response?.current_stock ?? 0))
                        .catch(() => $currentStock.val(0));
                },
            }));
        });
    </script>
@stop
