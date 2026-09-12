@extends('admin::shared.layout')
@php
    $isEditing = !empty($id);
    $headerName = $isEditing ? __('booking.form.edit_title') : __('booking.form.create_title');
    $bookingReference = $isEditing
        ? (($data->invoice_number ?? null) ? '#' . $data->invoice_number : '#' . $id)
        : null;
    $bookingPageTitle = $isEditing ? __('booking.form.edit_title') : __('booking.order_details');
    $bookingActionLabel = $isEditing ? __('booking.button.update_booking') : __('booking.button.confirm_booking');
    $bookingConfirmMessage = $isEditing ? __('booking.confirm.update') : __('booking.confirm.create');
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => $headerName])
    <div class="form-admin booking booking-dashboard booking-order-ui" x-data="XDatacreateorder">
        <div class="booking-pos-workspace">
            <!-- Left Main Column (Catalog & Recent Bookings) -->
            <div class="booking-pos-main-col">
                <!-- Recent Bookings Reel -->
                <section class="booking-pos-recent-section">
                    <div class="booking-pos-section-header">
                        <div class="title-wrap">
                            <i data-feather="clock"></i>
                            <h2>{{ __('booking.recent_bookings') }}</h2>
                        </div>
                        <button type="button" class="btn-link-all" s-click-link="{{ route('admin-booking-list', 'Pending') }}">
                            <span>{{ __('booking.view_all') }}</span>
                            <i data-feather="arrow-right"></i>
                        </button>
                    </div>
                    <div class="booking-pos-recent-cards">
                        @forelse ($recentBookings as $recentBooking)
                            @php
                                $recentStatus = $recentBooking->payment_status ?: 'Pending';
                                $recentStatusClass = $recentStatus === 'Paid'
                                    ? 'ready'
                                    : ($recentStatus === 'Partial'
                                        ? 'waiting'
                                        : ($recentStatus === 'Cancel'
                                        ? 'canceled'
                                        : 'waiting'));
                                $recentStatusLabel = match ($recentStatus) {
                                    'Paid' => __('booking.status.ready'),
                                    'Partial' => __('booking.status.partial'),
                                    'Cancel' => __('booking.status.canceled'),
                                    default => __('booking.status.waiting'),
                                };
                                $recentCustomer = $recentBooking->customer?->name
                                    ?: ($recentBooking->customer?->phone ?: __('booking.walk_in_customer'));
                                $customerInitials = strtoupper(substr($recentCustomer, 0, 2));
                                $recentDate = $recentBooking->booking_date
                                    ? \Carbon\Carbon::parse($recentBooking->booking_date)->format('d M Y, h:i a')
                                    : '---';
                            @endphp
                            <article class="booking-pos-order-card"
                                s-click-link="{{ route('admin-booking-edit', $recentBooking->id) }}">
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
                                    <span class="invoice-num">{{ $recentBooking->invoice_number ? '#' . $recentBooking->invoice_number : '#' . $recentBooking->id }}</span>
                                </div>
                                <p class="booking-pos-order-date">
                                    <i data-feather="calendar"></i>
                                    <span>{{ $recentDate }}</span>
                                </p>
                            </article>
                        @empty
                            <div class="booking-pos-order-empty">
                                <i data-feather="calendar"></i>
                                <span>{{ __('booking.no_recent_bookings') }}</span>
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- Menu Catalog Section -->
                <section class="booking-pos-catalog-section">
                    <div class="booking-pos-catalog-header">
                        <div class="catalog-header-left">
                            <div class="title-wrap">
                                <i data-feather="grid"></i>
                                <h2>{{ __('booking.menu_catalog') }}</h2>
                            </div>
                        </div>
                    </div>
                    <!-- Modern Catalog Toolbar -->
                    <div class="booking-pos-catalog-toolbar">
                        <div class="toolbar-left-group">
                            <!-- Category Filter Tabs with Dynamic Counts -->
                            <div class="booking-pos-tabs" role="tablist">
                                <button type="button" class="booking-pos-tab"
                                    :class="selectType === 'all' ? 'active' : ''" @click="changeSelectType('all')">
                                    <i data-feather="layers"></i>
                                    <span>{{ __('booking.tab.all') }}</span>
                                    <span class="tab-count-badge" x-text="getCatalogCount('all')"></span>
                                </button>
                                <button type="button" class="booking-pos-tab"
                                    :class="selectType === 'product' ? 'active' : ''" @click="changeSelectType('product')">
                                    <i data-feather="package"></i>
                                    <span>{{ __('booking.tab.products') }}</span>
                                    <span class="tab-count-badge" x-text="getCatalogCount('product')"></span>
                                </button>
                                <button type="button" class="booking-pos-tab"
                                    :class="selectType === 'service' ? 'active' : ''" @click="changeSelectType('service')">
                                    <i data-feather="scissors"></i>
                                    <span>{{ __('booking.tab.services') }}</span>
                                    <span class="tab-count-badge" x-text="getCatalogCount('service')"></span>
                                </button>
                            </div>
                        </div>

                        <div class="toolbar-right-group">
                            <!-- Sort Select Dropdown -->
                            <div class="booking-catalog-select-wrap">
                                <select class="booking-pos-catalog-select" x-model="catalogSort" @change="sortCatalog($event.target.value)">
                                    <option value="default">Sort: Default</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                    <option value="name_asc">Name: A to Z</option>
                                </select>
                                <svg class="select-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>

                            <!-- Modern Search Box with Clear & Shortcut Badge -->
                            <div class="booking-pos-search-box">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" placeholder="{{ __('booking.search_catalog') }}" x-ref="bookingSearch" x-model="searchFilter"
                                    x-on:input="fiterProduct($event.target.value)" autocomplete="off">
                                <button type="button" class="btn-clear-search" x-show="searchFilter" @click="clearSearch()" title="Clear search">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                                <kbd class="search-kbd-hint" x-show="!searchFilter">⌘K</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Catalog Cards Grid -->
                    <div class="booking-pos-catalog-body">
                        <template x-if="loading">
                            <div class="booking-pos-products">
                                <template x-for="item in 8" :key="item">
                                    <div class="booking-pos-skeleton-card">
                                        <div class="booking-pos-skeleton-media"></div>
                                        <div class="booking-pos-skeleton-body">
                                            <div class="booking-pos-skeleton-line"></div>
                                            <div class="booking-pos-skeleton-line booking-pos-skeleton-line--sm"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="!loading && dataFilter?.length > 0">
                            <section class="booking-pos-products" aria-label="Products">
                                <template x-for="item in dataFilter" :key="catalogKey(item)">
                                    <article class="booking-pos-product-card" :class="isItemInCart(item) ? 'is-selected' : ''">
                                        <div class="booking-pos-product-media">
                                            <img :src="catalogImage(item)" :alt="catalogName(item)"
                                                onerror="this.src='{{ asset('images/logo/default.png') }}'">
                                            <span class="booking-pos-type-badge" :class="catalogBadge(item).toLowerCase()" x-text="catalogBadge(item)"></span>
                                            <template x-if="isItemInCart(item)">
                                                <span class="booking-pos-product-selected">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="booking-pos-product-body">
                                            <h3 class="booking-pos-product-title" x-text="catalogName(item)"></h3>
                                            <div class="booking-pos-price-row">
                                                <p class="booking-pos-price" x-text="formatCurrency(catalogPrice(item))"></p>
                                            </div>
                                            <div class="booking-pos-chip-list" x-show="item?.discount || item?.discount === 0">
                                                <span class="booking-pos-chip booking-pos-chip--discount"
                                                    x-text="catalogDiscountText(item)"></span>
                                                <span class="booking-pos-chip booking-pos-chip--commission" x-show="item?.commission || item?.commission === 0"
                                                    x-text="catalogCommissionText(item)"></span>
                                            </div>
                                        </div>
                                        <div class="booking-pos-product-foot">
                                            <!-- Product: Concept B Transforming Add to Cart / Morph Stepper -->
                                            <template x-if="item.product_type === 'product'">
                                                <div class="card-action-container">
                                                    <!-- Unadded State: Clean full-width + Add to Cart button -->
                                                    <template x-if="getCartQty(item) === 0">
                                                        <button type="button" class="booking-pos-add-btn booking-pos-add-btn--full"
                                                            :disabled="!canEditBookingItems()" @click="addToCart(item, 1)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                            <span>{{ __('booking.button.add_to_cart') }}</span>
                                                        </button>
                                                    </template>
                                                    <!-- Added State: Ergonomic pill stepper [ - ] X in Cart [ + ] -->
                                                    <template x-if="getCartQty(item) > 0">
                                                        <div class="booking-catalog-stepper--morph">
                                                            <button type="button" class="morph-stepper-btn morph-minus"
                                                                :disabled="!canEditBookingItems()"
                                                                @click.stop="decreaseCatalogItem(item)"
                                                                title="Decrease quantity">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                            </button>
                                                            <div class="morph-stepper-label">
                                                                <span class="morph-qty" x-text="getCartQty(item)"></span>
                                                                <span class="morph-in-cart">{{ __('booking.button.in_cart') }}</span>
                                                            </div>
                                                            <button type="button" class="morph-stepper-btn morph-plus"
                                                                :disabled="!canEditBookingItems()"
                                                                @click.stop="increaseCatalogItem(item)"
                                                                title="Increase quantity">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            <!-- Service: Single-click Toggle Button -->
                                            <template x-if="item.product_type !== 'product'">
                                                <div class="card-action-container">
                                                    <button type="button" class="booking-pos-add-btn booking-pos-add-btn--full"
                                                        :class="item?.addToCart ? 'is-service-added' : ''"
                                                        :disabled="!canEditBookingItems()" @click="toggleServiceCart(item)">
                                                        <svg x-show="!item?.addToCart" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                        <svg x-show="item?.addToCart" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                        <span x-text="item?.addToCart ? lblAddedToCart : lblAddToCart"></span>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </article>
                                </template>
                            </section>
                        </template>

                        <template x-if="!loading && (!dataFilter || dataFilter.length === 0)">
                            <div class="booking-pos-empty">
                                <div class="empty-icon-wrap">
                                    <i data-feather="search"></i>
                                </div>
                                <h3>{{ __('booking.empty.no_items_found') }}</h3>
                                <p>{{ __('booking.empty.try_adjusting_search') }}</p>
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
                            <h2>{{ $bookingPageTitle }}</h2>
                            @if ($bookingReference)
                                <span class="booking-ref-badge">{{ $bookingReference }}</span>
                            @endif
                        </div>
                        <span class="cart-items-counter">
                            <i data-feather="shopping-bag"></i>
                            <span x-text="(dataCart?.length || 0) + ' ' + (lblItems || 'Items')"></span>
                        </span>
                    </div>

                    <template x-if="validationMessages().length">
                        <div class="booking-error-summary">
                            <i data-feather="alert-circle" class="error-icon"></i>
                            <div class="booking-error-summary-list">
                                <template x-for="message in validationMessages()" :key="message">
                                    <span x-text="message"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Compact Customer & Shop Form Grid -->
                    <div class="sidebar-form-card">
                        <div class="sidebar-section-title">
                            <i data-feather="user"></i>
                            <span>{{ __('booking.form.customer_shop_info') }}</span>
                        </div>
                        <div class="sidebar-form-grid">
                            <div class="form-group form-group--full">
                                <label><i data-feather="user"></i> {{ __('booking.form.customer') }}</label>
                                <select name="customer_id" id="customer_id" x-model="formData.customer_id"
                                    :disabled="!canEditBookingItems()"
                                    class="booking-select booking-select--customer" x-init="fetchSelectCustomer()">
                                    <option value="">{{ __('booking.form.select_customer') }}</option>
                                </select>
                                <template x-for="item in dataError?.customer_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group">
                                <label><i data-feather="home"></i> {{ __('booking.form.shop') }}</label>
                                <select name="shop_id" id="shop_id" x-model="formData.shop_id"
                                    :disabled="!canEditBookingItems()"
                                    class="booking-select booking-select--shop" x-init="fetchSelectShop()">
                                    <option value="">{{ __('booking.form.select_shop') }}</option>
                                </select>
                                <template x-for="item in dataError?.shop_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group">
                                <label><i data-feather="user-check"></i> {{ __('booking.form.barber') }}</label>
                                <select name="barber_id" id="barber_id" x-model="formData.barber_id"
                                    :disabled="!canEditBookingItems()"
                                    class="booking-select booking-select--barber" x-init="fetchSelectBarber()">
                                    <option value="">{{ __('booking.form.select_barber') }}</option>
                                </select>
                                <template x-for="item in dataError?.barber_id">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                            <div class="form-group form-group--full">
                                <label><i data-feather="calendar"></i> {{ __('booking.form.booking_date') }}</label>
                                <input type="text" id="booking_date" x-model="formData.booking_date"
                                    :disabled="!canEditBookingItems()" autocomplete="off" class="sidebar-date-input">
                                <template x-for="item in dataError?.booking_date">
                                    <span class="error" x-text="item">Error</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Modern Booking Cart Item Cards */
                    .booking-pos-sidebar-middle .booking-pos-cart-item {
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 12px 14px !important;
                        margin-bottom: 10px !important;
                        display: flex !important;
                        gap: 12px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-item:hover {
                        border-color: #cbd5e1 !important;
                        box-shadow: 0 4px 10px -2px rgba(15, 23, 42, 0.06) !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-thumb {
                        width: 52px !important;
                        height: 52px !important;
                        border-radius: 10px !important;
                        overflow: hidden !important;
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        flex-shrink: 0 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-thumb img {
                        width: 100% !important;
                        height: 100% !important;
                        object-fit: cover !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-info {
                        flex: 1 !important;
                        min-width: 0 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 8px !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-title-row {
                        display: flex !important;
                        align-items: flex-start !important;
                        justify-content: space-between !important;
                        gap: 8px !important;
                        margin-bottom: 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-title-wrap {
                        min-width: 0 !important;
                        flex: 1 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-name {
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        margin: 0 !important;
                        line-height: 1.35 !important;
                        word-break: break-word !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-type {
                        display: inline-block !important;
                        padding: 1px 6px !important;
                        border-radius: 4px !important;
                        font-size: 10px !important;
                        font-weight: 700 !important;
                        margin-top: 3px !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-type.product {
                        background: #eff6ff !important;
                        color: #2563eb !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-type.service {
                        background: #f0fdf4 !important;
                        color: #16a34a !important;
                    }

                    /* Minimalist Soft Action Buttons */
                    .booking-pos-sidebar-middle .booking-pos-cart-actions {
                        display: flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        flex-shrink: 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-action {
                        width: 28px !important;
                        height: 28px !important;
                        border-radius: 8px !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        cursor: pointer !important;
                        transition: all 0.15s ease !important;
                        border: 1px solid transparent !important;
                        padding: 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-action svg {
                        width: 13px !important;
                        height: 13px !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-edit {
                        background: #f1f5f9 !important;
                        border-color: #e2e8f0 !important;
                        color: #475569 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-edit:hover:not(:disabled) {
                        background: #e2e8f0 !important;
                        border-color: #cbd5e1 !important;
                        color: #0f172a !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-remove {
                        background: #fef2f2 !important;
                        border-color: #fee2e2 !important;
                        color: #ef4444 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions .btn-cart-remove:hover:not(:disabled) {
                        background: #fee2e2 !important;
                        border-color: #fca5a5 !important;
                        color: #dc2626 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-actions button:disabled {
                        opacity: 0.45 !important;
                        cursor: not-allowed !important;
                    }

                    /* 3-Column Inputs Row */
                    .booking-pos-sidebar-middle .booking-pos-cart-adjustments {
                        display: grid !important;
                        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                        gap: 6px !important;
                        margin: 4px 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-adjustments:not(.has-commission) {
                        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-adjustments .booking-pos-adjustment {
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 3px !important;
                        min-width: 0 !important;
                        grid-column: auto !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-adjustments .booking-pos-adjustment:first-child {
                        grid-column: auto !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-adjustments label {
                        font-size: 10px !important;
                        font-weight: 600 !important;
                        color: #64748b !important;
                        margin: 0 !important;
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                        line-height: 1.2 !important;
                    }

                    /* Input Prefix Group */
                    .booking-input-prefix-group,
                    .booking-pos-sidebar-middle .booking-input-prefix-group {
                        display: flex !important;
                        align-items: stretch !important;
                        width: 100% !important;
                        height: 34px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 7px !important;
                        background: #ffffff !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-input-prefix-group:focus-within,
                    .booking-pos-sidebar-middle .booking-input-prefix-group:focus-within {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }
                    .booking-input-prefix-group .input-addon-prefix,
                    .booking-pos-sidebar-middle .booking-input-prefix-group .input-addon-prefix {
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
                        height: 100% !important;
                    }
                    .booking-input-prefix-group input,
                    .booking-pos-sidebar-middle .booking-input-prefix-group input {
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
                    .booking-input-prefix-group input::-webkit-inner-spin-button,
                    .booking-pos-sidebar-middle .booking-input-prefix-group input::-webkit-outer-spin-button,
                    .booking-pos-sidebar-middle .booking-input-prefix-group input::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }

                    /* Unified Discount Segmented Input Group */
                    .booking-pos-sidebar-middle .booking-discount-group {
                        display: flex !important;
                        align-items: stretch !important;
                        width: 100% !important;
                        height: 32px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 7px !important;
                        background: #ffffff !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-pos-sidebar-middle .booking-discount-group:focus-within {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }
                    .booking-pos-sidebar-middle .booking-discount-group .discount-prefix-select {
                        width: 36px !important;
                        min-width: 36px !important;
                        height: 100% !important;
                        border: none !important;
                        border-right: 1px solid #e2e8f0 !important;
                        outline: none !important;
                        background: #f8fafc !important;
                        font-size: 11px !important;
                        font-weight: 700 !important;
                        color: #475569 !important;
                        padding: 0 2px 0 6px !important;
                        cursor: pointer !important;
                        text-align: center !important;
                        border-radius: 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-discount-group input {
                        flex: 1 !important;
                        min-width: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        border: none !important;
                        outline: none !important;
                        padding: 0 6px !important;
                        font-size: 12px !important;
                        font-weight: 600 !important;
                        color: #0f172a !important;
                        background: transparent !important;
                        -moz-appearance: textfield !important;
                    }
                    .booking-pos-sidebar-middle .booking-discount-group input::-webkit-outer-spin-button,
                    .booking-pos-sidebar-middle .booking-discount-group input::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }

                    /* Footer Row */
                    .booking-pos-sidebar-middle .booking-pos-cart-footer-row {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        gap: 8px !important;
                        padding-top: 8px !important;
                        border-top: 1px dashed #e2e8f0 !important;
                        margin-top: 2px !important;
                    }

                    /* Pill-Shaped Quantity Stepper */
                    .booking-pos-sidebar-middle .booking-pos-qty-pill {
                        display: inline-flex !important;
                        align-items: center !important;
                        height: 28px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 9999px !important;
                        background: #f8fafc !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill:hover,
                    .booking-pos-sidebar-middle .booking-pos-qty-pill:focus-within {
                        border-color: #94a3b8 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-btn {
                        width: 28px !important;
                        height: 100% !important;
                        border: none !important;
                        background: transparent !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        font-size: 16px !important;
                        font-weight: 700 !important;
                        color: #475569 !important;
                        cursor: pointer !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        user-select: none !important;
                        transition: background-color 0.12s ease, color 0.12s ease !important;
                        line-height: 1 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-btn:hover:not(:disabled) {
                        background: #e2e8f0 !important;
                        color: #0f172a !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-btn:disabled {
                        opacity: 0.35 !important;
                        cursor: not-allowed !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-input {
                        width: 30px !important;
                        height: 100% !important;
                        border: none !important;
                        background: transparent !important;
                        text-align: center !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        outline: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        -moz-appearance: textfield !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-input::-webkit-outer-spin-button,
                    .booking-pos-sidebar-middle .booking-pos-qty-pill .qty-pill-input::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }

                    /* Service Qty Pill */
                    .booking-pos-sidebar-middle .booking-pos-service-qty-pill {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 4px !important;
                        padding: 2px 8px !important;
                        border-radius: 9999px !important;
                        background: #f1f5f9 !important;
                        border: 1px solid #e2e8f0 !important;
                        font-size: 11px !important;
                        font-weight: 600 !important;
                        color: #64748b !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-service-qty-pill .service-qty-val {
                        font-weight: 700 !important;
                        color: #0f172a !important;
                    }

                    /* Subtotal / Total */
                    .booking-pos-sidebar-middle .booking-pos-cart-price {
                        display: flex !important;
                        align-items: baseline !important;
                        gap: 4px !important;
                        margin: 0 !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-price .subtext {
                        font-size: 11.5px !important;
                        font-weight: 600 !important;
                        color: #64748b !important;
                    }
                    .booking-pos-sidebar-middle .booking-pos-cart-price strong {
                        font-size: 15px !important;
                        font-weight: 800 !important;
                        color: #2563eb !important;
                        letter-spacing: -0.2px !important;
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
                    .booking-select,
                    .sidebar-date-input {
                        width: 100% !important;
                        height: 34px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 7px !important;
                        padding: 0 10px !important;
                        font-size: 12.5px !important;
                        font-weight: 600 !important;
                        color: #0f172a !important;
                        background: #ffffff !important;
                        outline: none !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-select:focus,
                    .sidebar-date-input:focus {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
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
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 14px !important;
                        margin-top: 12px !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 12px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                    }
                    .booking-payment-form-title {
                        display: flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: none !important;
                    }
                    .booking-payment-form-title svg {
                        width: 16px !important;
                        height: 16px !important;
                        color: #2563eb !important;
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
                        height: 36px !important;
                        min-height: 36px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 8px !important;
                        background: #ffffff !important;
                        overflow: hidden !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group:focus-within {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }
                    .booking-payment-form--record .booking-input-prefix-group .input-addon-prefix {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        padding: 0 10px !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #64748b !important;
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
                        font-size: 13px !important;
                        font-weight: 600 !important;
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
                        height: 36px !important;
                        min-height: 36px !important;
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
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }

                    /* Record Payment Button */
                    .booking-pos-pay-btn--record {
                        width: 100% !important;
                        height: 38px !important;
                        min-height: 38px !important;
                        background: #2563eb !important;
                        border: 1px solid #1d4ed8 !important;
                        border-radius: 8px !important;
                        color: #ffffff !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 6px !important;
                        cursor: pointer !important;
                        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2) !important;
                        transition: all 0.15s ease !important;
                        padding: 0 12px !important;
                        margin-top: 2px !important;
                    }
                    .booking-pos-pay-btn--record span {
                        color: #ffffff !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                    }
                    .booking-pos-pay-btn--record svg {
                        width: 15px !important;
                        height: 15px !important;
                        color: #ffffff !important;
                    }
                    .booking-pos-pay-btn--record:hover:not(:disabled) {
                        background: #1d4ed8 !important;
                        border-color: #1e40af !important;
                        transform: translateY(-1px) !important;
                        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3) !important;
                    }
                    .booking-pos-pay-btn--record:disabled {
                        opacity: 0.6 !important;
                        cursor: not-allowed !important;
                        transform: none !important;
                        box-shadow: none !important;
                    }

                    /* Primary Submit CTA Button */
                    .booking-pos-sidebar-bottom .booking-pos-submit-btn {
                        margin-top: 14px !important;
                        height: 48px !important;
                        width: 100% !important;
                        border-radius: 12px !important;
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
                       Concept B: Modern Catalog Toolbar, Dropdowns & Cards
                       =================================================== */
                    .booking-pos-catalog-toolbar {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        gap: 12px !important;
                        margin-bottom: 16px !important;
                        flex-wrap: wrap !important;
                    }
                    .booking-pos-catalog-toolbar .toolbar-left-group {
                        display: flex !important;
                        align-items: center !important;
                        gap: 10px !important;
                        flex: 1 !important;
                        min-width: 280px !important;
                    }
                    .booking-pos-catalog-toolbar .toolbar-right-group {
                        display: flex !important;
                        align-items: center !important;
                        gap: 10px !important;
                        flex-shrink: 0 !important;
                    }

                    /* Category Filter Tabs with Badges */
                    .booking-pos-tabs {
                        display: flex !important;
                        align-items: center !important;
                        gap: 3px !important;
                        background: #f1f5f9 !important;
                        padding: 3px !important;
                        border-radius: 9px !important;
                        height: 38px !important;
                    }
                    .booking-pos-tabs .booking-pos-tab {
                        border: none !important;
                        background: transparent !important;
                        padding: 5px 12px !important;
                        border-radius: 7px !important;
                        font-size: 12.5px !important;
                        font-weight: 600 !important;
                        color: #64748b !important;
                        display: flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        cursor: pointer !important;
                        height: 100% !important;
                        transition: all 0.15s ease !important;
                    }
                    .booking-pos-tabs .booking-pos-tab svg {
                        width: 14px !important;
                        height: 14px !important;
                    }
                    .booking-pos-tabs .booking-pos-tab .tab-count-badge {
                        font-size: 11px !important;
                        font-weight: 700 !important;
                        padding: 0 6px !important;
                        border-radius: 9999px !important;
                        background: #e2e8f0 !important;
                        color: #475569 !important;
                        transition: all 0.15s ease !important;
                        line-height: 1.5 !important;
                    }
                    .booking-pos-tabs .booking-pos-tab.active {
                        background: #ffffff !important;
                        color: #2563eb !important;
                        font-weight: 700 !important;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
                    }
                    .booking-pos-tabs .booking-pos-tab.active .tab-count-badge {
                        background: #eff6ff !important;
                        color: #2563eb !important;
                    }
                    .booking-pos-tabs .booking-pos-tab:hover:not(.active) {
                        color: #0f172a !important;
                        background: rgba(255, 255, 255, 0.6) !important;
                    }

                    /* Sort Select Dropdown */
                    .booking-catalog-select-wrap {
                        position: relative !important;
                        display: inline-flex !important;
                        align-items: center !important;
                    }
                    .booking-pos-catalog-select {
                        height: 38px !important;
                        appearance: none !important;
                        -webkit-appearance: none !important;
                        background: #ffffff !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 8px !important;
                        padding: 0 30px 0 12px !important;
                        font-size: 12.5px !important;
                        font-weight: 600 !important;
                        color: #334155 !important;
                        cursor: pointer !important;
                        outline: none !important;
                        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-pos-catalog-select:focus {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }
                    .booking-pos-catalog-select:hover {
                        border-color: #94a3b8 !important;
                    }
                    .booking-catalog-select-wrap .select-chevron {
                        position: absolute !important;
                        right: 10px !important;
                        pointer-events: none !important;
                        width: 14px !important;
                        height: 14px !important;
                        color: #64748b !important;
                    }

                    /* Modern Search Box */
                    .booking-pos-search-box {
                        position: relative !important;
                        width: 260px !important;
                        height: 38px !important;
                        display: flex !important;
                        align-items: center !important;
                    }
                    .booking-pos-search-box .search-icon {
                        position: absolute !important;
                        left: 11px !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        width: 15px !important;
                        height: 15px !important;
                        color: #64748b !important;
                        pointer-events: none !important;
                    }
                    .booking-pos-search-box input {
                        width: 100% !important;
                        height: 100% !important;
                        padding: 0 38px 0 34px !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 8px !important;
                        font-size: 12.5px !important;
                        font-weight: 500 !important;
                        color: #0f172a !important;
                        outline: none !important;
                        background: #ffffff !important;
                        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
                    }
                    .booking-pos-search-box input::placeholder {
                        color: #94a3b8 !important;
                    }
                    .booking-pos-search-box input:focus {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
                    }
                    .booking-pos-search-box .btn-clear-search {
                        position: absolute !important;
                        right: 8px !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        width: 20px !important;
                        height: 20px !important;
                        border: none !important;
                        background: #f1f5f9 !important;
                        border-radius: 50% !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        cursor: pointer !important;
                        padding: 0 !important;
                        color: #64748b !important;
                        transition: all 0.12s ease !important;
                    }
                    .booking-pos-search-box .btn-clear-search:hover {
                        background: #e2e8f0 !important;
                        color: #0f172a !important;
                    }
                    .booking-pos-search-box .search-kbd-hint {
                        position: absolute !important;
                        right: 9px !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        font-size: 10px !important;
                        font-weight: 700 !important;
                        color: #94a3b8 !important;
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 4px !important;
                        padding: 1px 5px !important;
                        pointer-events: none !important;
                        line-height: 1.2 !important;
                    }

                    /* Product Cards Redesign */
                    .booking-pos-product-card {
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        overflow: hidden !important;
                        display: flex !important;
                        flex-direction: column !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
                        transition: all 0.2s ease !important;
                    }
                    .booking-pos-product-card:hover {
                        border-color: #cbd5e1 !important;
                        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07) !important;
                        transform: translateY(-1px) !important;
                    }
                    .booking-pos-product-card.is-selected {
                        border-color: #2563eb !important;
                        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18) !important;
                    }

                    .booking-pos-product-card .booking-pos-type-badge {
                        position: absolute !important;
                        top: 8px !important;
                        left: 8px !important;
                        padding: 3px 9px !important;
                        border-radius: 9999px !important;
                        font-size: 10px !important;
                        font-weight: 700 !important;
                        letter-spacing: 0.3px !important;
                        backdrop-filter: blur(8px) !important;
                        -webkit-backdrop-filter: blur(8px) !important;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
                        z-index: 2 !important;
                    }
                    .booking-pos-product-card .booking-pos-type-badge.product {
                        background: rgba(255, 255, 255, 0.92) !important;
                        color: #1e40af !important;
                        border: 1px solid rgba(191, 219, 254, 0.9) !important;
                    }
                    .booking-pos-product-card .booking-pos-type-badge.service {
                        background: rgba(255, 255, 255, 0.92) !important;
                        color: #15803d !important;
                        border: 1px solid rgba(187, 247, 208, 0.9) !important;
                    }

                    .booking-pos-product-card .booking-pos-product-selected {
                        position: absolute !important;
                        top: 8px !important;
                        right: 8px !important;
                        width: 24px !important;
                        height: 24px !important;
                        border-radius: 50% !important;
                        background: #10b981 !important;
                        color: #ffffff !important;
                        display: flex;
                        align-items: center !important;
                        justify-content: center !important;
                        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4) !important;
                        z-index: 2 !important;
                    }
                    .booking-pos-product-card .booking-pos-product-selected[style*="display: none"] {
                        display: none !important;
                    }
                    .booking-pos-product-card .booking-pos-product-selected svg {
                        width: 13px !important;
                        height: 13px !important;
                        color: #ffffff !important;
                    }

                    .booking-pos-product-card .booking-pos-product-body {
                        padding: 10px 12px !important;
                        flex: 1 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 6px !important;
                    }
                    .booking-pos-product-card .booking-pos-product-title {
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #0f172a !important;
                        margin: 0 !important;
                        line-height: 1.35 !important;
                        min-height: 35px !important;
                        display: -webkit-box !important;
                        -webkit-line-clamp: 2 !important;
                        -webkit-box-orient: vertical !important;
                        overflow: hidden !important;
                        word-break: break-word !important;
                    }
                    .booking-pos-product-card .booking-pos-price {
                        font-size: 16px !important;
                        font-weight: 800 !important;
                        color: #2563eb !important;
                        letter-spacing: -0.2px !important;
                        margin: 0 !important;
                    }

                    /* Concept B Card Foot & Transforming Buttons */
                    .booking-pos-product-card .booking-pos-product-foot {
                        padding: 8px 10px !important;
                        border-top: 1px solid #f1f5f9 !important;
                        background: #ffffff !important;
                        display: flex !important;
                        align-items: center !important;
                    }
                    .booking-pos-product-card .card-action-container {
                        width: 100% !important;
                    }

                    /* Full-width primary Add to Cart button */
                    .booking-pos-product-card .booking-pos-add-btn--full {
                        width: 100% !important;
                        height: 36px !important;
                        border-radius: 8px !important;
                        border: 1px solid #1d4ed8 !important;
                        background: #2563eb !important;
                        color: #ffffff !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 6px !important;
                        cursor: pointer !important;
                        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.22) !important;
                        transition: all 0.15s ease !important;
                        padding: 0 10px !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full span {
                        color: #ffffff !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full svg {
                        width: 15px !important;
                        height: 15px !important;
                        color: #ffffff !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full:hover:not(:disabled) {
                        background: #1d4ed8 !important;
                        border-color: #1e40af !important;
                        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35) !important;
                        transform: translateY(-1px) !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full:disabled {
                        opacity: 0.55 !important;
                        cursor: not-allowed !important;
                        transform: none !important;
                        box-shadow: none !important;
                    }

                    /* Service Added State */
                    .booking-pos-product-card .booking-pos-add-btn--full.is-service-added {
                        background: #ecfdf5 !important;
                        border-color: #a7f3d0 !important;
                        box-shadow: none !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full.is-service-added span {
                        color: #059669 !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full.is-service-added svg {
                        color: #059669 !important;
                    }
                    .booking-pos-product-card .booking-pos-add-btn--full.is-service-added:hover:not(:disabled) {
                        background: #d1fae5 !important;
                        border-color: #6ee7b7 !important;
                    }

                    /* Concept B Full-width Morph Stepper */
                    .booking-pos-product-card .booking-catalog-stepper--morph {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        width: 100% !important;
                        height: 36px !important;
                        border: 1px solid #bfdbfe !important;
                        border-radius: 8px !important;
                        background: #eff6ff !important;
                        padding: 2px !important;
                        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.05) !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-btn {
                        width: 32px !important;
                        height: 30px !important;
                        min-width: 32px !important;
                        border: none !important;
                        background: #ffffff !important;
                        border-radius: 6px !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        color: #2563eb !important;
                        cursor: pointer !important;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06) !important;
                        transition: all 0.12s ease !important;
                        padding: 0 !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-btn svg {
                        width: 13px !important;
                        height: 13px !important;
                        color: #2563eb !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-btn:hover:not(:disabled) {
                        background: #2563eb !important;
                        color: #ffffff !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-btn:hover:not(:disabled) svg {
                        color: #ffffff !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-btn:disabled {
                        opacity: 0.35 !important;
                        cursor: not-allowed !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-label {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 5px !important;
                        flex: 1 !important;
                        text-align: center !important;
                        user-select: none !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-label .morph-qty {
                        font-size: 14px !important;
                        font-weight: 800 !important;
                        color: #1d4ed8 !important;
                    }
                    .booking-pos-product-card .booking-catalog-stepper--morph .morph-stepper-label .morph-in-cart {
                        font-size: 11.5px !important;
                        font-weight: 600 !important;
                        color: #3b82f6 !important;
                    }
                </style>

                <!-- Sidebar Middle: Order Items Scrollable List -->
                <div class="booking-pos-sidebar-middle">
                    <div class="cart-head-row">
                        <div class="cart-head-title">
                            <i data-feather="shopping-cart"></i>
                            <span>{{ __('booking.order_items') }}</span>
                        </div>
                        <button type="button" class="btn-clear-cart" :disabled="!canEditBookingItems()"
                            @click="resetCart()" title="{{ __('booking.button.reset_order') }}">
                            <i data-feather="trash-2"></i>
                            <span>{{ __('booking.button.reset_order') }}</span>
                        </button>
                    </div>

                    <div class="cart-items-container">
                        <template x-if="dataCart?.length > 0">
                            <div class="cart-items-wrapper">
                                <template x-for="(item, index) in dataCart" :key="`${item.product_type}-${item.product_id}-${index}`">
                                    <article class="booking-pos-cart-item" :data-cart-item="catalogKey(item)">
                                        <div class="booking-pos-cart-thumb">
                                            <img :src="item.image" :alt="item.name"
                                                onerror="this.src='{{ asset('images/logo/default.png') }}'">
                                        </div>
                                        <div class="booking-pos-cart-info">
                                            <div class="booking-pos-cart-title-row">
                                                <div class="booking-pos-cart-title-wrap">
                                                    <h3 class="booking-pos-cart-name" x-text="item?.name"></h3>
                                                    <span class="booking-pos-cart-type" :class="item.product_type"
                                                        x-text="item.product_type === 'service' ? lblTabService : lblTabProduct"></span>
                                                </div>
                                                <div class="booking-pos-cart-actions">
                                                    <button type="button" class="btn-cart-action btn-cart-edit" @click="focusCartItem(item)" title="{{ __('booking.action.edit') }}"
                                                        aria-label="{{ __('booking.action.edit') }}" :disabled="!canEditBookingItems()">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <button type="button" class="btn-cart-action btn-cart-remove" @click="removeShippingCart(item, index)"
                                                        title="{{ __('booking.action.delete') }}" aria-label="{{ __('booking.action.delete') }}"
                                                        :disabled="!canEditBookingItems()">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="booking-pos-cart-adjustments" :class="{'has-commission': item?.itemData?.commission || item?.itemData?.commission === 0}">
                                                <div class="booking-pos-adjustment">
                                                    <label>{{ __('booking.unit_price') }}</label>
                                                    <div class="booking-input-prefix-group">
                                                        <span class="input-addon-prefix">$</span>
                                                        <input type="number" min="0" step="0.01" data-cart-price
                                                            x-model="item.itemData.price"
                                                            :disabled="!canEditBookingItems()"
                                                            x-on:input="priceRealTimeAction(item)">
                                                    </div>
                                                </div>
                                                <div class="booking-pos-adjustment">
                                                    <label>{{ __('booking.discount') }}</label>
                                                    <div class="booking-discount-group">
                                                        <select class="discount-prefix-select"
                                                            x-model="item.itemData.discountType"
                                                            :value="item?.itemData?.discountType"
                                                            :disabled="!canEditBookingItems()"
                                                            @change="discountSelectOpton($event, item)">
                                                            <option value="usd">$</option>
                                                            <option value="percent">%</option>
                                                        </select>
                                                        <input type="number" min="0" step="1"
                                                            x-model="item.itemData.discount"
                                                            :disabled="!canEditBookingItems()"
                                                            x-on:input="discountRealTiemAction(item)">
                                                    </div>
                                                </div>
                                                <div class="booking-pos-adjustment"
                                                    x-show="item?.itemData?.commission || item?.itemData?.commission === 0">
                                                    <label>{{ __('booking.commission') }}</label>
                                                    <div class="booking-input-prefix-group">
                                                        <span class="input-addon-prefix" x-text="item?.itemData?.commissionType === 'percent' ? '%' : '$'">$</span>
                                                        <input type="number" min="0" step="1"
                                                            x-model="item.itemData.commission"
                                                            :disabled="!canEditBookingItems()"
                                                            x-on:input="commissionRealTimeAction(item)">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="booking-pos-cart-footer-row">
                                                <div class="booking-pos-qty-pill" x-show="item.product_type === 'product'"
                                                    aria-label="Quantity selector">
                                                    <button type="button" class="qty-pill-btn qty-btn-minus"
                                                        :disabled="!canEditBookingItems() || Number(item.product_qty) <= 1" @click="decreaseCartQty(item)"
                                                        aria-label="Decrease">&minus;</button>
                                                    <input type="number" min="1" step="1" data-cart-qty
                                                        x-model="item.product_qty" :disabled="!canEditBookingItems()"
                                                        x-on:input="qtyRealTimeAction(item)"
                                                        class="qty-pill-input">
                                                    <button type="button" class="qty-pill-btn qty-btn-plus"
                                                        :disabled="!canEditBookingItems()" @click="increaseCartQty(item)"
                                                        aria-label="Increase">+</button>
                                                </div>
                                                <div class="booking-pos-service-qty-pill" x-show="item.product_type !== 'product'">
                                                    <span class="service-qty-label">{{ __('booking.qty') }}:</span>
                                                    <span class="service-qty-val">1</span>
                                                </div>

                                                <div class="booking-pos-cart-price">
                                                    <span class="subtext">{{ __('booking.total') }}:</span>
                                                    <strong x-text="formatCurrency(cartLineTotal(item))"></strong>
                                                </div>
                                            </div>

                                            <template x-if="item?.error">
                                                <span class="booking-cart-error">
                                                    <i data-feather="alert-triangle"></i>
                                                    {{ __('booking.limited_or_out_of_stock') }}
                                                </span>
                                            </template>
                                        </div>
                                    </article>
                                </template>
                            </div>
                        </template>

                        <template x-if="!dataCart?.length">
                            <div class="sidebar-empty-cart">
                                <i data-feather="shopping-bag"></i>
                                <p>{{ __('booking.empty_cart.title') }}</p>
                                <small>{{ __('booking.empty_cart.description') }}</small>
                            </div>
                        </template>

                        <template x-for="item in dataError?.dataCarts">
                            <span class="booking-cart-error" x-text="item"></span>
                        </template>
                    </div>
                </div>

                <!-- Sidebar Bottom: Payment & Checkout -->
                <div class="booking-pos-sidebar-bottom">
                    <!-- Payment Methods Segmented Control -->
                    <div class="payment-methods-row">
                        <label class="payment-method-btn" :class="formData.pay_way === 'Cash' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="Cash" x-model="formData.pay_way" :disabled="!canEditBookingItems()">
                            <i data-feather="dollar-sign"></i>
                            <span>{{ __('booking.payment.cash') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'ABA' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="ABA" x-model="formData.pay_way" :disabled="!canEditBookingItems()">
                            <i data-feather="smartphone"></i>
                            <span>{{ __('booking.payment.aba') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'Card' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="Card" x-model="formData.pay_way" :disabled="!canEditBookingItems()">
                            <i data-feather="credit-card"></i>
                            <span>{{ __('booking.payment.card') }}</span>
                        </label>
                        <label class="payment-method-btn" :class="formData.pay_way === 'QR' ? 'is-active' : ''">
                            <input type="radio" name="pay_way" value="QR" x-model="formData.pay_way" :disabled="!canEditBookingItems()">
                            <i data-feather="grid"></i>
                            <span>{{ __('booking.payment.qr') }}</span>
                        </label>
                    </div>

                    <!-- Payment Summary Breakdown -->
                    <div class="payment-summary-box">
                        <div class="summary-line">
                            <span>{{ __('booking.subtotal') }}</span>
                            <span x-text="formatCurrency(subTotal)"></span>
                        </div>
                        <div class="summary-line">
                            <span>{{ __('booking.discount') }}</span>
                            <span class="deduct" x-text="formatDeductionCurrency(subTotal - total)"></span>
                        </div>
                        <div class="summary-line">
                            <span>{{ __('booking.commission') }}</span>
                            <span class="deduct" x-text="formatDeductionCurrency(commissionTotal)"></span>
                        </div>
                        <div class="summary-total-line">
                            <span>{{ __('booking.total_payable') }}</span>
                            <strong x-text="formatCurrency(amountPaid)"></strong>
                        </div>
                    </div>

                    <!-- New Booking Partial Payment -->
                    <template x-if="!bookingId">
                        <div class="partial-payment-input-wrap">
                            <label><i data-feather="dollar-sign"></i> {{ __('booking.initial_partial_payment') }}</label>
                            <input type="number" min="0" step="0.01" x-model="formData.partial_payment_amount"
                                :max="subTotal" placeholder="{{ __('booking.placeholder.partial_payment') }}" autocomplete="off">
                        </div>
                    </template>

                    <!-- Edit Booking Payment Ledger Progress -->
                    <template x-if="bookingId">
                        <div class="booking-payment-ledger">
                            <div class="booking-payment-progress-wrapper">
                                <div class="booking-payment-progress-head">
                                    <span>{{ __('booking.payment_progress') }}</span>
                                    <strong x-text="paymentProgressPercentage() + '%'"></strong>
                                </div>
                                <div class="booking-payment-progress-bar">
                                    <div class="booking-payment-progress-fill" :style="`width: ${paymentProgressPercentage()}%`" :class="statusClass(bookingStatus)"></div>
                                </div>
                            </div>
                            <div class="booking-payment-ledger-grid">
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('booking.paid') }}</span>
                                    <strong class="val val--paid" x-text="formatCurrency(paidAmount)"></strong>
                                </div>
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('booking.remaining') }}</span>
                                    <strong class="val val--remaining" x-text="formatCurrency(remainingAmount)"></strong>
                                </div>
                                <div class="booking-payment-ledger-card">
                                    <span class="label">{{ __('booking.status_label') }}</span>
                                    <span class="booking-payment-status-pill" :class="statusClass(bookingStatus)">
                                        <span class="status-dot"></span>
                                        <span x-text="statusLabel(bookingStatus)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Record Payment Form (Edit mode) -->
                    <template x-if="canRecordPayment()">
                        <div class="booking-payment-form booking-payment-form--record">
                            <div class="booking-payment-form-title">
                                <i data-feather="plus-circle"></i>
                                <span>{{ __('booking.record_payment') }}</span>
                            </div>
                            <div class="booking-payment-form-grid">
                                <div class="form-group">
                                    <label>{{ __('booking.amount') }}</label>
                                    <div class="booking-input-prefix-group">
                                        <span class="input-addon-prefix">$</span>
                                        <input type="number" min="0.01" step="0.01" x-model="paymentAmount"
                                            :max="remainingAmount" placeholder="0.00" autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('booking.note') }}</label>
                                    <input type="text" x-model="paymentNote" class="booking-note-input"
                                        placeholder="{{ __('booking.placeholder.note') }}" maxlength="500" autocomplete="off">
                                </div>
                            </div>
                            <button type="button" class="booking-pos-pay-btn booking-pos-pay-btn--record" :disabled="paymentLoading"
                                @click="submitPayment()">
                                <span class="booking-spinner" x-show="paymentLoading">
                                    <span id="spinner"></span>
                                </span>
                                <i data-feather="check-circle" x-show="!paymentLoading"></i>
                                <span x-text="paymentLoading ? lblRecording : lblRecordPayment"></span>
                            </button>
                        </div>
                    </template>

                    <!-- Submit CTA Button -->
                    <button type="button" class="booking-pos-submit-btn" :disabled="submitLoading || !canEditBookingItems()"
                        @click="submitBooking()">
                        <span class="booking-spinner" x-show="submitLoading">
                            <span id="spinner"></span>
                        </span>
                        <span x-text="!canEditBookingItems() ? lblBookingLocked : (submitLoading ? lblSaving : btnSubmit)"></span>
                        <i data-feather="arrow-right" x-show="!submitLoading"></i>
                    </button>
                </div>
            </aside>
        </div>
    </div>
    @include('admin::components.confirm-dialog')
@stop

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('XDatacreateorder', () => ({
                loading: false,
                dataError: {},
                formData: {
                    shop_id: null,
                    barber_id: null,
                    status: 'confirmed',
                    customer_id: null,
                    disable: false,
                    booking_date: moment().format('YYYY-MM-DD'),
                    pay_way: 'Cash',
                    partial_payment_amount: null,
                },
                btnSubmit: @json($bookingActionLabel),
                confirmMessage: @json($bookingConfirmMessage),
                lblRecording: @json(__('booking.recording')),
                lblRecordPayment: @json(__('booking.record_payment')),
                lblBookingLocked: @json(__('booking.booking_locked')),
                lblSaving: @json(__('booking.saving')),
                lblItems: @json(__('booking.items')),
                lblTabService: @json(__('booking.tab.service')),
                lblTabProduct: @json(__('booking.tab.product')),
                lblInCart: @json(__('booking.button.in_cart')),
                lblAddToCart: @json(__('booking.button.add_to_cart')),
                lblAddedToCart: @json(__('booking.button.added_to_cart')),
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
                bookingId: null,
                paidAmount: 0,
                remainingAmount: 0,
                bookingStatus: 'Pending',
                paymentHistory: [],
                paymentAmount: null,
                paymentNote: '',
                editingPaymentId: null,
                editPaymentAmount: null,
                editPaymentNote: '',
                selectType: 'all',
                shopData: null,
                bookingDeleteId: [],
                initialBooking: null,
                initialSelectedShop: null,
                initialSelectedBarber: null,
                init() {
                    const payload = @json($data);
                    const booking = payload?.data ?? payload ?? null;
                    const selectedShop = @json($selectedShop);
                    const selectedBarber = @json($selectedBarber);
                    const details = this.bookingDetails(booking);
                    this.initialBooking = booking;
                    this.initialSelectedShop = selectedShop;
                    this.initialSelectedBarber = selectedBarber;

                    this.shopData = booking?.shop || selectedShop || null;
                    this.formData.shop_id = booking?.shop_id || selectedShop?.id || null;
                    this.formData.barber_id = booking?.barber_id || selectedBarber?.id || booking?.barber?.id || null;
                    this.formData.customer_id = booking?.customer_id || booking?.customer?.id || null;
                    this.formData.booking_date = booking?.booking_date
                        ? moment(booking.booking_date).format('YYYY-MM-DD')
                        : moment().format('YYYY-MM-DD');
                    this.formData.pay_way = booking?.pay_way || 'Cash';
                    this.dataCart = [];

                    window.addEventListener('keydown', (e) => {
                        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                            e.preventDefault();
                            this.$refs.bookingSearch?.focus();
                            this.$refs.bookingSearch?.select();
                        }
                    });

                    this.bookingId = booking?.id || null;
                    this.syncPaymentState({
                        paid_amount: booking?.paid_amount || 0,
                        remaining_amount: booking?.remaining_amount || 0,
                        payment_status: booking?.payment_status || 'Pending',
                        payments: booking?.payments || [],
                    });
                    if (details.length > 0) {
                        details.forEach((detail) => {
                            this.dataCart.push(this.mapDetailToCart(detail));
                        });
                    }

                    this.calculatorProductPrice();
                    this.fiterProduct(this.searchFilter);
                    this.initBookingDatepicker();
                    this.$nextTick(() => {
                        this.prefillSelectFields(booking, selectedShop, selectedBarber);
                    });
                    this.refreshIcons();
                },
                initBookingDatepicker() {
                    const vm = this;
                    $("#booking_date").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeYear: true,
                        changeMonth: true,
                        gotoCurrent: true,
                        yearRange: "-10:+10",
                        onSelect(selectedDate) {
                            vm.formData.booking_date = selectedDate;
                        }
                    });
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
                bookingDetails(booking) {
                    return booking?.bookingDetail || booking?.booking_detail || [];
                },
                canEditBookingItems() {
                    return !this.bookingId || this.bookingStatus === 'Pending';
                },
                canRecordPayment() {
                    return this.bookingId &&
                        this.bookingStatus !== 'Paid' &&
                        this.bookingStatus !== 'Cancel' &&
                        Number(this.remainingAmount || 0) > 0;
                },
                paymentProgressPercentage() {
                    const total = Number(this.total || 0);
                    const paid = Number(this.paidAmount || 0);
                    if (total <= 0) return 0;
                    return Math.min(100, Math.max(0, Math.round((paid / total) * 100)));
                },
                syncPaymentState(data = {}) {
                    this.paidAmount = Number(data?.paid_amount || 0);
                    this.remainingAmount = Number(data?.remaining_amount || 0);
                    this.bookingStatus = data?.payment_status || 'Pending';
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
                setSelect2Value(selector, value, text) {
                    if (!value) {
                        return;
                    }

                    const $select = $(selector);
                    $select.find('option').filter((index, option) => String(option.value) === String(value)).remove();
                    $select.append(new Option(text || value, value, true, true)).trigger('change');
                },
                prefillSelectFields(booking, selectedShop, selectedBarber) {
                    const shop = booking?.shop || selectedShop;
                    const barber = booking?.barber || selectedBarber;
                    const customer = booking?.customer;

                    this.setSelect2Value('#shop_id', this.formData.shop_id, shop?.name || shop?.phone);
                    this.setSelect2Value('#barber_id', this.formData.barber_id, barber?.name || barber?.phone);
                    this.setSelect2Value('#customer_id', this.formData.customer_id, customer?.name || customer?.phone);
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

                    return {
                        id: detail?.id,
                        product_id: productId,
                        name: item?.name || '---',
                        image: item?.image_url || '{{ asset('images/logo/default.png') }}',
                        itemData: {
                            price: price,
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
                fetchSelectShop() {
                    $('#shop_id').select2({
                        placeholder: @json(__('booking.form.select_shop')),
                        ajax: {
                            url: '{{ route('admin-select-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : item?.phone,
                                    id: item.id,
                                    item: item,
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        document.querySelector('.select2-search__field')?.focus();
                    }).on('select2:select', (event) => {
                        const selected = event.params.data;
                        this.formData.shop_id = selected.id;
                        this.shopData = selected.item || {
                            id: selected.id,
                            name: selected.text
                        };
                        this.dataCart = [];
                        this.bookingDeleteId = [];
                        this.syncCatalogSelection();
                        this.calculatorProductPrice();
                        this.fiterProduct(this.searchFilter);
                    });
                    this.prefillSelectFields(this.initialBooking, this.initialSelectedShop, this.initialSelectedBarber);
                },
                fetchSelectBarber() {
                    $('#barber_id').select2({
                        placeholder: @json(__('booking.form.select_barber')),
                        allowClear: true,
                        ajax: {
                            url: '{{ route('admin-select-barber') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : item?.phone,
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        document.querySelector('.select2-search__field')?.focus();
                    }).on('select2:select', (event) => {
                        this.formData.barber_id = event.params.data.id;
                    }).on('select2:clear', () => {
                        this.formData.barber_id = null;
                    });
                    this.prefillSelectFields(this.initialBooking, this.initialSelectedShop, this.initialSelectedBarber);
                },
                fetchSelectCustomer() {
                    $('#customer_id').select2({
                        placeholder: @json(__('booking.form.select_customer')),
                        ajax: {
                            url: '{{ route('admin-select-customer') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : item?.phone,
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        document.querySelector('.select2-search__field')?.focus();
                    }).on('select2:select', (event) => {
                        this.formData.customer_id = event.params.data.id;
                    }).on('select2:clear', () => {
                        this.formData.customer_id = null;
                    });
                    this.prefillSelectFields(this.initialBooking, this.initialSelectedShop, this.initialSelectedBarber);
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
                            const selectedTypes = this.selectType === 'all' ? ['product', 'service'] : [this.selectType];
                            const responses = await Promise.all(selectedTypes.map(async (type) => {
                                const res = await this.fetchJson(
                                    `/admin/select/product?search=${encodeURIComponent(search || '')}&shop_id=${shopId}&type=${type}`
                                );
                                return this.normalizeCatalogItems(res?.data || [], type);
                            }));

                            if (this.selectType === 'all') {
                                this.totalProductsCount = responses[0]?.length || 0;
                                this.totalServicesCount = responses[1]?.length || 0;
                            } else if (this.selectType === 'product') {
                                this.totalProductsCount = responses[0]?.length || 0;
                            } else if (this.selectType === 'service') {
                                this.totalServicesCount = responses[0]?.length || 0;
                            }

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
                            name: source?.name || '---',
                            image: source?.image_url || '{{ asset('images/logo/default.png') }}',
                            price: price,
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
                catalogImage(item) {
                    return item?.image || this.catalogSource(item)?.image_url || '{{ asset('images/logo/default.png') }}';
                },
                catalogPrice(item) {
                    return Number(item?.price ?? this.catalogSource(item)?.price ?? 0);
                },
                catalogBadge(item) {
                    return item?.product_type === 'service' ? @json(__('booking.tab.service')) : @json(__('booking.tab.product'));
                },
                catalogDiscountText(item) {
                    const type = item?.type;
                    const discount = Number(item?.discount || 0);
                    return type === 'percent' ? `-${discount}%` : `-${this.formatCurrency(discount)}`;
                },
                catalogCommissionText(item) {
                    const type = item?.commission_type;
                    const commission = Number(item?.commission || 0);
                    const commText = @json(__('booking.commission_short'));
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
                cartLineTotal(item) {
                    const qty = item?.product_type === 'product' ? Number(item?.product_qty || 1) : 1;
                    const unitPrice = item?.itemData?.discount
                        ? Number(item?.itemData?.total || 0)
                        : Number(item?.itemData?.price || 0);
                    return qty * unitPrice;
                },
                formatDate(value, pattern = 'DD MMM YYYY') {
                    if (!value) {
                        return '--';
                    }
                    return moment(value).format(pattern);
                },
                statusLabel(status) {
                    if (status === 'Paid') {
                        return @json(__('booking.status.ready'));
                    }
                    if (status === 'Partial') {
                        return @json(__('booking.status.partial'));
                    }
                    if (status === 'Cancel') {
                        return @json(__('booking.status.canceled'));
                    }
                    return @json(__('booking.status.waiting'));
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
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.selectedQty = 1;
                        return;
                    }

                    item.selectedQty = Number(item.selectedQty || 1) + 1;
                },
                decreasePreviewQty(item) {
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.selectedQty = 1;
                        return;
                    }

                    item.selectedQty = Math.max(1, Number(item.selectedQty || 1) - 1);
                },
                addToCart(item, qty = null) {
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    const type = item?.product_type || 'product';
                    const source = type === 'service' ? item?.service : item?.product;
                    const productId = item?.product_id || source?.id;
                    const price = Number(item?.price ?? source?.price ?? 0);
                    const quantity = type === 'product' ? Number(qty || item?.selectedQty || 1) : 1;
                    const total = this.totalDiscount(item?.type, price, item?.discount);
                    const totalCommission = this.getCommssion(item?.commission_type, price, item?.commission);

                    const payload = {
                        id: null,
                        product_id: productId,
                        name: this.catalogName(item),
                        product_type: type,
                        image: this.catalogImage(item),
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
                        } else {
                            this.dataCart[findIndex].product_qty = 1;
                        }
                    } else {
                        this.dataCart.push(payload);
                    }

                    item.addToCart = true;
                    item.selectedQty = 1;
                    this.syncCatalogSelection();
                    this.calculatorProductPrice();
                    this.refreshIcons();
                },
                increaseCartQty(item) {
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    if (item?.product_type !== 'product') {
                        item.product_qty = 1;
                        this.calculatorProductPrice();
                        return;
                    }

                    item.product_qty = Number(item.product_qty || 1) + 1;
                    this.calculatorProductPrice();
                },
                decreaseCartQty(item) {
                    if (!this.canEditBookingItems()) {
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
                    if (!this.canEditBookingItems()) return;
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
                    if (!this.canEditBookingItems()) return;
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
                    if (!this.canEditBookingItems()) return;
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
                    this.$refs.bookingSearch?.focus();
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
                    const cartItem = document.querySelector(selector);
                    (cartItem?.querySelector('[data-cart-price]') || cartItem?.querySelector('[data-cart-qty]'))?.focus();
                },
                priceRealTimeAction(item) {
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
                    item.itemData.discount = parseFloat(item.itemData.discount || 0);
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData.price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                discountRealTiemAction(item) {
                    if (!item.itemData.discountType || item.itemData.discountType === null || item.itemData.discountType === 'null') {
                        item.itemData.discountType = 'usd';
                    }
                    item.itemData.discount = parseFloat(item.itemData.discount || 0);
                    item.itemData.total = this.totalDiscount(item?.itemData.discountType, item.itemData.price, item?.itemData.discount);
                    this.calculatorProductPrice();
                },
                qtyRealTimeAction(item) {
                    if (!item.product_qty) {
                        item.product_qty = 1;
                    }
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
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    this.dataFilter.find((val) => {
                        if (this.catalogKey(val) === this.catalogKey(item)) {
                            val.addToCart = false;
                        }
                    });
                    if (item?.id) {
                        this.bookingDeleteId.push(item.id);
                    }
                    this.dataCart.splice(index, 1);
                    this.syncCatalogSelection();
                    this.calculatorProductPrice();
                    this.refreshIcons();
                },
                resetCart() {
                    if (!this.canEditBookingItems()) {
                        return;
                    }
                    this.dataCart = [];
                    this.dataFilter.forEach((item) => {
                        item.addToCart = false;
                    });
                    this.bookingDeleteId = [];
                    this.calculatorProductPrice();
                    this.refreshIcons();
                },
                totalDiscount(type, price, discount) {
                    let amount = 0;
                    const basePrice = Number(price || 0);
                    const baseDiscount = Number(discount || 0);

                    if (type === 'percent') {
                        amount = basePrice - (basePrice * baseDiscount / 100);
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
                            const qty = item?.product_qty ? Number(item.product_qty) : 1;
                            const unitPrice = Number(item?.itemData?.price || 0);
                            const price = item?.itemData?.discount ? Number(item?.itemData?.total || unitPrice) : unitPrice;
                            const totalCommission = Number(item?.itemData?.totalCommission || 0);

                            this.subTotal += qty * unitPrice;
                            this.commissionTotal += totalCommission * qty;
                            this.total += qty * price;
                        });
                    }

                    this.amountPaid = this.total - this.commissionTotal;
                },
                async productValidation($cb) {
                    const errors = [];
                    const bookingPayload = @json($data);
                    const booking = bookingPayload?.data ?? bookingPayload ?? null;
                    const details = this.bookingDetails(booking);

                    if (this.dataCart.length) {
                        this.submitLoading = true;
                        for (const val of this.dataCart) {
                            val.error = false;
                            let findBookingDetailQty = 0;
                            if (details.length) {
                                const findBookingDetail = details.find((bkItem) => {
                                    const productId = bkItem.product?.id || bkItem.product_id;
                                    return bkItem.type === 'product' && productId == val.product_id;
                                });
                                findBookingDetailQty = findBookingDetail?.qty ?? 0;
                            }

                            if (val.product_type === 'product') {
                                const url = `/admin/select/find-shop-product?shop_id=${this.formData.shop_id || this.shopData?.id}&product_id=${val.product_id}`;
                                await this.fetchJson(url).then((res) => {
                                    if (res) {
                                        const currentStock = Number(res.current_stock || 0) + Number(findBookingDetailQty);
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
                submitBooking() {
                    this.dataError = {};
                    if (!this.canEditBookingItems()) {
                        this.setValidationErrors({
                            payment_status: ['Only pending bookings can be updated.']
                        });
                        return;
                    }

                    const clientErrors = this.validateBookingBeforeSubmit();

                    if (this.hasValidationErrors(clientErrors)) {
                        this.setValidationErrors(clientErrors);
                        return;
                    }

                    this.productValidation((valid) => {
                        if (valid.length > 0) {
                            const stockTemplate = @json(__('booking.validation.out_of_stock_item'));
                            this.setValidationErrors({
                                dataCarts: valid.map((name) => stockTemplate.replace(':name', name))
                            });
                            return true;
                        }

                        this.confirmBookingSubmit(() => {
                            this.saveBooking();
                        });
                    });
                },
                confirmBookingSubmit(onConfirm) {
                    const confirmDialog = this.$store?.confirmDialog;

                    if (confirmDialog && typeof confirmDialog.open === 'function') {
                        confirmDialog.open({
                            data: {
                                title: @json(__('booking.dialog.message')),
                                message: this.confirmMessage,
                                btnClose: @json(__('booking.button.close')),
                                btnSave: this.btnSubmit,
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

                    if (confirmDialog && typeof confirmDialog.open === 'function') {
                        confirmDialog.open({
                            data: {
                                title: @json(__('booking.dialog.message')),
                                message: message,
                                btnClose: @json(__('booking.button.close')),
                                btnSave: btnSave,
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
                            amount: [@json(__('booking.validation.payment_amount_required'))]
                        });
                        return;
                    }

                    if (amount > Number(this.remainingAmount || 0)) {
                        this.setValidationErrors({
                            amount: [@json(__('booking.validation.payment_amount_exceeds'))]
                        });
                        return;
                    }

                    this.paymentLoading = true;
                    Axios.post(`{{ url('admin/booking/add-payment') }}/${this.bookingId}`, {
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
                    const availableBalance = Number(this.remainingAmount || 0) + Number(pay?.amount || 0);

                    if (amount <= 0) {
                        this.setValidationErrors({
                            amount: [@json(__('booking.validation.payment_amount_required'))]
                        });
                        return;
                    }

                    if (amount > availableBalance) {
                        this.setValidationErrors({
                            amount: [@json(__('booking.validation.payment_amount_exceeds'))]
                        });
                        return;
                    }

                    this.paymentLoading = true;
                    Axios.put(`{{ url('admin/booking/update-payment') }}/${pay.id}`, {
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
                    this.confirmPaymentAction(@json(__('booking.confirm.delete_payment')), @json(__('booking.button.delete_payment')), () => {
                        this.paymentLoading = true;
                        Axios.delete(`{{ url('admin/booking/delete-payment') }}/${pay.id}`, {
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
                saveBooking() {
                    this.submitLoading = true;
                    const data = {
                        ...this.formData,
                        dataCarts: this.dataCart.length ? JSON.stringify(this.dataCart) : JSON.stringify([]),
                        shop: this.shopData ? JSON.stringify(this.shopData) : null,
                        shop_id: this.formData.shop_id || this.shopData?.id,
                        commissionTotal: this.commissionTotal,
                        subTotal: this.subTotal,
                        total: this.total,
                        total_discount: parseFloat(this.subTotal) - parseFloat(this.total),
                        bookingDelete: this.bookingDeleteId,
                    };

                    setTimeout(() => {
                        Axios({
                            url: `{{ route('admin-booking-save', $id) }}`,
                            method: 'POST',
                            data: {
                                ...data,
                                id: this.bookingId,
                            }
                        }).then((res) => {
                            if (res.data.message === 'success') {
                                this.submitLoading = false;
                                setTimeout(() => {
                                    const status = res.data.payment_status || 'Pending';
                                    window.location.href = `{{ url('admin/booking/list') }}/${status}`;
                                }, 100);
                            }
                        }).catch((e) => {
                            const fallbackMessage = e.response?.data?.error ||
                                e.response?.data?.message ||
                                'Unable to save booking. Please check the booking details.';
                            this.setValidationErrors(e.response?.data?.errors || {
                                general: [fallbackMessage]
                            });
                            this.submitLoading = false;
                        }).finally(() => {
                            this.submitLoading = false;
                        });
                    }, 300);
                },
                validateBookingBeforeSubmit() {
                    const errors = {};
                    const shopId = this.formData.shop_id || this.shopData?.id;

                    if (!shopId) {
                        errors.shop_id = [@json(__('booking.validation.shop_required'))];
                    }

                    if (!this.formData.customer_id) {
                        errors.customer_id = [@json(__('booking.validation.customer_required'))];
                    }

                    if (!this.formData.booking_date) {
                        errors.booking_date = [@json(__('booking.validation.booking_date_required'))];
                    }

                    if (!this.dataCart?.length) {
                        errors.dataCarts = [@json(__('booking.validation.cart_required'))];
                    } else if (this.dataCart.some((item) => !item?.product_id || !item?.product_type)) {
                        errors.dataCarts = [@json(__('booking.validation.cart_item_invalid'))];
                    }

                    const partialPayment = Number(this.formData.partial_payment_amount || 0);
                    if (partialPayment < 0) {
                        errors.partial_payment_amount = [@json(__('booking.validation.partial_min'))];
                    } else if (partialPayment > Number(this.subTotal || 0)) {
                        errors.partial_payment_amount = [@json(__('booking.validation.partial_max'))];
                    }

                    return errors;
                },
                hasValidationErrors(errors) {
                    return Object.keys(errors || {}).length > 0;
                },
                setValidationErrors(errors = {}) {
                    this.dataError = errors || {};
                    this.showValidationToast();
                    this.$nextTick(() => {
                        document.querySelector('.booking-error-summary, .booking-cart-error, .error')
                            ?.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                    });
                },
                validationMessages() {
                    return Object.values(this.dataError || {})
                        .flatMap((messages) => Array.isArray(messages) ? messages : [messages])
                        .filter(Boolean);
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
            }));
        });
    </script>
@stop
