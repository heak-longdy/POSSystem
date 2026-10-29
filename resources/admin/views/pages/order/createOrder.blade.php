@extends('admin::shared.layout')
@php
    $isEditing = !empty($id);
    $headerName = $isEditing ? __('order.form.edit_title') : __('order.form.create_title');
    $orderReference = $isEditing
        ? (($data->invoice_number ?? null) ? '#' . $data->invoice_number : '#' . $id)
        : null;
    $orderPageTitle = $isEditing ? __('order.form.edit_title') : __('order.order_details');
    $orderActionLabel = $isEditing ? __('order.button.update_order') : __('order.button.confirm_order');
    $orderConfirmMessage = $isEditing ? __('order.confirm.update') : __('order.confirm.create');
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => $headerName])
    <div class="form-admin booking booking-dashboard booking-order-ui" x-data="XDatacreateorder"
        x-id="['order-date', 'delivery-date', 'order-quantity-error']">
        <div class="booking-pos-workspace">
            <div class="booking-pos-header">
                <div class="form-header">
                    <h3>
                        <a href="{!! route('admin-' . ($routeName ?? 'order') . '-list', 'Pending') !!}" s-click-link="{!! route('admin-' . ($routeName ?? 'order') . '-list', 'Pending') !!}" class="back-link-icon" title="Back to listing">
                            <i data-feather="arrow-left"></i>
                        </a>
                        <span>{{ $headerName }}</span>
                    </h3>
                </div>

                <!-- Transferred Notice Banner (if order balance was consolidated into a newer order) -->
                <template x-if="isTransferred">
                    <div class="booking-transferred-banner" style="margin-top: 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <div>
                                <div style="color: #1e40af; font-size: 13.5px; font-weight: 600;">{{ __('order.balance_transferred_notice_title') }}</div>
                                <div style="color: #3b82f6; font-size: 12.5px; margin-top: 2px;">
                                    {{ __('order.balance_transferred_notice_desc') }}
                                    <template x-if="transferredToOrder">
                                        <span>({{ __('order.transferred_to') }}: <strong x-text="transferredToOrder.invoice_number"></strong>)</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <span style="background: #dbeafe; color: #1d4ed8; font-weight: 600; font-size: 12px; padding: 4px 10px; border-radius: 9999px; white-space: nowrap;">
                            {{ __('order.status.paid_through_transfer') }}
                        </span>
                    </div>
                </template>
            </div>

            <!-- Left Main Column (Catalog & Recent Bookings) -->
            <div class="booking-pos-main-col">
                <!-- Recent Bookings Reel -->
                <section class="booking-pos-recent-section" x-data="{
                    canScrollBack: false,
                    canScrollForward: false,
                    resizeObserver: null,
                    init() {
                        this.$nextTick(() => {
                            this.updateScroll();
                            this.resizeObserver = new ResizeObserver(() => this.updateScroll());
                            this.resizeObserver.observe(this.$refs.recentOrders);
                        });
                    },
                    destroy() {
                        this.resizeObserver?.disconnect();
                    },
                    updateScroll() {
                        const reel = this.$refs.recentOrders;
                        this.canScrollBack = reel.scrollLeft > 1;
                        this.canScrollForward = reel.scrollLeft + reel.clientWidth < reel.scrollWidth - 1;
                    },
                    scrollOrders(direction) {
                        const reel = this.$refs.recentOrders;
                        const card = reel.querySelector('.booking-pos-order-card');
                        if (!card) return;
                        const step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(reel).columnGap);
                        reel.scrollBy({ left: direction * step });
                    }
                }" x-id="['recent-orders']">
                    <div class="booking-pos-section-header">
                        <div class="title-wrap">
                            <i data-feather="clock"></i>
                            <h2>{{ __('order.recent_orders') }}</h2>
                        </div>
                        <div class="booking-pos-recent-actions">
                            <div class="booking-pos-recent-navigation" x-show="canScrollBack || canScrollForward" x-cloak>
                                <button type="button" class="booking-pos-recent-scroll" @click="scrollOrders(-1)"
                                    :disabled="!canScrollBack" :aria-controls="$id('recent-orders')"
                                    aria-label="{{ __('order.recent_orders_previous') }}" title="{{ __('order.recent_orders_previous') }}">
                                    <i data-feather="chevron-left" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="booking-pos-recent-scroll" @click="scrollOrders(1)"
                                    :disabled="!canScrollForward" :aria-controls="$id('recent-orders')"
                                    aria-label="{{ __('order.recent_orders_next') }}" title="{{ __('order.recent_orders_next') }}">
                                    <i data-feather="chevron-right" aria-hidden="true"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-link-all" s-click-link="{{ route('admin-' . ($routeName ?? 'order') . '-list', 'Pending') }}">
                                <span>{{ __('order.view_all') }}</span>
                                <i data-feather="arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="booking-pos-recent-cards" x-ref="recentOrders" :id="$id('recent-orders')"
                        role="region" aria-label="{{ __('order.recent_orders') }}" tabindex="0"
                        @scroll.passive="updateScroll()">
                        @php
                            $recentOrdersList = $recentOrders ?? collect([]);
                        @endphp
                        @forelse ($recentOrdersList as $recentOrder)
                            @php
                                $recentStatus = $recentOrder->payment_status ?: 'Pending';
                                $recentStatusClass = $recentStatus === 'Paid'
                                    ? 'ready'
                                    : ($recentStatus === 'Partial'
                                        ? 'waiting'
                                        : ($recentStatus === 'Cancel'
                                        ? 'canceled'
                                        : 'waiting'));
                                $recentStatusLabel = match ($recentStatus) {
                                    'Paid' => __('order.status.ready'),
                                    'Partial' => __('order.status.partial'),
                                    'Cancel' => __('order.status.canceled'),
                                    default => __('order.status.pending'),
                                };
                                $recentCustomer = $recentOrder->customer?->name
                                    ?: ($recentOrder->customer?->phone ?: __('order.walk_in_customer'));
                                $customerInitials = mb_strtoupper(mb_substr($recentCustomer, 0, 2, 'UTF-8'), 'UTF-8');
                                $recentDate = $recentOrder->order_date
                                    ? \Carbon\Carbon::parse($recentOrder->order_date)->format('d M Y, h:i a')
                                    : '---';
                            @endphp
                            <article class="booking-pos-order-card" role="link" tabindex="0"
                                @keydown.enter.prevent="$el.click()"
                                s-click-link="{{ route('admin-' . ($routeName ?? 'order') . '-edit', $recentOrder->id) }}">
                                <div class="booking-pos-order-card-top">
                                    <div class="order-customer-avatar">
                                        <span>{{ $customerInitials }}</span>
                                    </div>
                                    <span class="booking-pos-order-status {{ $recentStatusClass }}">
                                        <span class="status-dot"></span>
                                        {{ $recentStatusLabel }}
                                    </span>
                                </div>
                                <div class="booking-pos-order-card-mid">
                                    <strong>{{ $recentCustomer }}</strong>
                                    <span class="invoice-num">{{ $recentOrder->invoice_number ? '#' . $recentOrder->invoice_number : '#' . $recentOrder->id }}</span>
                                </div>
                                <p class="booking-pos-order-date">
                                    <i data-feather="calendar"></i>
                                    <span>{{ $recentDate }}</span>
                                </p>
                            </article>
                        @empty
                            <div class="booking-pos-order-empty">
                                <i data-feather="calendar"></i>
                                <span>{{ __('order.no_recent_orders') }}</span>
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- Selected Products Section -->
                <section class="booking-pos-items-section">
                    <div class="booking-pos-items-header">
                        <div class="header-left">
                            <div class="title-wrap">
                                <i data-feather="shopping-bag"></i>
                                <h2>{{ __('order.order_items') }}</h2>
                            </div>
                            <span class="items-count-badge" x-text="(dataCart?.length || 0) + ' ' + (lblItems || 'Items')"></span>
                        </div>
                        <div class="header-actions">
                            <button type="button" class="btn-reset-order-items" :disabled="!canEditOrderItems() || !dataCart?.length"
                                @click="resetCart()" title="{{ __('order.button.reset_order') }}">
                                <i data-feather="trash-2"></i>
                                <span>{{ __('order.button.reset_order') }}</span>
                            </button>
                            <button type="button" class="booking-pos-add-btn order-add-product btn-main-add-product"
                                @click="openProductPicker($event.currentTarget)"
                                :disabled="!canEditOrderItems()" aria-haspopup="dialog">
                                <i data-feather="plus"></i>
                                <span>{{ __('order.product_picker.add_product') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="booking-pos-items-body">
                        <template x-if="quantityValidationMessage()">
                            <div class="booking-error-summary booking-quantity-error" role="alert"
                                :id="$id('order-quantity-error')">
                                <span x-html="getValidationIcon(quantityValidationMessage())" aria-hidden="true"></span>
                                <span x-text="quantityValidationMessage()"></span>
                            </div>
                        </template>
                        <!-- Empty State -->
                        <template x-if="!dataCart?.length">
                            <div class="booking-pos-items-empty">
                                <div class="empty-icon-box">
                                    <i data-feather="shopping-bag"></i>
                                </div>
                                <h3>{{ __('order.empty_cart.title') }}</h3>
                                <p>{{ __('order.empty_cart.description') }}</p>
                                <button type="button" class="booking-pos-add-btn order-add-product btn-empty-add-product"
                                    @click="openProductPicker($event.currentTarget)"
                                    :disabled="!canEditOrderItems()" aria-haspopup="dialog">
                                    <i data-feather="plus"></i>
                                    <span>{{ __('order.product_picker.add_product') }}</span>
                                </button>
                            </div>
                        </template>

                        <!-- Selected Items Table -->
                        <template x-if="dataCart?.length > 0">
                            <div class="order-items-table-wrap">
                                <table class="order-items-table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="col-product">{{ __('order.table.product') }}</th>
                                            <th scope="col" class="col-stock">{{ __('order.current_stock') }}</th>
                                            <th scope="col" class="col-price">{{ __('order.unit_price') }}</th>
                                            <th scope="col" class="col-discount">{{ __('order.discount') }}</th>
                                            <th scope="col" class="col-qty">{{ __('order.qty') }}</th>
                                            <th scope="col" class="col-total">{{ __('order.total') }}</th>
                                            <th scope="col" class="col-action"><span>{{ __('order.table.actions') }}</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in dataCart" :key="`${item.product_type}-${item.product_id}-${index}`">
                                            <tr class="booking-pos-cart-item" :data-cart-item="catalogKey(item)">
                                                <td class="col-product">
                                                    <div class="product-cell">
                                                        <div class="product-thumb">
                                                            <img :src="item.image" :alt="cartItemDisplayName(item)"
                                                                onerror="this.src='{{ asset('images/logo/default.png') }}'">
                                                        </div>
                                                        <div class="product-details">
                                                            <h4 class="product-name" x-text="cartItemDisplayName(item)"></h4>
                                                            <div class="product-meta">
                                                                <template x-if="item.product_type === 'product'">
                                                                    <span class="item-type-badge item-type-badge--uom">
                                                                        <span class="item-meta-label">{{ __('product.uom') }}:</span>
                                                                        <span x-text="item.uom_title || '—'"></span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="item.product_type === 'product'">
                                                                    <span class="item-type-badge item-type-badge--category">
                                                                        <span class="item-meta-label">{{ __('product.category') }}:</span>
                                                                        <span x-text="item.category_title || '—'"></span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="item.product_type === 'service'">
                                                                    <span class="item-type-badge service" x-text="lblTabService"></span>
                                                                </template>
                                                            </div>
                                                            <template x-if="item?.error">
                                                                <div class="item-stock-error">
                                                                    <i data-feather="alert-triangle"></i>
                                                                    <span>{{ __('order.limited_or_out_of_stock') }}</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="col-stock">
                                                    <div class="cell-label-mobile">{{ __('order.current_stock') }}</div>
                                                    <template x-if="item.product_type === 'product'">
                                                        <span class="item-stock-val"
                                                            :class="{
                                                                'stock-out': Number(item.current_stock ?? 0) <= 0,
                                                                'stock-warning': Number(item.current_stock ?? 0) > 0 && Number(item.current_stock ?? 0) < Number(item.product_qty)
                                                            }"
                                                            x-text="item.current_stock !== undefined && item.current_stock !== null ? item.current_stock : '---'">
                                                        </span>
                                                    </template>
                                                    <template x-if="item.product_type !== 'product'">
                                                        <span class="item-stock-na">---</span>
                                                    </template>
                                                </td>
                                                <td class="col-price">
                                                    <div class="cell-label-mobile">{{ __('order.unit_price') }}</div>
                                                    <template x-if="item.product_type === 'product'">
                                                        <div class="item-price-editor">
                                                            <div class="booking-discount-group">
                                                                <input type="text" inputmode="decimal" data-cart-price
                                                                    x-model.unintrusive="item.itemData.unitPrice"
                                                                    :disabled="!canEditOrderItems()"
                                                                    :aria-invalid="!validProductPrice(item)"
                                                                    aria-label="{{ __('order.unit_price') }} (USD)"
                                                                    @input="priceRealTimeAction(item)"
                                                                    @blur="normalizeUnitPrice(item)">
                                                            </div>
                                                            <small class="item-original-price" data-original-price>
                                                                <span>{{ __('order.original_price') }}:</span>
                                                                <span class="item-original-price-amount" x-text="formatCurrency(item.itemData.originalPrice)"></span>
                                                            </small>
                                                        </div>
                                                    </template>
                                                    <template x-if="item.product_type !== 'product'">
                                                        <span class="item-unit-price" x-text="formatCurrency(item?.itemData?.price || 0)"></span>
                                                    </template>
                                                </td>
                                                <td class="col-discount">
                                                    <div class="cell-label-mobile">{{ __('order.discount') }}</div>
                                                    <template x-if="item.product_type === 'product'">
                                                        <span class="item-unit-price" data-automatic-discount
                                                            x-text="validProductPrice(item) ? formatCurrency(cartUnitDiscount(item)) : '—'"></span>
                                                    </template>
                                                    <template x-if="item.product_type !== 'product'">
                                                        <div class="booking-discount-group">
                                                            <select class="discount-prefix-select"
                                                                x-model="item.itemData.discountType"
                                                                :value="item?.itemData?.discountType"
                                                                :disabled="!canEditOrderItems()"
                                                                @change="discountSelectOpton($event, item)">
                                                                <option value="usd">$</option>
                                                                <option value="percent">%</option>
                                                            </select>
                                                            <input type="number" min="0" step="any" inputmode="decimal"
                                                                x-model.unintrusive="item.itemData.discount"
                                                                :disabled="!canEditOrderItems()"
                                                                x-on:input="discountRealTiemAction(item)">
                                                        </div>
                                                    </template>
                                                </td>
                                                
                                                <td class="col-qty">
                                                    <div class="cell-label-mobile">{{ __('order.qty') }}</div>
                                                    <div class="booking-pos-qty-pill" x-show="item.product_type === 'product'"
                                                        :class="{ 'has-quantity-error': hasQuantityValidationError(item) }"
                                                        aria-label="Quantity selector">
                                                        <button type="button" class="qty-pill-btn qty-btn-minus"
                                                            :disabled="!canEditOrderItems() || Number(item.product_qty) <= 1"
                                                            @click="decreaseCartQty(item)"
                                                            aria-label="Decrease">&minus;</button>
                                                        <input type="number" min="1" step="1" data-cart-qty
                                                            x-model="item.product_qty" :disabled="!canEditOrderItems()"
                                                            :aria-invalid="hasQuantityValidationError(item)"
                                                            :aria-describedby="hasQuantityValidationError(item) ? $id('order-quantity-error') : null"
                                                            x-on:input="qtyRealTimeAction(item)"
                                                            class="qty-pill-input">
                                                        <button type="button" class="qty-pill-btn qty-btn-plus"
                                                            :disabled="!canEditOrderItems()" @click="increaseCartQty(item)"
                                                            aria-label="Increase">+</button>
                                                    </div>
                                                    <div class="booking-pos-service-qty-pill" x-show="item.product_type !== 'product'" style="display: none;">
                                                        <span class="service-qty-label">{{ __('order.qty') }}:</span>
                                                        <span class="service-qty-val" x-text="item.product_qty || 1">1</span>
                                                    </div>
                                                </td>
                                                <td class="col-total">
                                                    <div class="cell-label-mobile">{{ __('order.total') }}</div>
                                                    <strong class="item-line-total" x-text="formatCurrency(cartLineTotal(item))"></strong>
                                                </td>
                                                <td class="col-action">
                                                    <button type="button" class="btn-item-action btn-item-remove"
                                                        @click="removeShippingCart(item, index)"
                                                        title="{{ __('order.action.delete') }}"
                                                        aria-label="{{ __('order.action.delete') }}"
                                                        :disabled="!canEditOrderItems()">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <div class="order-items-footer-bar">
                                    <div class="footer-left">
                                        <button type="button" class="booking-pos-add-btn order-add-product btn-table-add-more"
                                            @click="openProductPicker($event.currentTarget)"
                                            :disabled="!canEditOrderItems()" aria-haspopup="dialog">
                                            <i data-feather="plus"></i>
                                            <span>{{ __('order.product_picker.add_product') }}</span>
                                        </button>
                                    </div>
                                    <div class="footer-right">
                                        <div class="footer-summary-badge">
                                            <span class="label">{{ __('order.subtotal') }}:</span>
                                            <strong class="value" x-text="formatCurrency(subTotal)"></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <!-- Right Sidebar: Cart & Billing -->
            <aside class="booking-pos-sidebar">
                <!-- Sidebar Top: Form Header & Inputs -->
                <div class="booking-pos-sidebar-top">
                    <div class="sidebar-header-row">
                        <div>
                            <h2>{{ $orderPageTitle }}</h2>
                            @if ($orderReference)
                                <span class="booking-ref-badge">{{ $orderReference }}</span>
                            @endif
                        </div>
                        <span class="cart-items-counter">
                            <i data-feather="shopping-bag"></i>
                            <span x-text="(dataCart?.length || 0) + ' ' + (lblItems || 'Items')"></span>
                        </span>
                    </div>

                    <template x-if="validationMessages('details').length">
                        <div class="booking-error-summary">
                            <div class="booking-error-summary-list" style="display: flex;flex-direction: column;grid-gap: 5px;">
                                <template x-for="message in validationMessages('details')" :key="message">
                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                        <span x-html="getValidationIcon(message)" style="display: inline-flex; align-items: center; line-height: 1; margin-top: 3px;"></span>
                                        <span x-text="message"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- <template x-for="item in dataError?.dataCarts">
                        <span class="booking-cart-error" x-text="item"></span>
                    </template> --}}

                    <!-- Compact Customer & Shop Form Grid -->
                    <div class="sidebar-form-card">
                        <div class="sidebar-section-title">
                            <i data-feather="user"></i>
                            <span>{{ __('order.form.customer_shop_info') }}</span>
                        </div>
                        <div class="sidebar-form-grid">
                            <div class="form-group form-group--full">
                                <label><i data-feather="user"></i> {{ __('order.form.customer') }}</label>
                                <div class="select2Group">
                                    <select name="customer_id" class="SelectCustomer" id="customer_id"
                                        x-ref="customerSelect"
                                        :disabled="!canEditOrderItems()"
                                        x-init="fetchSelectCustomer()">
                                        <option value=""> {{ __('order.form.select_customer') }}</option>
                                    </select>
                                    <div class="select2Reset" x-show="(formData?.customer_id || customerData?.id) && canEditOrderItems()" @click.stop="resetCustomer()" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <template x-for="item in dataError?.customer_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group form-group--full" x-show="formData.customer_id || previousBalance > 0" x-cloak>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <label style="margin-bottom: 0; display: inline-flex; align-items: center; gap: 5px;">
                                        <i data-feather="clock" style="width: 14px; height: 14px;"></i> {{ __('order.previous_balance') }}
                                    </label>
                                    <template x-if="previousOrdersCount > 0">
                                        <button type="button" @click="showPreviousBalanceBreakdown = !showPreviousBalanceBreakdown"
                                            class="badge-transfer-toggle"
                                            style="border: none; background: #e0f2fe; color: #0369a1; border-radius: 9999px; font-size: 11px; font-weight: 600; padding: 2px 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <span x-text="previousOrdersCount + ' ' + (previousOrdersCount === 1 ? '{{ __('order.order_singular') }}' : '{{ __('order.orders_plural') }}')"></span>
                                            <span x-text="showPreviousBalanceBreakdown ? '▲' : '▼'" style="font-size: 8px;"></span>
                                        </button>
                                    </template>
                                </div>
                                <div class="booking-input-prefix-group" style="position: relative;">
                                    <span class="input-addon-prefix" style="color: #64748b; font-weight: 600;">$</span>
                                    <input type="text" readonly :value="Number(previousBalance || 0).toFixed(2)"
                                        class="sidebar-date-input"
                                        style="background: #f8fafc; cursor: default; font-weight: 600; color: #0f172a;"
                                        :placeholder="loadingPreviousBalance ? '{{ __('order.calculating') }}...' : '0.00'">
                                    <div x-show="loadingPreviousBalance" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                                        <span class="booking-spinner" style="width: 16px; height: 16px; border-width: 2px;"></span>
                                    </div>
                                </div>
                                <!-- Collapsible breakdown of previous orders -->
                                <div x-show="showPreviousBalanceBreakdown && previousOrders.length > 0" x-transition
                                    style="margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; font-size: 12px;">
                                    <div style="font-weight: 600; color: #475569; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                        <span>{{ __('order.consolidated_orders') }}</span>
                                        <span style="font-size: 11px; color: #64748b;">{{ __('order.unpaid_balance') }}</span>
                                    </div>
                                    <div style="max-height: 160px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;">
                                        <template x-for="pOrder in previousOrders" :key="pOrder.id">
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 6px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px;">
                                                <div>
                                                    <div style="font-weight: 600; color: #1e293b;" x-text="pOrder.invoice_number"></div>
                                                    <div style="font-size: 10.5px; color: #64748b;" x-text="pOrder.order_date"></div>
                                                </div>
                                                <div style="text-align: right;">
                                                    <span style="font-weight: 700; color: #dc2626;" x-text="pOrder.remaining_amount_formatted || pOrder.transferred_amount_formatted || ('$' + Number(pOrder.remaining_amount || pOrder.transferred_amount || 0).toFixed(2))"></span>
                                                    <div style="font-size: 10px; text-transform: uppercase; font-weight: 600; color: #f59e0b;" x-text="pOrder.payment_status"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group form-group--full">
                                <label><i data-feather="home"></i> {{ __('order.form.shop') }}</label>
                                <div class="select2Group">
                                    <select name="shop_id" class="SelectShop" id="shop_id"
                                        x-ref="shopSelect"
                                        :disabled="!canEditOrderItems()"
                                        x-init="fetchSelectShop()">
                                        <option value=""> {{ __('order.form.select_shop') }}</option>
                                    </select>
                                    <div class="select2Reset" x-show="(formData?.shop_id || shopData?.id) && canEditOrderItems()" @click.stop="resetShop()" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <template x-for="item in dataError?.shop_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group form-group--full">
                                <label :for="$id('order-date')"><i data-feather="calendar" aria-hidden="true"></i> {{ __('order.form.order_date') }}</label>
                                <div class="filter-date-input" @click="$refs.orderDate && $refs.orderDate.focus()">
                                    <input type="text" :id="$id('order-date')" x-ref="orderDate" name="order_date" x-model="formData.order_date"
                                        :disabled="!canEditOrderItems()" autocomplete="off" class="sidebar-date-input">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                                <template x-for="item in dataError?.order_date">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group form-group--full">
                                <label :for="$id('delivery-date')"><i data-feather="truck" aria-hidden="true"></i> {{ __('order.form.delivery_date') }}</label>
                                <div class="filter-date-input" @click="$refs.deliveryDate && $refs.deliveryDate.focus()">
                                    <input type="text" :id="$id('delivery-date')" x-ref="deliveryDate" x-model="formData.delivery_date"
                                        :disabled="!canEditOrderItems()" autocomplete="off" class="sidebar-date-input">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                                <template x-for="item in dataError?.delivery_date">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Keep the recent-order reel contained within the main column. */
                    .booking-dashboard.booking-order-ui .booking-pos-recent-section {
                        min-width: 0;
                        max-width: 100%;
                        box-sizing: border-box;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-section-header {
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .booking-order-ui .booking-pos-recent-actions,
                    .booking-order-ui .booking-pos-recent-navigation {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .booking-order-ui .booking-pos-recent-actions {
                        margin-left: auto;
                    }
                    .booking-order-ui .booking-pos-recent-navigation[style*="display: none"] {
                        display: none !important;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-recent-scroll {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        min-width: 32px;
                        height: 32px;
                        padding: 0;
                        border: 1px solid #e2e8f0;
                        border-radius: 9px;
                        background: #fff;
                        color: #475569;
                        cursor: pointer;
                        transition: border-color .2s ease, background-color .2s ease;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-recent-scroll svg {
                        width: 18px;
                        height: 18px;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-recent-scroll:hover:not(:disabled) {
                        border-color: #2563eb;
                        background: #eff6ff;
                        color: #2563eb;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-recent-scroll:disabled {
                        background: #f8fafc !important;
                        color: #94a3b8 !important;
                        opacity: .5;
                        cursor: default !important;
                    }
                    .booking-dashboard.booking-order-ui .booking-pos-recent-section .booking-pos-recent-cards {
                        display: flex;
                        flex-wrap: nowrap;
                        gap: 10px;
                        min-width: 0;
                        max-width: 100%;
                        overflow-x: auto;
                        padding: 3px 2px 10px;
                        scroll-behavior: smooth;
                        scroll-snap-type: x proximity;
                        scroll-padding-inline: 2px;
                        overscroll-behavior-x: contain;
                        scrollbar-width: thin;
                        scrollbar-color: #cbd5e1 transparent;
                    }
                    .booking-order-ui .booking-pos-recent-cards::-webkit-scrollbar {
                        height: 6px;
                    }
                    .booking-order-ui .booking-pos-recent-cards::-webkit-scrollbar-thumb {
                        border-radius: 999px;
                        background: #cbd5e1;
                    }
                    .booking-dashboard.booking-order-ui .booking-pos-recent-section .booking-pos-order-card {
                        flex: 0 0 224px;
                        min-width: 0;
                        max-width: 100%;
                        box-sizing: border-box;
                        scroll-snap-align: start;
                    }
                    .booking-order-ui .booking-pos-recent-section .invoice-num {
                        flex-shrink: 0;
                        max-width: 45%;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-order-date span {
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-order-date svg {
                        flex-shrink: 0;
                    }
                    .booking-order-ui .booking-pos-recent-section .booking-pos-order-empty {
                        flex: 1 1 100%;
                    }
                    .booking-order-ui .booking-pos-recent-section :is(.booking-pos-recent-scroll, .booking-pos-order-card, .booking-pos-recent-cards):focus-visible {
                        outline: 2px solid #2563eb;
                        outline-offset: -2px;
                    }
                    @media (prefers-reduced-motion: reduce) {
                        .booking-dashboard.booking-order-ui .booking-pos-recent-section .booking-pos-recent-cards {
                            scroll-behavior: auto;
                        }
                    }

                    /* Shared visual language for the order items and product picker. */
                    .booking-order-ui .booking-pos-items-section,
                    .order-product-picker {
                        --order-ink: #0f172a;
                        --order-muted: #64748b;
                        --order-border: #e2e8f0;
                        --order-soft: #f8fafc;
                        --order-accent: #2563eb;
                        --order-accent-soft: #eff6ff;
                    }
                    .booking-order-ui .booking-pos-items-section {
                        container: order-items / inline-size;
                        min-width: 0; background: #fff !important;
                        border: 1px solid var(--order-border) !important; border-radius: 16px !important;
                        box-shadow: 0 4px 20px rgba(15, 23, 42, .04) !important;
                        overflow: hidden !important; display: flex !important; flex-direction: column !important;
                    }
                    .booking-order-ui .booking-pos-items-section * { box-sizing: border-box; }
                    .booking-order-ui .booking-pos-items-header {
                        display: flex !important; align-items: center !important; justify-content: space-between !important;
                        flex-wrap: wrap !important; gap: 16px !important; padding: 20px !important;
                        background: #fff !important; border-bottom: 1px solid var(--order-border) !important;
                    }
                    .booking-order-ui .booking-pos-items-header :is(.header-left, .title-wrap, .header-actions) {
                        display: flex !important; align-items: center !important; gap: 10px !important;
                    }
                    .booking-order-ui .booking-pos-items-header .header-left { flex-wrap: wrap; }
                    .booking-order-ui .booking-pos-items-header .title-wrap svg {
                        width: 38px !important; height: 38px !important; padding: 9px; border-radius: 11px;
                        background: var(--order-accent-soft); color: var(--order-accent) !important;
                    }
                    .booking-order-ui .booking-pos-items-header h2 {
                        font-size: 17px !important; font-weight: 700 !important; line-height: 1.4;
                        color: var(--order-ink) !important; margin: 0 !important; letter-spacing: -.3px;
                    }
                    .booking-order-ui .booking-pos-items-header .items-count-badge {
                        padding: 4px 9px !important; border: 1px solid var(--order-border); border-radius: 7px !important;
                        background: var(--order-soft) !important; color: #475569 !important;
                        font-size: 11px !important; font-weight: 600 !important; white-space: nowrap;
                    }
                    .booking-order-ui .booking-pos-items-section :is(.order-add-product, .btn-reset-order-items) {
                        display: inline-flex !important; align-items: center !important; justify-content: center !important;
                        gap: 7px !important; min-height: 43px !important; height: auto !important; min-width: 0 !important;
                        padding: 10px 15px !important; border: 1px solid var(--order-accent) !important; border-radius: 9px !important;
                        background: var(--order-accent) !important; color: #fff !important; box-shadow: 0 2px 4px rgba(37, 99, 235, .12) !important;
                        font-size: 12.5px !important; font-weight: 600 !important; line-height: 1.5 !important;
                        cursor: pointer; width: auto !important; flex-shrink: 0; white-space: nowrap !important;
                        transition: background-color .15s, border-color .15s, box-shadow .15s !important;
                    }
                    .booking-order-ui .booking-pos-items-section .order-add-product:hover:not(:disabled) {
                        background: #1d4ed8 !important; border-color: #1d4ed8 !important;
                        box-shadow: 0 3px 8px rgba(37, 99, 235, .18) !important;
                    }
                    .booking-order-ui .booking-pos-items-section :is(.order-add-product, .btn-reset-order-items) svg {
                        width: 16px !important; height: 16px !important; flex-shrink: 0; color: inherit !important;
                    }
                    .booking-order-ui .booking-pos-items-section .btn-reset-order-items {
                        background: #fff !important; border-color: var(--order-border) !important; color: #64748b !important; box-shadow: none !important;
                    }
                    .booking-order-ui .booking-pos-items-section .btn-reset-order-items:hover:not(:disabled) {
                        background: #fff1f2 !important; color: #be123c !important; border-color: #fecdd3 !important;
                    }
                    .booking-order-ui .booking-pos-items-section button:disabled {
                        opacity: .45 !important; cursor: not-allowed !important; box-shadow: none !important; transform: none !important;
                    }
                    .booking-order-ui .booking-pos-items-section button:focus-visible {
                        outline: 2px solid var(--order-accent) !important; outline-offset: 3px;
                    }
                    .form-admin.booking.booking-dashboard.booking-order-ui .booking-pos-items-section :is(button, button span) {
                        line-height: 1.5 !important;
                    }
                    .booking-order-ui .booking-pos-items-section .booking-quantity-error {
                        display: flex; align-items: flex-start; gap: 6px; margin: 16px 20px;
                    }
                    .booking-order-ui .booking-quantity-error > span:first-child {
                        display: inline-flex; flex-shrink: 0; margin-top: 2px;
                    }
                    .booking-order-ui .booking-pos-items-empty {
                        display: flex !important; flex-direction: column !important; align-items: center !important;
                        justify-content: center !important; text-align: center !important; gap: 0;
                        margin: 20px; padding: 48px 24px !important; border: 1px dashed #cbd5e1; border-radius: 12px;
                        background: var(--order-soft) !important;
                    }
                    .booking-order-ui .booking-pos-items-empty .empty-icon-box {
                        display: grid !important; place-items: center; width: 64px; height: 64px; margin-bottom: 18px;
                        border: 1px solid #dbeafe; border-radius: 18px; background: #fff; box-shadow: 0 3px 10px rgba(37, 99, 235, .06);
                    }
                    .booking-order-ui .booking-pos-items-empty .empty-icon-box svg { width: 28px; height: 28px; color: var(--order-accent); }
                    .booking-order-ui .booking-pos-items-empty h3 { margin: 0 0 8px !important; font-size: 16px !important; font-weight: 700; color: var(--order-ink); }
                    .booking-order-ui .booking-pos-items-empty p { max-width: 340px; margin: 0 0 24px !important; font-size: 13px; line-height: 1.7; color: var(--order-muted); }
                    .booking-order-ui .order-items-table-wrap { width: 100%; min-width: 0; overflow-x: auto; background: #fff; }
                    .booking-order-ui .order-items-table {
                        width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important;
                        border-spacing: 0 !important; text-align: left !important; margin: 0 !important;
                    }
                    .booking-order-ui .order-items-table thead { display: table-header-group !important; }
                    .booking-order-ui .order-items-table thead tr { display: table-row !important; }
                    .booking-order-ui .order-items-table thead th {
                        display: table-cell !important; padding: 13px 12px !important; background: var(--order-soft) !important;
                        border: 0 !important; border-bottom: 1px solid var(--order-border) !important;
                        font-size: 11px !important; font-weight: 600 !important; color: var(--order-muted) !important;
                        line-height: 1.5; vertical-align: middle !important; white-space: normal !important;
                    }
                    .booking-order-ui .order-items-table tbody { display: table-row-group !important; }
                    .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item {
                        display: table-row !important; padding: 0 !important; margin: 0 !important; gap: 0 !important;
                        border: 0 !important; border-radius: 0 !important; background: #fff !important; box-shadow: none !important;
                        transition: background-color .15s !important;
                    }
                    .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item:is(:hover, :focus-within) { background: #f8fbff !important; }
                    .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item > td {
                        display: table-cell !important; padding: 18px 12px !important; border: 0 !important;
                        border-bottom: 1px solid #edf2f7 !important; vertical-align: middle !important; background: transparent !important;
                    }
                    .booking-order-ui .order-items-table :is(th, td).col-product { width: auto !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-stock { width: 100px !important; text-align: center !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-price { width: 130px !important; text-align: right !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-discount { width: 100px !important;text-align: center !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-qty { width: 132px !important; text-align: center !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-total { width: 110px !important; text-align: right !important; }
                    .booking-order-ui .order-items-table :is(th, td).col-action { width: 64px !important; text-align: center !important; }
                    .booking-order-ui .order-items-table .cell-label-mobile { display: none; }
                    .booking-order-ui .order-items-table .product-cell { display: flex; align-items: center; gap: 12px; min-width: 0; }
                    .booking-order-ui .order-items-table .product-thumb {
                        display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; flex-shrink: 0;
                        overflow: hidden; border-radius: 10px; background: var(--order-soft); border: 1px solid var(--order-border);
                    }
                    .booking-order-ui .order-items-table .product-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
                    .booking-order-ui .order-items-table .product-details { display: flex; flex-direction: column; gap: 6px; min-width: 0; flex: 1; }
                    .booking-order-ui .order-items-table .product-name {
                        margin: 0 !important; font-size: 13px !important; font-weight: 600 !important; color: var(--order-ink) !important;
                        line-height: 1.6 !important; white-space: normal !important; overflow-wrap: anywhere;
                    }
                    .booking-order-ui .order-items-table .product-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
                    .booking-order-ui .order-items-table .item-type-badge {
                        display: inline-flex; align-items: baseline; gap: 4px; padding: 2px 7px; border-radius: 5px;
                        font-size: 10px; font-weight: 600; line-height: 1.5; width: fit-content; max-width: 100%;
                        min-width: 0; overflow-wrap: anywhere;
                    }
                    .booking-order-ui .order-items-table .item-meta-label { flex-shrink: 0; font-weight: 500; }
                    .booking-order-ui .order-items-table .item-type-badge--uom { background: var(--order-accent-soft); color: #1d4ed8; }
                    .booking-order-ui .order-items-table .item-type-badge--category { background: #f0fdfa; color: #0f766e; }
                    .booking-order-ui .order-items-table .item-type-badge.service { background: #f0fdfa; color: #0f766e; }
                    .booking-order-ui .order-items-table .item-stock-error { display: flex; align-items: center; gap: 5px; color: #be123c; font-size: 11px; line-height: 1.5; }
                    .booking-order-ui .order-items-table .item-stock-error svg { width: 14px; height: 14px; flex-shrink: 0; }
                    .booking-order-ui .order-items-table :is(.item-unit-price, .item-line-total, .item-stock-val) { font-variant-numeric: tabular-nums; }
                    .booking-order-ui .order-items-table .item-unit-price { font-size: 13px; font-weight: 500; color: #475569; overflow-wrap: anywhere; }
                    .booking-order-ui .order-items-table .item-line-total { display: block; font-size: 14px; font-weight: 700; color: var(--order-ink); overflow-wrap: anywhere; }
                    .booking-order-ui .order-items-table .item-stock-val {
                        display: inline-block; padding: 4px 9px; border: 1px solid var(--order-border); border-radius: 6px;
                        background: var(--order-soft); color: #475569; font-size: 12px; font-weight: 600;
                    }
                    .booking-order-ui .order-items-table .item-stock-val.stock-out { background: #fff1f2; border-color: #ffe4e6; color: #be123c; }
                    .booking-order-ui .order-items-table .item-stock-val.stock-warning { background: #fffbeb; border-color: #fef3c7; color: #b45309; }
                    .booking-order-ui .order-items-table .item-stock-na { color: #94a3b8; font-size: 12px; }
                    .booking-order-ui .order-items-table .btn-item-remove {
                        display: inline-flex !important; align-items: center !important; justify-content: center !important;
                        width: 36px !important; height: 36px !important; min-width: 0 !important; padding: 0 !important;
                        border: 1px solid var(--order-border) !important; border-radius: 8px !important;
                        background: #fff !important; color: #94a3b8 !important; cursor: pointer; box-shadow: none !important;
                        transition: color .15s, background-color .15s, border-color .15s !important;
                    }
                    .booking-order-ui .order-items-table .btn-item-remove:hover:not(:disabled) { background: #fff1f2 !important; border-color: #fecdd3 !important; color: #be123c !important; }
                    .booking-order-ui .order-items-table .btn-item-remove svg { width: 16px !important; height: 16px !important; color: inherit !important; }
                    .booking-order-ui .order-items-table :is(.booking-discount-group, .booking-pos-qty-pill) {
                        display: inline-flex !important; align-items: stretch !important; height: 33px !important;
                        border: 1px solid #cbd5e1 !important; border-radius: 7px !important; background: #fff !important;
                        overflow: hidden; transition: border-color .15s, box-shadow .15s !important;
                    }
                    .booking-order-ui .order-items-table .booking-discount-group { width: 100%; }
                    .booking-order-ui .order-items-table :is(.booking-discount-group, .booking-pos-qty-pill):focus-within {
                        border-color: var(--order-accent) !important; box-shadow: 0 0 0 3px rgba(37, 99, 235, .1) !important;
                    }
                    .booking-order-ui .order-items-table .booking-pos-qty-pill.has-quantity-error {
                        border-color: #ef4444 !important; box-shadow: 0 0 0 2px rgba(239, 68, 68, .12) !important;
                    }
                    .booking-order-ui .order-items-table .discount-prefix-select {
                        width: 38px !important; min-width: 38px; height: 100% !important; margin: 0 !important;
                        padding: 0 2px 0 5px !important; border: 0 !important; border-right: 1px solid var(--order-border) !important;
                        border-radius: 0 !important; background: var(--order-soft) !important; color: #475569 !important;
                        font: inherit; font-size: 12px !important; font-weight: 600; outline: none !important; cursor: pointer;
                    }
                    .booking-order-ui .order-items-table :is(.booking-discount-group input, .qty-pill-input) {
                        min-width: 0 !important; height: 100% !important; padding: 0 6px !important; margin: 0 !important;
                        border: 0 !important; border-radius: 0 !important; outline: none !important; box-shadow: none !important;
                        background: transparent !important; color: var(--order-ink) !important; font-size: 13px !important;
                        font-weight: 600; font-variant-numeric: tabular-nums; -moz-appearance: textfield;
                    }
                    .booking-order-ui .order-items-table .booking-discount-group input { width: 100%; flex: 1; }
                    .booking-order-ui .order-items-table .item-price-editor {
                        display: flex; flex-direction: column; gap: 6px; width: 100%; min-width: 0;position: relative;
                    }
                    .booking-order-ui .order-items-table .item-price-editor input[data-cart-price] {
                        padding-inline: 8px !important; text-align: right !important;
                    }
                    .booking-order-ui .order-items-table .item-original-price {
                        display: flex; align-items: baseline; justify-content: flex-end; flex-wrap: wrap; gap: 2px 4px;
                        margin: 0; font-size: 10.5px; font-weight: 500; line-height: 1.5; color: var(--order-muted);
                        position:absolute;bottom: -20px;right: 0;
                    }
                    .booking-order-ui .order-items-table .item-original-price > span { white-space: nowrap; }
                    .booking-order-ui .order-items-table .item-original-price-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
                    .booking-order-ui .order-items-table input::-webkit-inner-spin-button,
                    .booking-order-ui .order-items-table input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
                    .booking-order-ui .order-items-table .booking-pos-qty-pill .qty-pill-btn {
                        display: inline-flex !important; align-items: center !important; justify-content: center !important;
                        width: 32px !important; height: 100% !important; min-width: 0 !important; padding: 0 !important; margin: 0 !important;
                        border: 0 !important; border-radius: 0 !important; background: var(--order-soft) !important; color: #475569 !important;
                        font-size: 18px !important; font-weight: 500; cursor: pointer; box-shadow: none !important; line-height: 1;
                    }
                    .booking-order-ui .order-items-table .booking-pos-qty-pill .qty-pill-btn:hover:not(:disabled) { background: var(--order-accent-soft) !important; color: var(--order-accent) !important; }
                    .booking-order-ui .order-items-table .booking-pos-qty-pill .qty-pill-input { width: 40px !important; text-align: center !important; }
                    .booking-order-ui .order-items-table .booking-pos-service-qty-pill { display: inline-flex; gap: 5px; padding: 6px 10px; border-radius: 7px; background: var(--order-soft); color: var(--order-muted); font-size: 12px; }
                    .booking-order-ui .order-items-table .service-qty-val { color: var(--order-ink); font-weight: 600; }
                    .booking-order-ui .order-items-table :is(.booking-pos-service-qty-pill, .booking-pos-qty-pill)[style*="display: none"] { display: none !important; }
                    .booking-order-ui .order-items-table :is(input, select):disabled { color: #94a3b8 !important; cursor: not-allowed; }
                    .booking-order-ui .order-items-footer-bar {
                        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
                        padding: 18px 20px; background: var(--order-soft); border-top: 1px solid var(--order-border);
                    }
                    .booking-order-ui .order-items-footer-bar .btn-table-add-more { background: #fff !important; color: var(--order-accent) !important; border-color: #bfdbfe !important; box-shadow: none !important; }
                    .booking-order-ui .order-items-footer-bar .btn-table-add-more:hover:not(:disabled) { color: #fff !important; }
                    .booking-order-ui .order-items-footer-bar .footer-summary-badge { display: flex; align-items: center; gap: 14px; color: var(--order-muted); font-size: 12px; }
                    .booking-order-ui .order-items-footer-bar .value { font-size: 20px; font-weight: 700; letter-spacing: -.4px; color: var(--order-ink); font-variant-numeric: tabular-nums; }
                    @container order-items (max-width: 780px) {
                        .booking-order-ui .order-items-table thead { display: none !important; }
                        .booking-order-ui .order-items-table tbody { display: grid !important; gap: 12px; padding: 16px; background: var(--order-soft); }
                        .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item {
                            display: grid !important; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 16px 12px !important;
                            padding: 16px !important; border: 1px solid var(--order-border) !important; border-radius: 12px !important;
                        }
                        .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item > td {
                            display: block !important; width: auto !important; min-width: 0 !important; max-width: none !important;
                            padding: 0 !important; border: 0 !important; text-align: left !important;
                        }
                        .booking-order-ui .order-items-table td.col-product { grid-column: 1 / 6; grid-row: 1; }
                        .booking-order-ui .order-items-table td.col-action { grid-column: 6; grid-row: 1; justify-self: end; }
                        .booking-order-ui .order-items-table td.col-stock { grid-column: 1 / 3; grid-row: 2; }
                        .booking-order-ui .order-items-table td.col-price { grid-column: 3 / 5; grid-row: 2; }
                        .booking-order-ui .order-items-table .item-original-price { justify-content: flex-start; }
                        .booking-order-ui .order-items-table td.col-total { grid-column: 5 / 7; grid-row: 2; }
                        .booking-order-ui .order-items-table td.col-discount { grid-column: 1 / 4; grid-row: 3; }
                        .booking-order-ui .order-items-table td.col-qty { grid-column: 4 / 7; grid-row: 3; }
                        .booking-order-ui .order-items-table .cell-label-mobile { display: block; margin-bottom: 6px; font-size: 10.5px; font-weight: 500; line-height: 1.5; color: var(--order-muted); }
                        .booking-order-ui .order-items-table .booking-pos-qty-pill { width: 100%; }
                        .booking-order-ui .order-items-table .booking-pos-qty-pill .qty-pill-input { flex: 1; }
                        .booking-order-ui .order-items-table .booking-pos-qty-pill .qty-pill-btn { width: 38px !important; }
                        .booking-order-ui .order-items-table .btn-item-remove { width: 40px !important; height: 40px !important; }
                    }
                    @container order-items (max-width: 480px) {
                        .booking-order-ui .booking-pos-items-header { padding: 16px !important; gap: 14px !important; }
                        .booking-order-ui .booking-pos-items-header .header-actions { width: 100%; display: grid !important; grid-template-columns: 1fr 1fr; align-items: stretch !important; }
                        .booking-order-ui .booking-pos-items-header .header-actions button { width: 100% !important; white-space: normal !important; }
                        .booking-order-ui .order-items-table tbody { padding: 12px; }
                        .booking-order-ui .order-items-table tbody tr.booking-pos-cart-item { padding: 12px !important; }
                        .booking-order-ui .order-items-table .item-original-price { flex-direction: column; align-items: flex-start; }
                        .booking-order-ui .booking-pos-items-empty { margin: 12px; padding: 32px 16px !important; }
                        .booking-order-ui .order-items-footer-bar { padding: 14px 16px; gap: 12px; }
                        .booking-order-ui .order-items-footer-bar .footer-summary-badge { gap: 8px; }
                        .booking-order-ui .order-items-footer-bar .value { font-size: 17px; }
                    }
                    /* Let the item list use the available width before the billing column stacks. */
                    @media (max-width: 1050px) {
                        .booking-order-ui .booking-pos-workspace { grid-template-columns: minmax(0, 1fr) !important; width: calc(100% - 32px) !important; margin: 0 16px !important; }
                        .booking-order-ui .booking-pos-header { padding-left: 0 !important; padding-right: 0 !important; }
                        .booking-order-ui .booking-pos-main-col { padding: 0 !important; min-width: 0; }
                        .booking-order-ui .booking-pos-main-col.is-sticky-column,
                        .booking-order-ui .booking-pos-sidebar.is-sticky-column { position: static !important; }
                    }
                    @media (prefers-reduced-motion: reduce) {
                        .booking-order-ui .booking-pos-items-section * { transition: none !important; }
                    }

                    /* Input Prefix Group */
                    .booking-input-prefix-group {
                        display: flex !important;
                        align-items: stretch !important;
                        width: 100% !important;
                        /* height: 34px !important; */
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 7px !important;
                        background: #ffffff !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }

                    .booking-input-prefix-group:focus-within {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }

                    .booking-input-prefix-group .input-addon-prefix {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        padding: 0 8px !important;
                        font-size: 12px !important;
                        font-weight: 700 !important;
                        color: #64748b !important;
                        background: #f8fafc !important;
                        border: none !important;
                        border-right: 1px solid #e2e8f0 !important;
                        flex-shrink: 0 !important;
                        user-select: none !important;
                        min-width: 24px !important;
                        line-height: 15px !important;
                        /* height: 100% !important; */
                    }

                    .booking-input-prefix-group input {
                        flex: 1 !important;
                        min-width: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        min-height: 0 !important;
                        border: none !important;
                        outline: none !important;
                        padding: 0 8px !important;
                        font-size: 12.5px !important;
                        font-weight: 600 !important;
                        color: #0f172a !important;
                        background: transparent !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        -moz-appearance: textfield !important;
                    }

                    .booking-input-prefix-group input::-webkit-outer-spin-button,
                    .booking-input-prefix-group input::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }

                    /* Compact Customer & Shop Form Controls */
                    .sidebar-form-card {
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 12px 14px !important;
                        margin-bottom: 12px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                    }
                    .sidebar-section-title {
                        display: flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        font-size: 11.5px !important;
                        font-weight: 700 !important;
                        color: #475569 !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.5px !important;
                        margin-bottom: 10px !important;
                    }
                    .sidebar-section-title svg {
                        width: 14px !important;
                        height: 14px !important;
                        color: #2563eb !important;
                    }
                    .sidebar-form-grid {
                        display: grid !important;
                        grid-template-columns: 1fr 1fr !important;
                        gap: 8px !important;
                    }
                    .sidebar-form-grid .form-group--full {
                        grid-column: 1 / -1 !important;
                    }
                    .sidebar-form-grid label {
                        display: flex !important;
                        align-items: center !important;
                        gap: 4px !important;
                        font-size: 11px !important;
                        font-weight: 600 !important;
                        color: #64748b !important;
                        margin-bottom: 3px !important;
                    }
                    .sidebar-form-grid label svg {
                        width: 12px !important;
                        height: 12px !important;
                    }
                    .sidebar-form-grid .select2Group {
                        width: 100% !important;
                        position: relative !important;
                    }
                    .sidebar-form-grid .select2Group select,
                    .sidebar-form-grid .SelectShop,
                    .sidebar-form-grid .SelectCustomer,
                    .booking-select,
                    .sidebar-date-input {
                        width: 100% !important;
                        height: 43px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 7px !important;
                        padding: 0 10px !important;
                        font-size: 12.5px !important;
                        font-weight: 500 !important;
                        color: #0f172a !important;
                        background: #ffffff !important;
                        outline: none !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .sidebar-form-grid .select2Group .select2.select2-container {
                        width: 100% !important;
                    }
                    .sidebar-form-grid .select2Group .select2-container .select2-selection--single {
                        height: 43px !important;
                        border-radius: 7px !important;
                        border: 1px solid #cbd5e1 !important;
                        background-color: #ffffff !important;
                        display: flex !important;
                        align-items: center !important;
                        padding: 0 10px !important;
                        box-shadow: none !important;
                    }
                    .sidebar-form-grid .select2Group .select2-container--default .select2-selection--single .select2-selection__rendered {
                        color: #334155 !important;
                        font-size: 12.5px !important;
                        font-weight: 500 !important;
                        line-height: normal !important;
                        padding-left: 0 !important;
                        padding-right: 28px !important;
                    }
                    .sidebar-form-grid .select2Group .select2-container--default .select2-selection--single .select2-selection__arrow {
                        height: 100% !important;
                        top: 0 !important;
                        right: 8px !important;
                    }
                    .sidebar-form-grid .select2Group .select2Reset {
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
                    .sidebar-form-grid .select2Group .select2Reset[style*="display: none"],
                    .sidebar-form-grid .select2Group .select2Reset[style*="display:none"],
                    .sidebar-form-grid .select2Group .select2Reset[x-cloak] {
                        display: none !important;
                    }
                    .sidebar-form-grid .select2-container--default .select2-selection--single .select2-selection__clear {
                        display: none !important;
                    }
                    .sidebar-form-grid .select2Group .select2Reset svg {
                        fill: #94a3b8 !important;
                        width: 18px !important;
                        height: 18px !important;
                        background: #ffffff !important;
                        border-radius: 50% !important;
                        transition: fill 0.15s ease !important;
                    }
                    .sidebar-form-grid .select2Group .select2Reset:hover svg {
                        fill: #ef4444 !important;
                    }
                    .booking-select:focus,
                    .sidebar-date-input:focus {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }

                    /* Sidebar Datepicker Input Wrappers with Calendar Icon */
                    .sidebar-form-grid .filter-date-input {
                        position: relative !important;
                        cursor: pointer;
                        width: 100%;
                    }
                    .sidebar-form-grid .filter-date-input input.sidebar-date-input {
                        padding-right: 36px !important;
                        cursor: pointer;
                    }
                    .sidebar-form-grid .filter-date-input i.bx-calendar {
                        position: absolute !important;
                        right: 12px !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        font-size: 18px !important;
                        color: #64748b !important;
                        pointer-events: none !important;
                        line-height: 1 !important;
                    }
                    .sidebar-form-grid .filter-date-input:hover i.bx-calendar {
                        color: #334155 !important;
                    }
                    .sidebar-form-grid .filter-date-input input:disabled {
                        cursor: not-allowed !important;
                    }
                    .sidebar-form-grid .filter-date-input input:disabled ~ i.bx-calendar {
                        color: #94a3b8 !important;
                    }

                    /* Payment Ledger Card */
                    .booking-pos-sidebar-bottom .booking-payment-ledger {
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 14px !important;
                        margin-top: 14px !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 12px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                    }
                    .booking-payment-progress-wrapper {
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 8px !important;
                    }
                    .booking-payment-progress-head {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                    }
                    .booking-payment-progress-head strong {
                        font-size: 15px !important;
                        font-weight: 800 !important;
                        color: #0f172a !important;
                    }
                    .booking-payment-progress-bar {
                        width: 100% !important;
                        height: 6px !important;
                        background: #e2e8f0 !important;
                        border-radius: 9999px !important;
                        overflow: hidden !important;
                    }
                    .booking-payment-progress-fill {
                        height: 100% !important;
                        border-radius: 9999px !important;
                        background: #2563eb !important;
                        transition: width 0.3s ease !important;
                    }
                    .booking-payment-progress-fill.is-paid { background: #16a34a !important; }
                    .booking-payment-progress-fill.is-partial { background: #2563eb !important; }
                    .booking-payment-progress-fill.is-waiting,
                    .booking-payment-progress-fill.is-pending { background: #f59e0b !important; }
                    .booking-payment-progress-fill.is-cancel,
                    .booking-payment-progress-fill.is-canceled { background: #ef4444 !important; }

                    .booking-payment-ledger-grid {
                        display: grid !important;
                        grid-template-columns: repeat(3, 1fr) !important;
                        gap: 8px !important;
                        padding-top: 8px !important;
                        border-top: 1px solid #f1f5f9 !important;
                    }
                    .booking-payment-ledger-card {
                        display: flex !important;
                        flex-direction: column !important;
                        align-items: center !important;
                        justify-content: center !important;
                        padding: 8px 4px !important;
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 8px !important;
                        text-align: center !important;
                        gap: 3px !important;
                    }
                    .booking-payment-ledger-card .label {
                        font-size: 10px !important;
                        font-weight: 700 !important;
                        color: #64748b !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.4px !important;
                    }
                    .booking-payment-ledger-card .val {
                        font-size: 13.5px !important;
                        font-weight: 800 !important;
                        line-height: 1.2 !important;
                    }
                    .booking-payment-ledger-card .val--paid { color: #16a34a !important; }
                    .booking-payment-ledger-card .val--remaining { color: #0f172a !important; }

                    .booking-payment-status-pill {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 4px !important;
                        padding: 3px 8px !important;
                        border-radius: 9999px !important;
                        font-size: 11px !important;
                        font-weight: 700 !important;
                        line-height: 1.2 !important;
                    }
                    .booking-payment-status-pill .status-dot {
                        width: 6px !important;
                        height: 6px !important;
                        border-radius: 50% !important;
                        display: inline-block !important;
                        flex-shrink: 0 !important;
                    }
                    .booking-payment-status-pill.is-paid { background: #dcfce7 !important; color: #15803d !important; }
                    .booking-payment-status-pill.is-paid .status-dot { background: #16a34a !important; }
                    .booking-payment-status-pill.is-partial { background: #eff6ff !important; color: #2563eb !important; }
                    .booking-payment-status-pill.is-partial .status-dot { background: #2563eb !important; }
                    .booking-payment-status-pill.is-waiting,
                    .booking-payment-status-pill.is-pending { background: #fef3c7 !important; color: #b45309 !important; }
                    .booking-payment-status-pill.is-waiting .status-dot,
                    .booking-payment-status-pill.is-pending .status-dot { background: #f59e0b !important; }
                    .booking-payment-status-pill.is-cancel,
                    .booking-payment-status-pill.is-canceled { background: #fee2e2 !important; color: #b91c1c !important; }
                    .booking-payment-status-pill.is-cancel .status-dot,
                    .booking-payment-status-pill.is-canceled .status-dot { background: #ef4444 !important; }

                    /* Record Payment Card */
                    .booking-payment-form--record {
                        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 13px 14px 14px 14px !important;
                        margin-top: 14px !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 12px !important;
                        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05) !important;
                    }
                    .booking-payment-form-header {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        gap: 8px !important;
                    }
                    .booking-payment-form-title {
                        display: flex !important;
                        align-items: center !important;
                        gap: 8px !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: none !important;
                    }
                    .booking-payment-title-icon {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 24px !important;
                        height: 24px !important;
                        background: #ecfdf5 !important;
                        border: 1px solid #a7f3d0 !important;
                        border-radius: 6px !important;
                        flex-shrink: 0 !important;
                    }
                    .booking-payment-title-icon svg {
                        width: 14px !important;
                        height: 14px !important;
                        color: #059669 !important;
                    }
                    .booking-pay-full-btn {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 4px !important;
                        padding: 4px 9px !important;
                        background: #ecfdf5 !important;
                        border: 1px solid #a7f3d0 !important;
                        border-radius: 6px !important;
                        color: #059669 !important;
                        font-size: 11px !important;
                        font-weight: 600 !important;
                        cursor: pointer !important;
                        line-height: 1 !important;
                        transition: all 0.15s ease !important;
                    }
                    .booking-pay-full-btn svg {
                        width: 11px !important;
                        height: 11px !important;
                        color: #059669 !important;
                        flex-shrink: 0 !important;
                    }
                    .booking-pay-full-btn strong {
                        font-weight: 800 !important;
                        color: #047857 !important;
                    }
                    .booking-pay-full-btn:hover {
                        background: #d1fae5 !important;
                        border-color: #6ee7b7 !important;
                        color: #047857 !important;
                        transform: translateY(-1px) !important;
                        box-shadow: 0 2px 5px rgba(5, 150, 105, 0.15) !important;
                    }
                    .booking-pay-full-btn:active {
                        transform: translateY(0) !important;
                    }
                    .booking-payment-form-grid {
                        display: grid !important;
                        grid-template-columns: 1fr 1fr !important;
                        gap: 10px !important;
                    }
                    .booking-payment-form-grid .form-group {
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 4px !important;
                        margin: 0 !important;
                    }
                    .booking-payment-form-grid label {
                        font-size: 11px !important;
                        font-weight: 600 !important;
                        color: #475569 !important;
                        margin: 0 !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group {
                        display: flex !important;
                        align-items: stretch !important;
                        width: 100% !important;
                        height: 43px !important;
                        min-height: 38px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 8px !important;
                        background: #ffffff !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group:focus-within {
                        border-color: #10b981 !important;
                        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.16) !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group .input-addon-prefix {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        padding: 0 10px !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #059669 !important;
                        background: #f8fafc !important;
                        border: none !important;
                        border-right: 1px solid #e2e8f0 !important;
                        flex-shrink: 0 !important;
                        user-select: none !important;
                        min-width: 32px !important;
                        height: 100% !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group input {
                        flex: 1 !important;
                        min-width: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        min-height: 100% !important;
                        border: none !important;
                        outline: none !important;
                        padding: 0 10px !important;
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        background: transparent !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        -moz-appearance: textfield !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group input::-webkit-outer-spin-button,
                    .booking-payment-form--record .booking-input-prefix-group input::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }
                    .booking-payment-form--record .booking-note-input {
                        width: 100% !important;
                        height: 43px !important;
                        min-height: 38px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 8px !important;
                        padding: 0 10px !important;
                        font-size: 13px !important;
                        color: #0f172a !important;
                        background: #ffffff !important;
                        outline: none !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-payment-form--record .booking-note-input:focus {
                        border-color: #10b981 !important;
                        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.16) !important;
                    }

                    /* Record Payment Button */
                    .booking-pos-pay-btn--record {
                        width: 100% !important;
                        height: 40px !important;
                        min-height: 40px !important;
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                        border: 1px solid #059669 !important;
                        border-radius: 8px !important;
                        color: #ffffff !important;
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 7px !important;
                        cursor: pointer !important;
                        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25) !important;
                        transition: all 0.15s ease !important;
                        padding: 0 14px !important;
                        margin-top: 2px !important;
                    }
                    .booking-pos-pay-btn--record span {
                        color: #ffffff !important;
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                    }
                    .booking-pos-pay-btn--record svg {
                        width: 15px !important;
                        height: 15px !important;
                        color: #ffffff !important;
                        stroke-width: 2.5 !important;
                    }
                    .booking-pos-pay-btn--record:hover:not(:disabled) {
                        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
                        border-color: #047857 !important;
                        transform: translateY(-1px) !important;
                        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35) !important;
                    }
                    .booking-pos-pay-btn--record:active:not(:disabled) {
                        transform: translateY(0) !important;
                        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.2) !important;
                    }
                    .booking-pos-pay-btn--record:disabled {
                        opacity: 0.55 !important;
                        cursor: not-allowed !important;
                        transform: none !important;
                        box-shadow: none !important;
                    }

                    /* Primary Submit CTA Button */
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn {
                        margin-top: 14px !important;
                        height: 48px !important;
                        width: 100% !important;
                        border-radius: 7px !important;
                        background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
                        border: none !important;
                        color: #ffffff !important;
                        font-size: 15px !important;
                        font-weight: 700 !important;
                        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3) !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 8px !important;
                        cursor: pointer !important;
                        transition: all 0.2s ease !important;
                    }
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn span {
                        color: #ffffff !important;
                        font-size: 15px !important;
                        font-weight: 700 !important;
                    }
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn svg {
                        width: 16px !important;
                        height: 16px !important;
                        color: #ffffff !important;
                        stroke-width: 2.5 !important;
                    }
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn:hover:not(:disabled) {
                        background: linear-gradient(135deg, #1d4ed8, #1e40af) !important;
                        transform: translateY(-1px) !important;
                        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4) !important;
                    }
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn:disabled {
                        opacity: 0.55 !important;
                        cursor: not-allowed !important;
                        box-shadow: none !important;
                        transform: none !important;
                    }


                    /* ===================================================
                       Two-Column Scroll & Dynamic Sticky Optimization
                       =================================================== */
                    @media screen {
                        /* Keep document scrolling when a detail tab's styles are still loaded. */
                        #content:has(.workspace-tab-pane.active .booking-order-ui) {
                            height: auto !important;
                            max-height: none !important;
                            overflow: visible !important;
                        }
                    }

                    .form-admin.booking.booking-order-ui,
                    .form-admin.booking-order-ui,
                    .booking-dashboard.booking-order-ui,
                    .booking-order-ui {
                        height: auto !important;
                        min-height: 100% !important;
                        max-height: none !important;
                        overflow: visible !important;
                        overflow-y: visible !important;
                        overflow-x: visible !important;
                    }

                    .booking-pos-workspace {
                        display: grid !important;
                        grid-template-columns: minmax(0, 1fr) 400px !important;
                        align-items: start !important;
                        overflow: visible !important;
                        height: auto !important;
                        min-height: 0 !important;
                        padding-bottom: 32px !important;
                    }

                    .booking-order-ui .booking-pos-header {
                        grid-column: 1 / -1 !important;
                        width: 100% !important;
                    }

                    .booking-order-ui .booking-transferred-banner {
                        grid-column: 1 / -1 !important;
                        width: 100% !important;
                        box-sizing: border-box !important;
                    }

                    /* Left Column: Catalog & Recent Bookings */
                    .booking-pos-main-col {
                        height: fit-content !important;
                        min-height: auto !important;
                        overflow: visible !important;
                        align-self: start !important;
                        transition: top 0.2s ease;
                    }
                    .booking-pos-items-section {
                        height: auto !important;
                        min-height: auto !important;
                        overflow: visible !important;
                    }

                    /* Right Sidebar: Cart & Billing */
                    .booking-pos-sidebar {
                        height: fit-content !important;
                        min-height: auto !important;
                        overflow: visible !important;
                        align-self: start !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 14px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                        background: #ffffff !important;
                        transition: top 0.2s ease;
                    }

                    .booking-pos-sidebar-top {
                        border-top-left-radius: 14px !important;
                        border-top-right-radius: 14px !important;
                    }

                    .booking-pos-sidebar-bottom {
                        border-bottom-left-radius: 14px !important;
                        border-bottom-right-radius: 14px !important;
                    }

                    /* Dynamic Sticky Class - Applied strictly to the shorter column */
                    .booking-pos-main-col.is-sticky-column,
                    .booking-pos-sidebar.is-sticky-column {
                        position: -webkit-sticky !important;
                        position: sticky !important;
                        top: var(--sticky-top, 60px) !important;
                        align-self: start !important;
                        z-index: 10;
                    }
                </style>



                <!-- Sidebar Bottom: Payment & Checkout -->
                <div class="booking-pos-sidebar-bottom">
                    <!-- Payment Methods Segmented Control -->
                    <div class="payment-methods-row">
                        <label class="payment-method-btn" :class="formData.pay_way === 'Cash' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="Cash" x-model="formData.pay_way" :disabled="!canEditOrderItems()">
                            <i data-feather="dollar-sign"></i>
                            <span>{{ __('order.payment.cash') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'ABA' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="ABA" x-model="formData.pay_way" :disabled="!canEditOrderItems()">
                            <i data-feather="smartphone"></i>
                            <span>{{ __('order.payment.aba') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'Card' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="Card" x-model="formData.pay_way" :disabled="!canEditOrderItems()">
                            <i data-feather="credit-card"></i>
                            <span>{{ __('order.payment.card') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'QR' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="QR" x-model="formData.pay_way" :disabled="!canEditOrderItems()">
                            <i data-feather="grid"></i>
                            <span>{{ __('order.payment.qr') }}</span>
                        </label>
                    </div>

                    <!-- Payment Summary Breakdown -->
                    <div class="payment-summary-box">
                        <div class="summary-line">
                            <span>{{ __('order.subtotal') }}</span>
                            <span x-text="formatCurrency(subTotal)"></span>
                        </div>
                        <div class="summary-line" x-show="subTotal - total > 0">
                            <span>{{ __('order.discount') }}</span>
                            <span class="deduct" x-text="formatDeductionCurrency(subTotal - total)"></span>
                        </div>
                        <div class="summary-line" x-show="commissionTotal > 0">
                            <span>{{ __('order.commission') }}</span>
                            <span class="deduct" x-text="formatDeductionCurrency(commissionTotal)"></span>
                        </div>
                        <div class="summary-line" x-show="previousBalance > 0">
                            <span>{{ __('order.current_order_total') }}</span>
                            <span x-text="formatCurrency(amountPaid)"></span>
                        </div>
                        <div class="summary-line" x-show="previousBalance > 0" style="color: #b45309; font-weight: 600;">
                            <span style="display: inline-flex; align-items: center; gap: 6px;">
                                <span>{{ __('order.previous_balance') }}</span>
                                <template x-if="previousOrdersCount > 0">
                                    <span style="background: #fef3c7; color: #92400e; font-size: 10px; padding: 1px 6px; border-radius: 9999px;"
                                        x-text="previousOrdersCount + ' ' + (previousOrdersCount === 1 ? '{{ __('order.order_singular') }}' : '{{ __('order.orders_plural') }}')"></span>
                                </template>
                            </span>
                            <span x-text="'+' + formatCurrency(previousBalance)"></span>
                        </div>
                        <div class="summary-total-line">
                            <span>{{ __('order.total_amount_due') }}</span>
                            <strong x-text="formatCurrency(totalAmountDue())"></strong>
                        </div>
                    </div>

                    <!-- New Booking Partial Payment -->
                    <template x-if="!orderId">
                        <div class="booking-payment-form booking-payment-form--record">
                            <template x-if="showRestrictionError && isCustomerRestricted">
                                <div class="booking-restricted-customer-banner" style="display: flex; align-items: center; gap: 8px; padding: 9px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; margin-bottom: 12px; color: #991b1b; font-size: 12px; font-weight: 500; line-height: 1.4;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span>{{ __('order.notice.full_payment_restricted') }}</span>
                                </div>
                            </template>
                            <div class="booking-payment-form-header">
                                <div class="booking-payment-form-title">
                                    <span class="booking-payment-title-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                    </span>
                                    <span>{{ __('order.initial_partial_payment') }}</span>
                                </div>
                                <button type="button" class="booking-pay-full-btn"
                                    @click="formData.partial_payment_amount = Math.round(Number(totalAmountDue() || 0) * 100) / 100; showRestrictionError = false; clearError('partial_payment_amount');"
                                    :title="'Fill ' + formatCurrency(totalAmountDue())">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                    <span>{{ __('order.pay_full') }} (<span x-text="formatCurrency(totalAmountDue())"></span>)</span>
                                </button>
                            </div>
                            <div class="booking-payment-form-grid">
                                <div class="form-group">
                                    <label>{{ __('order.amount') }}</label>
                                    <div class="booking-input-prefix-group">
                                        <span class="input-addon-prefix">$</span>
                                        <input type="number" min="0" step="0.01" x-model="formData.partial_payment_amount"
                                            @input="if (isCustomerRestricted && Math.round(Number(formData.partial_payment_amount || 0) * 100) >= Math.round(Number(totalAmountDue() || 0) * 100)) { showRestrictionError = false; clearError('partial_payment_amount'); }"
                                            :max="totalAmountDue()" placeholder="0.00" autocomplete="off">
                                    </div>
                                    <template x-for="item in (dataError?.partial_payment_amount || []).filter(msg => !showRestrictionError || msg !== @json(__('order.validation.full_payment_required')))">
                                        <span class="error" x-text="item"></span>
                                    </template>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('order.note') }}</label>
                                    <input type="text" x-model="formData.partial_payment_note" class="booking-note-input"
                                        placeholder="{{ __('order.placeholder.note') }}" maxlength="500" autocomplete="off">
                                    <template x-for="item in dataError?.partial_payment_note">
                                        <span class="error" x-text="item"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Edit Booking Payment Ledger Progress -->
                    <template x-if="orderId">
                        <div class="booking-payment-ledger">
                            <div class="booking-payment-progress-wrapper">
                                <div class="booking-payment-progress-head">
                                    <span>{{ __('order.payment_progress') }}</span>
                                    <strong x-text="paymentProgressPercentage() + '%'"></strong>
                                </div>
                                <div class="booking-payment-progress-bar">
                                    <div class="booking-payment-progress-fill" :style="`width: ${paymentProgressPercentage()}%`" :class="statusClass(orderStatus)"></div>
                                </div>
                            </div>
                            <div class="booking-payment-ledger-grid">
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('order.paid') }}</span>
                                    <strong class="val val--paid" x-text="formatCurrency(paidAmount)"></strong>
                                </div>
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('order.remaining') }}</span>
                                    <strong class="val val--remaining" x-text="formatCurrency(remainingAmount)"></strong>
                                </div>
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('order.status_label') }}</span>
                                    <span class="booking-payment-status-pill" :class="isTransferred ? 'ready' : statusClass(orderStatus)">
                                        <span class="status-dot"></span>
                                        <span x-text="isTransferred ? '{{ __('order.status.paid_through_transfer') }}' : statusLabel(orderStatus)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Record Payment Form (Edit mode) -->
                    <template x-if="canRecordPayment()">
                        <div class="booking-payment-form booking-payment-form--record">
                            <div class="booking-payment-form-header">
                                <div class="booking-payment-form-title">
                                    <span class="booking-payment-title-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    </span>
                                    <span>{{ __('order.record_payment') }}</span>
                                </div>
                                <button type="button" class="booking-pay-full-btn"
                                    @click="formData.partial_payment_amount = remainingAmount"
                                    :title="'Fill ' + formatCurrency(remainingAmount)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                    <span>{{ __('order.pay_full') }} (<span x-text="formatCurrency(remainingAmount)"></span>)</span>
                                </button>
                            </div>
                            <div class="booking-payment-form-grid">
                                <div class="form-group">
                                    <label>{{ __('order.amount') }}</label>
                                    <div class="booking-input-prefix-group">
                                        <span class="input-addon-prefix">$</span>
                                        <input type="number" min="0" step="0.01" x-model="formData.partial_payment_amount"
                                            :max="remainingAmount" placeholder="0.00" autocomplete="off">
                                    </div>
                                    <template x-for="item in dataError?.partial_payment_amount">
                                        <span class="error" x-text="item"></span>
                                    </template>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('order.note') }}</label>
                                    <input type="text" x-model="formData.partial_payment_note" class="booking-note-input"
                                        placeholder="{{ __('order.placeholder.note') }}" maxlength="500" autocomplete="off">
                                    <template x-for="item in dataError?.partial_payment_note">
                                        <span class="error" x-text="item"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Submit CTA Button -->
                    <button type="button" class="booking-pos-submit-btn" :disabled="submitLoading || !canEditOrderItems()"
                        @click="submitOrder()">
                        <span class="booking-spinner" x-show="submitLoading">
                            <span id="spinner"></span>
                        </span>
                        <span x-text="!canEditOrderItems() ? lblOrderLocked : (submitLoading ? lblSaving : btnSubmit)"></span>
                        <i data-feather="arrow-right" x-show="!submitLoading"></i>
                    </button>
                </div>
            </aside>
        </div>
    </div>
@stop

@section('script')
    <script>
        function XDatacreateorder() {
            return {
                loading: false,
                dataError: {},
                formData: {
                    shop_id: null,
                    status: 'confirmed',
                    customer_id: null,
                    disable: false,
                    order_date: moment().locale('en').format('YYYY-MM-DD'),
                    delivery_date: moment().locale('en').format('YYYY-MM-DD'),
                    pay_way: 'Cash',
                    partial_payment_amount: null,
                    partial_payment_note: '',
                },
                btnSubmit: @json($orderActionLabel),
                confirmMessage: @json($orderConfirmMessage),
                lblRecording: @json(__('order.recording')),
                lblRecordPayment: @json(__('order.record_payment')),
                lblOrderLocked: @json(__('order.order_locked')),
                lblSaving: @json(__('order.saving')),
                lblItems: @json(__('order.items')),
                lblTabService: @json(__('order.tab.service')),
                lblTabProduct: @json(__('order.tab.product')),
                lblInCart: @json(__('order.button.in_cart')),
                lblAddToCart: @json(__('order.button.add_to_cart')),
                lblAddedToCart: @json(__('order.button.added_to_cart')),
                catalogSort: 'default',
                totalProductsCount: 0,
                totalServicesCount: 0,
                searchFilter: '',
                searchTimer: null,
                dataFilter: [],
                dataCart: [],
                subTotal: 0,
                commissionTotal: 0,
                total: 0,
                amountPaid: 0,
                submitLoading: false,
                paymentLoading: false,
                orderId: null,
                paidAmount: 0,
                remainingAmount: 0,
                orderStatus: 'Pending',
                previousBalance: 0,
                previousOrders: [],
                previousOrdersCount: 0,
                previousBalanceFingerprint: '',
                loadingPreviousBalance: false,
                creationRequestKey: '',
                showPreviousBalanceBreakdown: false,
                isTransferred: false,
                transferredToOrder: null,
                paymentHistory: [],
                paymentAmount: null,
                paymentNote: '',
                editingPaymentId: null,
                editPaymentAmount: null,
                editPaymentNote: '',
                selectType: 'product',
                shopData: null,
                customerData: null,
                isCustomerRestricted: false,
                showRestrictionError: false,
                orderDeleteId: [],
                initialOrder: null,
                initialSelectedShop: null,
                init() {
                    const payload = @json($data);
                    const order = payload?.data ?? payload ?? null;
                    const selectedShop = @json($selectedShop);
                    const details = this.orderDetails(order);
                    this.initialOrder = order;
                    this.initialSelectedShop = selectedShop;

                    this.creationRequestKey = 'ord_req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    this.isTransferred = Boolean(order?.is_transferred || (order?.transferred_out_amount && order.transferred_out_amount > 0));
                    this.transferredToOrder = order?.completed_outgoing_balance_transfer?.new_order || null;
                    this.previousBalance = Number(order?.previous_balance || 0);
                    if (order?.incoming_balance_transfers && order.incoming_balance_transfers.length > 0) {
                        this.previousOrders = order.incoming_balance_transfers.map(t => ({
                            id: t.previous_order_id,
                            invoice_number: t.previous_order?.invoice_number || ('#' + t.previous_order_id),
                            order_date: t.previous_order?.order_date ? moment(t.previous_order.order_date).format('YYYY-MM-DD HH:mm') : '---',
                            payment_status: t.previous_order?.payment_status,
                            transferred_amount: Number(t.transferred_amount || 0),
                            transferred_amount_formatted: '$' + Number(t.transferred_amount || 0).toFixed(2),
                        }));
                        this.previousOrdersCount = this.previousOrders.length;
                    }

                    this.shopData = order?.shop || selectedShop || null;
                    this.customerData = order?.customer || null;
                    this.isCustomerRestricted = String(order?.customer?.payment_restriction || '').toUpperCase() === 'Y';
                    this.formData.shop_id = order?.shop_id || selectedShop?.id || null;
                    this.formData.customer_id = order?.customer_id || order?.customer?.id || null;
                    const orderDateVal = order?.order_date;
                    this.formData.order_date = orderDateVal
                        ? moment(orderDateVal).locale('en').format('YYYY-MM-DD')
                        : moment().locale('en').format('YYYY-MM-DD');
                    this.formData.delivery_date = order?.delivery_date
                        ? moment(order.delivery_date).locale('en').format('YYYY-MM-DD')
                        : (!order?.id
                            ? moment().locale('en').format('YYYY-MM-DD')
                            : (orderDateVal
                                ? moment(orderDateVal).locale('en').add(2, 'days').format('YYYY-MM-DD')
                                : moment().locale('en').add(2, 'days').format('YYYY-MM-DD')));
                    this.formData.pay_way = order?.pay_way || 'Cash';
                    this.dataCart = [];

                    window.addEventListener('keydown', (e) => {
                        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                            e.preventDefault();
                            if (this.canEditOrderItems()) {
                                this.openProductPicker();
                            }
                        }
                    });

                    this.orderId = order?.id || null;
                    this.syncPaymentState({
                        paid_amount: order?.paid_amount || 0,
                        remaining_amount: order?.remaining_amount || 0,
                        payment_status: order?.payment_status || 'Pending',
                        payments: order?.payments || [],
                    });
                    const initialPayment = (order?.payments && order.payments.length > 0) ? order.payments[0] : null;
                    if (initialPayment) {
                        this.formData.partial_payment_amount = Number(initialPayment.amount || 0) || null;
                        this.formData.partial_payment_note = initialPayment.note || '';
                    }
                    if (details.length > 0) {
                        details.forEach((detail) => {
                            const cartItem = this.mapDetailToCart(detail);
                            this.dataCart.push(cartItem);
                            if (cartItem.product_type === 'product' && (cartItem.current_stock === null || cartItem.current_stock === undefined)) {
                                this.fetchCartItemStock(cartItem);
                            }
                        });
                    }

                    if (!this.orderId && this.formData.customer_id) {
                        this.fetchPreviousBalance(this.formData.customer_id);
                    }

                    this.calculatorProductPrice();
                    this.fiterProduct(this.searchFilter);
                    this.$nextTick(() => {
                        this.initOrderDatepicker();
                        this.fetchSelectShop();
                        this.fetchSelectCustomer();
                        this.prefillSelectFields(order, selectedShop);
                        this.initTwoColumnSticky();
                        this.restoreDraft();
                    });
                    this.$watch('formData', () => this.saveDraft(), { deep: true });
                    this.$watch('dataCart', () => this.saveDraft(), { deep: true });
                    window.addEventListener('workspace:tab-change', () => this.saveDraft());
                    window.addEventListener('beforeunload', () => this.saveDraft());
                    window.addEventListener('workspace:tab-activated', (e) => {
                        const currentTabKey = this.$root?.closest('.workspace-tab-pane')?.getAttribute('data-tab-key');
                        if (!e.detail?.key || e.detail.key === currentTabKey || e.detail.key === 'admin-order-create') {
                            this.fetchSelectShop();
                            this.fetchSelectCustomer();
                            this.refreshIcons();
                        }
                    });
                    this.refreshIcons();
                },
                initOrderDatepicker() {
                    if (!window.jQuery || !$.fn.datepicker) return;

                    const vm = this;
                    const options = {
                        dateFormat: 'yy-mm-dd',
                        changeYear: true,
                        changeMonth: true,
                        gotoCurrent: true,
                        yearRange: '-50:+10',
                    };
                    [
                        [this.$refs.orderDate, 'order_date'],
                        [this.$refs.deliveryDate, 'delivery_date'],
                    ].forEach(([input, field]) => {
                        if (!input || $(input).hasClass('hasDatepicker')) return;

                        $(input).datepicker({
                            ...options,
                            onSelect(selectedDate) {
                                vm.formData[field] = selectedDate;
                            }
                        });
                    });
                },
                initTwoColumnSticky() {
                    const leftCol = (this.$el ? this.$el.querySelector('.booking-pos-main-col') : null) || document.querySelector('.booking-pos-main-col');
                    const rightCol = (this.$el ? this.$el.querySelector('.booking-pos-sidebar') : null) || document.querySelector('.booking-pos-sidebar');

                    if (!leftCol || !rightCol) return;

                    let rAF = null;
                    const updateSticky = () => {
                        if (window.innerWidth <= 1024) {
                            leftCol.classList.remove('is-sticky-column');
                            rightCol.classList.remove('is-sticky-column');
                            leftCol.style.removeProperty('--sticky-top');
                            rightCol.style.removeProperty('--sticky-top');
                            return;
                        }

                        const leftHeight = leftCol.offsetHeight;
                        const rightHeight = rightCol.offsetHeight;
                        const viewportHeight = window.innerHeight;
                        const tabBar = document.getElementById('workspace-tab-bar') || document.querySelector('.mdi-workspace-tabs-container');
                        const topSpacing = (tabBar ? tabBar.offsetHeight : 44) + 16;

                        // If the Left Column is shorter than the Right Sidebar, make Left Column sticky
                        if (leftHeight < rightHeight) {
                            leftCol.classList.add('is-sticky-column');
                            rightCol.classList.remove('is-sticky-column');
                            rightCol.style.removeProperty('--sticky-top');

                            if (leftHeight + topSpacing + 16 <= viewportHeight) {
                                leftCol.style.setProperty('--sticky-top', `${topSpacing}px`);
                            } else {
                                const offsetTop = Math.min(topSpacing, viewportHeight - leftHeight - 16);
                                leftCol.style.setProperty('--sticky-top', `${offsetTop}px`);
                            }
                        // If the Right Sidebar is shorter than the Left Column, make Right Sidebar sticky
                        } else if (rightHeight < leftHeight) {
                            rightCol.classList.add('is-sticky-column');
                            leftCol.classList.remove('is-sticky-column');
                            leftCol.style.removeProperty('--sticky-top');

                            if (rightHeight + topSpacing + 16 <= viewportHeight) {
                                rightCol.style.setProperty('--sticky-top', `${topSpacing}px`);
                            } else {
                                const offsetTop = Math.min(topSpacing, viewportHeight - rightHeight - 16);
                                rightCol.style.setProperty('--sticky-top', `${offsetTop}px`);
                            }
                        } else {
                            leftCol.classList.remove('is-sticky-column');
                            rightCol.classList.remove('is-sticky-column');
                            leftCol.style.removeProperty('--sticky-top');
                            rightCol.style.removeProperty('--sticky-top');
                        }
                    };

                    const requestUpdate = () => {
                        if (rAF) cancelAnimationFrame(rAF);
                        rAF = requestAnimationFrame(updateSticky);
                    };

                    // Initial calculation
                    this.$nextTick(requestUpdate);

                    // ResizeObserver on both columns to react immediately to content/DOM changes
                    if (window.ResizeObserver) {
                        const resizeObserver = new ResizeObserver(() => {
                            requestUpdate();
                        });
                        resizeObserver.observe(leftCol);
                        resizeObserver.observe(rightCol);
                    }

                    // Window resize listener
                    window.addEventListener('resize', requestUpdate);

                    this.$watch('dataCart', () => {
                        this.$nextTick(requestUpdate);
                        this.refreshIcons();
                    });
                    this.$watch('dataFilter', () => this.$nextTick(requestUpdate));
                    this.$watch('loading', () => this.$nextTick(requestUpdate));
                },
                reloadPage() {
                    window.location.reload();
                },
                refreshIcons() {
                    this.$nextTick(() => {
                        if (window.feather) {
                            try {
                                feather.replace();
                            } catch (err) {
                                console.warn('Feather icon replace warning:', err);
                            }
                        }
                    });
                },
                orderDetails(order) {
                    return order?.orderDetails || order?.order_details || [];
                },
                totalAmountDue() {
                    return this.roundMoney(Number(this.amountPaid || 0) + Number(this.previousBalance || 0));
                },
                canEditOrderItems() {
                    return (!this.orderId || this.orderStatus === 'Pending') && !this.isTransferred;
                },
                canRecordPayment() {
                    return this.orderId &&
                        this.orderStatus !== 'Paid' &&
                        this.orderStatus !== 'Cancel' &&
                        !this.isTransferred &&
                        Number(this.remainingAmount || 0) > 0;
                },
                paymentProgressPercentage() {
                    const total = Number(this.totalAmountDue() || this.total || 0);
                    const paid = Number(this.paidAmount || 0);
                    if (total <= 0) return 0;
                    return Math.min(100, Math.max(0, Math.round((paid / total) * 100)));
                },
                syncPaymentState(data = {}) {
                    this.paidAmount = Number(data?.paid_amount || 0);
                    this.remainingAmount = Math.round(Number(data?.remaining_amount || 0) * 100) / 100;
                    this.orderStatus = data?.payment_status || 'Pending';
                    this.paymentHistory = data?.payments || [];
                    this.refreshIcons();
                },
                paymentUser(pay) {
                    const createdBy = pay?.created_by;
                    if (createdBy && typeof createdBy === 'object') {
                        return createdBy?.name || createdBy?.phone || createdBy?.email || '-';
                    }

                    return createdBy || '-';
                },
                paymentDate(pay) {
                    const d = pay?.payment_date || pay?.created_at;
                    return d ? moment(d).format('YYYY-MM-DD HH:mm') : '--';
                },
                getScopeEl(selector) {
                    if (this.$refs && selector === '#shop_id' && this.$refs.shopSelect) {
                        return $(this.$refs.shopSelect);
                    }
                    if (this.$refs && selector === '#customer_id' && this.$refs.customerSelect) {
                        return $(this.$refs.customerSelect);
                    }
                    if (this.$el && $(this.$el).is(selector)) {
                        return $(this.$el);
                    }
                    return $(this.$root).find(selector);
                },
                setSelect2Value(selector, value, text) {
                    const $select = this.getScopeEl(selector);
                    if (!$select.length || !$select.is('select')) return;
                    if (!value) {
                        $select.val(null).trigger('change');
                        return;
                    }
                    if (String($select.val()) === String(value) && $select.find('option:selected').text().trim() === String(text || value).trim()) {
                        return;
                    }
                    $select.find('option').filter((index, option) => String(option.value) === String(value)).remove();
                    const option = "<option selected></option>";
                    const selectOptionHTML = $(option).val(value).text(text || value);
                    $select.append(selectOptionHTML).trigger('change');
                },
                prefillSelectFields(order, selectedShop) {
                    const shop = order?.shop || selectedShop || this.shopData;
                    const customer = order?.customer || this.customerData;

                    const shopName = shop?.name || shop?.phone;
                    if (this.formData.shop_id && shopName) {
                        this.setSelect2Value('#shop_id', this.formData.shop_id, shopName);
                    }
                    const custName = customer?.name || customer?.phone;
                    if (this.formData.customer_id && custName) {
                        this.setSelect2Value('#customer_id', this.formData.customer_id, custName);
                    }
                },
                fetchCartItemStock(cartItem) {
                    const shopId = this.formData?.shop_id || this.shopData?.id;
                    if (!shopId || cartItem?.product_type !== 'product' || !cartItem?.product_id) return;
                    const url = `/admin/select/find-shop-product?shop_id=${shopId}&product_id=${cartItem.product_id}`;
                    this.fetchJson(url).then((res) => {
                        if (res && res.current_stock !== undefined && res.current_stock !== null) {
                            cartItem.current_stock = Number(res.current_stock);
                        }
                    }).catch(() => {});
                },
                mapDetailToCart(detail) {
                    const type = detail?.type || 'product';
                    const item = type === 'service' ? detail?.service : detail?.product;
                    const productId = item?.id || (type === 'service' ? detail?.service_id : detail?.product_id);
                    const price = Number(detail?.price ?? item?.price ?? 0);
                    const commission = type === 'service' ? detail?.service_commission : detail?.product_commission;
                    const commissionType = type === 'service'
                        ? detail?.service_commission_type
                        : detail?.product_commission_type;
                    const discount = type === 'service' ? detail?.service_discount : detail?.product_discount;
                    const discountType = type === 'service'
                        ? detail?.service_discount_type
                        : detail?.product_discount_type;
                    const currentStock = type === 'product'
                        ? (detail?.current_stock !== undefined && detail?.current_stock !== null
                            ? Number(detail.current_stock)
                            : (item?.current_stock !== undefined && item?.current_stock !== null
                                ? Number(item.current_stock)
                                : null))
                        : null;

                    return {
                        id: detail?.id,
                        product_id: productId,
                        code: item?.code || detail?.code || '',
                        name: item?.name || '---',
                        uom_title: type === 'product' ? (item?.uom_title || '') : '',
                        category_title: type === 'product' ? (item?.category_title || '') : '',
                        image: item?.image_url || '{{ asset('images/logo/default.png') }}',
                        current_stock: currentStock,
                        itemData: {
                            price: price,
                            ...(type === 'product' ? {
                                originalPrice: detail?.original_price ?? price,
                                unitPrice: detail?.unit_price ?? String(this.totalDiscount(discountType, price, discount)),
                                pricingMethod: detail?.pricing_method ?? null,
                                legacyUnitPrice: this.totalDiscount(discountType, price, discount),
                            } : {}),
                            point: detail?.point || 0,
                            discount: discount || 0,
                            discountType: discountType,
                            commission: commission || 0,
                            commissionType: commissionType,
                            totalCommission: this.getCommssion(commissionType, price, commission),
                            total: this.totalDiscount(discountType, price, discount),
                        },
                        product_qty: detail?.qty || 1,
                        product_type: type,
                        selectedQty: 1,
                    };
                },
                changeSelectType(type) {
                    this.selectType = type || 'all';
                    this.searchFilter = '';
                    this.fiterProduct('');
                },
                shopSelectOptions() {
                    return {
                        placeholder: `{{ __('order.form.select_shop') }}`,
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-shop') }}',
                            dataType: 'json',
                            type: "GET",
                            quietMillis: 50,
                            data: (param) => ({
                                search: param.term
                            }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : item?.phone,
                                    id: item.id,
                                    item: item
                                }))
                            }),
                            error: (xhr, status, error) => {
                                console.error('Error fetching shops:', error);
                            }
                        }
                    };
                },
                fetchSelectShop() {
                    const $shop = this.getScopeEl('#shop_id');
                    if (!$shop.length) return;
                    if (!$shop.hasClass('select2-hidden-accessible')) {
                        $shop.select2(this.shopSelectOptions()).on('select2:select', (event) => {
                            this.shopData = event.params.data.item || {
                                id: event.params.data.id,
                                name: event.params.data.text
                            };
                            this.formData.shop_id = event.params.data.id;
                            delete this.dataError?.shop_id;
                            this.dataCart = [];
                            this.orderDeleteId = [];
                            this.syncCatalogSelection();
                            this.calculatorProductPrice();
                            this.fiterProduct(this.searchFilter);
                            this.saveDraft();
                        }).on('change', (e) => {
                            if (!$(e.target).val()) {
                                this.shopData = null;
                                this.formData.shop_id = null;
                                this.dataCart = [];
                                this.orderDeleteId = [];
                                this.syncCatalogSelection();
                                this.calculatorProductPrice();
                                this.fiterProduct(this.searchFilter);
                                this.saveDraft();
                            }
                        }).on('select2:clear select2:unselect', () => {
                            this.shopData = null;
                            this.formData.shop_id = null;
                            this.dataCart = [];
                            this.orderDeleteId = [];
                            this.syncCatalogSelection();
                            this.calculatorProductPrice();
                            this.fiterProduct(this.searchFilter);
                            this.saveDraft();
                        }).on('select2:open', () => {
                            $select2FocusInputSearch();
                        });
                    }

                    const shopName = this.shopData?.name || this.shopData?.phone;
                    if (this.formData.shop_id && shopName) {
                        this.setSelect2Value('#shop_id', this.formData.shop_id, shopName);
                    }
                },
                fetchSelectCustomer() {
                    const $customer = this.getScopeEl('#customer_id');
                    if (!$customer.length) return;
                    if (!$customer.hasClass('select2-hidden-accessible')) {
                        $customer.select2({
                            placeholder: `{{ __('order.form.select_customer') }}`,
                            width: '100%',
                            ajax: {
                                url: '{{ route('admin-select-customer') }}',
                                dataType: 'json',
                                type: "GET",
                                quietMillis: 50,
                                data: (param) => ({
                                    search: param.term
                                }),
                                processResults: (data) => ({
                                    results: $.map(data.data, (item) => ({
                                        text: item?.name ? item.name : item?.phone,
                                        id: item.id,
                                        item: item
                                    }))
                                }),
                                error: (xhr, status, error) => {
                                    console.error('Error fetching customers:', error);
                                }
                            }
                        }).on('select2:select', (event) => {
                            this.customerData = event.params.data.item || {
                                id: event.params.data.id,
                                name: event.params.data.text
                            };
                            this.formData.customer_id = event.params.data.id;
                            this.isCustomerRestricted = String(this.customerData?.payment_restriction || '').toUpperCase() === 'Y';
                            this.showRestrictionError = false;
                            this.clearError('customer_id');
                            this.clearError('partial_payment_amount');
                            this.fetchPreviousBalance(event.params.data.id);
                            this.saveDraft();
                        }).on('change', (e) => {
                            if (!$(e.target).val()) {
                                this.customerData = null;
                                this.formData.customer_id = null;
                                this.isCustomerRestricted = false;
                                this.showRestrictionError = false;
                                this.clearError('customer_id');
                                this.clearError('partial_payment_amount');
                                this.fetchPreviousBalance(null);
                                this.saveDraft();
                            }
                        }).on('select2:clear select2:unselect', () => {
                            this.customerData = null;
                            this.formData.customer_id = null;
                            this.isCustomerRestricted = false;
                            this.showRestrictionError = false;
                            this.clearError('customer_id');
                            this.clearError('partial_payment_amount');
                            this.fetchPreviousBalance(null);
                            this.saveDraft();
                        }).on('select2:open', () => {
                            $select2FocusInputSearch();
                        });
                    }

                    const custName = this.customerData?.name || this.customerData?.phone;
                    if (this.formData.customer_id && custName) {
                        this.setSelect2Value('#customer_id', this.formData.customer_id, custName);
                    }
                },
                resetShop() {
                    this.setSelect2Value('#shop_id', null);
                    this.shopData = null;
                    this.formData.shop_id = null;
                    this.dataCart = [];
                    this.orderDeleteId = [];
                    this.syncCatalogSelection();
                    this.calculatorProductPrice();
                    this.fiterProduct(this.searchFilter);
                    this.saveDraft();
                },
                resetCustomer() {
                    this.setSelect2Value('#customer_id', null);
                    this.customerData = null;
                    this.formData.customer_id = null;
                    this.isCustomerRestricted = false;
                    this.showRestrictionError = false;
                    this.clearError('customer_id');
                    this.clearError('partial_payment_amount');
                    this.fetchPreviousBalance(null);
                    this.saveDraft();
                },
                fetchPreviousBalance(customerId) {
                    if (this.orderId || !customerId) {
                        this.previousBalance = 0;
                        this.previousOrders = [];
                        this.previousOrdersCount = 0;
                        this.previousBalanceFingerprint = '';
                        this.isCustomerRestricted = false;
                        this.showRestrictionError = false;
                        return;
                    }
                    this.loadingPreviousBalance = true;
                    Axios.get('{{ route("admin-order-preview-previous-balance") }}', {
                        params: { customer_id: customerId }
                    }).then((res) => {
                        if (res.data?.status === 200 && res.data?.data) {
                            const d = res.data.data;
                            this.previousBalance = Number(d.previous_balance || 0);
                            this.previousOrders = d.previous_orders || [];
                            this.previousOrdersCount = Number(d.previous_orders_count || 0);
                            this.previousBalanceFingerprint = d.fingerprint || '';
                            if (d.payment_restriction !== undefined || d.is_restricted !== undefined) {
                                this.isCustomerRestricted = Boolean(d.is_restricted ?? (String(d.payment_restriction || '').toUpperCase() === 'Y'));
                            }
                            this.showRestrictionError = false;
                        } else {
                            this.previousBalance = 0;
                            this.previousOrders = [];
                            this.previousOrdersCount = 0;
                            this.previousBalanceFingerprint = '';
                        }
                    }).catch((err) => {
                        console.error('Failed to preview previous balance:', err);
                        this.previousBalance = 0;
                        this.previousOrders = [];
                        this.previousOrdersCount = 0;
                        this.previousBalanceFingerprint = '';
                    }).finally(() => {
                        this.loadingPreviousBalance = false;
                        this.refreshIcons();
                    });
                },
                openProductPicker(trigger) {
                    if (!this.canEditOrderItems() || this.$store.confirmDialog.show) return;
                    this.$store.confirmDialog.open({
                        data: {
                            typeAction: 'order-products',
                            class: 'order-product-picker-container',
                            picker: this.createProductPicker(),
                        },
                        afterClosed: () => trigger?.isConnected && trigger.focus(),
                    });
                },
                createProductPicker() {
                    const order = this;
                    let request = null;
                    let searchTimer = null;
                    const pageCache = new Map();
                    let prefetchController = null;
                    let prefetchKey = null;
                    let prefetchPromise = null;
                    let loadVersion = 0;
                    let $shopSelect = null;

                    const getCacheKey = (shopId, page, query = '') => `${shopId}::${(query || '').trim()}::${page}`;

                    return {
                        shopId: order.formData.shop_id || order.shopData?.id,
                        shopName: order.shopData?.name || order.shopData?.phone || '',
                        products: [],
                        selected: [],
                        search: '',
                        page: 1,
                        perPage: 12,
                        total: 0,
                        lastPage: 1,
                        from: 0,
                        to: 0,
                        hasMore: false,
                        loading: false,
                        failed: false,
                        closing: false,
                        init() {
                            this.loadProducts();
                            this.$nextTick(() => {
                                if (this.closing) return;
                                this.initShopSelect();
                                if (this.canSelect) this.$refs.productSearch?.focus();
                                else $shopSelect?.next('.select2-container').find('.select2-selection').trigger('focus');
                            });
                        },
                        destroy() {
                            this.cancelRequests();
                            $shopSelect?.off('.productPicker');
                            if ($shopSelect?.data('select2')) $shopSelect.select2('destroy');
                            $shopSelect = null;
                        },
                        cancelRequests() {
                            loadVersion++;
                            clearTimeout(searchTimer);
                            request?.abort();
                            prefetchController?.abort();
                            prefetchKey = null;
                            prefetchPromise = null;
                            pageCache.clear();
                        },
                        initShopSelect() {
                            $shopSelect = $(this.$refs.pickerShop);
                            if ($shopSelect.data('select2')) return;
                            if (this.shopId) {
                                $shopSelect.append(new Option(this.shopName || this.shopId, this.shopId, true, true));
                            }
                            $shopSelect.select2({
                                ...order.shopSelectOptions(),
                                dropdownParent: $(this.$el),
                            }).on('select2:open.productPicker', () => {
                                if (typeof window.$select2FocusInputSearch === 'function') {
                                    window.$select2FocusInputSearch();
                                }
                            }).on('change.productPicker select2:select.productPicker select2:clear.productPicker select2:unselect.productPicker', () => {
                                this.changeShop($shopSelect.val() ? $shopSelect.select2('data')[0] : null);
                            });
                            // Bundled Select2 4.0.0 uses page offsets even with dropdownParent.
                            // Convert them to dialog offsets without changing the shared dropdown styles.
                            const dropdown = $shopSelect.data('select2').dropdown;
                            const positionDropdown = dropdown._positionDropdown;
                            dropdown._positionDropdown = function () {
                                positionDropdown.call(this);
                                const parent = this.$dropdownParent;
                                const offset = parent.offset();
                                this.$dropdownContainer.css({
                                    top: parseFloat(this.$dropdownContainer.css('top')) - offset.top + parent.scrollTop(),
                                    left: parseFloat(this.$dropdownContainer.css('left')) - offset.left + parent.scrollLeft(),
                                });
                            };
                        },
                        changeShop(selection) {
                            const shopId = selection?.id || null;
                            if (!this.canChangeShop || String(shopId || '') === String(this.shopId || '')) return;
                            this.cancelRequests();
                            this.selected = [];
                            this.page = 1;
                            this.total = 0;
                            this.lastPage = 1;
                            this.from = 0;
                            this.to = 0;
                            this.hasMore = false;
                            this.failed = false;
                            this.shopId = shopId;
                            this.shopName = selection?.text || '';
                            // Reuse the order's existing shop-change handlers, including cart reset and draft sync.
                            if (shopId) {
                                order.setSelect2Value('#shop_id', shopId, this.shopName);
                                order.getScopeEl('#shop_id').trigger({ type: 'select2:select', params: { data: selection } });
                            } else {
                                order.resetShop();
                                this.products = [];
                            }
                            this.loadProducts(1);
                        },
                        resetShop() {
                            if (this.canChangeShop) $shopSelect?.val(null).trigger('change');
                        },
                        get canChangeShop() {
                            return order.canEditOrderItems() && !this.closing;
                        },
                        get validShop() {
                            return Boolean(this.shopId) && String(this.shopId) === String(order.formData.shop_id || order.shopData?.id);
                        },
                        get canSelect() {
                            return this.validShop && order.canEditOrderItems() && !this.closing;
                        },
                        key(item) { return order.catalogKey(item); },
                        code(item) { return order.catalogCode(item); },
                        name(item) { return order.catalogName(item); },
                        image(item) { return order.catalogImage(item); },
                        price(item) { return order.formatCurrency(order.catalogPrice(item)); },
                        discount(item) { return order.catalogDiscountText(item); },
                        stock(item) { return Number(item?.current_stock ?? item?.product?.current_stock ?? 0); },
                        itemNo(index) {
                            const base = this.from > 0 ? this.from : ((this.page - 1) * 12 + 1);
                            return base + index;
                        },
                        inOrder(item) {
                            return order.dataCart.some(cart => this.key(cart) === this.key(item));
                        },
                        isSelected(item) {
                            return this.selected.some(product => this.key(product) === this.key(item));
                        },
                        get selectedItems() {
                            return this.selected.filter(item => !this.inOrder(item));
                        },
                        get selectableItems() {
                            return this.products.filter(item => !this.inOrder(item));
                        },
                        get allSelected() {
                            return this.selectableItems.length > 0 && this.selectableItems.every(item => this.isSelected(item));
                        },
                        get someSelected() {
                            return this.selectableItems.some(item => this.isSelected(item));
                        },
                        toggle(item) {
                            if (!this.canSelect || this.loading || this.inOrder(item)) return;
                            this.selected = this.isSelected(item)
                                ? this.selected.filter(product => this.key(product) !== this.key(item))
                                : [...this.selected, item];
                        },
                        togglePage(checked) {
                            if (!this.canSelect || this.loading) return;
                            const keys = new Set(this.selectableItems.map(item => this.key(item)));
                            this.selected = this.selected.filter(item => !keys.has(this.key(item)));
                            if (checked) this.selected.push(...this.selectableItems);
                        },
                        searchProducts() {
                            this.cancelRequests();
                            this.products = [];
                            this.loading = true;
                            this.failed = false;
                            this.page = 1;
                            this.total = 0;
                            this.lastPage = 1;
                            this.from = 0;
                            this.to = 0;
                            searchTimer = setTimeout(() => this.loadProducts(), 250);
                        },
                        prefetch(page) {
                            if (!this.canSelect || page < 1 || (this.total > 0 && this.lastPage && page > this.lastPage)) return;
                            const key = getCacheKey(this.shopId, page, this.search);
                            if (pageCache.has(key) || prefetchKey === key) return;

                            prefetchController?.abort();
                            prefetchKey = key;
                            prefetchController = new AbortController();
                            const signal = prefetchController.signal;

                            prefetchPromise = (async () => {
                                try {
                                    const params = new URLSearchParams({
                                        shop_id: this.shopId,
                                        type: 'product',
                                        search: this.search.trim(),
                                        page
                                    });
                                    const response = await fetch(`/admin/select/product?${params}`, {
                                        headers: { 'Accept': 'application/json' },
                                        signal
                                    });
                                    if (!response.ok) return null;
                                    const result = await response.json();
                                    if (!Array.isArray(result?.data) || signal.aborted || !this.canSelect) return null;

                                    const unique = new Map(order.normalizeCatalogItems(result.data, 'product')
                                        .filter(item => item.product_id)
                                        .map(item => [this.key(item), item]));
                                    const items = [...unique.values()];
                                    const hasMore = Boolean(result.has_more);
                                    const total = Number(result.total ?? (hasMore ? (page * 12 + 1) : ((page - 1) * 12 + items.length)));
                                    const lastPage = Number(result.last_page ?? (result.total ? Math.max(1, Math.ceil(result.total / 12)) : (hasMore ? page + 1 : page)));
                                    const from = Number(result.from ?? (items.length ? (page - 1) * 12 + 1 : 0));
                                    const to = Number(result.to ?? (items.length ? (page - 1) * 12 + items.length : 0));
                                    const data = {
                                        products: items,
                                        hasMore,
                                        total,
                                        lastPage,
                                        from,
                                        to
                                    };
                                    pageCache.set(key, data);
                                    return data;
                                } catch (e) {
                                    return null;
                                } finally {
                                    if (prefetchController?.signal === signal) {
                                        prefetchKey = null;
                                        prefetchPromise = null;
                                        prefetchController = null;
                                    }
                                }
                            })();
                        },
                        async loadProducts(page = this.page) {
                            if (page < 1) page = 1;
                            if (this.total > 0 && this.lastPage && page > this.lastPage) page = this.lastPage;

                            const version = ++loadVersion;
                            clearTimeout(searchTimer);
                            request?.abort();

                            if (!this.canSelect) {
                                this.loading = false;
                                return;
                            }

                            const key = getCacheKey(this.shopId, page, this.search);

                            // Instant cache hit: 0ms seamless transition
                            if (pageCache.has(key)) {
                                const cached = pageCache.get(key);
                                this.page = page;
                                this.products = cached.products;
                                this.hasMore = cached.hasMore;
                                this.total = cached.total;
                                this.lastPage = cached.lastPage;
                                this.from = cached.from;
                                this.to = cached.to;
                                this.loading = false;
                                this.failed = false;
                                this.$nextTick(() => {
                                    this.$refs.pickerBody?.scrollTo({ top: 0, behavior: 'instant' });
                                });
                                if (cached.hasMore && page < cached.lastPage) {
                                    this.prefetch(page + 1);
                                }
                                return;
                            }

                            // In-flight prefetch deduplication: attach to existing promise
                            if (prefetchKey === key && prefetchPromise) {
                                this.page = page;
                                this.loading = true;
                                this.failed = false;
                                const res = await prefetchPromise;
                                if (version !== loadVersion || !this.canSelect) return;
                                if (res && this.canSelect && this.page === page) {
                                    this.products = res.products;
                                    this.hasMore = res.hasMore;
                                    this.total = res.total;
                                    this.lastPage = res.lastPage;
                                    this.from = res.from;
                                    this.to = res.to;
                                    this.loading = false;
                                    this.failed = false;
                                    this.$nextTick(() => {
                                        this.$refs.pickerBody?.scrollTo({ top: 0, behavior: 'instant' });
                                    });
                                    if (res.hasMore && page < res.lastPage) {
                                        this.prefetch(page + 1);
                                    }
                                    return;
                                }
                            }

                            // Fresh fetch:
                            // Do not clear this.products if transitioning pages; keeps table visible without height jumps
                            if (!this.products.length) {
                                this.products = [];
                            }
                            this.hasMore = false;
                            this.page = page;
                            this.loading = true;
                            this.failed = false;

                            const activeRequest = new AbortController();
                            request = activeRequest;

                            try {
                                const params = new URLSearchParams({
                                    shop_id: this.shopId,
                                    type: 'product',
                                    search: this.search.trim(),
                                    page
                                });
                                const response = await fetch(`/admin/select/product?${params}`, {
                                    headers: { 'Accept': 'application/json' },
                                    signal: activeRequest.signal,
                                });
                                if (!response.ok) throw new Error('Product request failed');
                                const result = await response.json();
                                if (!Array.isArray(result.data)) throw new Error('Invalid product response');
                                if (activeRequest.signal.aborted || version !== loadVersion || !this.canSelect) return;

                                const unique = new Map(order.normalizeCatalogItems(result.data, 'product')
                                    .filter(item => item.product_id)
                                    .map(item => [this.key(item), item]));
                                const items = [...unique.values()];
                                const hasMore = Boolean(result.has_more);
                                const total = Number(result.total ?? (hasMore ? (page * 12 + 1) : ((page - 1) * 12 + items.length)));
                                const lastPage = Number(result.last_page ?? (result.total ? Math.max(1, Math.ceil(result.total / 12)) : (hasMore ? page + 1 : page)));
                                const from = Number(result.from ?? (items.length ? (page - 1) * 12 + 1 : 0));
                                const to = Number(result.to ?? (items.length ? (page - 1) * 12 + items.length : 0));

                                pageCache.set(key, { products: items, hasMore, total, lastPage, from, to });
                                this.products = items;
                                this.hasMore = hasMore;
                                this.total = total;
                                this.lastPage = lastPage;
                                this.from = from;
                                this.to = to;
                                this.$nextTick(() => {
                                    this.$refs.pickerBody?.scrollTo({ top: 0, behavior: 'instant' });
                                });

                                if (hasMore && page < lastPage) {
                                    this.prefetch(page + 1);
                                }
                            } catch (error) {
                                if (!activeRequest.signal.aborted && version === loadVersion) this.failed = true;
                            } finally {
                                if (request === activeRequest && !activeRequest.signal.aborted && version === loadVersion) {
                                    this.loading = false;
                                }
                            }
                        },
                        addItems() {
                            if (!this.canSelect || this.loading || this.failed || !this.selectedItems.length) return;
                            this.closing = true;
                            this.selectedItems.forEach(item => order.addToCart(item, 1));
                            order.$store.confirmDialog.close(true);
                        },
                        cancel() {
                            if (this.closing) return;
                            this.closing = true;
                            this.cancelRequests();
                            order.$store.confirmDialog.close(false);
                        },
                        trapFocus(event) {
                            const controls = [...this.$el.querySelectorAll('button:not(:disabled), input:not(:disabled), [tabindex="0"]')]
                                .filter(element => element.getClientRects().length);
                            const first = controls[0], last = controls[controls.length - 1];
                            if (event.shiftKey && document.activeElement === first) {
                                event.preventDefault(); last?.focus();
                            } else if (!event.shiftKey && document.activeElement === last) {
                                event.preventDefault(); first?.focus();
                            }
                        },
                    };
                },
                async fiterProduct(search = this.searchFilter) {
                    const shopId = this.formData.shop_id || this.shopData?.id;
                    this.searchFilter = search || '';

                    if (!shopId) {
                        this.dataFilter = [];
                        this.loading = false;
                        this.refreshIcons();
                        return;
                    }

                    this.loading = true;
                    clearTimeout(this.searchTimer);
                    this.searchTimer = setTimeout(async () => {
                        try {
                            const responses = await Promise.all(['product'].map(async (type) => {
                                const res = await this.fetchJson(
                                    `/admin/select/product?search=${encodeURIComponent(search || '')}&shop_id=${shopId}&type=${type}`
                                );
                                return this.normalizeCatalogItems(res?.data || [], type);
                            }));

                            this.totalProductsCount = responses[0]?.length || 0;
                            this.totalServicesCount = 0;

                            this.dataFilter = responses.flat();
                            this.applyCatalogSort();
                            this.syncCatalogSelection();
                        } finally {
                            this.loading = false;
                            this.refreshIcons();
                        }
                    }, 250);
                },
                async fetchJson(url) {
                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            }
                        });
                        return await response.json();
                    } catch (error) {
                        return null;
                    }
                },
                normalizeCatalogItems(items, type) {
                    return items.map((itemVal) => {
                        const source = type === 'service' ? itemVal?.service : itemVal?.product;
                        const productId = source?.id || itemVal?.product_id || itemVal?.service_id;
                        const price = Number(itemVal?.price ?? source?.price ?? 0);
                        const key = `${type}-${productId}`;

                        return {
                            ...itemVal,
                            product_type: type,
                            product_id: productId,
                            code: source?.code || itemVal?.code || '',
                            name: source?.name || '---',
                            image: source?.image_url || '{{ asset('images/logo/default.png') }}',
                            price: price,
                            current_stock: Number(itemVal?.current_stock ?? source?.current_stock ?? 0),
                            selectedQty: itemVal?.selectedQty || 1,
                            addToCart: this.dataCart.some((cart) => this.catalogKey(cart) === key),
                        };
                    });
                },
                catalogKey(item) {
                    return `${item?.product_type || 'product'}-${item?.product_id || ''}`;
                },
                syncCatalogSelection() {
                    this.dataFilter.forEach((item) => {
                        item.addToCart = this.dataCart.some((cart) => this.catalogKey(cart) === this.catalogKey(item));
                    });
                },
                catalogSource(item) {
                    return item?.product_type === 'service' ? item?.service : item?.product;
                },
                catalogName(item) {
                    return item?.name || this.catalogSource(item)?.name || '---';
                },
                catalogCode(item) {
                    return item?.code || this.catalogSource(item)?.code || '';
                },
                catalogImage(item) {
                    return item?.image || this.catalogSource(item)?.image_url || '{{ asset('images/logo/default.png') }}';
                },
                catalogPrice(item) {
                    return Number(item?.price ?? this.catalogSource(item)?.price ?? 0);
                },
                catalogBadge(item) {
                    return item?.product_type === 'service' ? @json(__('order.tab.service')) : @json(__('order.tab.product'));
                },
                catalogDiscountText(item) {
                    const type = item?.type;
                    const discount = Number(item?.discount || 0);
                    return type === 'percent' ? `-${discount}%` : `-${this.formatCurrency(discount)}`;
                },
                catalogCommissionText(item) {
                    const type = item?.commission_type;
                    const commission = Number(item?.commission || 0);
                    const commText = @json(__('order.commission_short'));
                    return type === 'percent' ? `${commText} ${commission}%` : `${commText} ${this.formatCurrency(commission)}`;
                },
                formatCurrency(value) {
                    const amount = Number(value || 0);
                    return `$${amount.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}`;
                },
                formatDeductionCurrency(value) {
                    const amount = Number(value || 0);
                    return amount > 0 ? `-${this.formatCurrency(amount)}` : this.formatCurrency(0);
                },
                cartItemDisplayName(item) {
                    const name = item?.name || this.catalogName(item) || '---';
                    const code = item?.code || this.catalogCode(item);
                    if (code) {
                        const prefix = `${code} - `;
                        return name.startsWith(prefix) ? name : `${prefix}${name}`;
                    }
                    return name;
                },
                cartLineTotal(item) {
                    if (this.isAutomaticProduct(item)) {
                        return this.automaticProductAmounts(item)?.total ?? 0;
                    }
                    const qty = this.cartQuantity(item);
                    const unitTotal = this.totalDiscount(item?.itemData?.discountType,
                        item?.itemData?.price, item?.itemData?.discount);
                    return this.roundMoney(qty * unitTotal);
                },
                usdCents(value) {
                    if (!['string', 'number'].includes(typeof value)) return null;
                    const match = String(value).trim().match(/^([0-9]+)(?:\.([0-9]{0,2}))?$/);
                    if (!match) return null;
                    const cents = Number(match[1]) * 100 + Number((match[2] || '').padEnd(2, '0'));
                    return Number.isSafeInteger(cents) && cents <= 999999999999999 ? cents : null;
                },
                usdString(cents) {
                    return `${Math.floor(cents / 100)}.${String(cents % 100).padStart(2, '0')}`;
                },
                isAutomaticProduct(item) {
                    return item?.product_type === 'product' && item?.itemData?.pricingMethod === 'auto_unit_price_v1';
                },
                automaticProductAmounts(item) {
                    const original = this.usdCents(item?.itemData?.originalPrice);
                    const unit = this.usdCents(item?.itemData?.unitPrice);
                    const qty = this.cartQuantity(item);
                    if (original === null || unit === null || !Number.isSafeInteger(qty)) return null;
                    const price = Math.max(original, unit);
                    if (price > 0 && qty > Math.floor(999999999999999 / price)) return null;
                    const discount = Math.max(original - unit, 0);
                    return { price: price / 100, unit: unit / 100, discount: discount / 100,
                        subtotal: price * qty / 100, total: unit * qty / 100 };
                },
                validProductPrice(item) {
                    if (this.isAutomaticProduct(item)) return this.automaticProductAmounts(item) !== null;
                    const data = item?.itemData;
                    return data?.pricingMethod == null && data?.unitPrice !== ''
                        && Number.isFinite(Number(data?.unitPrice))
                        && Number(data.unitPrice) === data.legacyUnitPrice;
                },
                cartUnitDiscount(item) {
                    if (this.isAutomaticProduct(item)) return this.automaticProductAmounts(item)?.discount ?? 0;
                    return Math.max(0, Number(item.itemData.price) - this.totalDiscount(
                        item.itemData.discountType, item.itemData.price, item.itemData.discount));
                },
                initializeProductPrice(item) {
                    if (item?.product_type !== 'product' || item.itemData.unitPrice !== undefined) return;
                    const data = item.itemData;
                    const unit = this.totalDiscount(data.discountType, data.price, data.discount);
                    const rounded = this.roundMoney(unit);
                    // Remove binary arithmetic noise only; do not round fractional-cent adjustments.
                    const exactCents = Math.abs(unit - rounded) <= Number.EPSILON * Math.max(1, Math.abs(unit)) * 4;
                    data.originalPrice = data.price;
                    data.unitPrice = exactCents ? this.usdString(Math.round(rounded * 100)) : String(unit);
                    data.pricingMethod = 'auto_unit_price_v1';
                },
                normalizeUnitPrice(item) {
                    if (!this.canEditOrderItems() || !this.isAutomaticProduct(item)) return;
                    const cents = this.usdCents(item.itemData.unitPrice);
                    if (cents !== null) item.itemData.unitPrice = this.usdString(cents);
                },
                cartQuantity(item) {
                    return item?.product_type === 'service' ? 1 : Math.max(1, Math.trunc(Number(item?.product_qty || 1)));
                },
                roundMoney(value) {
                    const cents = Number(value || 0) * 100;
                    const rounded = Math.round(Math.abs(cents) + Number.EPSILON * Math.max(1, Math.abs(cents)) * 4);
                    return (cents < 0 ? -rounded : rounded) / 100;
                },
                formatDate(value, pattern = 'DD MMM YYYY') {
                    if (!value) {
                        return '--';
                    }
                    return moment(value).format(pattern);
                },
                statusLabel(status) {
                    if (status === 'Paid') {
                        return @json(__('order.status.ready'));
                    }
                    if (status === 'Partial') {
                        return @json(__('order.status.partial'));
                    }
                    if (status === 'Cancel') {
                        return @json(__('order.status.canceled'));
                    }
                    return @json(__('order.status.waiting'));
                },
                statusClass(status) {
                    if (status === 'Paid') {
                        return 'is-paid';
                    }
                    if (status === 'Partial') {
                        return 'is-partial';
                    }
                    if (status === 'Cancel') {
                        return 'is-cancel';
                    }
                    return 'is-pending';
                },
                increasePreviewQty(item) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.selectedQty = 1;
                        return;
                    }

                    item.selectedQty = Number(item.selectedQty || 1) + 1;
                },
                decreasePreviewQty(item) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.selectedQty = 1;
                        return;
                    }

                    item.selectedQty = Math.max(1, Number(item.selectedQty || 1) - 1);
                },
                addToCart(item, qty = null) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    const type = item?.product_type || 'product';
                    const source = type === 'service' ? item?.service : item?.product;
                    const productId = item?.product_id || source?.id;
                    const price = Number(item?.price ?? source?.price ?? 0);
                    const quantity = type === 'product' ? Number(qty || item?.selectedQty || 1) : 1;
                    const total = this.totalDiscount(item?.type, price, item?.discount);
                    const totalCommission = this.getCommssion(item?.commission_type, price, item?.commission);
                    const currentStock = type === 'product'
                        ? (item?.current_stock !== undefined && item?.current_stock !== null
                            ? Number(item.current_stock)
                            : (source?.current_stock !== undefined && source?.current_stock !== null
                                ? Number(source.current_stock)
                                : null))
                        : null;

                    const payload = {
                        id: null,
                        product_id: productId,
                        code: this.catalogCode(item),
                        name: this.catalogName(item),
                        uom_title: type === 'product' ? (source?.uom_title || '') : '',
                        category_title: type === 'product' ? (source?.category_title || '') : '',
                        product_type: type,
                        image: this.catalogImage(item),
                        current_stock: currentStock,
                        itemData: {
                            price: price,
                            discount: item?.discount || 0,
                            point: item?.point || 0,
                            discountType: item?.type || null,
                            commission: item?.commission || 0,
                            commissionType: item?.commission_type || null,
                            totalCommission: totalCommission,
                            total: total,
                        },
                        product_qty: quantity,
                    };

                    const findIndex = this.dataCart.findIndex((val) => val.product_id == payload.product_id && val.product_type === type);
                    if (findIndex > -1) {
                        if (type === 'product') {
                            this.dataCart[findIndex].product_qty = Number(this.dataCart[findIndex].product_qty || 1) + quantity;
                            if (currentStock !== null) {
                                this.dataCart[findIndex].current_stock = currentStock;
                            }
                        } else {
                            this.dataCart[findIndex].product_qty = 1;
                        }
                    } else {
                        this.initializeProductPrice(payload);
                        this.dataCart.push(payload);
                        if (type === 'product' && currentStock === null) {
                            this.fetchCartItemStock(payload);
                        }
                    }

                    item.addToCart = true;
                    item.selectedQty = 1;
                    this.syncCatalogSelection();
                    this.calculatorProductPrice();
                    this.refreshIcons();
                },
                increaseCartQty(item) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.product_qty = 1;
                        this.calculatorProductPrice();
                        return;
                    }

                    item.product_qty = Number(item.product_qty || 0) + 1;
                    this.calculatorProductPrice();
                },
                decreaseCartQty(item) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.product_qty = 1;
                        this.calculatorProductPrice();
                        return;
                    }

                    item.product_qty = Math.max(1, Number(item.product_qty || 1) - 1);
                    this.calculatorProductPrice();
                    this.syncCatalogSelection();
                },
                isItemInCart(item) {
                    if (!item) return false;
                    if (item.product_type === 'product') {
                        return this.getCartQty(item) > 0;
                    }
                    return Boolean(item?.addToCart || this.getCartQty(item) > 0);
                },
                getCartQty(item) {
                    if (!item) return 0;
                    const key = this.catalogKey(item);
                    const found = this.dataCart.find((cart) => this.catalogKey(cart) === key);
                    return found ? Number(found.product_qty || 1) : 0;
                },
                increaseCatalogItem(item) {
                    if (!this.canEditOrderItems()) return;
                    const key = this.catalogKey(item);
                    const found = this.dataCart.find((cart) => this.catalogKey(cart) === key);
                    if (found) {
                        this.increaseCartQty(found);
                    } else {
                        this.addToCart(item, 1);
                    }
                    this.syncCatalogSelection();
                    this.refreshIcons();
                },
                decreaseCatalogItem(item) {
                    if (!this.canEditOrderItems()) return;
                    const key = this.catalogKey(item);
                    const index = this.dataCart.findIndex((cart) => this.catalogKey(cart) === key);
                    if (index > -1) {
                        const found = this.dataCart[index];
                        if (Number(found.product_qty || 1) <= 1) {
                            this.removeShippingCart(found, index);
                        } else {
                            this.decreaseCartQty(found);
                        }
                    }
                    this.syncCatalogSelection();
                    this.refreshIcons();
                },
                toggleServiceCart(item) {
                    if (!this.canEditOrderItems()) return;
                    const key = this.catalogKey(item);
                    const index = this.dataCart.findIndex((cart) => this.catalogKey(cart) === key);
                    if (index > -1) {
                        this.removeShippingCart(this.dataCart[index], index);
                    } else {
                        this.addToCart(item, 1);
                    }
                    this.syncCatalogSelection();
                    this.refreshIcons();
                },
                getCatalogCount(type) {
                    if (!this.dataFilter) return 0;
                    if (type === 'all') {
                        return (this.totalProductsCount + this.totalServicesCount) || this.dataFilter.length;
                    }
                    if (type === 'product') {
                        return this.totalProductsCount || this.dataFilter.filter((i) => i.product_type === 'product').length;
                    }
                    if (type === 'service') {
                        return this.totalServicesCount || this.dataFilter.filter((i) => i.product_type === 'service').length;
                    }
                    return 0;
                },
                clearSearch() {
                    this.searchFilter = '';
                    this.fiterProduct('');
                    this.$refs.orderSearch?.focus();
                },
                sortCatalog(order) {
                    this.catalogSort = order;
                    this.applyCatalogSort();
                },
                applyCatalogSort() {
                    if (!this.dataFilter || !this.dataFilter.length) return;
                    if (this.catalogSort === 'price_asc') {
                        this.dataFilter.sort((a, b) => this.catalogPrice(a) - this.catalogPrice(b));
                    } else if (this.catalogSort === 'price_desc') {
                        this.dataFilter.sort((a, b) => this.catalogPrice(b) - this.catalogPrice(a));
                    } else if (this.catalogSort === 'name_asc') {
                        this.dataFilter.sort((a, b) => this.catalogName(a).localeCompare(this.catalogName(b)));
                    }
                },
                focusCartItem(item) {
                    const selector = `[data-cart-item="${this.catalogKey(item)}"]`;
                    const cartItem = (this.$el ? this.$el.querySelector(selector) : null) || document.querySelector(selector);
                    (cartItem?.querySelector('[data-cart-price]') || cartItem?.querySelector('[data-cart-qty]'))?.focus();
                },
                priceRealTimeAction(item) {
                    if (!this.canEditOrderItems()) return;
                    if (item.product_type === 'product') {
                        const data = item.itemData;
                        if (data.pricingMethod !== 'auto_unit_price_v1'
                            && (data.unitPrice === '' || Number(data.unitPrice) !== data.legacyUnitPrice)) {
                            data.pricingMethod = 'auto_unit_price_v1';
                        }
                        this.calculatorProductPrice();
                        return;
                    }
                    const price = Math.max(0, Number(item?.itemData?.price || 0));
                    item.itemData.price = price;
                    item.itemData.total = this.totalDiscount(
                        item?.itemData?.discountType,
                        price,
                        item?.itemData?.discount
                    );
                    item.itemData.totalCommission = this.getCommssion(
                        item?.itemData?.commissionType,
                        price,
                        item?.itemData?.commission
                    );
                    this.calculatorProductPrice();
                },
                discountSelectOpton($event, item) {
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData.price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                discountRealTiemAction(item) {
                    if (!item.itemData.discountType || item.itemData.discountType === null || item.itemData.discountType === 'null') {
                        item.itemData.discountType = 'usd';
                    }
                    // Preserve the typed decimal; totalDiscount converts it for calculation.
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData.price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                qtyRealTimeAction(item) {
                    if (item?.product_type === 'service') {
                        item.product_qty = 1;
                    }
                    this.calculatorProductPrice();
                },
                commissionRealTimeAction(item) {
                    const commissionType = item?.itemData?.commissionType;
                    const price = item?.itemData?.price;
                    const commission = item?.itemData?.commission;
                    item.itemData.totalCommission = this.getCommssion(commissionType, price, commission);
                    if (!commission && commission !== 0) {
                        item.itemData.commission = 1;
                    }
                    this.calculatorProductPrice();
                },
                removeShippingCart(item, index) {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    this.dataFilter.find((val) => {
                        if (this.catalogKey(val) === this.catalogKey(item)) {
                            val.addToCart = false;
                        }
                    });
                    if (item?.id) {
                        this.orderDeleteId.push(item.id);
                    }
                    this.dataCart.splice(index, 1);
                    this.syncCatalogSelection();
                    this.calculatorProductPrice();
                    this.refreshIcons();
                },
                resetCart() {
                    if (!this.canEditOrderItems()) {
                        return;
                    }
                    this.dataCart = [];
                    this.dataFilter.forEach((item) => {
                        item.addToCart = false;
                    });
                    this.orderDeleteId = [];
                    this.calculatorProductPrice();
                    this.refreshIcons();
                    this.clearDraft();
                },
                saveDraft() {
                    if (this.orderId) return;
                    try {
                        const rawCustomerText = this.getScopeEl('#customer_id').find('option:selected').text()?.trim() || '';
                        const customerPlaceholder = @json(__('order.form.select_customer'));
                        const customerText = (rawCustomerText && rawCustomerText !== customerPlaceholder)
                            ? rawCustomerText
                            : (this.customerData?.name || this.customerData?.phone || '');

                        const rawShopText = this.getScopeEl('#shop_id').find('option:selected').text()?.trim() || '';
                        const shopPlaceholder = @json(__('order.form.select_shop'));
                        const shopText = (rawShopText && rawShopText !== shopPlaceholder)
                            ? rawShopText
                            : (this.shopData?.name || this.shopData?.phone || '');

                        const draft = {
                            formData: JSON.parse(JSON.stringify(this.formData)),
                            dataCart: JSON.parse(JSON.stringify(this.dataCart)),
                            shopData: this.shopData ? JSON.parse(JSON.stringify(this.shopData)) : null,
                            customerData: this.customerData ? JSON.parse(JSON.stringify(this.customerData)) : null,
                            customerText: customerText,
                            shopText: shopText,
                            subTotal: this.subTotal,
                            total: this.total,
                            commissionTotal: this.commissionTotal,
                            savedAt: Date.now()
                        };
                        sessionStorage.setItem('pos_order_create_draft', JSON.stringify(draft));
                        if (window.MDI && (this.dataCart.length > 0 || this.formData.customer_id || this.formData.shop_id)) {
                            window.MDI.markTabDirty(true);
                        }
                    } catch (e) {}
                },
                clearDraft() {
                    try {
                        sessionStorage.removeItem('pos_order_create_draft');
                        if (window.MDI) {
                            window.MDI.markTabDirty(false);
                        }
                    } catch (e) {}
                },
                restoreDraft() {
                    if (this.orderId) return;
                    try {
                        const stored = sessionStorage.getItem('pos_order_create_draft');
                        if (!stored) return;
                        const draft = JSON.parse(stored);
                        if (!draft) return;

                        if (draft.formData) {
                            this.formData = Object.assign({}, this.formData, draft.formData);
                            ['order_date', 'delivery_date'].forEach((field) => {
                                this.formData[field] = String(this.formData[field] || '').replace(/[០-៩]/g,
                                    digit => String(digit.charCodeAt(0) - 0x17E0));
                            });
                            if (this.formData.order_date) {
                                this.formData.order_date = moment(this.formData.order_date).format('YYYY-MM-DD');
                            }
                        }
                        if (draft.shopData) {
                            this.shopData = draft.shopData;
                        }
                        if (draft.customerData) {
                            this.customerData = draft.customerData;
                        }
                        if (Array.isArray(draft.dataCart) && draft.dataCart.length > 0) {
                            this.dataCart = draft.dataCart;
                            this.dataCart.forEach((item) => {
                                this.initializeProductPrice(item);
                                if (item.product_type === 'product' && (item.current_stock === null || item.current_stock === undefined)) {
                                    this.fetchCartItemStock(item);
                                }
                            });
                        }

                        this.$nextTick(() => {
                            if (draft.customerData) {
                                this.customerData = draft.customerData;
                                this.isCustomerRestricted = String(draft.customerData?.payment_restriction || '').toUpperCase() === 'Y';
                            }
                            const customerPlaceholder = @json(__('order.form.select_customer'));
                            if (draft.formData?.customer_id) {
                                const custText = (draft.customerText && draft.customerText !== customerPlaceholder)
                                    ? draft.customerText
                                    : (draft.customerData?.name || draft.customerData?.phone || '');
                                if (custText && this.getScopeEl('#customer_id').val() != draft.formData.customer_id) {
                                    this.setSelect2Value('#customer_id', draft.formData.customer_id, custText);
                                }
                                this.fetchPreviousBalance(draft.formData.customer_id);
                            }

                            const shopPlaceholder = @json(__('order.form.select_shop'));
                            if (draft.formData?.shop_id) {
                                const sText = (draft.shopText && draft.shopText !== shopPlaceholder)
                                    ? draft.shopText
                                    : (draft.shopData?.name || draft.shopData?.phone || '');
                                if (sText && this.getScopeEl('#shop_id').val() != draft.formData.shop_id) {
                                    this.setSelect2Value('#shop_id', draft.formData.shop_id, sText);
                                }
                            }

                            if (draft.formData?.order_date) {
                                $(this.$refs.orderDate).val(this.formData.order_date);
                            }
                            if (draft.formData?.delivery_date) {
                                $(this.$refs.deliveryDate).val(this.formData.delivery_date);
                            }
                            this.fiterProduct(this.searchFilter);
                            this.calculatorProductPrice();
                            this.refreshIcons();
                        });
                    } catch (e) {
                        console.error('Error restoring order draft:', e);
                    }
                },
                totalDiscount(type, price, discount) {
                    let amount = 0;
                    const basePrice = Number(price || 0);
                    const baseDiscount = Number(discount || 0);

                    if (type === 'percent') {
                        amount = Math.max(0, basePrice - (basePrice * baseDiscount / 100));
                    } else if (type === 'usd' || type === 'khr') {
                        amount = baseDiscount > basePrice ? 0 : basePrice - baseDiscount;
                    } else {
                        amount = basePrice;
                    }
                    return amount;
                },
                getCommssion(type, price, commission) {
                    let amount = Number(commission || 0);
                    if (type === 'percent') {
                        amount = Number(price || 0) * (Number(commission || 0) / 100);
                    }
                    return amount;
                },
                calculatorProductPrice() {
                    this.subTotal = 0;
                    this.total = 0;
                    this.commissionTotal = 0;
                    this.amountPaid = 0;

                    if (this.dataCart?.length > 0) {
                        this.dataCart.forEach((item) => {
                            const qty = this.cartQuantity(item);
                            let automatic = null;
                            if (this.isAutomaticProduct(item)) {
                                automatic = this.automaticProductAmounts(item);
                                if (!automatic) return;
                                item.itemData.price = automatic.price;
                                item.itemData.discount = automatic.discount;
                                item.itemData.discountType = 'usd';
                                item.itemData.total = automatic.unit;
                            }
                            const unitPrice = Number(item?.itemData?.price || 0);
                            const totalCommission = this.getCommssion(item?.itemData?.commissionType,
                                unitPrice, item?.itemData?.commission);

                            this.subTotal += automatic?.subtotal ?? this.roundMoney(qty * unitPrice);
                            this.commissionTotal += this.roundMoney(totalCommission * qty);
                            this.total += this.cartLineTotal(item);
                        });
                    }

                    this.subTotal = this.roundMoney(this.subTotal);
                    this.total = this.roundMoney(this.total);
                    this.commissionTotal = this.roundMoney(this.commissionTotal);
                    this.amountPaid = this.roundMoney(this.total - this.commissionTotal);
                    this.saveDraft();
                },
                async productValidation($cb) {
                    const errors = [];
                    const orderPayload = @json($data);
                    const order = orderPayload?.data ?? orderPayload ?? null;
                    const details = this.orderDetails(order);

                    if (this.dataCart.length) {
                        this.submitLoading = true;
                        for (const val of this.dataCart) {
                            val.error = false;
                            let findOrderDetailQty = 0;
                            if (details.length) {
                                const findOrderDetail = details.find((bkItem) => {
                                    const productId = bkItem.product?.id || bkItem.product_id;
                                    return bkItem.type === 'product' && productId == val.product_id;
                                });
                                findOrderDetailQty = findOrderDetail?.qty ?? 0;
                            }

                            if (val.product_type === 'product') {
                                const url = `/admin/select/find-shop-product?shop_id=${this.formData.shop_id || this.shopData?.id}&product_id=${val.product_id}`;
                                await this.fetchJson(url).then((res) => {
                                    if (res) {
                                        const currentStock = Number(res.current_stock || 0) + Number(findOrderDetailQty);
                                        val.error = currentStock < Number(val.product_qty);
                                    } else {
                                        val.error = true;
                                    }
                                });
                            }

                            if (val.error) {
                                errors.push(val.name);
                            }
                        }
                    }

                    $cb(errors);
                    this.submitLoading = false;
                },
                submitOrder() {
                    this.dataError = {};
                    if (!this.canEditOrderItems()) {
                        this.setValidationErrors({
                            payment_status: ['Only pending orders can be updated.']
                        });
                        return;
                    }

                    const clientErrors = this.validateOrderBeforeSubmit();

                    if (this.hasValidationErrors(clientErrors)) {
                        this.setValidationErrors(clientErrors);
                        return;
                    }

                    this.productValidation((valid) => {
                        if (valid.length > 0) {
                            const stockTemplate = @json(__('order.validation.out_of_stock_item'));
                            this.setValidationErrors({
                                dataCarts: valid.map((name) => stockTemplate.replace(':name', name))
                            });
                            return true;
                        }

                        this.confirmOrderSubmit(() => {
                            this.saveOrder();
                        });
                    });
                },
                confirmOrderSubmit(onConfirm) {
                    const confirmDialog = this.$store?.confirmDialog;

                    if (confirmDialog && typeof confirmDialog.open === 'function') {
                        confirmDialog.open({
                            data: {
                                title: @json(__('order.dialog.message')),
                                message: this.confirmMessage,
                                btnClose: @json(__('order.button.close')),
                                btnSave: this.btnSubmit,
                                typeAction: 'manual',
                                digPosition: "posTop",
                            },
                            afterClosed: (result) => {
                                if (!result) {
                                    return;
                                }

                                onConfirm();
                            }
                        });
                        return;
                    }

                    if (window.confirm(this.confirmMessage)) {
                        onConfirm();
                    }
                },
                confirmPaymentAction(message, btnSave, onConfirm) {
                    const confirmDialog = this.$store?.confirmDialog;

                    console.log('hiiiiiiii');

                    if (confirmDialog && typeof confirmDialog.open === 'function') {
                        confirmDialog.open({
                            data: {
                                title: @json(__('order.dialog.message')),
                                message: message,
                                btnClose: @json(__('order.button.close')),
                                btnSave: btnSave,
                                typeAction: 'manual',
                                digPosition: "posTop",
                            },
                            afterClosed: (result) => {
                                if (result) {
                                    onConfirm();
                                }
                            }
                        });
                        return;
                    }

                    if (window.confirm(message.replace(/<[^>]*>/g, ''))) {
                        onConfirm();
                    }
                },
                submitPayment() {
                    this.dataError = {};
                    const amount = Number(this.paymentAmount || 0);

                    if (amount <= 0) {
                        this.setValidationErrors({
                            amount: [@json(__('order.validation.payment_amount_required'))]
                        });
                        return;
                    }

                    if (Math.round(amount * 100) > Math.round(Number(this.remainingAmount || 0) * 100)) {
                        this.setValidationErrors({
                            amount: [@json(__('order.validation.payment_amount_exceeds'))]
                        });
                        return;
                    }

                    this.paymentLoading = true;
                    Axios.post(`{{ url('admin/' . ($routeName ?? 'order') . '/add-payment') }}/${this.orderId}`, {
                        amount: amount,
                        note: this.paymentNote,
                    }).then((res) => {
                        if (res.data.message === 'success') {
                            this.syncPaymentState(res.data);
                            this.paymentAmount = null;
                            this.paymentNote = '';
                        }
                    }).catch((e) => {
                        this.setValidationErrors(e.response?.data?.errors || {
                            general: [e.response?.data?.error || 'Unable to record payment.']
                        });
                    }).finally(() => {
                        this.paymentLoading = false;
                    });
                },
                startEditPayment(pay) {
                    this.editingPaymentId = pay?.id || null;
                    this.editPaymentAmount = Number(pay?.amount || 0);
                    this.editPaymentNote = pay?.note || '';
                    this.refreshIcons();
                },
                cancelPaymentEdit() {
                    this.editingPaymentId = null;
                    this.editPaymentAmount = null;
                    this.editPaymentNote = '';
                    this.refreshIcons();
                },
                savePaymentEdit(pay) {
                    this.dataError = {};
                    const amount = Number(this.editPaymentAmount || 0);
                    const availableBalanceCents = Math.round(Number(this.remainingAmount || 0) * 100) + Math.round(Number(pay?.amount || 0) * 100);

                    if (amount <= 0) {
                        this.setValidationErrors({
                            amount: [@json(__('order.validation.payment_amount_required'))]
                        });
                        return;
                    }

                    if (Math.round(amount * 100) > availableBalanceCents) {
                        this.setValidationErrors({
                            amount: [@json(__('order.validation.payment_amount_exceeds'))]
                        });
                        return;
                    }

                    this.paymentLoading = true;
                    Axios.put(`{{ url('admin/' . ($routeName ?? 'order') . '/update-payment') }}/${pay.id}`, {
                        amount: amount,
                        note: this.editPaymentNote,
                    }).then((res) => {
                        if (res.data.message === 'success') {
                            this.cancelPaymentEdit();
                            this.syncPaymentState(res.data);
                        }
                    }).catch((e) => {
                        this.setValidationErrors(e.response?.data?.errors || {
                            general: [e.response?.data?.error || 'Unable to update payment.']
                        });
                    }).finally(() => {
                        this.paymentLoading = false;
                    });
                },
                deletePayment(pay) {
                    this.confirmPaymentAction(@json(__('order.confirm.delete_payment')), @json(__('order.button.delete_payment')), () => {
                        this.paymentLoading = true;
                        Axios.delete(`{{ url('admin/' . ($routeName ?? 'order') . '/delete-payment') }}/${pay.id}`, {
                            data: {
                                _token: '{{ csrf_token() }}',
                            }
                        }).then((res) => {
                            if (res.data.message === 'success') {
                                this.cancelPaymentEdit();
                                this.syncPaymentState(res.data);
                            }
                        }).catch((e) => {
                            this.setValidationErrors(e.response?.data?.errors || {
                                general: [e.response?.data?.error || 'Unable to delete payment.']
                            });
                        }).finally(() => {
                            this.paymentLoading = false;
                        });
                    });
                },
                saveOrder() {
                    this.submitLoading = true;
                    const orderDateBase = this.formData.order_date
                        ? String(this.formData.order_date).split('T')[0].split(' ')[0]
                        : '';
                    let orderTime = moment().format('HH:mm:ss');
                    if (this.orderId && this.initialOrder?.order_date) {
                        const existingTime = moment(this.initialOrder.order_date).format('HH:mm:ss');
                        if (existingTime && existingTime !== '00:00:00') {
                            orderTime = existingTime;
                        }
                    }
                    const orderDateVal = orderDateBase ? `${orderDateBase} ${orderTime}` : '';
                    const data = {
                        ...this.formData,
                        order_date: orderDateVal,
                        dataCarts: this.dataCart.length ? JSON.stringify(this.dataCart) : JSON.stringify([]),
                        shop: this.shopData ? JSON.stringify(this.shopData) : null,
                        shop_id: this.formData.shop_id || this.shopData?.id,
                        commissionTotal: this.commissionTotal,
                        subTotal: this.subTotal,
                        total: this.total,
                        total_discount: parseFloat(this.subTotal) - parseFloat(this.total),
                        orderDelete: this.orderDeleteId,
                        creation_request_key: this.creationRequestKey,
                        previous_balance_fingerprint: this.previousBalanceFingerprint,
                    };

                    setTimeout(() => {
                        Axios({
                            url: `{{ route('admin-' . ($routeName ?? 'order') . '-save', $id) }}`,
                            method: 'POST',
                            data: {
                                ...data,
                                id: this.orderId,
                            }
                        }).then((res) => {
                            if (res.data.message === 'success') {
                                this.clearDraft();
                                this.submitLoading = false;
                                setTimeout(() => {
                                    const status = res.data.payment_status || 'Pending';
                                    const targetUrl = `{{ url('admin/' . ($routeName ?? 'order') . '/list') }}/${status}`;
                                    if (window.MDI) {
                                        const createTab = window.MDI.tabs.find(t => t.key === 'admin-order-create' || (t.url && t.url.includes('/admin/order/create')) || t.key === window.MDI.activeTabKey);
                                        if (createTab) {
                                            createTab.isDirty = false;
                                            window.MDI.closeTab(createTab, null, false);
                                        }
                                        const listKey = window.MDI.generateKey(targetUrl);
                                        const existingListTab = window.MDI.tabs.find(t => t.key === listKey);
                                        if (existingListTab) {
                                            existingListTab.url = targetUrl;
                                            const listPane = document.getElementById('tab-pane-' + existingListTab.key);
                                            if (listPane) {
                                                listPane.remove();
                                            }
                                        }
                                        if (window.iziToast) {
                                            const title = (window.workspaceTranslations && window.workspaceTranslations.success) || 'Success';
                                            const msg = @json(__('order.message.create_success')) || 'Order created successfully!';
                                            window.iziToast.success({ title: title, message: msg });
                                        }
                                        window.MDI.openUrlInTab(targetUrl, 'Order Management');
                                    } else {
                                        window.location.href = targetUrl;
                                    }
                                }, 100);
                            }
                        }).catch((e) => {
                            const fallbackMessage = e.response?.data?.error ||
                                e.response?.data?.message ||
                                'Unable to save order. Please check the order details.';
                            this.setValidationErrors(e.response?.data?.errors || {
                                general: [fallbackMessage]
                            });
                            this.submitLoading = false;
                        }).finally(() => {
                            this.submitLoading = false;
                        });
                    }, 300);
                },
                validateOrderBeforeSubmit() {
                    const errors = {};
                    const shopId = this.formData.shop_id || this.shopData?.id;

                    if (!shopId) {
                        errors.shop_id = [@json(__('order.validation.shop_required'))];
                    }

                    if (!this.formData.customer_id) {
                        errors.customer_id = [@json(__('order.validation.customer_required'))];
                    }

                    if (!this.formData.order_date) {
                        errors.order_date = [@json(__('order.validation.order_date_required'))];
                    }

                    if (!this.dataCart?.length) {
                        errors.dataCarts = [@json(__('order.validation.cart_required'))];
                    } else if (this.dataCart.some((item) => !item?.product_id || !item?.product_type)) {
                        errors.dataCarts = [@json(__('order.validation.cart_item_invalid'))];
                    } else if (this.dataCart.some((item) => item.product_type === 'product'
                        && (!Number.isSafeInteger(Number(item.product_qty)) || Number(item.product_qty) < 1))) {
                        errors.dataCarts = [@json(__('order.validation.quantity_invalid'))];
                    } else if (this.dataCart.some((item) => item.product_type === 'product' && !this.validProductPrice(item))) {
                        errors.dataCarts = [@json(__('order.validation.unit_price_invalid'))];
                    } else if (this.dataCart.some((item) => {
                        if (this.isAutomaticProduct(item)) return false;
                        const discount = Number(item?.itemData?.discount ?? 0);
                        return !Number.isFinite(discount) || discount < 0;
                    }) || Array.from(this.$el?.querySelectorAll('.booking-discount-group input:not([data-cart-price])') || [])
                        .some((input) => !input.validity.valid)) {
                        errors.dataCarts = [@json(__('order.validation.discount_invalid'))];
                    }

                    const partialPayment = Number(this.formData.partial_payment_amount || 0);
                    const maxAllowedPayment = this.orderId && !this.canEditOrderItems()
                        ? Number(this.remainingAmount || 0) : Number(this.totalAmountDue() || 0);
                    const totalDue = Number(this.totalAmountDue() || 0);
                    const paymentCents = Math.round(partialPayment * 100);
                    const totalDueCents = Math.round(totalDue * 100);

                    if (this.isCustomerRestricted && totalDueCents > 0) {
                        if (paymentCents < totalDueCents) {
                            this.showRestrictionError = true;
                            errors.partial_payment_amount = [@json(__('order.validation.full_payment_required'))];
                        } else {
                            this.showRestrictionError = false;
                            if (paymentCents > Math.round(maxAllowedPayment * 100)) {
                                errors.partial_payment_amount = [@json(__('order.validation.partial_max'))];
                            }
                        }
                    } else {
                        this.showRestrictionError = false;
                        if (partialPayment < 0) {
                            errors.partial_payment_amount = [@json(__('order.validation.partial_min'))];
                        } else if (paymentCents > Math.round(maxAllowedPayment * 100)) {
                            errors.partial_payment_amount = [@json(__('order.validation.partial_max'))];
                        }
                    }
                    if (this.formData.partial_payment_note && this.formData.partial_payment_note.length > 500) {
                        errors.partial_payment_note = ['Note cannot exceed 500 characters.'];
                    }

                    return errors;
                },
                hasValidationErrors(errors) {
                    return Object.keys(errors || {}).length > 0;
                },
                clearError(field) {
                    if (this.dataError && this.dataError[field]) {
                        const updated = { ...this.dataError };
                        delete updated[field];
                        this.dataError = updated;
                    }
                    if (field === 'partial_payment_amount') {
                        this.showRestrictionError = false;
                    }
                },
                setValidationErrors(errors = {}) {
                    this.dataError = errors || {};
                    this.showValidationToast();
                    this.$nextTick(() => {
                        this.refreshIcons();
                        ((this.$el ? this.$el.querySelector('.booking-error-summary, .booking-cart-error, .error') : null)
                            || document.querySelector('.booking-error-summary, .booking-cart-error, .error'))
                            ?.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                    });
                },
                quantityValidationMessage() {
                    const messages = this.dataError?.dataCarts;
                    return (Array.isArray(messages) ? messages : [messages])
                        .find((message) => message === @json(__('order.validation.quantity_invalid'))) || '';
                },
                hasQuantityValidationError(item) {
                    return Boolean(this.quantityValidationMessage()) && item?.product_type === 'product'
                        && (!Number.isSafeInteger(Number(item.product_qty)) || Number(item.product_qty) < 1);
                },
                validationMessages(section = null) {
                    return Object.values(this.dataError || {})
                        .flatMap((messages) => Array.isArray(messages) ? messages : [messages])
                        .filter((message) => message && (section !== 'details' || message !== this.quantityValidationMessage()));
                },
                getValidationIcon(message = '') {
                    const text = String(message || '').toLowerCase();
                    if (text.includes('customer')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`;
                    }
                    if (text.includes('shop')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`;
                    }
                    if (text.includes('barber') || text.includes('staff')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>`;
                    }
                    if (text.includes('delivery')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>`;
                    }
                    if (text.includes('order_date') || text.includes('date') || text.includes('order')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`;
                    }
                    if (text.includes('cart') || text.includes('item') || text.includes('product') || text.includes('service')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>`;
                    }
                    if (text.includes('pay') || text.includes('amount') || text.includes('price')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>`;
                    }
                    if (text.includes('note')) {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`;
                    }
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
                },
                showValidationToast() {
                    const message = this.validationMessages()[0];

                    if (!message) {
                        return;
                    }

                    if (window.iziToast) {
                        iziToast.error({
                            title: 'Error',
                            message: message,
                            position: 'topCenter',
                            timeout: 3500,
                        });
                    }
                }
            };
        }

        window.XDatacreateorder = XDatacreateorder;

        if (window.Alpine && typeof window.Alpine.data === 'function') {
            window.Alpine.data('XDatacreateorder', XDatacreateorder);
        }
        document.addEventListener('alpine:init', () => {
            if (window.Alpine && typeof window.Alpine.data === 'function') {
                window.Alpine.data('XDatacreateorder', XDatacreateorder);
            }
        });
    </script>
@stop
