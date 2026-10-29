@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('order_bulk_print.title')])

    <style>
        .bulk-print-wrapper {
            padding: 20px;
            background: #f8fafc;
            min-height: calc(100vh - 120px);
        }

        /* Filter Card */
        .bulk-filter-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-status-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-pill:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .status-pill.active {
            background: #3C91E6 !important;
            color: #ffffff;
            border-color: #3C91E6 !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            align-items: flex-end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
        }

        .filter-input {
            height: 43px;
            width: 100%;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0 12px;
            box-sizing: border-box;
            font-size: 12.5px;
            font-weight: 500;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .filter-date-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .filter-date-wrapper .filter-input {
            padding-right: 36px;
        }

        .filter-date-icon {
            position: absolute;
            right: 10px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        /* Select2 Standard Rules */
        .select2Group {
            position: relative;
            width: 100%;
        }

        .select2Group .select2-container .select2-selection--single {
            height: 43px !important;
            border-radius: 7px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0 12px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2Group .select2-container--open .select2-selection--single,
        .select2Group .select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .select2Group .select2-selection__rendered {
            font-size: 12.5px !important;
            font-weight: 500 !important;
            color: #334155 !important;
            padding-left: 0 !important;
            line-height: 41px !important;
        }

        .select2Group .select2-selection__arrow {
            top: 0 !important;
            right: 8px !important;
            height: 100% !important;
        }

        .select2Group .select2Reset {
            position: absolute;
            top: 50%;
            right: 26px;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }

        .select2Group .select2Reset svg {
            width: 18px;
            height: 18px;
            fill: #94a3b8;
            transition: fill 0.15s ease;
        }

        .select2Group .select2Reset:hover svg {
            fill: #ef4444;
        }

        .select2-selection__clear {
            display: none !important;
        }

        .filter-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-filter-apply {
            height: 43px;
            padding: 0 20px;
            border-radius: 7px;
            background: #3C91E6 !important;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }

        .btn-filter-apply:hover {
            background: #1d4ed8;
        }

        .btn-filter-reset {
            height: 43px;
            padding: 0 16px;
            border-radius: 7px;
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Floating Selection Toolbar */
        .selection-toolbar-card {
            position: sticky;
            top: 70px;
            z-index: 100;
            background: #1e293b;
            color: #ffffff;
            border-radius: 7px;
            padding: 14px 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3), 0 8px 10px -6px rgba(15, 23, 42, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .selection-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
            padding: 6px 14px;
            border-radius: 7px;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .lang-toggle-group {
            display: inline-flex;
            background: #334155;
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }

        .lang-toggle-btn {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .lang-toggle-btn.active {
            background: #2563eb;
            color: #ffffff;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-bulk-action {
            height: 38px !important;
            padding: 0 16px;
            border-radius: 8px !important;
            font-size: 13px;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-print-invoice {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-print-invoice:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-print-delivery {
            background: #059669;
            color: #ffffff;
        }

        .btn-print-delivery:hover {
            background: #047857;
            transform: translateY(-1px);
        }

        .btn-print-consolidated {
            background: #0284c7;
            color: #ffffff;
        }

        .btn-print-consolidated:hover {
            background: #0369a1;
            transform: translateY(-1px);
        }

        .btn-clear-selection {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-clear-selection:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #ef4444;
        }

        /* All Matching Banner */
        .selection-matching-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #1e40af;
        }

        .btn-select-all-matching {
            font-weight: 700;
            color: #2563eb;
            text-decoration: underline;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
        }

        .btn-select-all-matching:hover {
            color: #1d4ed8;
        }

        /* Order Table Card */
        .bulk-table-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .bulk-table-container {
            width: 100%;
            overflow-x: auto;
        }

        .bulk-orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .bulk-orders-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .bulk-orders-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }

        .bulk-orders-table tr:hover td {
            background: #f8fafc;
        }

        .bulk-orders-table tr.selected td {
            background: #eff6ff;
        }

        /* Custom Checkbox */
        .custom-checkbox-cell {
            width: 44px;
            text-align: center;
            padding: 0 10px !important;
        }

        .bulk-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #2563eb;
            cursor: pointer;
            border-radius: 4px;
        }

        .invoice-pill {
            font-family: monospace;
            font-weight: 700;
            font-size: 13px;
            color: #2563eb;
            background: #eff6ff;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.partial {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.rejected,
        .status-badge.cancel {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-orders-state {
            text-align: center;
            padding: 48px 20px;
            color: #64748b;
        }

        .empty-orders-state i {
            font-size: 40px;
            color: #cbd5e1;
            margin-bottom: 12px;
            display: block;
        }

        /* Datepicker stacking */
        div#ui-datepicker-div,
        #ui-datepicker-div.order-bulk-print-datepicker {
            z-index: 9999 !important;
        }
    </style>

    <div class="bulk-print-wrapper" x-data="orderBulkPrintApp()">
        <!-- Filter Card -->
        <div class="bulk-filter-card">
            <!-- Status Tabs -->
            <div class="filter-status-nav">
                @php
                    $tabs = [
                        'all' => __('order_bulk_print.status.all'),
                        'Pending' => __('order_bulk_print.status.pending'),
                        'Partial' => __('order_bulk_print.status.partial'),
                        'Paid' => __('order_bulk_print.status.paid'),
                        'Rejected' => __('order_bulk_print.status.rejected'),
                    ];
                @endphp
                @foreach ($tabs as $key => $label)
                    @php
                        $tabParams = array_merge(request()->query(), ['status' => $key, 'page' => 1]);
                        $isActive = ($status === $key);
                    @endphp
                    <a href="{{ route('admin-order-bulk-print-index', $tabParams) }}" 
                       @click="clearSelection()"
                       class="status-pill {{ $isActive ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Filter Fields Form -->
            <form id="bulkFilterForm" method="GET" action="{{ route('admin-order-bulk-print-index') }}" @submit="clearSelection()">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="filter-grid">
                    <!-- Shop Filter -->
                    <div class="filter-field">
                        <label for="shop_id" class="filter-label">{{ __('order_bulk_print.filters.shop') }}</label>
                        <div class="select2Group">
                            <select name="shop_id" id="bulk_shop_id" class="SelectField select2">
                                <option value="">{{ __('order_bulk_print.filters.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="select2Reset" x-show="filterValues.shop_id" @click.stop="resetShopFilter()" title="{{ __('order_bulk_print.filters.reset') }}" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- From Date -->
                    <div class="filter-field">
                        <label for="bulk_from_date" class="filter-label">{{ __('order_bulk_print.filters.from_date') }}</label>
                        <div class="filter-date-wrapper">
                            <input type="text" name="from_date" id="bulk_from_date" x-ref="fromDate"
                                   class="filter-input" value="{{ $fromDate }}" placeholder="YYYY-MM-DD" autocomplete="off">
                            <span class="filter-date-icon"><i class="bx bx-calendar"></i></span>
                        </div>
                    </div>

                    <!-- To Date -->
                    <div class="filter-field">
                        <label for="bulk_to_date" class="filter-label">{{ __('order_bulk_print.filters.to_date') }}</label>
                        <div class="filter-date-wrapper">
                            <input type="text" name="to_date" id="bulk_to_date" x-ref="toDate"
                                   class="filter-input" value="{{ $toDate }}" placeholder="YYYY-MM-DD" autocomplete="off">
                            <span class="filter-date-icon"><i class="bx bx-calendar"></i></span>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="filter-field">
                        <label for="bulk_search" class="filter-label">{{ __('order_bulk_print.filters.search') }}</label>
                        <input type="text" name="search" id="bulk_search" class="filter-input"
                               value="{{ $search }}" placeholder="{{ __('order_bulk_print.filters.search_placeholder') }}">
                    </div>

                    <!-- Action Buttons -->
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter-apply">
                            <i class="bx bx-filter-alt"></i> {{ __('order_bulk_print.filters.apply') }}
                        </button>
                        <a href="{{ route('admin-order-bulk-print-index', ['status' => $status]) }}" class="btn-filter-reset" @click="clearSelection()">
                            <i class="bx bx-reset"></i> {{ __('order_bulk_print.filters.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Floating Selection Toolbar (Visible when >= 1 selected) -->
        <div class="selection-toolbar-card" x-show="selectedIds.length > 0" x-cloak>
            <div class="toolbar-left">
                <div class="selection-count-badge">
                    <i class="bx bx-check-double"></i>
                    <span x-text="getSelectionCountText()"></span>
                </div>

                <!-- Language Toggle -->
                <div class="lang-toggle-group">
                    <button type="button" class="lang-toggle-btn"
                            :class="{ 'active': selectedLanguage === 'km' }"
                            @click="selectedLanguage = 'km'">
                        {{ __('order_bulk_print.selection.khmer') }}
                    </button>
                    <button type="button" class="lang-toggle-btn"
                            :class="{ 'active': selectedLanguage === 'en' }"
                            @click="selectedLanguage = 'en'">
                        {{ __('order_bulk_print.selection.english') }}
                    </button>
                </div>
            </div>

            <div class="toolbar-right">
                <button type="button" class="btn-bulk-action btn-print-invoice" @click="submitPrint('invoice')">
                    <i class="bx bx-printer"></i> {{ __('order_bulk_print.selection.print_invoices') }}
                </button>

                <button type="button" class="btn-bulk-action btn-print-delivery" @click="submitPrint('delivery_slip')">
                    <i class="bx bx-file"></i> {{ __('order_bulk_print.selection.print_delivery_slips') }}
                </button>

                <button type="button" class="btn-bulk-action btn-print-consolidated" @click="submitPrint('consolidated')">
                    <i class="bx bx-layer"></i> {{ __('order_bulk_print.selection.consolidated_product_quantity') }}
                </button>

                <button type="button" class="btn-bulk-action btn-clear-selection" @click="clearSelection()">
                    <i class="bx bx-x"></i> {{ __('order_bulk_print.selection.clear') }}
                </button>
            </div>
        </div>

        <!-- Page All-Matching Banner -->
        <div class="selection-matching-banner" x-show="showAllMatchingBanner()" x-cloak>
            <div>
                <i class="bx bx-info-circle"></i>
                <span x-text="'{{ __('order_bulk_print.selection.page_selected_notice', ['count' => $orders->count()]) }}'"></span>
                <template x-if="totalMatchingCount > currentPageIds.length && !allMatchingSelected">
                    <button type="button" class="btn-select-all-matching" @click="selectAllMatching()" :disabled="isLoadingMatching">
                        <span x-show="!isLoadingMatching" x-text="'{{ __('order_bulk_print.selection.select_all_matching', ['count' => $orders->total()]) }}'"></span>
                        <span x-show="isLoadingMatching">{{ __('order_bulk_print.selection.loading_matching') }}</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Order Table Card -->
        <div class="bulk-table-card">
            <div class="bulk-table-container">
                <table class="bulk-orders-table">
                    <thead>
                        <tr>
                            <th class="custom-checkbox-cell">
                                <input type="checkbox" class="bulk-checkbox bulk-header-checkbox"
                                       :checked="isCurrentPageAllSelected()"
                                       @change="toggleSelectAllCurrentPage($event.target.checked)">
                            </th>
                            <th style="width: 60px;">{{ __('order_bulk_print.table.no') }}</th>
                            <th>{{ __('order_bulk_print.table.invoice_number') }}</th>
                            <th>{{ __('order_bulk_print.table.date') }}</th>
                            <th>{{ __('order_bulk_print.table.shop') }}</th>
                            <th>{{ __('order_bulk_print.table.customer') }}</th>
                            <th style="text-align: right;">{{ __('order_bulk_print.table.total') }}</th>
                            <th style="text-align: center;">{{ __('order_bulk_print.table.payment_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr :class="{ 'selected': isOrderSelected({{ $order->id }}) }">
                                <td class="custom-checkbox-cell">
                                    <input type="checkbox" class="bulk-checkbox"
                                           data-order-id="{{ $order->id }}"
                                           :checked="isOrderSelected({{ $order->id }})"
                                           @change="toggleOrder({{ $order->id }})">
                                </td>
                                <td>{{ $orders->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="invoice-pill">{{ $order->display_invoice_number }}</span>
                                </td>
                                <td>{{ $order->display_date }}</td>
                                <td>{{ $order->display_shop_name }}</td>
                                <td>{{ $order->display_customer_name }}</td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                    {{ $order->display_total_price }}
                                </td>
                                <td style="text-align: center;">
                                    @php
                                        $pStatus = strtolower($order->display_payment_status);
                                    @endphp
                                    <span class="status-badge {{ $pStatus }}">
                                        {{ $order->display_payment_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-orders-state">
                                        <i class="bx bx-receipt"></i>
                                        <p>{{ __('order_bulk_print.table.no_records') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if ($orders->count() > 0)
                <div class="paginationLayout2">
                    @include('admin::components.pagination', ['paginate' => $orders])
                </div>
            @endif
        </div>

    </div>

    <script>
        function orderBulkPrintApp() {
            return {
                selectedIds: [],
                allMatchingSelected: false,
                selectionStorageKey: 'pos_order_bulk_print_selection_{{ auth()->id() }}',
                // Use the rendered filters: the MDI tab can differ from window.location.
                selectionContext: {{ \Illuminate\Support\Js::from([$status, $paymentStatus, (string) request('shop_id', ''), $fromDate, $toDate, $search]) }},
                totalMatchingCount: {{ (int) $orders->total() }},
                currentPageIds: {{ json_encode($orders->pluck('id')->all()) }},
                maxOrders: {{ (int) $maxOrders }},
                maxDetailRows: {{ (int) $maxDetailRows }},
                selectedLanguage: '{{ app()->getLocale() === 'km' ? 'km' : 'en' }}',
                isLoadingMatching: false,
                filterValues: {
                    shop_id: '{{ request('shop_id') }}',
                },

                init() {
                    this.restoreSelection();
                    this.$nextTick(() => {
                        this.initSelect2();
                        this.initDatepickers();
                        this.initCheckboxWatcher();
                        this.syncCheckboxes();
                    });
                },

                restoreSelection() {
                    try {
                        const stored = JSON.parse(sessionStorage.getItem(this.selectionStorageKey));
                        if (!stored || stored.context !== JSON.stringify(this.selectionContext) || !Array.isArray(stored.ids)) return;

                        this.selectedIds = [...new Set(stored.ids.map(Number).filter(id => Number.isSafeInteger(id) && id > 0))];
                        this.allMatchingSelected = stored.allMatchingSelected === true;
                    } catch (e) {
                        // Unavailable storage must not prevent selecting or printing orders.
                    }
                },

                persistSelection() {
                    try {
                        if (!this.selectedIds.length) {
                            sessionStorage.removeItem(this.selectionStorageKey);
                            return;
                        }
                        sessionStorage.setItem(this.selectionStorageKey, JSON.stringify({
                            context: JSON.stringify(this.selectionContext),
                            ids: this.selectedIds,
                            allMatchingSelected: this.allMatchingSelected
                        }));
                    } catch (e) {
                        // Keep the current selection usable when storage is unavailable.
                    }
                },

                initCheckboxWatcher() {
                    this.$watch('selectedIds', () => {
                        this.syncCheckboxes();
                    });
                },

                syncCheckboxes() {
                    this.persistSelection();
                    this.$nextTick(() => {
                        const rowCheckboxes = this.$el.querySelectorAll('tbody input.bulk-checkbox[data-order-id]');
                        rowCheckboxes.forEach(cb => {
                            const orderId = parseInt(cb.getAttribute('data-order-id'), 10);
                            cb.checked = this.selectedIds.includes(orderId);
                        });

                        const headerCb = this.$el.querySelector('thead input.bulk-checkbox');
                        if (headerCb) {
                            headerCb.checked = this.isCurrentPageAllSelected();
                            headerCb.indeterminate = this.isCurrentPagePartiallySelected();
                        }
                    });
                },

                initSelect2() {
                    if (!window.jQuery || !$.fn.select2) return;

                    const $select = $('#bulk_shop_id');
                    if (!$select.length || $select.hasClass('select2-hidden-accessible')) return;

                    $select.select2({
                        placeholder: '{{ __('order_bulk_print.filters.all_shops') }}',
                        width: '100%'
                    }).on('select2:open', function() {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        }
                    }).on('change select2:select select2:clear select2:unselect', (e) => {
                        this.filterValues.shop_id = $select.val() || '';
                    });
                },

                resetShopFilter() {
                    $('#bulk_shop_id').val('').trigger('change');
                    this.filterValues.shop_id = '';
                },

                initDatepickers() {
                    if (!window.jQuery || !$.fn.datepicker || !this.$refs.fromDate || !this.$refs.toDate) return;

                    const $from = $(this.$refs.fromDate);
                    const $to = $(this.$refs.toDate);
                    const options = {
                        dateFormat: 'yy-mm-dd',
                        changeYear: true,
                        changeMonth: true,
                        gotoCurrent: true,
                        yearRange: '-50:+10',
                        beforeShow: function(input, instance) {
                            instance.dpDiv.addClass('order-bulk-print-datepicker');
                        },
                        onClose: function(dateText, instance) {
                            instance.dpDiv.removeClass('order-bulk-print-datepicker');
                        }
                    };

                    if ($from.hasClass('hasDatepicker')) $from.datepicker('destroy');
                    if ($to.hasClass('hasDatepicker')) $to.datepicker('destroy');

                    $from.datepicker({
                        ...options,
                        onSelect: (selected) => {
                            $to.datepicker('option', 'minDate', selected);
                        }
                    }).on('change', () => {
                        $to.datepicker('option', 'minDate', $from.datepicker('getDate') || null);
                    });

                    $to.datepicker({
                        ...options,
                        onSelect: (selected) => {
                            $from.datepicker('option', 'maxDate', selected);
                        }
                    }).on('change', () => {
                        $from.datepicker('option', 'maxDate', $to.datepicker('getDate') || null);
                    });

                    if ($from.val()) {
                        $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                    }
                    if ($to.val()) {
                        $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                    }
                },

                isOrderSelected(id) {
                    return this.selectedIds.includes(parseInt(id, 10));
                },

                toggleOrder(id) {
                    const numId = parseInt(id, 10);
                    const index = this.selectedIds.indexOf(numId);
                    if (index > -1) {
                        this.selectedIds.splice(index, 1);
                        this.allMatchingSelected = false;
                    } else {
                        if (this.selectedIds.length >= this.maxOrders) {
                            alert('{{ __('order_bulk_print.validation.order_ids_max', ['max' => $maxOrders]) }}');
                            this.syncCheckboxes();
                            return;
                        }
                        this.selectedIds.push(numId);
                    }
                    this.syncCheckboxes();
                },

                isCurrentPageAllSelected() {
                    if (!this.currentPageIds.length) return false;
                    return this.currentPageIds.every(id => this.selectedIds.includes(parseInt(id, 10)));
                },

                isCurrentPagePartiallySelected() {
                    const count = this.currentPageIds.filter(id => this.selectedIds.includes(parseInt(id, 10))).length;
                    return count > 0 && count < this.currentPageIds.length;
                },

                toggleSelectAllCurrentPage(checked) {
                    if (checked) {
                        const newIds = [...this.selectedIds];
                        for (const rawId of this.currentPageIds) {
                            const id = parseInt(rawId, 10);
                            if (!newIds.includes(id)) {
                                if (newIds.length >= this.maxOrders) {
                                    alert('{{ __('order_bulk_print.validation.order_ids_max', ['max' => $maxOrders]) }}');
                                    break;
                                }
                                newIds.push(id);
                            }
                        }
                        this.selectedIds = newIds;
                    } else {
                        this.selectedIds = this.selectedIds.filter(id => !this.currentPageIds.includes(parseInt(id, 10)));
                        this.allMatchingSelected = false;
                    }
                    this.syncCheckboxes();
                },

                showAllMatchingBanner() {
                    return this.isCurrentPageAllSelected() && (this.totalMatchingCount > this.currentPageIds.length || this.allMatchingSelected);
                },

                selectAllMatching() {
                    this.isLoadingMatching = true;

                    const params = new URLSearchParams(window.location.search);
                    params.delete('page');

                    fetch('{{ route('admin-order-bulk-print-selection') }}?' + params.toString(), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isLoadingMatching = false;
                        if (data.success && Array.isArray(data.ids)) {
                            this.selectedIds = data.ids.map(id => parseInt(id, 10));
                            this.allMatchingSelected = true;
                            this.syncCheckboxes();
                        } else {
                            alert(data.message || '{{ __('order_bulk_print.validation.order_ids_invalid') }}');
                        }
                    })
                    .catch(err => {
                        this.isLoadingMatching = false;
                        alert('Error fetching matching order IDs.');
                    });
                },

                clearSelection() {
                    this.selectedIds = [];
                    this.allMatchingSelected = false;
                    this.syncCheckboxes();
                },

                getSelectionCountText() {
                    if (this.allMatchingSelected) {
                        return '{{ __('order_bulk_print.selection.all_matching_selected', ['count' => ':count']) }}'.replace(':count', this.selectedIds.length);
                    }
                    return '{{ __('order_bulk_print.selection.selected_count', ['count' => ':count']) }}'.replace(':count', this.selectedIds.length);
                },

                submitPrint(docType) {
                    if (!this.selectedIds.length) {
                        alert('{{ __('order_bulk_print.validation.no_orders_selected') }}');
                        return;
                    }

                    if (this.selectedIds.length > this.maxOrders) {
                        alert('{{ __('order_bulk_print.validation.order_ids_max', ['max' => $maxOrders]) }}');
                        return;
                    }

                    const params = new URLSearchParams();
                    params.set('document_type', docType);
                    params.set('print_locale', this.selectedLanguage);
                    params.set('order_ids', this.selectedIds.join(','));

                    const baseRoute = '{{ route('admin-order-bulk-print-preview') }}';
                    const targetUrl = baseRoute.replace(/\/+$/, '') + '/' + docType + '?' + params.toString();

                    let titlePrefix;
                    let iconClass = 'bx bx-printer';
                    if (docType === 'invoice') {
                        titlePrefix = '{{ __('order_bulk_print.preview.title_invoice') }}';
                    } else if (docType === 'delivery_slip') {
                        titlePrefix = '{{ __('order_bulk_print.preview.title_delivery_slip') }}';
                    } else {
                        titlePrefix = '{{ __('order_bulk_print.preview.title_consolidated') }}';
                        iconClass = 'bx bx-layer';
                    }
                    const tabTitle = titlePrefix + ' (' + this.selectedIds.length + ')';

                    if (window.MDI && typeof window.MDI.openUrlInTab === 'function') {
                        window.MDI.openUrlInTab(targetUrl, tabTitle, iconClass);
                    } else {
                        window.location.href = targetUrl;
                    }
                }
            };
        }
    </script>
@endsection
