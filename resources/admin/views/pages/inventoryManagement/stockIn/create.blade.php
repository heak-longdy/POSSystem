@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $purchase = $purchase ?? null;
    $readonly = $readonly ?? false;
    $isEdit = !empty($id);
    $formTitle = $readonly ? __('stock_in.form.title.view') : ($isEdit ? __('stock_in.form.title.update') : __('stock_in.form.title.create'));
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xPurchaseForm">
        <div class="form-bg"></div>
        <form id="purchaseForm" class="form-wrapper stock-in-form" action="{!! route('admin-' . $routeName . '-save', $id) !!}" method="POST" enctype="multipart/form-data" @submit="handleFormSubmit($event)">
            {{ csrf_field() }}

            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 'all') !!}"></i>
                    {{ $formTitle }}
                    @if ($purchase)
                        <span class="text-sm font-normal text-slate-500">({{ $purchase->purchase_number }})</span>
                    @endif
                </h3>
            </div>

            <div class="form-body">
                {{-- 1. Procurement Header Card --}}
                <div class="stock-in-header-card">
                    <div class="stock-in-card-title">
                        <span class="stock-in-card-icon"><i class='bx bx-building-house'></i></span>
                        <span>{{ __('stock_in.table.supplier') }} & {{ __('stock_in.table.shop') }}</span>
                    </div>

                    <div class="row-2">
                        {{-- Supplier (Select2 Standard) --}}
                        <div class="form-row">
                            <label>{{ __('stock_in.form.supplier') }}<span class="req">*</span></label>
                            <div class="select2Group">
                                <select name="supplier_id" class="SelectSupplier" id="supplier_id" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value="">{{ __('stock_in.form.select_supplier') }}</option>
                                    @if (isset($selectedSupplier) && $selectedSupplier)
                                        <option value="{{ $selectedSupplier->id }}" selected>{{ $selectedSupplier->name }}</option>
                                    @endif
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="supplier?.id" @click="resetSupplier()" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <template x-if="errors?.supplier_id?.[0]">
                                <label class="error" x-text="errors.supplier_id[0]"></label>
                            </template>
                            @error('supplier_id')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>

                        {{-- Shop / Destination (Select2 Standard) --}}
                        <div class="form-row">
                            <label>{{ __('stock_in.form.shop') }}<span class="req">*</span></label>
                            <div class="select2Group">
                                <select name="shop_id" class="SelectShop" id="shop_id" {!! $readonly ? 'disabled' : '' !!}>
                                    <option value="">{{ __('stock_in.form.select_shop') }}</option>
                                    @if (isset($selectedShop) && $selectedShop)
                                        <option value="{{ $selectedShop->id }}" selected>{{ $selectedShop->name }}</option>
                                    @endif
                                </select>
                                @if (!$readonly)
                                    <div class="select2Reset" x-show="shopId" @click="resetShop()" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <template x-if="errors?.shop_id?.[0]">
                                <label class="error" x-text="errors.shop_id[0]"></label>
                            </template>
                            @error('shop_id')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>

                    {{-- Dates & Reference Row --}}
                    <div class="row-3">
                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.order_date') }}<span class="req">*</span></label>
                            <input type="text" name="order_date" id="order_date" class="datepicker-input"
                                value="{{ old('order_date', $purchase?->order_date ? \Carbon\Carbon::parse($purchase->order_date)->format('Y-m-d') : date('Y-m-d')) }}"
                                placeholder="YYYY-MM-DD" autocomplete="off" {!! $readonly ? 'readonly' : '' !!}>
                            <i class='bx bx-calendar'></i>
                            @error('order_date')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>

                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.expected_delivery_date') }}</label>
                            <input type="text" name="expected_delivery_date" id="expected_delivery_date" class="datepicker-input"
                                value="{{ old('expected_delivery_date', $purchase?->expected_delivery_date ? \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('Y-m-d') : '') }}"
                                placeholder="YYYY-MM-DD" autocomplete="off" {!! $readonly ? 'readonly' : '' !!}>
                            <i class='bx bx-calendar-event'></i>
                        </div>

                        <div class="form-row iconInput">
                            <label>{{ __('stock_in.form.reference_no') }}</label>
                            <input type="text" name="reference_no" value="{{ old('reference_no', $purchase?->reference_no ?? '') }}"
                                placeholder="{{ __('stock_in.form.reference_placeholder') }}" {!! $readonly ? 'readonly' : '' !!}>
                            <i class='bx bx-receipt'></i>
                        </div>
                    </div>

                    {{-- Fulfillment Mode Selector (Creation Only) --}}
                    @if (!$isEdit && !$readonly)
                        <div class="fulfillment-mode-card">
                            <div class="fulfillment-option" :class="{ 'is-selected': fulfillmentType === 'standard' }" @click="fulfillmentType = 'standard'; immediateStockIn = false">
                                <div class="option-radio">
                                    <input type="radio" name="fulfillment_type" value="standard" x-model="fulfillmentType">
                                </div>
                                <div class="option-info">
                                    <strong>{{ __('stock_in.form.mode_standard') }}</strong>
                                    <span>Create PO as ordered. Goods are received and verified upon warehouse delivery.</span>
                                </div>
                            </div>

                            <div class="fulfillment-option" :class="{ 'is-selected': fulfillmentType === 'direct' }" @click="fulfillmentType = 'direct'; immediateStockIn = true">
                                <div class="option-radio">
                                    <input type="radio" name="fulfillment_type" value="direct" x-model="fulfillmentType">
                                </div>
                                <div class="option-info">
                                    <strong>{{ __('stock_in.form.mode_direct') }}</strong>
                                    <span>Stock arrives on the spot. Immediately increases warehouse stock upon saving.</span>
                                </div>
                            </div>
                            <input type="hidden" name="immediate_stock_in" :value="immediateStockIn ? '1' : '0'">
                        </div>
                    @endif
                </div>

                {{-- 2. Line Items Section --}}
                <div class="stock-in-items-section">
                    <template x-if="errors?.items?.[0]">
                        <div class="stock-in-alert stock-in-alert--error">
                            <i class='bx bx-error-circle'></i>
                            <span x-text="errors.items[0]"></span>
                        </div>
                    </template>

                    <div class="stock-in-items-toolbar">
                        <div class="stock-in-toolbar-left">
                            <div class="stock-in-heading-icon">
                                <i class='bx bx-package'></i>
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
                                <i class='bx bx-layer'></i>
                                <span class="stat-pill-label">{{ __('stock_in.form.total_qty') }}:</span>
                                <strong class="stat-pill-value" x-text="totalQty()"></strong>
                            </div>
                            @if (!$readonly)
                                <button type="button" class="btn-stock-add" @click="addRow()">
                                    <i class='bx bx-plus'></i>
                                    <span>{{ __('stock_in.button.add_row') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Dynamic Product Items Table --}}
                    <div class="stock-in-table-container">
                        <table class="stock-in-table">
                            <thead>
                                <tr>
                                    <th class="th-num">#</th>
                                    <th class="th-product">{{ __('stock_in.table.product') }} <span class="req">*</span></th>
                                    <th class="th-stock">{{ __('stock_in.form.current_stock') }}</th>
                                    <th class="th-cost">{{ __('stock_in.form.unit_cost') }}</th>
                                    <th class="th-qty">{{ __('stock_in.table.qty') }} <span class="req">*</span></th>
                                    <th class="th-subtotal">{{ __('stock_in.table.subtotal') }}</th>
                                    <th class="th-remark">{{ __('stock_in.table.remark') }}</th>
                                    @if (!$readonly)
                                        <th class="th-action">{{ __('stock_in.form.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in items" :key="row.uid">
                                    <tr class="stock-in-row" :class="{'dropdown-open': row.dropdownOpen}">
                                        <td class="td-num">
                                            <input type="hidden" :name="`items[${index}][id]`" :value="row.id || ''">
                                            <span class="row-num" x-text="index + 1"></span>
                                        </td>

                                        {{-- Product Selector --}}
                                        <td class="td-product">
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id">

                                            {{-- Selected Product Card --}}
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
                                                @if (!$readonly)
                                                    <button type="button" class="btn-change-product" @click="openDropdown(row, $event)">
                                                        <i class='bx bx-sync'></i>
                                                        <span>Change</span>
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- Product Search Dropdown --}}
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
                                                                <div class="product-option-stock">
                                                                    <span class="stock-pill" :class="{'stock-zero': !product.current_stock || product.current_stock <= 0}">
                                                                        <i class='bx bx-archive'></i>
                                                                        <span x-text="`Stock: ${product.current_stock ?? 0}`"></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <div class="product-option-empty" x-show="availableProducts(row).length === 0">
                                                            <i class='bx bx-search'></i>
                                                            <span>{{ __('stock_in.form.no_products_found') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <template x-if="fieldError(index, 'product_id')">
                                                <label class="error" x-text="fieldError(index, 'product_id')"></label>
                                            </template>
                                        </td>

                                        {{-- Current Stock --}}
                                        <td class="td-stock">
                                            <div class="current-stock-badge" :class="{'is-zero': !row.current_stock || row.current_stock === 0}">
                                                <i class='bx bx-archive'></i>
                                                <span x-text="shopId ? (row.current_stock ?? 0) : '-'"></span>
                                            </div>
                                        </td>

                                        {{-- Unit Cost --}}
                                        <td class="td-cost">
                                            <div class="currency-input-wrapper">
                                                <span class="currency-symbol">$</span>
                                                <input type="number" step="0.01" min="0"
                                                    :name="`items[${index}][unit_cost]`"
                                                    x-model.number="row.unit_cost"
                                                    class="input-cost"
                                                    placeholder="0.00"
                                                    {!! $readonly ? 'readonly' : '' !!}>
                                            </div>
                                        </td>

                                        {{-- Qty Ordered --}}
                                        <td class="td-qty">
                                            <div class="qty-control-wrapper">
                                                @if (!$readonly)
                                                    <button type="button" class="btn-qty-step" @click="row.qty = Math.max(1, (parseFloat(row.qty) || 1) - 1)" title="Decrease">
                                                        <i class='bx bx-minus'></i>
                                                    </button>
                                                @endif
                                                <input type="number" step="any" min="0.01"
                                                    :name="`items[${index}][qty]`"
                                                    x-model.number="row.qty"
                                                    class="input-qty"
                                                    placeholder="1"
                                                    {!! $readonly ? 'readonly' : '' !!}>
                                                @if (!$readonly)
                                                    <button type="button" class="btn-qty-step" @click="row.qty = (parseFloat(row.qty) || 0) + 1" title="Increase">
                                                        <i class='bx bx-plus'></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <template x-if="fieldError(index, 'qty')">
                                                <label class="error" x-text="fieldError(index, 'qty')"></label>
                                            </template>
                                        </td>

                                        {{-- Line Subtotal --}}
                                        <td class="td-subtotal">
                                            <strong class="line-subtotal-val" x-text="formatCurrency((row.qty || 0) * (row.unit_cost || 0))"></strong>
                                        </td>

                                        {{-- Remark --}}
                                        <td class="td-remark">
                                            <div class="remark-input-wrapper">
                                                <i class='bx bx-note'></i>
                                                <input type="text"
                                                    :name="`items[${index}][remark]`"
                                                    x-model="row.remark"
                                                    class="input-remark"
                                                    placeholder="{{ __('stock_in.form.item_remark_placeholder') }}"
                                                    {!! $readonly ? 'readonly' : '' !!}>
                                            </div>
                                        </td>

                                        {{-- Action --}}
                                        @if (!$readonly)
                                            <td class="td-action">
                                                <button type="button" class="btn-row-remove" @click="removeRow(index)" title="{{ __('stock_in.button.remove_row') }}">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        @if (!$readonly)
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
                        @endif
                    </div>
                </div>

                {{-- 3. Financial Summary & Notes --}}
                <div class="financial-summary-grid">
                    <div class="financial-notes-col">
                        <div class="form-row">
                            <label>{{ __('stock_in.form.general_remark') }}</label>
                            <textarea name="remark" rows="4" placeholder="{{ __('stock_in.form.placeholder_remark') }}" {!! $readonly ? 'readonly' : '' !!}>{{ old('remark', $purchase?->remark ?? '') }}</textarea>
                        </div>

                        {{-- Optional Upfront Payment Card (Creation Only) --}}
                        @if (!$isEdit && !$readonly)
                            <div class="upfront-payment-card" x-show="fulfillmentType === 'direct' || showInitialPayment">
                                <div class="upfront-header" @click="showInitialPayment = !showInitialPayment">
                                    <div class="upfront-title">
                                        <i class='bx bx-credit-card-front'></i>
                                        <span>{{ __('stock_in.form.initial_payment') }}</span>
                                    </div>
                                    <i class='bx bx-chevron-down' :class="{ 'bx-rotate-180': showInitialPayment }"></i>
                                </div>

                                <div class="upfront-body" x-show="fulfillmentType === 'direct' || showInitialPayment">
                                    <div class="row-2">
                                        <div class="form-row">
                                            <label>{{ __('stock_in.form.payment_amount') }}</label>
                                            <input type="number" step="0.01" min="0" name="initial_payment_amount" x-model.number="initialPaymentAmount" placeholder="0.00">
                                        </div>
                                        <div class="form-row">
                                            <label>{{ __('stock_in.form.payment_method') }}</label>
                                            <select name="initial_payment_method" class="filter-select" style="width: 100%;">
                                                <option value="cash">{{ __('stock_in.payment_method.cash') }}</option>
                                                <option value="aba">ABA Pay</option>
                                                <option value="bank_transfer">{{ __('stock_in.payment_method.bank_transfer') }}</option>
                                                <option value="cheque">{{ __('stock_in.payment_method.cheque') }}</option>
                                                <option value="other">{{ __('stock_in.payment_method.other') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row-2">
                                        <div class="form-row">
                                            <label>{{ __('stock_in.form.payment_reference') }}</label>
                                            <input type="text" name="payment_reference_no" placeholder="e.g. Bank slip # / Cheque #">
                                        </div>
                                        <div class="form-row">
                                            <label>{{ __('stock_in.form.payment_receipt') }}</label>
                                            <input type="file" name="payment_receipt" accept="image/*,application/pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Financial Calculation Card --}}
                    <div class="financial-card">
                        <div class="financial-row">
                            <span>{{ __('stock_in.form.subtotal') }}</span>
                            <strong x-text="formatCurrency(calculateSubtotal())"></strong>
                        </div>

                        <div class="financial-row">
                            <label>{{ __('stock_in.form.discount') }}</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discountAmount" class="calc-input" placeholder="0.00" {!! $readonly ? 'readonly' : '' !!}>
                        </div>

                        <div class="financial-row">
                            <label>{{ __('stock_in.form.tax') }}</label>
                            <input type="number" step="0.01" min="0" name="tax_amount" x-model.number="taxAmount" class="calc-input" placeholder="0.00" {!! $readonly ? 'readonly' : '' !!}>
                        </div>

                        <div class="financial-row">
                            <label>{{ __('stock_in.form.shipping') }}</label>
                            <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" class="calc-input" placeholder="0.00" {!! $readonly ? 'readonly' : '' !!}>
                        </div>

                        <div class="financial-row total-row">
                            <span>{{ __('stock_in.form.grand_total') }}</span>
                            <span class="grand-total-val" x-text="formatCurrency(calculateGrandTotal())"></span>
                        </div>
                    </div>
                </div>

                {{-- Form Action Buttons --}}
                <div class="form-button">
                    @if (!$readonly)
                        <button type="submit" color="primary">
                            <i data-feather="save"></i>
                            <span>{{ __('global.button.submit') }}</span>
                        </button>
                        @if (!$isEdit)
                            <button type="submit" name="save_opt" value="save_new" color="success">
                                <i data-feather="plus-circle"></i>
                                <span>{{ __('global.button.save_new') }}</span>
                            </button>
                        @endif
                    @endif
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 'all') !!}">
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
        .form-wrapper.stock-in-form {
            max-width: 78rem !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding: 0 32px 32px 32px !important;
            box-sizing: border-box !important;
        }

        .stock-in-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .stock-in-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
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
            color: #2563eb;
            font-size: 18px;
        }

        .row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 14px;
        }

        /* Fulfillment Mode Card */
        .fulfillment-mode-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px dashed #e2e8f0;
        }
        .fulfillment-option {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .fulfillment-option.is-selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .fulfillment-option .option-info strong {
            display: block;
            font-size: 13.5px;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .fulfillment-option .option-info span {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        /* Items Section */
        .stock-in-items-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .stock-in-items-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .stock-in-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .stock-in-heading-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stock-in-heading-content h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .stock-in-section-subtitle {
            font-size: 12px;
            color: #64748b;
        }

        .stock-in-toolbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .stock-in-stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 12px;
            color: #475569;
        }
        .stock-in-stat-pill.stat-pill--primary {
            background: #eff6ff;
            color: #2563eb;
        }

        .btn-stock-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-stock-add:hover {
            background: #1d4ed8;
        }

        /* Items Table */
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
            padding: 11px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            text-align: left;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }

        .stock-in-table th .req {
            color: #ef4444;
            font-weight: 700;
            margin-left: 2px;
        }

        .stock-in-table td {
            padding: 10px 8px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
            background: #ffffff;
            box-sizing: border-box !important;
        }

        .stock-in-row {
            transition: background 0.12s ease;
        }

        .stock-in-row:hover {
            background: #fafbff;
        }

        .stock-in-row.dropdown-open {
            position: relative !important;
            z-index: 100 !important;
            background: #f8fafc;
        }

        .stock-in-row:last-child td {
            border-bottom: none !important;
        }

        .th-num, .td-num {
            width: 44px !important;
            min-width: 44px !important;
            max-width: 44px !important;
            text-align: center !important;
        }

        .th-product, .td-product {
            width: 30% !important;
            position: relative;
        }

        .th-stock, .td-stock {
            width: 90px !important;
            min-width: 90px !important;
            max-width: 90px !important;
            text-align: center !important;
        }

        .th-cost, .td-cost {
            width: 110px !important;
            min-width: 110px !important;
            max-width: 110px !important;
            text-align: right !important;
        }

        .th-qty, .td-qty {
            width: 118px !important;
            min-width: 118px !important;
            max-width: 118px !important;
            text-align: center !important;
        }

        .th-subtotal, .td-subtotal {
            width: 100px !important;
            min-width: 100px !important;
            max-width: 100px !important;
            text-align: right !important;
        }

        .th-remark, .td-remark {
            width: auto !important;
        }

        .th-action, .td-action {
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
            text-align: center !important;
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
            font-size: 11.5px;
            font-weight: 700;
        }

        /* Selected Product Card */
        .selected-product-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            transition: all 0.15s ease;
            box-sizing: border-box;
            width: 100%;
        }

        .selected-product-card:hover {
            border-color: #cbd5e1;
        }

        .selected-product-img {
            width: 36px;
            height: 36px;
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
            gap: 5px;
            margin-top: 2px;
            flex-wrap: wrap;
        }

        .meta-tag {
            font-size: 10.5px;
            font-weight: 600;
            padding: 1.5px 6px;
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

        .btn-change-product {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
            height: 28px !important;
            min-height: 28px !important;
            padding: 0 9px !important;
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
            border: 1px solid #cbd5e1;
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
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
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
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            min-width: 360px;
            max-width: 500px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.16), 0 4px 12px rgba(15, 23, 42, 0.08);
            z-index: 1050;
            overflow: hidden;
            animation: dropdownSlideDown 0.15s ease-out;
            box-sizing: border-box;
        }

        .product-dropdown-menu.dropup-menu {
            top: auto;
            bottom: calc(100% + 4px);
            box-shadow: 0 -14px 34px rgba(15, 23, 42, 0.16), 0 -4px 12px rgba(15, 23, 42, 0.08);
            animation: dropdownSlideUp 0.15s ease-out;
        }

        @keyframes dropdownSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes dropdownSlideUp {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
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

        /* Current Stock Badge */
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

        /* Currency Input Wrapper (Unit Cost) */
        .currency-input-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
            height: 34px;
            box-sizing: border-box;
            width: 100%;
            max-width: 104px;
            margin-left: auto;
        }

        .currency-input-wrapper:hover {
            border-color: #94a3b8;
        }

        .currency-input-wrapper:focus-within {
            border-color: #024de3;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12);
        }

        .currency-symbol {
            position: absolute;
            left: 9px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            pointer-events: none;
            line-height: 1;
        }

        .currency-input-wrapper:focus-within .currency-symbol {
            color: #024de3;
        }

        .input-cost {
            width: 100% !important;
            min-width: 0 !important;
            height: 100% !important;
            padding: 0 10px 0 22px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            text-align: right !important;
            box-sizing: border-box !important;
            background: transparent !important;
            outline: none !important;
            box-shadow: none !important;
            -moz-appearance: textfield;
        }

        .input-cost::-webkit-outer-spin-button,
        .input-cost::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* QTY Stepper Control */
        .qty-control-wrapper {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
            transition: all 0.15s ease !important;
            overflow: hidden !important;
            height: 34px !important;
            min-height: 34px !important;
            width: 104px !important;
            min-width: 104px !important;
            max-width: 104px !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }

        .qty-control-wrapper:hover {
            border-color: #94a3b8 !important;
        }

        .qty-control-wrapper:focus-within {
            border-color: #024de3 !important;
            box-shadow: 0 0 0 3px rgba(2, 77, 227, 0.12) !important;
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
            box-shadow: none !important;
            border-radius: 0 !important;
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
            max-width: 44px !important;
            height: 34px !important;
            min-height: 34px !important;
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

        .line-subtotal-val {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
        }

        /* Remark Input */
        .remark-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            min-width: 0 !important;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
            height: 34px;
            box-sizing: border-box;
        }

        .remark-input-wrapper:hover {
            border-color: #94a3b8;
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

        /* Financial Grid */
        .financial-summary-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .financial-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .financial-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13.5px;
            color: #475569;
        }
        .calc-input {
            width: 120px;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0 10px;
            text-align: right;
            font-weight: 600;
            font-size: 13px;
            outline: none;
        }
        .financial-row.total-row {
            border-top: 2px solid #e2e8f0;
            padding-top: 14px;
            margin-top: 6px;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }
        .grand-total-val {
            font-size: 20px;
            color: #2563eb;
            font-weight: 800;
        }

        .upfront-payment-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 16px;
            overflow: hidden;
        }
        .upfront-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #f8fafc;
            cursor: pointer;
            font-weight: 600;
            font-size: 13.5px;
            color: #1e293b;
        }
        .upfront-title { display: flex; align-items: center; gap: 8px; }
        .upfront-body { padding: 18px; }

        .ui-datepicker.stock-in-datepicker-popup {
            z-index: 9999 !important;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xPurchaseForm', () => ({
                isEdit: @json($isEdit),
                readonly: @json($readonly),
                shopId: @json(old('shop_id', $purchase?->shop_id ?? '')),
                supplier: {
                    id: @json(old('supplier_id', $purchase?->supplier_id ?? '')),
                    text: @json($purchase?->supplier?->name ?? ''),
                },
                fulfillmentType: 'standard',
                immediateStockIn: false,
                showInitialPayment: false,
                initialPaymentAmount: 0.00,
                discountAmount: @json((float) old('discount_amount', $purchase?->discount_amount ?? 0)),
                taxAmount: @json((float) old('tax_amount', $purchase?->tax_amount ?? 0)),
                shippingCost: @json((float) old('shipping_cost', $purchase?->shipping_cost ?? 0)),
                productsCatalog: @json($preloadedProducts ?? []),
                items: @json(!empty($initialItems) ? $initialItems : []),
                errors: {},

                init() {
                    if (this.items.length === 0) {
                        this.addRow();
                    }

                    this.$nextTick(() => {
                        this.initSelectSupplier();
                        this.initSelectShop();
                        this.initDatepickers();
                    });
                },

                newRow() {
                    return {
                        id: '',
                        product_id: '',
                        product_name: '',
                        product_category: '',
                        product_uom: '',
                        product_image: '',
                        current_stock: 0,
                        unit_cost: 0.00,
                        qty: 1,
                        subtotal: 0.00,
                        remark: '',
                        dropdownOpen: false,
                        isDropup: false,
                        searchQuery: '',
                        uid: 'row_' + Math.random().toString(36).substring(2, 9),
                    };
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

                initSelectSupplier() {
                    const self = this;
                    const $select = $(this.$root).find('#supplier_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_in.form.select_supplier')),
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-supplier') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) { return { search: param.term }; },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return { text: item.name, id: item.id };
                                    })
                                };
                            }
                        }
                    }).on('select2:open', function() {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        self.supplier.id = $(this).val();
                        if (self.errors && self.errors['supplier_id']) {
                            delete self.errors['supplier_id'];
                        }
                    });
                },

                resetSupplier() {
                    const $select = $(this.$root).find('#supplier_id');
                    $select.val(null).trigger('change');
                    this.supplier.id = '';
                },

                initSelectShop() {
                    const self = this;
                    const $select = $(this.$root).find('#shop_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_in.form.select_shop')),
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) { return { search: param.term }; },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return { text: item?.name ? item.name : item?.phone, id: item.id };
                                    })
                                };
                            }
                        }
                    }).on('select2:open', function() {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        const newShopId = $(this).val();
                        self.shopId = newShopId;
                        if (newShopId) {
                            self.fetchShopProducts(newShopId);
                        }
                        if (self.errors && self.errors['shop_id']) {
                            delete self.errors['shop_id'];
                        }
                    });
                },

                resetShop() {
                    const $select = $(this.$root).find('#shop_id');
                    $select.val(null).trigger('change');
                    this.shopId = '';
                    this.productsCatalog = [];
                },

                async fetchShopProducts(shopId) {
                    try {
                        const res = await fetch(`/admin/stock-in/products/${shopId}`);
                        const json = await res.json();
                        if (json.status === 200) {
                            this.productsCatalog = json.data || [];
                            // Update stocks on loaded items
                            this.items.forEach(row => {
                                if (row.product_id) {
                                    const match = this.productsCatalog.find(p => String(p.id) === String(row.product_id));
                                    if (match) {
                                        row.current_stock = match.current_stock;
                                    }
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Failed to load shop products', e);
                    }
                },

                initDatepickers() {
                    const $orderDate = $(this.$root).find('#order_date');
                    const $expectedDate = $(this.$root).find('#expected_delivery_date');

                    if (!$orderDate.length || typeof $.fn.datepicker !== 'function') return;

                    $orderDate.datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        gotoCurrent: true,
                        yearRange: '-50:+10',
                        beforeShow: function() {
                            setTimeout(() => { $('#ui-datepicker-div').addClass('stock-in-datepicker-popup'); }, 0);
                        },
                        onClose: function(selectedDate) {
                            $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                            if (selectedDate && $expectedDate.length) {
                                $expectedDate.datepicker('option', 'minDate', selectedDate);
                            }
                        }
                    });

                    $expectedDate.datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        gotoCurrent: true,
                        yearRange: '-50:+10',
                        beforeShow: function() {
                            setTimeout(() => { $('#ui-datepicker-div').addClass('stock-in-datepicker-popup'); }, 0);
                        },
                        onClose: function(selectedDate) {
                            $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                        }
                    });

                    if ($orderDate.val() && $expectedDate.length) {
                        $expectedDate.datepicker('option', 'minDate', $orderDate.val());
                    }
                },

                availableProducts(row) {
                    const q = (row.searchQuery || '').trim().toLowerCase();
                    return this.productsCatalog.filter(p => {
                        return !q || p.name.toLowerCase().includes(q) || (p.category && p.category.toLowerCase().includes(q));
                    });
                },

                openDropdown(row, event) {
                    if (this.readonly) return;
                    this.items.forEach(r => { if (r !== row) r.dropdownOpen = false; });
                    this.computeDropDirection(row, event);
                    row.dropdownOpen = true;
                    this.$nextTick(() => {
                        const input = document.querySelector(`[data-dropdown-input="${row.uid}"]`);
                        if (input) input.focus();
                    });
                },

                toggleDropdown(row, event) {
                    if (this.readonly) return;
                    if (row.dropdownOpen) {
                        this.closeDropdown(row);
                    } else {
                        this.openDropdown(row, event);
                    }
                },

                computeDropDirection(row, event) {
                    const trigger = event ? (event.currentTarget || event.target) : null;
                    if (trigger && typeof trigger.getBoundingClientRect === 'function') {
                        const rect = trigger.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom;
                        const spaceAbove = rect.top;
                        row.isDropup = spaceBelow < 280 && spaceAbove > spaceBelow;
                    } else {
                        row.isDropup = false;
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
                    if (!row.unit_cost || row.unit_cost == 0) {
                        row.unit_cost = parseFloat(product.cost) || 0.00;
                    }
                    row.dropdownOpen = false;
                    row.searchQuery = '';
                },

                totalQty() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
                },

                calculateSubtotal() {
                    return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.unit_cost) || 0)), 0);
                },

                calculateGrandTotal() {
                    const subtotal = this.calculateSubtotal();
                    const discount = parseFloat(this.discountAmount) || 0;
                    const tax = parseFloat(this.taxAmount) || 0;
                    const shipping = parseFloat(this.shippingCost) || 0;
                    return Math.max(0, subtotal - discount + tax + shipping);
                },

                formatCurrency(val) {
                    return '$' + (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                fieldError(index, field) {
                    return this.errors?.[`items.${index}.${field}`]?.[0] ?? '';
                },

                handleFormSubmit(e) {
                    if (this.readonly) return true;

                    const supplierId = $(this.$root).find('#supplier_id').val() || this.supplier.id;
                    const shopId = $(this.$root).find('#shop_id').val() || this.shopId;
                    const newErrors = {};
                    let isValid = true;

                    if (!supplierId) {
                        newErrors['supplier_id'] = [@json(__('stock_in.validation.supplier_required'))];
                        isValid = false;
                    }

                    if (!shopId) {
                        newErrors['shop_id'] = [@json(__('stock_in.validation.shop_required'))];
                        isValid = false;
                    }

                    const validItems = this.items.filter(item => item.product_id);
                    if (validItems.length === 0) {
                        newErrors['items'] = [@json(__('stock_in.validation.items_required'))];
                        isValid = false;
                    }

                    for (let i = 0; i < this.items.length; i++) {
                        const item = this.items[i];
                        if (!item.product_id) {
                            newErrors[`items.${i}.product_id`] = [@json(__('stock_in.validation.product_required'))];
                            isValid = false;
                        }
                        if (!item.qty || parseFloat(item.qty) < 0.01) {
                            newErrors[`items.${i}.qty`] = [@json(__('stock_in.validation.qty_min'))];
                            isValid = false;
                        }
                    }

                    this.errors = newErrors;

                    if (!isValid) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    return true;
                }
            }));
        });
    </script>
@stop
