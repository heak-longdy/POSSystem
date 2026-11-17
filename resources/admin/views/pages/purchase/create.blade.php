@extends('admin::shared.layout')
@php
    $id = $id ?? '';
    $purchase = $purchase ?? null;
    $isEdit = !empty($id) && $purchase;
    $formTitle = $isEdit ? __('purchase.form.title.update') : __('purchase.form.title.create');

    $products = $products ?? \App\Models\Product::where('status', 1)->with(['category', 'uom'])->orderBy('name')->get();

    $productList = [];
    foreach ($products as $p) {
        $productList[] = [
            'id'       => (string) $p->id,
            'name'     => $p->name,
            'code'     => $p->code ?? '',
            'category' => $p->category?->name ?? '',
            'uom'      => $p->uom?->name ?? '',
            'cost'     => (float) ($p->cost ?? 0),
            'image'    => $p->image_url ?? '',
        ];
    }

    $initialItems = [];
    if ($purchase && $purchase->items && $purchase->items->count() > 0) {
        foreach ($purchase->items as $item) {
            $prod = $item->product;
            $initialItems[] = [
                'uid'                 => uniqid('item_'),
                'product_id'          => (string) $item->product_id,
                'product_name'        => $prod?->name ?? '',
                'product_code'        => $prod?->code ?? '',
                'product_category'    => $prod?->category?->name ?? '',
                'product_uom'         => $item->uom?->name ?? $prod?->uom?->name ?? '',
                'product_image'       => $prod?->image_url ?? '',
                'ordered_qty'         => (float) $item->ordered_qty,
                'unit_cost'           => (float) $item->unit_cost,
                'discount_amount'     => (float) ($item->discount_amount ?? 0),
                'tax_amount'          => (float) ($item->tax_amount ?? 0),
                'subtotal'            => (float) $item->subtotal,
                'update_product_cost' => (bool) $item->update_product_cost,
                'remark'              => (string) ($item->remark ?? ''),
                'searchQuery'         => '',
                'dropdownOpen'        => false,
                'isDropup'            => false,
            ];
        }
    } else {
        $initialItems[] = [
            'uid'                 => uniqid('item_'),
            'product_id'          => '',
            'product_name'        => '',
            'product_code'        => '',
            'product_category'    => '',
            'product_uom'         => '',
            'product_image'       => '',
            'ordered_qty'         => 1,
            'unit_cost'           => 0,
            'discount_amount'     => 0,
            'tax_amount'          => 0,
            'subtotal'            => 0,
            'update_product_cost' => true,
            'remark'              => '',
            'searchQuery'         => '',
            'dropdownOpen'        => false,
            'isDropup'            => false,
        ];
    }
@endphp

