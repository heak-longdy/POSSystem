@extends('admin::shared.layout')
@section('layout')
    <style>
        .order-report-wrapper {
            padding: 0;
            width: 100%;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin: 18px 0 20px 0;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .kpi-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .kpi-icon-wrap.primary { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .kpi-icon-wrap.success { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .kpi-icon-wrap.warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
        .kpi-icon-wrap.danger { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
        .kpi-icon-wrap.purple { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
        .kpi-icon-wrap.indigo { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }

        .kpi-info {
            flex: 1;
            min-width: 0;
        }

        .kpi-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .kpi-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Filter Panel */
        .report-filter-panel {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
        }

        .filter-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preset-badge-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .preset-btn {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .preset-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .preset-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .filter-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            align-items: flex-end;
        }

        .filter-field-wrap label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 5px;
            letter-spacing: 0.3px;
        }

        .filter-input, .filter-select {
            width: 100%;
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 13px;
            color: #1e293b;
            background-color: #ffffff;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter-input:focus, .filter-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .filter-actions-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 38px;
        }

        .btn-filter-search {
            height: 38px;
            padding: 0 16px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-filter-search:hover {
            background: #1d4ed8;
        }

        .btn-filter-reset {
            height: 38px;
            padding: 0 14px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-excel-export {
            height: 38px;
            padding: 0 14px;
            background: #059669;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-excel-export:hover {
            background: #047857;
        }

        /* Table Card */
        .report-table-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-custom-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-custom-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-responsive-custom {
            width: 100%;
            overflow-x: auto;
        }

        .order-data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .order-data-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .order-data-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .order-data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .order-data-table tr.row-today {
            background-color: #eff6ff;
        }

        .order-data-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
            padding: 14px;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Badges & Tags */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-badge.paid { background: #dcfce7; color: #166534; }
        .status-badge.partial { background: #fef3c7; color: #92400e; }
        .status-badge.pending { background: #fee2e2; color: #991b1b; }
        .status-badge.cancel { background: #f1f5f9; color: #64748b; }
        .status-badge.today { background: #dbeafe; color: #1e40af; }

        .method-tag {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 4px;
            margin: 1px 2px;
        }

        .btn-drilldown {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
        }

        .btn-drilldown:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* Detail Modal */
        .report-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .report-modal-card {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 1000px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }

        .report-modal-header {
            padding: 16px 22px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .report-modal-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-close-report-modal {
            background: transparent;
            border: none;
            font-size: 24px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 0 4px;
        }

        .btn-close-report-modal:hover { color: #1e293b; }

        .report-modal-body {
            padding: 18px 22px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-period-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .modal-summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .modal-summary-box span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .modal-summary-box strong {
            display: block;
            font-size: 16px;
            color: #1e293b;
            margin-top: 2px;
        }

        .invoice-item-chip {
            display: inline-block;
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 4px;
            margin: 2px 3px 2px 0;
        }

        .empty-placeholder {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #cbd5e1;
        }
    </style>

    <div class="content-wrapper order-report-wrapper" id="orderTransactionReportApp" x-data="xOrderTransactionReport">
        <!-- Main Header -->
        <div class="header box-shadow-bottom">
            @include('admin::shared.header', ['header_name' => 'Order Transaction Report'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            @php
                                $currentParams = request()->query();
                            @endphp
                            <a href="{{ route('admin-report-order-transaction-daily', $currentParams) }}"
                                class="{{ $viewMode === 'daily' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-event'></i>
                                {!! \App\Support\Language::translatedValue(['en' => 'Daily Order Transactions', 'km' => 'ប្រតិបត្តិការបញ្ជាទិញប្រចាំថ្ងៃ']) !!}
                            </a>
                            <a href="{{ route('admin-report-order-transaction-monthly', $currentParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {!! \App\Support\Language::translatedValue(['en' => 'Monthly Order Transactions', 'km' => 'ប្រតិបត្តិការបញ្ជាទិញប្រចាំខែ']) !!}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button type="button" @click="exportExcel()" class="btn-excel-export">
                        <i class='bx bx-download'></i>
                        <span>Export Excel</span>
                    </button>
                    <button type="button" s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body" style="padding: 20px;">
            <!-- Filter Panel -->
            <div class="report-filter-panel">
                <form id="orderFilterForm" method="GET" action="{{ url()->current() }}">
                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">Quick Presets:</span>
                                <a href="{{ route('admin-report-order-transaction-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'today'])) }}"
                                    class="preset-btn {{ request('preset') === 'today' ? 'active' : '' }}">Today</a>
                                <a href="{{ route('admin-report-order-transaction-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'yesterday'])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">Yesterday</a>
                                <a href="{{ route('admin-report-order-transaction-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '7days'])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">Last 7 Days</a>
                                <a href="{{ route('admin-report-order-transaction-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '30days'])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">Last 30 Days</a>
                                <a href="{{ route('admin-report-order-transaction-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'last_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'last_month' ? 'active' : '' }}">Last Month</a>
                            </div>
                        </div>
                    @endif

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="from_date">From Date</label>
                                <input type="text" name="from_date" id="from_date" class="filter-input datepicker-input"
                                    value="{{ $from_date }}" autocomplete="off" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_date">To Date</label>
                                <input type="text" name="to_date" id="to_date" class="filter-input datepicker-input"
                                    value="{{ $to_date }}" autocomplete="off" placeholder="YYYY-MM-DD">
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="filter-select">
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="from_month">From Month</label>
                                <select name="from_month" id="from_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $from_month === $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_month">To Month</label>
                                <select name="to_month" id="to_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $to_month === $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label for="shop_id">Shop / Branch</label>
                            <select name="shop_id" id="shop_id" class="filter-select">
                                <option value="">All Shops</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="barber_id">Staff / Barber</label>
                            <select name="barber_id" id="barber_id" class="filter-select">
                                <option value="">All Staff</option>
                                @foreach ($barbers as $barber)
                                    <option value="{{ $barber->id }}" {{ request('barber_id') == $barber->id ? 'selected' : '' }}>
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="filter-select">
                                <option value="">Active (Exclude Cancel)</option>
                                <option value="all" {{ request('payment_status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Fully Paid</option>
                                <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>Partial Paid</option>
                                <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Cancel" {{ request('payment_status') === 'Cancel' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="pay_way">Payment Method</label>
                            <select name="pay_way" id="pay_way" class="filter-select">
                                <option value="">All Methods</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm }}" {{ request('pay_way') === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="item_type">Item Type</label>
                            <select name="item_type" id="item_type" class="filter-select">
                                <option value="">All Items</option>
                                <option value="product" {{ request('item_type') === 'product' ? 'selected' : '' }}>Products Only</option>
                                <option value="service" {{ request('item_type') === 'service' ? 'selected' : '' }}>Services Only</option>
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="search">Keyword Search</label>
                            <input type="text" name="search" id="search" class="filter-input"
                                value="{{ request('search') }}" placeholder="Invoice #, Customer, Phone...">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class='bx bx-search'></i>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route($viewMode === 'daily' ? 'admin-report-order-transaction-daily' : 'admin-report-order-transaction-monthly') }}"
                                class="btn-filter-reset">
                                <i class='bx bx-reset'></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <!-- Net Sales -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary">
                        <i class='bx bx-dollar-circle'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Net Sales</div>
                        <div class="kpi-value text-primary">${{ number_format($summary['total_net_sales'], 2) }}</div>
                        <div class="kpi-sub">Gross: ${{ number_format($summary['total_gross_sales'], 2) }} | Disc: -${{ number_format($summary['total_discount'], 2) }}</div>
                    </div>
                </div>

                <!-- Total Invoices / Orders -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap indigo">
                        <i class='bx bx-receipt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Orders</div>
                        <div class="kpi-value">{{ number_format($summary['total_invoices']) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_items_sold']) }} items sold (Avg: ${{ number_format($summary['avg_invoice_value'], 2) }}/ord)</div>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success">
                        <i class='bx bx-check-shield'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Amount Paid</div>
                        <div class="kpi-value text-success">${{ number_format($summary['total_paid'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['paid_count'] }} fully paid • {{ $summary['partial_count'] }} partial</div>
                    </div>
                </div>

                <!-- Total Outstanding -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger">
                        <i class='bx bx-time'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Outstanding</div>
                        <div class="kpi-value text-danger">${{ number_format($summary['total_remaining'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['pending_count'] }} pending payment orders</div>
                    </div>
                </div>

                <!-- Product vs Service Revenue -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap purple">
                        <i class='bx bx-pie-chart-alt-2'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Revenue Split</div>
                        <div class="kpi-value" style="font-size: 16px;">
                            P: ${{ number_format($summary['product_sales'], 2) }} <span style="font-weight: normal; font-size: 12px; color: #94a3b8;">/</span> S: ${{ number_format($summary['service_sales'], 2) }}
                        </div>
                        <div class="kpi-sub">{{ $summary['total_product_qty'] }} products • {{ $summary['total_service_qty'] }} services</div>
                    </div>
                </div>
            </div>

            <!-- Data Listing Table -->
            <div class="report-table-card">
                <div class="table-custom-header">
                    <h4>
                        <i class='bx {{ $viewMode === 'daily' ? 'bx-calendar-event' : 'bx-calendar-alt' }}'></i>
                        {{ $viewMode === 'daily' ? 'Daily Order Transactions Breakdown' : 'Monthly Order Transactions Breakdown' }}
                        <span style="font-size: 12px; font-weight: normal; color: #64748b;">
                            ({{ $rows->count() }} {{ $viewMode === 'daily' ? 'days' : 'months' }} recorded)
                        </span>
                    </h4>
                </div>

                <div class="table-responsive-custom">
                    @if ($viewMode === 'daily')
                        <!-- DAILY TABLE -->
                        <table class="order-data-table" id="orderTransactionTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">Nº</th>
                                    <th>Date</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Items Sold</th>
                                    <th class="text-right">Gross Sales</th>
                                    <th class="text-right">Discount</th>
                                    <th class="text-right">Net Sales</th>
                                    <th class="text-right">Paid Amount</th>
                                    <th class="text-right">Remaining</th>
                                    <th>Pay Status</th>
                                    <th>Payment Methods</th>
                                    <th class="text-center" style="width: 110px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr class="{{ $row->is_today ? 'row-today' : '' }}">
                                        <td class="text-center">{{ $row->index }}</td>
                                        <td>
                                             <strong style="color: #1e293b;">{{ $row->date }}</strong>
                                             <small style="color: #64748b; margin-left: 4px;">({{ $row->day_name }})</small>
                                             @if ($row->is_today)
                                                 <span class="status-badge today" style="margin-left: 4px;">Today</span>
                                             @endif
                                        </td>
                                        <td class="text-center font-weight-bold">{{ $row->invoices_count }}</td>
                                        <td class="text-center">{{ $row->items_qty }}</td>
                                        <td class="text-right">${{ number_format($row->gross_sales, 2) }}</td>
                                        <td class="text-right text-muted">
                                            {{ $row->discount > 0 ? '-$' . number_format($row->discount, 2) : '$0.00' }}
                                        </td>
                                        <td class="text-right" style="font-weight: 700; color: #2563eb;">
                                            ${{ number_format($row->net_sales, 2) }}
                                        </td>
                                        <td class="text-right text-success font-weight-bold">
                                            ${{ number_format($row->paid_amount, 2) }}
                                        </td>
                                        <td class="text-right {{ $row->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            ${{ number_format($row->remaining_amount, 2) }}
                                        </td>
                                        <td>
                                            @if ($row->paid_count > 0)
                                                <span class="status-badge paid">{{ $row->paid_count }} Paid</span>
                                            @endif
                                            @if ($row->partial_count > 0)
                                                <span class="status-badge partial">{{ $row->partial_count }} Partial</span>
                                            @endif
                                            @if ($row->pending_count > 0)
                                                <span class="status-badge pending">{{ $row->pending_count }} Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($row->payment_methods as $method => $count)
                                                <span class="method-tag">{{ $method }}: {{ $count }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn-drilldown"
                                                @click="openPeriodDetails('{{ $row->date }}')">
                                                <i class='bx bx-detail'></i>
                                                <span>Orders</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="empty-placeholder">
                                            <i class='bx bx-calendar-x'></i>
                                            <p>No order transactions found for the selected period and filters.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($rows->count() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total Summary</td>
                                        <td class="text-center">{{ number_format($summary['total_invoices']) }}</td>
                                        <td class="text-center">{{ number_format($summary['total_items_sold']) }}</td>
                                        <td class="text-right">${{ number_format($summary['total_gross_sales'], 2) }}</td>
                                        <td class="text-right text-muted">-${{ number_format($summary['total_discount'], 2) }}</td>
                                        <td class="text-right text-primary">${{ number_format($summary['total_net_sales'], 2) }}</td>
                                        <td class="text-right text-success">${{ number_format($summary['total_paid'], 2) }}</td>
                                        <td class="text-right text-danger">${{ number_format($summary['total_remaining'], 2) }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <!-- MONTHLY TABLE -->
                        <table class="order-data-table" id="orderTransactionTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">Nº</th>
                                    <th>Month</th>
                                    <th class="text-center">Active Days</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Items Sold</th>
                                    <th class="text-right">Gross Sales</th>
                                    <th class="text-right">Discount</th>
                                    <th class="text-right">Net Sales</th>
                                    <th class="text-right">Paid Amount</th>
                                    <th class="text-right">Remaining</th>
                                    <th class="text-right">Avg Order</th>
                                    <th>Top Method</th>
                                    <th>Top Shop</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-center">{{ $row->index }}</td>
                                        <td>
                                            <strong style="color: #1e293b; font-size: 14px;">{{ $row->month_name }}</strong>
                                        </td>
                                        <td class="text-center">{{ $row->active_days }} days</td>
                                        <td class="text-center font-weight-bold">{{ $row->invoices_count }}</td>
                                        <td class="text-center">{{ $row->items_qty }}</td>
                                        <td class="text-right">${{ number_format($row->gross_sales, 2) }}</td>
                                        <td class="text-right text-muted">
                                            {{ $row->discount > 0 ? '-$' . number_format($row->discount, 2) : '$0.00' }}
                                        </td>
                                        <td class="text-right" style="font-weight: 700; color: #2563eb;">
                                            ${{ number_format($row->net_sales, 2) }}
                                        </td>
                                        <td class="text-right text-success font-weight-bold">
                                            ${{ number_format($row->paid_amount, 2) }}
                                        </td>
                                        <td class="text-right {{ $row->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            ${{ number_format($row->remaining_amount, 2) }}
                                        </td>
                                        <td class="text-right">${{ number_format($row->avg_ticket, 2) }}</td>
                                        <td>
                                            <span class="method-tag">{{ $row->top_pay_method }}</span>
                                        </td>
                                        <td>
                                            <small style="color: #475569;">{{ $row->top_shop_name }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div style="display: inline-flex; gap: 4px;">
                                                <a href="{{ route('admin-report-order-transaction-daily', ['from_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->startOfMonth()->format('Y-m-d'), 'to_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->endOfMonth()->format('Y-m-d')]) }}"
                                                    class="btn-drilldown" title="View Daily Transactions for this Month">
                                                    <i class='bx bx-calendar'></i> Daily
                                                </a>
                                                <button type="button" class="btn-drilldown"
                                                    @click="openPeriodDetails('{{ $row->month_key }}')">
                                                    <i class='bx bx-detail'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="empty-placeholder">
                                            <i class='bx bx-calendar-x'></i>
                                            <p>No monthly order transactions found for the selected year and filters.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($rows->count() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Total Summary</td>
                                        <td class="text-center">{{ number_format($summary['total_invoices']) }}</td>
                                        <td class="text-center">{{ number_format($summary['total_items_sold']) }}</td>
                                        <td class="text-right">${{ number_format($summary['total_gross_sales'], 2) }}</td>
                                        <td class="text-right text-muted">-${{ number_format($summary['total_discount'], 2) }}</td>
                                        <td class="text-right text-primary">${{ number_format($summary['total_net_sales'], 2) }}</td>
                                        <td class="text-right text-success">${{ number_format($summary['total_paid'], 2) }}</td>
                                        <td class="text-right text-danger">${{ number_format($summary['total_remaining'], 2) }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Period Order Drilldown Modal -->
        <template x-if="showDetailModal">
            <div class="report-modal-backdrop" @click.self="closePeriodDetails()">
                <div class="report-modal-card">
                    <div class="report-modal-header">
                        <h3>
                            <i class='bx bx-receipt text-primary'></i>
                            <span>Orders for <span x-text="periodData?.period_label || periodData?.period"></span></span>
                        </h3>
                        <button type="button" class="btn-close-report-modal" @click="closePeriodDetails()">&times;</button>
                    </div>

                    <div class="report-modal-body">
                        <template x-if="modalLoading">
                            <div style="text-align: center; padding: 40px;">
                                <i class='bx bx-loader-alt bx-spin' style="font-size: 36px; color: #2563eb;"></i>
                                <p style="margin-top: 10px; color: #64748b;">Loading order details...</p>
                            </div>
                        </template>

                        <template x-if="!modalLoading && periodData">
                            <div>
                                <!-- Summary Chips -->
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>Total Orders</span>
                                        <strong x-text="periodData.count"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Total Net Revenue</span>
                                        <strong class="text-primary" x-text="'$' + Number(periodData.total_revenue || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Total Paid</span>
                                        <strong class="text-success" x-text="'$' + Number(periodData.total_paid || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Total Outstanding</span>
                                        <strong class="text-danger" x-text="'$' + Number(periodData.total_remaining || 0).toFixed(2)"></strong>
                                    </div>
                                </div>

                                <!-- Orders List Table -->
                                <div style="overflow-x: auto;">
                                    <table class="order-data-table">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Date & Time</th>
                                                <th>Customer</th>
                                                <th>Shop / Staff</th>
                                                <th>Items (Products / Services)</th>
                                                <th>Pay Status</th>
                                                <th>Method</th>
                                                <th class="text-right">Total ($)</th>
                                                <th class="text-right">Paid ($)</th>
                                                <th class="text-right">Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="inv in periodData.bookings" :key="inv.id">
                                                <tr>
                                                    <td>
                                                        <strong style="color: #2563eb;" x-text="inv.invoice_number"></strong>
                                                    </td>
                                                    <td>
                                                        <small x-text="inv.booking_date_formatted"></small>
                                                    </td>
                                                    <td>
                                                        <div style="font-weight: 600;" x-text="inv.customer_name"></div>
                                                        <small class="text-muted" x-text="inv.customer_phone"></small>
                                                    </td>
                                                    <td>
                                                        <div x-text="inv.shop_name"></div>
                                                        <small class="text-muted" x-text="inv.barber_name"></small>
                                                    </td>
                                                    <td>
                                                        <template x-for="item in inv.items" :key="item.id">
                                                            <span class="invoice-item-chip">
                                                                <span x-text="item.name"></span>
                                                                <b x-text="' (x' + item.qty + ')'"></b>
                                                            </span>
                                                        </template>
                                                    </td>
                                                    <td>
                                                        <span :class="'status-badge ' + (inv.payment_status || 'pending').toLowerCase()"
                                                            x-text="inv.payment_status"></span>
                                                    </td>
                                                    <td>
                                                        <span class="method-tag" x-text="inv.pay_way || 'Cash'"></span>
                                                    </td>
                                                    <td class="text-right font-weight-bold" x-text="'$' + Number(inv.total_price || 0).toFixed(2)"></td>
                                                    <td class="text-right text-success" x-text="'$' + Number(inv.paid_amount || 0).toFixed(2)"></td>
                                                    <td class="text-right" :class="inv.remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted'"
                                                        x-text="'$' + Number(inv.remaining_amount || 0).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script>
        $(document).ready(function() {
            $(".datepicker-input").datepicker({
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                dateFormat: "yy-mm-dd",
            });
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('xOrderTransactionReport', () => ({
                viewMode: '{{ $viewMode }}',
                showDetailModal: false,
                modalLoading: false,
                periodData: null,
                exportLoading: false,

                async openPeriodDetails(period) {
                    this.showDetailModal = true;
                    this.modalLoading = true;
                    this.periodData = null;

                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        const response = await Axios.get(`{{ url('admin/report/order-transaction/details') }}/${period}?` + currentParams.toString());
                        this.periodData = response.data;
                    } catch (err) {
                        console.error('Failed to load period details:', err);
                        alert('Could not load period order details. Please try again.');
                        this.showDetailModal = false;
                    } finally {
                        this.modalLoading = false;
                    }
                },

                closePeriodDetails() {
                    this.showDetailModal = false;
                    this.periodData = null;
                },

                async exportExcel() {
                    this.exportLoading = true;
                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        currentParams.set('view_mode', this.viewMode);

                        const response = await Axios.get(`{{ route('admin-report-order-transaction-report') }}?` + currentParams.toString());
                        const reportData = response.data;

                        const workbook = new ExcelJS.Workbook();
                        const sheetName = this.viewMode === 'monthly' ? 'Monthly Order Report' : 'Daily Order Report';
                        const worksheet = workbook.addWorksheet(sheetName);

                        if (this.viewMode === 'daily') {
                            worksheet.columns = [
                                { header: 'Nº', key: 'index', width: 8 },
                                { header: 'Date', key: 'date', width: 16 },
                                { header: 'Day', key: 'day_name', width: 10 },
                                { header: 'Orders Count', key: 'invoices_count', width: 16 },
                                { header: 'Items Sold', key: 'items_qty', width: 14 },
                                { header: 'Gross Sales ($)', key: 'gross_sales', width: 16 },
                                { header: 'Discount ($)', key: 'discount', width: 14 },
                                { header: 'Net Sales ($)', key: 'net_sales', width: 16 },
                                { header: 'Paid Amount ($)', key: 'paid_amount', width: 16 },
                                { header: 'Remaining Balance ($)', key: 'remaining_amount', width: 22 },
                                { header: 'Fully Paid Count', key: 'paid_count', width: 16 },
                                { header: 'Partial Count', key: 'partial_count', width: 14 },
                                { header: 'Pending Count', key: 'pending_count', width: 14 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    date: r.date,
                                    day_name: r.day_name,
                                    invoices_count: r.invoices_count,
                                    items_qty: r.items_qty,
                                    gross_sales: Number(r.gross_sales || 0),
                                    discount: Number(r.discount || 0),
                                    net_sales: Number(r.net_sales || 0),
                                    paid_amount: Number(r.paid_amount || 0),
                                    remaining_amount: Number(r.remaining_amount || 0),
                                    paid_count: r.paid_count,
                                    partial_count: r.partial_count,
                                    pending_count: r.pending_count,
                                });
                            });
                        } else {
                            worksheet.columns = [
                                { header: 'Nº', key: 'index', width: 8 },
                                { header: 'Month', key: 'month_name', width: 18 },
                                { header: 'Active Days', key: 'active_days', width: 14 },
                                { header: 'Orders Count', key: 'invoices_count', width: 16 },
                                { header: 'Items Sold', key: 'items_qty', width: 14 },
                                { header: 'Gross Sales ($)', key: 'gross_sales', width: 16 },
                                { header: 'Discount ($)', key: 'discount', width: 14 },
                                { header: 'Net Sales ($)', key: 'net_sales', width: 16 },
                                { header: 'Paid Amount ($)', key: 'paid_amount', width: 16 },
                                { header: 'Remaining Balance ($)', key: 'remaining_amount', width: 22 },
                                { header: 'Avg Order Value ($)', key: 'avg_ticket', width: 18 },
                                { header: 'Top Payment Method', key: 'top_pay_method', width: 20 },
                                { header: 'Top Shop', key: 'top_shop_name', width: 22 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    month_name: r.month_name,
                                    active_days: r.active_days,
                                    invoices_count: r.invoices_count,
                                    items_qty: r.items_qty,
                                    gross_sales: Number(r.gross_sales || 0),
                                    discount: Number(r.discount || 0),
                                    net_sales: Number(r.net_sales || 0),
                                    paid_amount: Number(r.paid_amount || 0),
                                    remaining_amount: Number(r.remaining_amount || 0),
                                    avg_ticket: Number(r.avg_ticket || 0),
                                    top_pay_method: r.top_pay_method,
                                    top_shop_name: r.top_shop_name,
                                });
                            });
                        }

                        worksheet.getRow(1).font = { bold: true };
                        worksheet.getRow(1).fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FFE2E8F0' }
                        };

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        const filename = (this.viewMode === 'monthly' ? 'Monthly_Order_Report_' : 'Daily_Order_Report_') + moment().format('YYYY_MM_DD_HHmmss');
                        saveAs(blob, filename);
                    } catch (err) {
                        console.error('Export failed:', err);
                        alert('Excel export failed. Please try again.');
                    } finally {
                        this.exportLoading = false;
                    }
                }
            }));
        });
    </script>
@stop
