@extends('admin::shared.layout')
@section('layout')
    <style>
        .sales-report-wrapper {
            padding: 0;
            width: 100%;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin: 0 0 14px 0;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 12px 16px;
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
            padding: 14px 18px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin-bottom: 14px;
        }

        .filter-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
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

        /* Sales Table Container */
        .report-table-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .table-custom-header {
            padding: 12px 18px;
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

        .sales-data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .sales-data-table th {
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

        .sales-data-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .sales-data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .sales-data-table tr.row-today {
            background-color: #eff6ff;
        }

        .sales-data-table tfoot td {
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
            max-width: 950px;
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

    @include('admin::shared.header', ['header_name' => __('sales_report.title')])
    <div class="content-wrapper sales-report-wrapper" id="salesReportApp" x-data="xSalesReport">
        <!-- Tab Bar Header -->
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            @php
                                $currentParams = request()->query();
                            @endphp
                            <a href="{{ route('admin-report-sales-daily', $currentParams) }}"
                                class="{{ $viewMode === 'daily' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-event'></i>
                                {{ __('sales_report.tab.daily') }}
                            </a>
                            <a href="{{ route('admin-report-sales-monthly', $currentParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {{ __('sales_report.tab.monthly') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button type="button" @click="exportExcel()" class="btn-excel-export" :disabled="exportLoading">
                        <i class='bx bx-download'></i>
                        <span x-text="exportLoading ? '{{ __('sales_report.excel.exporting') }}' : '{{ __('sales_report.button.export_excel') }}'">{{ __('sales_report.button.export_excel') }}</span>
                    </button>
                    <button type="button" s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>{{ __('sales_report.button.reload') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body" style="padding: 14px 20px 24px 20px;">
            <!-- Filter Panel -->
            <div class="report-filter-panel">
                <form id="salesFilterForm" method="GET" action="{{ url()->current() }}">
                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">{{ __('sales_report.presets.title') }}</span>
                                <a href="{{ route('admin-report-sales-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'today'])) }}"
                                    class="preset-btn {{ request('preset') === 'today' ? 'active' : '' }}">{{ __('sales_report.presets.today') }}</a>
                                <a href="{{ route('admin-report-sales-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'yesterday'])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">{{ __('sales_report.presets.yesterday') }}</a>
                                <a href="{{ route('admin-report-sales-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '7days'])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">{{ __('sales_report.presets.last_7_days') }}</a>
                                <a href="{{ route('admin-report-sales-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '30days'])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">{{ __('sales_report.presets.last_30_days') }}</a>
                                <a href="{{ route('admin-report-sales-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'last_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'last_month' ? 'active' : '' }}">{{ __('sales_report.presets.last_month') }}</a>
                            </div>
                        </div>
                    @endif

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="from_date">{{ __('sales_report.filter.from_date') }}</label>
                                <input type="text" name="from_date" id="from_date" class="filter-input datepicker-input"
                                    value="{{ $from_date }}" autocomplete="off" placeholder="{{ __('sales_report.filter.placeholder_date') }}">
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_date">{{ __('sales_report.filter.to_date') }}</label>
                                <input type="text" name="to_date" id="to_date" class="filter-input datepicker-input"
                                    value="{{ $to_date }}" autocomplete="off" placeholder="{{ __('sales_report.filter.placeholder_date') }}">
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label for="year">{{ __('sales_report.filter.year') }}</label>
                                <select name="year" id="year" class="filter-select">
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="from_month">{{ __('sales_report.filter.from_month') }}</label>
                                <select name="from_month" id="from_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $from_month === $m ? 'selected' : '' }}>
                                            {{ __('sales_report.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_month">{{ __('sales_report.filter.to_month') }}</label>
                                <select name="to_month" id="to_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $to_month === $m ? 'selected' : '' }}>
                                            {{ __('sales_report.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label for="shop_id">{{ __('sales_report.filter.shop') }}</label>
                            <select name="shop_id" id="shop_id" class="filter-select">
                                <option value="">{{ __('sales_report.filter.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="barber_id">{{ __('sales_report.filter.staff') }}</label>
                            <select name="barber_id" id="barber_id" class="filter-select">
                                <option value="">{{ __('sales_report.filter.all_staff') }}</option>
                                @foreach ($barbers as $barber)
                                    <option value="{{ $barber->id }}" {{ request('barber_id') == $barber->id ? 'selected' : '' }}>
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="payment_status">{{ __('sales_report.filter.payment_status') }}</label>
                            <select name="payment_status" id="payment_status" class="filter-select">
                                <option value="">{{ __('sales_report.filter.status_active_exclude_cancel') }}</option>
                                <option value="all" {{ request('payment_status') === 'all' ? 'selected' : '' }}>{{ __('sales_report.filter.status_all') }}</option>
                                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>{{ __('sales_report.filter.status_paid') }}</option>
                                <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>{{ __('sales_report.filter.status_partial') }}</option>
                                <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>{{ __('sales_report.filter.status_pending') }}</option>
                                <option value="Cancel" {{ request('payment_status') === 'Cancel' ? 'selected' : '' }}>{{ __('sales_report.filter.status_cancel') }}</option>
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="pay_way">{{ __('sales_report.filter.payment_method') }}</label>
                            <select name="pay_way" id="pay_way" class="filter-select">
                                <option value="">{{ __('sales_report.filter.all_methods') }}</option>
                                @foreach ($paymentMethods as $pm)
                                    @php
                                        $pmKey = strtolower(str_replace(' ', '_', $pm));
                                        $pmLabel = __('sales_report.payment_methods.' . $pmKey);
                                        if ($pmLabel === 'sales_report.payment_methods.' . $pmKey) {
                                            $pmLabel = $pm;
                                        }
                                    @endphp
                                    <option value="{{ $pm }}" {{ request('pay_way') === $pm ? 'selected' : '' }}>{{ $pmLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="item_type">{{ __('sales_report.filter.item_type') }}</label>
                            <select name="item_type" id="item_type" class="filter-select">
                                <option value="">{{ __('sales_report.filter.all_items') }}</option>
                                <option value="product" {{ request('item_type') === 'product' ? 'selected' : '' }}>{{ __('sales_report.filter.products_only') }}</option>
                                <option value="service" {{ request('item_type') === 'service' ? 'selected' : '' }}>{{ __('sales_report.filter.services_only') }}</option>
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="search">{{ __('sales_report.filter.keyword_search') }}</label>
                            <input type="text" name="search" id="search" class="filter-input"
                                value="{{ request('search') }}" placeholder="{{ __('sales_report.filter.placeholder_search') }}">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class='bx bx-search'></i>
                                <span>{{ __('sales_report.button.filter') }}</span>
                            </button>
                            <a href="{{ route($viewMode === 'daily' ? 'admin-report-sales-daily' : 'admin-report-sales-monthly') }}"
                                class="btn-filter-reset">
                                <i class='bx bx-reset'></i>
                                <span>{{ __('sales_report.button.reset') }}</span>
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
                        <div class="kpi-title">{{ __('sales_report.kpi.total_net_sales') }}</div>
                        <div class="kpi-value text-primary">${{ number_format($summary['total_net_sales'], 2) }}</div>
                        <div class="kpi-sub">{{ __('sales_report.kpi.gross') }}: ${{ number_format($summary['total_gross_sales'], 2) }} | {{ __('sales_report.kpi.disc') }}: -${{ number_format($summary['total_discount'], 2) }}</div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap indigo">
                        <i class='bx bx-receipt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('sales_report.kpi.total_invoices') }}</div>
                        <div class="kpi-value">{{ number_format($summary['total_invoices']) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_items_sold']) }} {{ __('sales_report.kpi.items_sold') }} ({{ __('sales_report.kpi.avg') }}: ${{ number_format($summary['avg_invoice_value'], 2) }}{{ __('sales_report.kpi.per_invoice') }})</div>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success">
                        <i class='bx bx-check-shield'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('sales_report.kpi.total_amount_paid') }}</div>
                        <div class="kpi-value text-success">${{ number_format($summary['total_paid'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['paid_count'] }} {{ __('sales_report.kpi.fully_paid') }} • {{ $summary['partial_count'] }} {{ __('sales_report.kpi.partial') }}</div>
                    </div>
                </div>

                <!-- Total Outstanding -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger">
                        <i class='bx bx-time'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('sales_report.kpi.total_outstanding') }}</div>
                        <div class="kpi-value text-danger">${{ number_format($summary['total_remaining'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['pending_count'] }} {{ __('sales_report.kpi.pending_invoices') }}</div>
                    </div>
                </div>

                <!-- Product vs Service Revenue -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap purple">
                        <i class='bx bx-pie-chart-alt-2'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('sales_report.kpi.revenue_split') }}</div>
                        <div class="kpi-value" style="font-size: 16px;">
                            {{ __('sales_report.kpi.product_prefix') }}: ${{ number_format($summary['product_sales'], 2) }} <span style="font-weight: normal; font-size: 12px; color: #94a3b8;">/</span> {{ __('sales_report.kpi.service_prefix') }}: ${{ number_format($summary['service_sales'], 2) }}
                        </div>
                        <div class="kpi-sub">{{ $summary['total_product_qty'] }} {{ __('sales_report.kpi.products') }} • {{ $summary['total_service_qty'] }} {{ __('sales_report.kpi.services') }}</div>
                    </div>
                </div>
            </div>

            <!-- Sales Data Listing Table -->
            <div class="report-table-card">
                <div class="table-custom-header">
                    <h4>
                        <i class='bx {{ $viewMode === 'daily' ? 'bx-calendar-event' : 'bx-calendar-alt' }}'></i>
                        {{ $viewMode === 'daily' ? __('sales_report.table.daily_breakdown') : __('sales_report.table.monthly_breakdown') }}
                        <span style="font-size: 12px; font-weight: normal; color: #64748b;">
                            ({{ $rows->count() }} {{ $viewMode === 'daily' ? __('sales_report.table.days_recorded') : __('sales_report.table.months_recorded') }})
                        </span>
                    </h4>
                </div>

                <div class="table-responsive-custom">
                    @if ($viewMode === 'daily')
                        <!-- DAILY TABLE -->
                        <table class="sales-data-table" id="salesReportTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">{{ __('sales_report.table.no') }}</th>
                                    <th>{{ __('sales_report.table.date') }}</th>
                                    <th class="text-center">{{ __('sales_report.table.invoices') }}</th>
                                    <th class="text-center">{{ __('sales_report.table.items') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.gross_sales') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.discount') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.net_sales') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.paid_amount') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.remaining') }}</th>
                                    <th>{{ __('sales_report.table.pay_status') }}</th>
                                    <th>{{ __('sales_report.table.payment_methods') }}</th>
                                    <th class="text-center" style="width: 110px;">{{ __('sales_report.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr class="{{ $row->is_today ? 'row-today' : '' }}">
                                        <td class="text-center">{{ $row->index }}</td>
                                        <td>
                                            <strong style="color: #1e293b;">{{ $row->date }}</strong>
                                            @php $dKey = strtolower($row->day_name); @endphp
                                            <small style="color: #64748b; margin-left: 4px;">({{ __('sales_report.days.' . $dKey) }})</small>
                                            @if ($row->is_today)
                                                <span class="status-badge today" style="margin-left: 4px;">{{ __('sales_report.badge.today') }}</span>
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
                                                <span class="status-badge paid">{{ $row->paid_count }} {{ __('sales_report.status.paid') }}</span>
                                            @endif
                                            @if ($row->partial_count > 0)
                                                <span class="status-badge partial">{{ $row->partial_count }} {{ __('sales_report.status.partial') }}</span>
                                            @endif
                                            @if ($row->pending_count > 0)
                                                <span class="status-badge pending">{{ $row->pending_count }} {{ __('sales_report.status.pending') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($row->payment_methods as $method => $count)
                                                @php
                                                    $pmKey = strtolower(str_replace(' ', '_', $method));
                                                    $pmLabel = __('sales_report.payment_methods.' . $pmKey);
                                                    if ($pmLabel === 'sales_report.payment_methods.' . $pmKey) {
                                                        $pmLabel = $method;
                                                    }
                                                @endphp
                                                <span class="method-tag">{{ $pmLabel }}: {{ $count }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn-drilldown"
                                                @click="openPeriodDetails('{{ $row->date }}')"
                                                title="{{ __('sales_report.button.view_invoices_tooltip') }}">
                                                <i class='bx bx-detail'></i>
                                                <span>{{ __('sales_report.button.invoices') }}</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="empty-placeholder">
                                            <i class='bx bx-calendar-x'></i>
                                            <p>{{ __('sales_report.empty.daily_description') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($rows->count() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="2">{{ __('sales_report.table.total_summary') }}</td>
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
                        <table class="sales-data-table" id="salesReportTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">{{ __('sales_report.table.no') }}</th>
                                    <th>{{ __('sales_report.table.month') }}</th>
                                    <th class="text-center">{{ __('sales_report.table.active_days') }}</th>
                                    <th class="text-center">{{ __('sales_report.table.invoices') }}</th>
                                    <th class="text-center">{{ __('sales_report.table.items_sold') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.gross_sales') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.discount') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.net_sales') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.paid_amount') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.remaining') }}</th>
                                    <th class="text-right">{{ __('sales_report.table.avg_order') }}</th>
                                    <th>{{ __('sales_report.table.top_method') }}</th>
                                    <th>{{ __('sales_report.table.top_shop') }}</th>
                                    <th class="text-center" style="width: 140px;">{{ __('sales_report.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-center">{{ $row->index }}</td>
                                        <td>
                                            @php
                                                $cMonthNum = (int) \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->month;
                                                $cYearNum = \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->year;
                                            @endphp
                                            <strong style="color: #1e293b; font-size: 14px;">{{ __('sales_report.months.' . $cMonthNum) }} {{ $cYearNum }}</strong>
                                        </td>
                                        <td class="text-center">{{ $row->active_days }} {{ __('sales_report.table.days') }}</td>
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
                                            @php
                                                $topMKey = strtolower(str_replace(' ', '_', $row->top_pay_method));
                                                $topMLabel = __('sales_report.payment_methods.' . $topMKey);
                                                if ($topMLabel === 'sales_report.payment_methods.' . $topMKey) {
                                                    $topMLabel = $row->top_pay_method;
                                                }
                                            @endphp
                                            <span class="method-tag">{{ $topMLabel }}</span>
                                        </td>
                                        <td>
                                            <small style="color: #475569;">{{ $row->top_shop_name }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div style="display: inline-flex; gap: 4px;">
                                                <a href="{{ route('admin-report-sales-daily', ['from_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->startOfMonth()->format('Y-m-d'), 'to_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->endOfMonth()->format('Y-m-d')]) }}"
                                                    class="btn-drilldown" title="{{ __('sales_report.button.view_daily_tooltip') }}">
                                                    <i class='bx bx-calendar'></i> {{ __('sales_report.button.daily') }}
                                                </a>
                                                <button type="button" class="btn-drilldown"
                                                    @click="openPeriodDetails('{{ $row->month_key }}')"
                                                    title="{{ __('sales_report.button.view_invoices_tooltip') }}">
                                                    <i class='bx bx-detail'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="empty-placeholder">
                                            <i class='bx bx-calendar-x'></i>
                                            <p>{{ __('sales_report.empty.monthly_description') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($rows->count() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">{{ __('sales_report.table.total_summary') }}</td>
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

        <!-- Period Invoice Drilldown Modal -->
        <template x-if="showDetailModal">
            <div class="report-modal-backdrop" @click.self="closePeriodDetails()">
                <div class="report-modal-card">
                    <div class="report-modal-header">
                        <h3>
                            <i class='bx bx-receipt text-primary'></i>
                            <span>{{ __('sales_report.modal.invoices_for') }} <span x-text="periodData?.period_label || periodData?.period"></span></span>
                        </h3>
                        <button type="button" class="btn-close-report-modal" @click="closePeriodDetails()">&times;</button>
                    </div>

                    <div class="report-modal-body">
                        <template x-if="modalLoading">
                            <div style="text-align: center; padding: 40px;">
                                <i class='bx bx-loader-alt bx-spin' style="font-size: 36px; color: #2563eb;"></i>
                                <p style="margin-top: 10px; color: #64748b;">{{ __('sales_report.modal.loading') }}</p>
                            </div>
                        </template>

                        <template x-if="!modalLoading && periodData">
                            <div>
                                <!-- Ledger Summary -->
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>{{ __('sales_report.modal.total_invoices') }}</span>
                                        <strong x-text="periodData.count"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('sales_report.modal.total_net_revenue') }}</span>
                                        <strong class="text-primary" x-text="'$' + Number(periodData.total_revenue || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('sales_report.modal.total_paid') }}</span>
                                        <strong class="text-success" x-text="'$' + Number(periodData.total_paid || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('sales_report.modal.total_outstanding') }}</span>
                                        <strong class="text-danger" x-text="'$' + Number(periodData.total_remaining || 0).toFixed(2)"></strong>
                                    </div>
                                </div>

                                <!-- Invoices List Table -->
                                <div style="overflow-x: auto;">
                                    <table class="sales-data-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('sales_report.modal.table.invoice_no') }}</th>
                                                <th>{{ __('sales_report.modal.table.date_time') }}</th>
                                                <th>{{ __('sales_report.modal.table.customer') }}</th>
                                                <th>{{ __('sales_report.modal.table.shop_staff') }}</th>
                                                <th>{{ __('sales_report.modal.table.items') }}</th>
                                                <th>{{ __('sales_report.modal.table.pay_status') }}</th>
                                                <th>{{ __('sales_report.modal.table.method') }}</th>
                                                <th class="text-right">{{ __('sales_report.modal.table.total') }}</th>
                                                <th class="text-right">{{ __('sales_report.modal.table.paid') }}</th>
                                                <th class="text-right">{{ __('sales_report.modal.table.remaining') }}</th>
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
                                                            x-text="getLocalizedStatus(inv.payment_status)"></span>
                                                    </td>
                                                    <td>
                                                        <span class="method-tag" x-text="getLocalizedMethod(inv.pay_way)"></span>
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
            Alpine.data('xSalesReport', () => ({
                viewMode: '{{ $viewMode }}',
                showDetailModal: false,
                modalLoading: false,
                periodData: null,
                exportLoading: false,

                getLocalizedStatus(status) {
                    const s = (status || '').toLowerCase();
                    const statusMap = {
                        'paid': '{{ __('sales_report.status.paid') }}',
                        'partial': '{{ __('sales_report.status.partial') }}',
                        'pending': '{{ __('sales_report.status.pending') }}',
                        'cancel': '{{ __('sales_report.status.cancel') }}',
                    };
                    return statusMap[s] || status || 'Pending';
                },

                getLocalizedMethod(method) {
                    const m = (method || 'Cash').toLowerCase().replace(/\s+/g, '_');
                    const methodMap = {
                        'cash': '{{ __('sales_report.payment_methods.cash') }}',
                        'aba_pay': 'ABA PAY',
                        'khqr': 'KHQR',
                        'credit_card': '{{ __('sales_report.payment_methods.credit_card') }}',
                        'bank_transfer': '{{ __('sales_report.payment_methods.bank_transfer') }}',
                    };
                    return methodMap[m] || method || 'Cash';
                },

                async openPeriodDetails(period) {
                    this.showDetailModal = true;
                    this.modalLoading = true;
                    this.periodData = null;

                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        const response = await Axios.get(`{{ url('admin/report/sales/details') }}/${period}?` + currentParams.toString());
                        this.periodData = response.data;
                    } catch (err) {
                        console.error('Failed to load period details:', err);
                        alert('{{ __('sales_report.modal.error_load') }}');
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

                        const response = await Axios.get(`{{ route('admin-report-sales-report') }}?` + currentParams.toString());
                        const reportData = response.data;

                        const workbook = new ExcelJS.Workbook();
                        const sheetName = this.viewMode === 'monthly' ? '{{ __('sales_report.excel.sheet_monthly') }}' : '{{ __('sales_report.excel.sheet_daily') }}';
                        const worksheet = workbook.addWorksheet(sheetName);

                        if (this.viewMode === 'daily') {
                            worksheet.columns = [
                                { header: '{{ __('sales_report.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('sales_report.excel.date') }}', key: 'date', width: 16 },
                                { header: '{{ __('sales_report.excel.day') }}', key: 'day_name', width: 10 },
                                { header: '{{ __('sales_report.excel.invoices_count') }}', key: 'invoices_count', width: 16 },
                                { header: '{{ __('sales_report.excel.items_sold') }}', key: 'items_qty', width: 14 },
                                { header: '{{ __('sales_report.excel.gross_sales') }}', key: 'gross_sales', width: 16 },
                                { header: '{{ __('sales_report.excel.discount') }}', key: 'discount', width: 14 },
                                { header: '{{ __('sales_report.excel.net_sales') }}', key: 'net_sales', width: 16 },
                                { header: '{{ __('sales_report.excel.paid_amount') }}', key: 'paid_amount', width: 16 },
                                { header: '{{ __('sales_report.excel.remaining_balance') }}', key: 'remaining_amount', width: 22 },
                                { header: '{{ __('sales_report.excel.fully_paid_count') }}', key: 'paid_count', width: 16 },
                                { header: '{{ __('sales_report.excel.partial_count') }}', key: 'partial_count', width: 14 },
                                { header: '{{ __('sales_report.excel.pending_count') }}', key: 'pending_count', width: 14 },
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
                                { header: '{{ __('sales_report.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('sales_report.excel.month') }}', key: 'month_name', width: 18 },
                                { header: '{{ __('sales_report.excel.active_days') }}', key: 'active_days', width: 14 },
                                { header: '{{ __('sales_report.excel.invoices_count') }}', key: 'invoices_count', width: 16 },
                                { header: '{{ __('sales_report.excel.items_sold') }}', key: 'items_qty', width: 14 },
                                { header: '{{ __('sales_report.excel.gross_sales') }}', key: 'gross_sales', width: 16 },
                                { header: '{{ __('sales_report.excel.discount') }}', key: 'discount', width: 14 },
                                { header: '{{ __('sales_report.excel.net_sales') }}', key: 'net_sales', width: 16 },
                                { header: '{{ __('sales_report.excel.paid_amount') }}', key: 'paid_amount', width: 16 },
                                { header: '{{ __('sales_report.excel.remaining_balance') }}', key: 'remaining_amount', width: 22 },
                                { header: '{{ __('sales_report.excel.avg_order_value') }}', key: 'avg_ticket', width: 18 },
                                { header: '{{ __('sales_report.excel.top_payment_method') }}', key: 'top_pay_method', width: 20 },
                                { header: '{{ __('sales_report.excel.top_shop') }}', key: 'top_shop_name', width: 22 },
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
                        const filename = (this.viewMode === 'monthly' ? '{{ __('sales_report.excel.file_monthly_prefix') }}' : '{{ __('sales_report.excel.file_daily_prefix') }}') + moment().format('YYYY_MM_DD_HHmmss');
                        saveAs(blob, filename);
                    } catch (err) {
                        console.error('Export failed:', err);
                        alert('{{ __('sales_report.excel.export_failed') }}');
                    } finally {
                        this.exportLoading = false;
                    }
                }
            }));
        });
    </script>
@stop