@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])

    <div class="form-admin" x-data="xPurchaseForm">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper purchase-form" action="{{ route('admin-purchase-save', $id) }}" method="POST" @submit="handleSubmit($event)">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 'all') !!}"></i>
                    {{ $formTitle }}
                </h3>
            </div>
            {{ csrf_field() }}

            <div class="form-body">
                {{-- Card 1: Header Information --}}
                <div class="purchase-header-card">
                    <div class="purchase-card-title-bar">
                        <div class="purchase-card-title">
                            <span class="purchase-card-icon"><i class='bx bx-building-house'></i></span>
                            <span>{{ __('purchase.form.supplier') }} & {{ __('purchase.form.shop') }}</span>
                        </div>
                        <div class="purchase-po-pill">
                            <span class="po-label">PO Ref:</span>
                            <strong class="po-number">{{ $nextPoNumber }}</strong>
                            <input type="hidden" name="purchase_number" value="{{ $nextPoNumber }}">
                        </div>
                    </div>

                    {{-- Primary Row: Supplier & Shop --}}
                    <div class="row-2">
                        <div class="form-row">
                            <label for="purchase_supplier_id">{{ __('purchase.form.supplier') }} <span>*</span></label>
                            <div class="select2Group">
                                <select name="supplier_id" id="purchase_supplier_id" class="SelectField select2" required>
                                    <option value="">{{ __('purchase.form.select_supplier') }}</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ (old('supplier_id', $purchase?->supplier_id) == $sup->id) ? 'selected' : '' }}>
                                            {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="select2Reset" x-show="supplierId" @click.stop="resetSupplier()" style="display: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('supplier_id') <label class="error">{{ $message }}</label> @enderror
                        </div>

                        <div class="form-row">
                            <label for="purchase_shop_id">{{ __('purchase.form.shop') }} <span>*</span></label>
                            <div class="select2Group">
                                <select name="shop_id" id="purchase_shop_id" class="SelectField select2" required>
                                    <option value="">{{ __('purchase.form.select_shop') }}</option>
                                    @foreach ($shops as $shp)
                                        <option value="{{ $shp->id }}" {{ (old('shop_id', $purchase?->shop_id) == $shp->id) ? 'selected' : '' }}>
                                            {{ $shp->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="select2Reset" x-show="shopId" @click.stop="resetShop()" style="display: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('shop_id') <label class="error">{{ $message }}</label> @enderror
                        </div>
                    </div>

                    {{-- Secondary Row: Dates & External Invoice # --}}
                    <div class="purchase-meta-grid">
                        <div class="form-row">
                            <label for="purchase_date">{{ __('purchase.form.purchase_date') }} <span>*</span></label>
                            <div class="filter-date-input" @click="$refs.purchaseDate.focus()">
                                <input type="text" name="purchase_date" id="purchase_date"
                                       x-ref="purchaseDate" value="{{ old('purchase_date', $purchase?->purchase_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                                       autocomplete="off" required placeholder="YYYY-MM-DD">
                                <i class='bx bx-calendar' aria-hidden="true"></i>
                            </div>
                            @error('purchase_date') <label class="error">{{ $message }}</label> @enderror
                        </div>

                        <div class="form-row">
                            <label for="delivery_date">{{ __('purchase.form.delivery_date') }}</label>
                            <div class="filter-date-input" @click="$refs.deliveryDate.focus()">
                                <input type="text" name="delivery_date" id="delivery_date"
                                       x-ref="deliveryDate" value="{{ old('delivery_date', $purchase?->delivery_date?->format('Y-m-d')) }}"
                                       autocomplete="off" placeholder="YYYY-MM-DD">
                                <i class='bx bx-calendar' aria-hidden="true"></i>
                            </div>
                            @error('delivery_date') <label class="error">{{ $message }}</label> @enderror
                        </div>

                        <div class="form-row iconInput">
                            <label for="supplier_invoice_no">{{ __('purchase.form.supplier_invoice_no') }}</label>
                            <input type="text" name="supplier_invoice_no" id="supplier_invoice_no"
                                   value="{{ old('supplier_invoice_no', $purchase?->supplier_invoice_no) }}"
                                   placeholder="{{ __('purchase.form.placeholder_invoice_no') }}">
                            <i class='bx bx-receipt'></i>
                            @error('supplier_invoice_no') <label class="error">{{ $message }}</label> @enderror
                        </div>

                        <div class="form-row">
                            <label for="payment_due_date">{{ __('purchase.form.payment_due_date') }}</label>
                            <div class="filter-date-input" @click="$refs.dueDate.focus()">
                                <input type="text" name="payment_due_date" id="payment_due_date"
                                       x-ref="dueDate" value="{{ old('payment_due_date', $purchase?->payment_due_date?->format('Y-m-d')) }}"
                                       autocomplete="off" placeholder="YYYY-MM-DD">
                                <i class='bx bx-calendar' aria-hidden="true"></i>
                            </div>
                            @error('payment_due_date') <label class="error">{{ $message }}</label> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Line Items Section --}}
                <div class="purchase-items-section">
                    {{-- Section Toolbar --}}
                    <div class="purchase-items-toolbar">
                        <div class="purchase-toolbar-left">
                            <div class="purchase-heading-icon">
                                <i class='bx bx-package'></i>
                            </div>
                            <div class="purchase-heading-content">
                                <h4>{{ __('purchase.form.items_section_title') }}</h4>
                                <span class="purchase-section-subtitle">{{ __('purchase.form.items_section_desc', ['default' => 'Add products, order quantities, unit costs, and discounts']) }}</span>
                            </div>
                        </div>

                        <div class="purchase-toolbar-right">
                            <div class="purchase-stat-pill">
                                <i class='bx bx-list-ol'></i>
                                <span class="stat-pill-label">Items:</span>
                                <strong class="stat-pill-value" x-text="items.length"></strong>
                            </div>
                            <div class="purchase-stat-pill">
                                <i class='bx bx-cube'></i>
                                <span class="stat-pill-label">Total Qty:</span>
                                <strong class="stat-pill-value" x-text="totalQty()"></strong>
                            </div>
                            <div class="purchase-stat-pill stat-pill--primary">
                                <i class='bx bx-dollar-circle'></i>
                                <span class="stat-pill-label">Est. Subtotal:</span>
                                <strong class="stat-pill-value" x-text="'$' + computeSubtotal().toFixed(2)"></strong>
                            </div>
                            <button type="button" class="btn-stock-add" @click="addRow()">
                                <i class='bx bx-plus'></i>
                                <span>{{ __('purchase.form.add_row') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Table Container --}}
                    <div class="purchase-table-container">
                        <table class="purchase-table">
                            <thead>
                                <tr>
                                    <th class="th-num">#</th>
                                    <th class="th-product">{{ __('purchase.table.product') }} <span class="req">*</span></th>
                                    <th class="th-uom">{{ __('purchase.table.uom') }}</th>
                                    <th class="th-qty">{{ __('purchase.table.ordered_qty') }} <span class="req">*</span></th>
                                    <th class="th-cost">{{ __('purchase.table.unit_cost') }} ($) <span class="req">*</span></th>
                                    <th class="th-discount">{{ __('purchase.table.discount') }} ($)</th>
                                    <th class="th-tax">{{ __('purchase.table.tax') }} ($)</th>
                                    <th class="th-subtotal">{{ __('purchase.table.subtotal') }} ($)</th>
                                    <th class="th-update-cost" title="Update master product catalog cost price">Update Cost</th>
                                    <th class="th-action"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in items" :key="row.uid">
                                    <tr class="purchase-table-row" :class="{'dropdown-open': row.dropdownOpen}">
                                        <td class="td-num">
                                            <span class="row-num" x-text="index + 1"></span>
                                        </td>

                                        <td class="td-product">
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id">

                                            {{-- When Product is Selected --}}
                                            <div class="selected-product-card" x-show="row.product_id && !row.dropdownOpen">
                                                <div class="selected-product-img">
                                                    <img :src="row.product_image || '{{ asset('images/logo/default.png') }}'"
                                                         onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                                </div>
                                                <div class="selected-product-info">
                                                    <div class="selected-product-name" x-text="row.product_name"></div>
                                                    <div class="selected-product-meta">
                                                        <span class="meta-tag meta-category" x-text="row.product_category" x-show="row.product_category"></span>
                                                        <span class="meta-tag meta-uom" x-text="row.product_uom" x-show="row.product_uom"></span>
                                                        <span class="meta-tag meta-code" x-text="row.product_code" x-show="row.product_code"></span>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-change-product" @click="openDropdown(row, $event)" title="Change product">
                                                    <i class='bx bx-sync'></i>
                                                    <span>Change</span>
                                                </button>
                                            </div>

                                            {{-- When Product is NOT Selected or Changing --}}
                                            <div class="product-dropdown-wrapper" x-show="!row.product_id || row.dropdownOpen" @click.outside="closeDropdown(row)">
                                                <div class="product-dropdown-trigger" :class="{'is-active': row.dropdownOpen}" @click="toggleDropdown(row, $event)">
                                                    <i class='bx bx-search'></i>
                                                    <span x-text="row.product_name || '{{ __('purchase.form.select_product') }}'" :class="{'placeholder-text': !row.product_id}"></span>
                                                    <i class='bx bx-chevron-down' :class="{'is-rotated': row.dropdownOpen}"></i>
                                                </div>

                                                <div class="product-dropdown-menu" :class="{'dropup-menu': row.isDropup}" x-show="row.dropdownOpen" style="display: none;">
                                                    <div class="product-dropdown-search">
                                                        <i class='bx bx-search'></i>
                                                        <input type="text"
                                                               :data-dropdown-input="row.uid"
                                                               x-model="row.searchQuery"
                                                               placeholder="{{ __('purchase.form.select_product') }}..."
                                                               @click.stop>
                                                        <button type="button" class="btn-clear-search" x-show="row.searchQuery" @click.stop="row.searchQuery = ''">
                                                            <i class='bx bx-x'></i>
                                                        </button>
                                                    </div>

                                                    <div class="product-dropdown-list">
                                                        <template x-for="product in availableProducts(row)" :key="product.id">
                                                            <div class="product-option-item" @click="selectProduct(row, product)">
                                                                <div class="product-option-img">
                                                                    <img :src="product.image || '{{ asset('images/logo/default.png') }}'"
                                                                         onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                                                </div>
                                                                <div class="product-option-detail">
                                                                    <div class="product-option-name" x-text="product.name"></div>
                                                                    <div class="product-option-sub">
                                                                        <span class="meta-tag meta-category" x-text="product.category" x-show="product.category"></span>
                                                                        <span class="meta-tag meta-uom" x-text="product.uom" x-show="product.uom"></span>
                                                                        <span class="meta-tag meta-code" x-text="product.code" x-show="product.code"></span>
                                                                        <span class="meta-tag meta-cost" x-text="'Cost: $' + (product.cost || 0).toFixed(2)"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <div class="product-option-empty" x-show="availableProducts(row).length === 0">
                                                            <i class='bx bx-search-alt'></i>
                                                            <span>No products found</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="td-uom">
                                            <span class="badge-uom" x-text="row.product_uom || '—'"></span>
                                        </td>

                                        <td class="td-qty">
                                            <div class="qty-control-wrapper">
                                                <button type="button" class="btn-qty-step" @click="row.ordered_qty = Math.max(1, (parseFloat(row.ordered_qty) || 1) - 1); updateLineTotal(row)">
                                                    <i class='bx bx-minus'></i>
                                                </button>
                                                <input type="number" step="0.01" min="0.01" :name="`items[${index}][ordered_qty]`"
                                                       x-model.number="row.ordered_qty" @input="updateLineTotal(row)" class="input-qty" required>
                                                <button type="button" class="btn-qty-step" @click="row.ordered_qty = (parseFloat(row.ordered_qty) || 0) + 1; updateLineTotal(row)">
                                                    <i class='bx bx-plus'></i>
                                                </button>
                                            </div>
                                        </td>

                                        <td class="td-cost">
                                            <div class="table-input-curr">
                                                <span class="curr-sign">$</span>
                                                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`"
                                                       x-model.number="row.unit_cost" @input="updateLineTotal(row)" class="input-table-num" required>
                                            </div>
                                        </td>

                                        <td class="td-discount">
                                            <div class="table-input-curr">
                                                <span class="curr-sign">$</span>
                                                <input type="number" step="0.01" min="0" :name="`items[${index}][discount_amount]`"
                                                       x-model.number="row.discount_amount" @input="updateLineTotal(row)" class="input-table-num" placeholder="0.00">
                                            </div>
                                        </td>

                                        <td class="td-tax">
                                            <div class="table-input-curr">
                                                <span class="curr-sign">$</span>
                                                <input type="number" step="0.01" min="0" :name="`items[${index}][tax_amount]`"
                                                       x-model.number="row.tax_amount" @input="updateLineTotal(row)" class="input-table-num" placeholder="0.00">
                                            </div>
                                        </td>

                                        <td class="td-subtotal">
                                            <strong class="line-subtotal-val" x-text="'$' + (row.subtotal || 0).toFixed(2)"></strong>
                                        </td>

                                        <td class="td-update-cost">
                                            <label class="custom-cost-checkbox" title="Update master product catalog cost price">
                                                <input type="checkbox" :name="`items[${index}][update_product_cost]`" value="1"
                                                       x-model="row.update_product_cost">
                                                <span class="checkbox-indicator"></span>
                                            </label>
                                        </td>

                                        <td class="td-action">
                                            <button type="button" class="btn-row-remove" @click="removeRow(index)" title="Remove item">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <div class="purchase-table-footer">
                            <button type="button" class="btn-stock-add-bottom" @click="addRow()">
                                <i class='bx bx-plus-circle'></i>
                                <span>{{ __('purchase.form.add_row') }}</span>
                            </button>
                            <div class="purchase-footer-hint">
                                <i class='bx bx-info-circle'></i>
                                <span>Each item's subtotal incorporates its line discount and tax before order-level adjustments.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Notes & Financial Summary Breakdown --}}
                <div class="purchase-summary-card">
                    <div class="purchase-summary-grid">
                        {{-- Left Column: Notes & Instructions --}}
                        <div class="summary-col-left">
                            <div class="purchase-card-title" style="margin-bottom: 12px;">
                                <span class="purchase-card-icon"><i class='bx bx-note'></i></span>
                                <span>{{ __('purchase.form.remark') }} & Instructions</span>
                            </div>

                            <div class="form-row">
                                <textarea name="remark" id="purchase_remark" rows="5" class="purchase-textarea"
                                          placeholder="{{ __('purchase.form.placeholder_remark') }}">{{ old('remark', $purchase?->remark) }}</textarea>
                            </div>

                            <div class="purchase-info-callout">
                                <i class='bx bx-shield-quarter'></i>
                                <div>
                                    <strong>Workflow Notice:</strong>
                                    <span>Saving as <em>Ordered</em> establishes the purchase order with the supplier. Warehouse inventory is only added to stock once the shipment is formally <em>Received</em>.</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Financial Summary Breakdown --}}
                        <div class="summary-col-right">
                            <div class="summary-receipt-box">
                                <div class="receipt-header">
                                    <i class='bx bx-receipt'></i>
                                    <span>Financial Breakdown</span>
                                </div>

                                {{-- Subtotal --}}
                                <div class="receipt-line">
                                    <span class="receipt-line-label">{{ __('purchase.form.subtotal') }}</span>
                                    <strong class="receipt-line-val" x-text="'$' + computeSubtotal().toFixed(2)"></strong>
                                </div>

                                {{-- Order Discount --}}
                                <div class="receipt-line receipt-line-input">
                                    <div class="receipt-line-title">
                                        <span>{{ __('purchase.form.discount_amount') }}</span>
                                        <strong class="text-danger" x-text="'- $' + computeDiscount().toFixed(2)"></strong>
                                    </div>
                                    <div class="receipt-control-group">
                                        <select name="discount_type" x-model="discountType" class="receipt-select">
                                            <option value="fixed">$ (Fixed)</option>
                                            <option value="percentage">% (Percent)</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discountVal"
                                               class="receipt-input" placeholder="0.00">
                                    </div>
                                </div>

                                {{-- Order Tax / VAT --}}
                                <div class="receipt-line receipt-line-input">
                                    <div class="receipt-line-title">
                                        <span>Tax / VAT (<span x-text="(taxRate || 0) + '%'"></span>)</span>
                                        <strong class="text-slate" x-text="'+ $' + computeTax().toFixed(2)"></strong>
                                    </div>
                                    <div class="receipt-control-group">
                                        <div class="receipt-input-group">
                                            <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model.number="taxRate"
                                                   class="receipt-input" placeholder="0">
                                            <span class="input-suffix">%</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping Fee --}}
                                <div class="receipt-line receipt-line-input">
                                    <div class="receipt-line-title">
                                        <span>{{ __('purchase.form.shipping_cost') }}</span>
                                        <strong class="text-slate" x-text="'+ $' + (shippingCost || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="receipt-control-group">
                                        <div class="receipt-input-group">
                                            <span class="input-prefix">$</span>
                                            <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost"
                                                   class="receipt-input" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                {{-- Grand Total --}}
                                <div class="receipt-total-line">
                                    <div class="total-label-wrap">
                                        <span class="total-label">{{ __('purchase.form.total_amount') }}</span>
                                        <span class="total-hint">Net Payable</span>
                                    </div>
                                    <span class="total-value" x-text="'$' + computeGrandTotal().toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="form-button">
                    <button type="submit" name="status" value="ordered" color="primary">
                        <i data-feather="check-circle"></i>
                        <span>{{ __('purchase.button.save_ordered') }}</span>
                    </button>
                    <button type="submit" name="status" value="draft" color="success">
                        <i data-feather="save"></i>
                        <span>{{ __('purchase.button.save_draft') }}</span>
                    </button>
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
        /* Form Wrapper & Container */
        .form-wrapper.purchase-form {
            max-width: 82rem !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding-left: 32px !important;
            padding-right: 32px !important;
            box-sizing: border-box !important;
        }

        /* Card Header & Title Bar */
        .purchase-header-card,
        .purchase-summary-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px 18px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .purchase-header-card:hover,
        .purchase-summary-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .purchase-card-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .purchase-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14.5px;
            font-weight: 600;
            color: #1e293b;
        }

        .purchase-card-icon {
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

        .purchase-po-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            font-size: 12px;
        }

        .purchase-po-pill .po-label {
            color: #64748b;
            font-weight: 500;
        }

        .purchase-po-pill .po-number {
            color: #024de3;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* 4-Column Meta Grid for Dates */
        .purchase-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 14px;
        }

        @media (max-width: 992px) {
            .purchase-meta-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .purchase-meta-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Datepicker Standard Pattern */
        .filter-date-input {
            position: relative !important;
            cursor: pointer;
            width: 100%;
        }

        .filter-date-input input {
            width: 100%;
            height: 43px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 0 36px 0 12px !important;
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
            background: #ffffff;
            cursor: pointer;
            outline: none;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }

        .filter-date-input input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .filter-date-input i.bx-calendar {
            position: absolute !important;
            right: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 18px !important;
            color: #64748b !important;
            pointer-events: none !important;
            line-height: 1 !important;
        }

        .filter-date-input:hover i.bx-calendar {
            color: #334155 !important;
        }

        /* Select2 Compliance Rules */
        .select2Group {
            position: relative;
            width: 100%;
        }

        .select2Group .select2-container .select2-selection--single {
            height: 43px !important;
            border-radius: 7px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 10px !important;
            box-shadow: none !important;
        }

        .select2Group .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            line-height: normal !important;
            padding-left: 0 !important;
            padding-right: 28px !important;
        }

        .select2Group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 8px !important;
        }

        .select2Group .select2Reset {
            position: absolute !important;
            top: 50% !important;
            right: 26px !important;
            transform: translateY(-50%) !important;
            cursor: pointer !important;
            z-index: 2 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .select2Group .select2Reset[style*="display: none"],
        .select2Group .select2Reset[style*="display:none"] {
            display: none !important;
        }

        .select2Group .select2-selection__clear {
            display: none !important;
        }

        .select2Group .select2Reset svg {
            fill: #94a3b8 !important;
            width: 18px !important;
            height: 18px !important;
            background: #ffffff !important;
            border-radius: 50% !important;
            transition: fill 0.15s ease !important;
        }

        .select2Group .select2Reset:hover svg {
            fill: #ef4444 !important;
        }

        /* Items Section & Toolbar */
        .purchase-items-section {
            margin-bottom: 24px;
        }

        .purchase-items-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .purchase-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .purchase-heading-icon {
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

        .purchase-heading-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .purchase-section-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin: 2px 0 0 0;
            display: block;
        }

        .purchase-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .purchase-stat-pill {
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
        }

        .purchase-stat-pill i {
            font-size: 16px;
            color: #64748b;
        }

        .purchase-stat-pill .stat-pill-label {
            font-weight: 500;
            color: #64748b;
            font-size: 12px;
        }

        .purchase-stat-pill .stat-pill-value {
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12.5px;
        }

        .purchase-stat-pill.stat-pill--primary {
            background: #f8faff;
            border-color: #bfdbfe;
            color: #024de3;
        }

        .purchase-stat-pill.stat-pill--primary i {
            color: #024de3;
        }

        .purchase-stat-pill.stat-pill--primary .stat-pill-label {
            color: #024de3;
            font-weight: 600;
        }

        .purchase-stat-pill.stat-pill--primary .stat-pill-value {
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
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(2, 77, 227, 0.22) !important;
            transition: all 0.15s ease !important;
        }

        .btn-stock-add:hover {
            background: linear-gradient(180deg, #024de3 0%, #003dbd 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(2, 77, 227, 0.3) !important;
        }

        .btn-stock-add span,
        .btn-stock-add i {
            color: #ffffff !important;
        }

        /* Items Table Container & Layout */
        .purchase-table-container {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
            overflow: visible !important;
            position: relative;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .purchase-table {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin: 0 !important;
            font-size: 13px;
        }

        .purchase-table thead tr {
            background: #f8fafc;
        }

        .purchase-table th {
            padding: 10px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            white-space: nowrap !important;
        }

        .purchase-table th:first-child {
            border-top-left-radius: 11px;
        }

        .purchase-table th:last-child {
            border-top-right-radius: 11px;
        }

        .purchase-table th.th-num,
        .purchase-table th.th-uom,
        .purchase-table th.th-qty,
        .purchase-table th.th-update-cost,
        .purchase-table th.th-action {
            text-align: center;
        }

        .purchase-table th.th-subtotal {
            text-align: right;
        }

        .purchase-table th .req {
            color: #ef4444;
            font-weight: 700;
        }

        .purchase-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            background: #ffffff;
        }

        .purchase-table-row {
            position: relative;
            transition: background 0.15s ease;
        }

        .purchase-table-row:hover td {
            background: #f8faff;
        }

        .purchase-table-row.dropdown-open {
            position: relative;
            z-index: 100 !important;
        }

        .purchase-table-row.dropdown-open td {
            background: #f8faff;
        }

        /* Column Specific Sizing */
        .th-num, .td-num {
            width: 44px !important;
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
            font-weight: 600;
            font-size: 11px;
            border: 1px solid #e2e8f0;
        }

        .th-product, .td-product {
            width: 32% !important;
            min-width: 260px !important;
            position: relative;
        }

        .th-uom, .td-uom {
            width: 75px !important;
            text-align: center !important;
        }

        .badge-uom {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .th-qty, .td-qty {
            width: 120px !important;
            text-align: center !important;
        }

        .th-cost, .td-cost {
            width: 120px !important;
        }

        .th-discount, .td-discount {
            width: 105px !important;
        }

        .th-tax, .td-tax {
            width: 105px !important;
        }

        .th-subtotal, .td-subtotal {
            width: 120px !important;
            text-align: right !important;
        }

        .line-subtotal-val {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .th-update-cost, .td-update-cost {
            width: 90px !important;
            text-align: center !important;
        }

        .th-action, .td-action {
            width: 48px !important;
            text-align: center !important;
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
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
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
            gap: 5px;
            margin-top: 3px;
            flex-wrap: wrap;
        }

        .meta-tag {
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.35;
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

        .meta-code {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .meta-cost {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #d1fae5;
        }

        .btn-change-product {
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            height: 26px !important;
            padding: 0 8px !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 6px !important;
            color: #024de3 !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            transition: all 0.15s ease !important;
        }

        .btn-change-product:hover {
            color: #ffffff !important;
            background: #024de3 !important;
            border-color: #024de3 !important;
        }

        .btn-change-product:hover span,
        .btn-change-product:hover i {
            color: #ffffff !important;
        }

        /* Product Dropdown */
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
            border-radius: 7px;
            background: #ffffff;
            color: #0f172a;
            cursor: pointer;
            height: 40px;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }

        .product-dropdown-trigger:hover {
            border-color: #2563eb;
            background: #fafbff;
        }

        .product-dropdown-trigger.is-active {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .product-dropdown-trigger span {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12.5px;
            font-weight: 500;
        }

        .product-dropdown-trigger span.placeholder-text {
            color: #94a3b8;
        }

        .product-dropdown-trigger i.bx-chevron-down {
            transition: transform 0.2s ease;
            font-size: 16px;
            color: #64748b;
        }

        .product-dropdown-trigger i.bx-chevron-down.is-rotated {
            transform: rotate(180deg);
        }

        .product-dropdown-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 380px;
            max-width: 90vw;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
            z-index: 9999;
            overflow: hidden;
        }

        .product-dropdown-menu.dropup-menu {
            top: auto;
            bottom: calc(100% + 4px);
        }

        .product-dropdown-search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .product-dropdown-search i {
            color: #64748b;
            font-size: 16px;
        }

        .product-dropdown-search input {
            border: none !important;
            background: transparent !important;
            outline: none !important;
            width: 100% !important;
            height: 28px !important;
            font-size: 12.5px !important;
            color: #0f172a !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .btn-clear-search {
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            padding: 2px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .btn-clear-search:hover {
            color: #0f172a !important;
        }

        .product-dropdown-list {
            max-height: 220px;
            overflow-y: auto;
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
            font-size: 12.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-option-sub {
            display: flex;
            gap: 4px;
            margin-top: 2px;
            flex-wrap: wrap;
        }

        .product-option-empty {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Qty Controls */
        .qty-control-wrapper {
            display: inline-flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            overflow: hidden;
            height: 38px;
            box-sizing: border-box;
        }

        .btn-qty-step {
            border: none !important;
            background: #f8fafc !important;
            color: #475569 !important;
            width: 28px !important;
            height: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            transition: all 0.12s ease !important;
            padding: 0 !important;
        }

        .btn-qty-step:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        .input-qty {
            width: 50px !important;
            height: 100% !important;
            border: none !important;
            text-align: center !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            color: #0f172a !important;
            outline: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            -moz-appearance: textfield;
        }

        .input-qty::-webkit-outer-spin-button,
        .input-qty::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Currency Input Wrapper */
        .table-input-curr {
            display: flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            height: 38px;
            padding: 0 8px;
            box-sizing: border-box;
            transition: border-color 0.15s ease;
        }

        .table-input-curr:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .table-input-curr .curr-sign {
            color: #94a3b8;
            font-weight: 600;
            font-size: 12px;
            margin-right: 4px;
        }

        .input-table-num {
            width: 100% !important;
            border: none !important;
            background: transparent !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            outline: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        /* Checkbox Customization */
        .custom-cost-checkbox {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .custom-cost-checkbox input {
            cursor: pointer;
            width: 16px;
            height: 16px;
            accent-color: #024de3;
        }

        /* Remove Row Button */
        .btn-row-remove {
            width: 32px !important;
            height: 32px !important;
            border-radius: 7px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid #fee2e2 !important;
            background: #fff !important;
            color: #ef4444 !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
        }

        .btn-row-remove:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
        }

        .btn-row-remove i {
            font-size: 16px;
        }

        /* Table Footer */
        .purchase-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom-left-radius: 11px;
            border-bottom-right-radius: 11px;
        }

        .btn-stock-add-bottom {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 7px !important;
            color: #024de3 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 6px 12px !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
        }

        .btn-stock-add-bottom:hover {
            border-color: #024de3 !important;
            background: #eff6ff !important;
        }

        .purchase-footer-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            color: #64748b;
        }

        /* Summary Card & Grid */
        .purchase-summary-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        @media (max-width: 900px) {
            .purchase-summary-grid {
                grid-template-columns: 1fr;
            }
        }

        .purchase-textarea {
            width: 100% !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 10px 12px !important;
            font-size: 12.5px !important;
            color: #334155 !important;
            outline: none !important;
            resize: vertical !important;
            box-sizing: border-box !important;
            transition: border-color 0.15s ease;
        }

        .purchase-textarea:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .purchase-info-callout {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            font-size: 12px;
            color: #1e40af;
            margin-top: 14px;
            line-height: 1.45;
        }

        .purchase-info-callout i {
            font-size: 18px;
            flex-shrink: 0;
            color: #024de3;
            margin-top: 1px;
        }

        /* Receipt Box in Summary Card */
        .summary-receipt-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
        }

        .receipt-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .receipt-header i {
            font-size: 17px;
            color: #024de3;
        }

        .receipt-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: #475569;
            border-bottom: 1px dashed #e2e8f0;
        }

        .receipt-line-label {
            font-weight: 500;
        }

        .receipt-line-val {
            font-size: 14px;
            color: #0f172a;
        }

        .receipt-line-input {
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
        }

        .receipt-line-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
        }

        .receipt-control-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .receipt-select {
            height: 32px !important;
            padding: 0 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            background: #ffffff !important;
            font-size: 12px !important;
            color: #334155 !important;
            outline: none !important;
            width: 100px !important;
        }

        .receipt-input {
            height: 32px !important;
            padding: 0 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            background: #ffffff !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            outline: none !important;
            flex: 1 !important;
            box-sizing: border-box !important;
        }

        .receipt-input:focus,
        .receipt-select:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .receipt-input-group {
            display: flex;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .input-suffix {
            position: absolute;
            right: 10px;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .input-prefix {
            position: absolute;
            left: 8px;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .receipt-input-group .input-prefix + .receipt-input {
            padding-left: 20px !important;
        }

        .receipt-total-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 2px solid #e2e8f0;
        }

        .total-label-wrap {
            display: flex;
            flex-direction: column;
        }

        .total-label {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .total-hint {
            font-size: 11px;
            color: #64748b;
        }

        .total-value {
            font-size: 22px;
            font-weight: 800;
            color: #024de3;
            letter-spacing: -0.02em;
        }

        .text-danger {
            color: #dc2626 !important;
        }

        .text-slate {
            color: #475569 !important;
        }
    </style>

    <script>
        (function() {
            const registerXPurchaseForm = () => {
                Alpine.data('xPurchaseForm', () => ({
                    supplierId: '{{ old('supplier_id', $purchase?->supplier_id ?? '') }}',
                    shopId: '{{ old('shop_id', $purchase?->shop_id ?? '') }}',

                    discountType: '{{ old('discount_type', $purchase?->discount_type ?? 'fixed') }}',
                    discountVal: {{ old('discount_amount', $purchase?->discount_amount ?? 0) }},
                    taxRate: {{ old('tax_rate', $purchase?->tax_rate ?? 0) }},
                    shippingCost: {{ old('shipping_cost', $purchase?->shipping_cost ?? 0) }},

                    products: @json($productList),
                    items: @json($initialItems),

                    init() {
                        this.$nextTick(() => {
                            this.initDatepickers();
                            this.initHeaderSelect2();
                        });
                    },

                    initDatepickers() {
                        if (!window.jQuery || !$.fn.datepicker) return;

                        const options = {
                            dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            changeMonth: true,
                            gotoCurrent: true,
                            yearRange: '-50:+10',
                            beforeShow: function(input, instance) {
                                instance.dpDiv.addClass('sales-report-datepicker');
                            },
                            onClose: function(dateText, instance) {
                                instance.dpDiv.removeClass('sales-report-datepicker');
                            },
                        };

                        const $pDate = $(this.$refs.purchaseDate);
                        const $dDate = $(this.$refs.deliveryDate);
                        const $dueDate = $(this.$refs.dueDate);

                        if ($pDate.length) {
                            $pDate.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    if ($dDate.length) $dDate.datepicker('option', 'minDate', selected);
                                }
                            }).on('change', function() {
                                if ($dDate.length) $dDate.datepicker('option', 'minDate', $pDate.datepicker('getDate'));
                            });
                        }

                        if ($dDate.length) {
                            $dDate.datepicker(options);
                        }

                        if ($dueDate.length) {
                            $dueDate.datepicker(options);
                        }
                    },

                    initHeaderSelect2() {
                        if (!window.jQuery || !$.fn.select2) return;

                        const self = this;
                        const $sup = $('#purchase_supplier_id');
                        const $shp = $('#purchase_shop_id');

                        $sup.select2({
                            placeholder: '{{ __('purchase.form.select_supplier') }}',
                            width: '100%'
                        }).on('select2:open', function () {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            }
                        }).on('change', function() {
                            self.supplierId = $(this).val();
                        });

                        $shp.select2({
                            placeholder: '{{ __('purchase.form.select_shop') }}',
                            width: '100%'
                        }).on('select2:open', function () {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            }
                        }).on('change', function() {
                            self.shopId = $(this).val();
                        });
                    },

                    resetSupplier() {
                        $('#purchase_supplier_id').val('').trigger('change');
                        this.supplierId = '';
                    },

                    resetShop() {
                        $('#purchase_shop_id').val('').trigger('change');
                        this.shopId = '';
                    },

                    selectedProductIds(exceptUid = null) {
                        return this.items
                            .filter(row => row.uid !== exceptUid && row.product_id)
                            .map(row => String(row.product_id));
                    },

                    availableProducts(row) {
                        const selectedIds = this.selectedProductIds(row.uid);
                        const query = (row.searchQuery || '').toLowerCase().trim();

                        return this.products.filter(p => {
                            if (selectedIds.includes(String(p.id))) {
                                return false;
                            }
                            if (!query) {
                                return true;
                            }
                            const name = (p.name || '').toLowerCase();
                            const code = (p.code || '').toLowerCase();
                            const cat = (p.category || '').toLowerCase();
                            const uom = (p.uom || '').toLowerCase();

                            return name.includes(query) || code.includes(query) || cat.includes(query) || uom.includes(query);
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
                        row.product_code = product.code;
                        row.product_category = product.category;
                        row.product_uom = product.uom;
                        row.product_image = product.image;
                        row.unit_cost = parseFloat(product.cost || 0);
                        row.dropdownOpen = false;
                        row.searchQuery = '';
                        this.updateLineTotal(row);
                    },

                    addRow() {
                        this.items.push({
                            uid: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                            product_id: '',
                            product_name: '',
                            product_code: '',
                            product_category: '',
                            product_uom: '',
                            product_image: '',
                            ordered_qty: 1,
                            unit_cost: 0,
                            discount_amount: 0,
                            tax_amount: 0,
                            subtotal: 0,
                            update_product_cost: true,
                            remark: '',
                            searchQuery: '',
                            dropdownOpen: false,
                            isDropup: false,
                        });
                    },

                    removeRow(index) {
                        if (this.items.length <= 1) {
                            alert('A purchase order must contain at least one line item.');
                            return;
                        }
                        this.items.splice(index, 1);
                    },

                    updateLineTotal(row) {
                        const qty = parseFloat(row.ordered_qty || 0);
                        const cost = parseFloat(row.unit_cost || 0);
                        const disc = parseFloat(row.discount_amount || 0);
                        const tax = parseFloat(row.tax_amount || 0);

                        row.subtotal = Math.max(0, (qty * cost) - disc + tax);
                    },

                    totalQty() {
                        return this.items.reduce((sum, item) => sum + (parseFloat(item.ordered_qty) || 0), 0);
                    },

                    computeSubtotal() {
                        return this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
                    },

                    computeDiscount() {
                        const sub = this.computeSubtotal();
                        const val = parseFloat(this.discountVal || 0);
                        if (this.discountType === 'percentage') {
                            return (sub * Math.min(100, Math.max(0, val))) / 100;
                        }
                        return Math.min(sub, Math.max(0, val));
                    },

                    computeTax() {
                        const taxable = Math.max(0, this.computeSubtotal() - this.computeDiscount());
                        const rate = parseFloat(this.taxRate || 0);
                        return (taxable * Math.min(100, Math.max(0, rate))) / 100;
                    },

                    computeGrandTotal() {
                        const sub = this.computeSubtotal();
                        const disc = this.computeDiscount();
                        const tax = this.computeTax();
                        const shipping = parseFloat(this.shippingCost || 0);

                        return Math.max(0, sub - disc + tax + shipping);
                    },

                    handleSubmit(e) {
                        if (!this.supplierId) {
                            alert('Please select a supplier.');
                            e.preventDefault();
                            return false;
                        }
                        if (!this.shopId) {
                            alert('Please select a destination shop.');
                            e.preventDefault();
                            return false;
                        }

                        const hasInvalid = this.items.some(i => !i.product_id || (parseFloat(i.ordered_qty) || 0) <= 0);
                        if (hasInvalid) {
                            alert('Please make sure every line item has a selected product and quantity greater than 0.');
                            e.preventDefault();
                            return false;
                        }

                        return true;
                    }
                }));
            };

            if (window.Alpine) {
                registerXPurchaseForm();
            } else {
                document.addEventListener('alpine:init', registerXPurchaseForm);
            }
        })();
    </script>
@stop
