@extends('admin::shared.layout')
@section('layout')
    <style>
        .inventory-report-wrapper {
            padding: 0;
            width: 100%;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
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

        .kpi-icon-wrap.success { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .kpi-icon-wrap.danger { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
        .kpi-icon-wrap.primary { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .kpi-icon-wrap.warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
        .kpi-icon-wrap.purple { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
        .kpi-icon-wrap.indigo { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
        .kpi-icon-wrap.teal { background: rgba(20, 184, 166, 0.12); color: #0d9488; }

        .kpi-info {
            flex: 1;
            min-width: 0;
        }

        .kpi-title {
            font-size: 11px;
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
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
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
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            transition: background 0.15s ease;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Chart Card */
        .report-chart-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 18px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin-bottom: 16px;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .chart-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
        }

        /* Data Table Card */
        .report-table-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .table-header-bar {
            padding: 12px 18px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-count-badge {
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .movement-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .movement-data-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .movement-data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .movement-data-table tr:hover td {
            background-color: #f8fafc;
        }

        .movement-data-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            border-top: 2px solid #cbd5e1;
            padding: 14px 16px;
        }

        /* Status & Movement Badges */
        .movement-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .movement-badge.stock_in { background: #dcfce7; color: #15803d; }
        .movement-badge.stock_out { background: #fee2e2; color: #b91c1c; }
        .movement-badge.sales { background: #e0f2fe; color: #0369a1; }
        .movement-badge.internal_out { background: #fef3c7; color: #b45309; }
        .movement-badge.transfer { background: #ede9fe; color: #6d28d9; }

        .qty-badge {
            font-weight: 700;
            font-size: 13px;
        }
        .qty-badge.in { color: #059669; }
        .qty-badge.out { color: #dc2626; }
        .qty-badge.neutral { color: #64748b; }
        .qty-badge.transfer { color: #7c3aed; }

        .btn-view-details {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #bfdbfe;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-view-details:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .btn-excel-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #10b981;
            color: #ffffff;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-excel-export:hover {
            background: #059669;
        }

        /* Drilldown Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-box {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 1050px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 16px 22px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 22px;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            line-height: 1;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .modal-close-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-period-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
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

    @include('admin::shared.header', ['header_name' => __('inventory_movement.title')])
    <div class="content-wrapper inventory-report-wrapper" id="inventoryReportApp" x-data="xInventoryReport">
        <!-- Tab Bar Header -->
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            @php
                                $currentParams = request()->query();
                            @endphp
                            <a href="{{ route('admin-report-inventory-movement-daily', $currentParams) }}"
                                class="{{ $viewMode === 'daily' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-event'></i>
                                {{ __('inventory_movement.tab.daily') }}
                            </a>
                            <a href="{{ route('admin-report-inventory-movement-monthly', $currentParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {{ __('inventory_movement.tab.monthly') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button type="button" @click="exportExcel()" class="btn-excel-export" :disabled="exportLoading">
                        <i class='bx bx-download'></i>
                        <span x-text="exportLoading ? '{{ __('inventory_movement.excel.exporting') }}' : '{{ __('inventory_movement.button.export_excel') }}'">{{ __('inventory_movement.button.export_excel') }}</span>
                    </button>
                    <button type="button" s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>{{ __('inventory_movement.button.reload') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body" style="padding: 14px 20px 24px 20px;">
            <!-- Filter Panel -->
            <div class="report-filter-panel">
                <form id="inventoryFilterForm" method="GET" action="{{ url()->current() }}">
                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">{{ __('inventory_movement.presets.title') }}</span>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'today'])) }}"
                                    class="preset-btn {{ request('preset') === 'today' ? 'active' : '' }}">{{ __('inventory_movement.presets.today') }}</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'yesterday'])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">{{ __('inventory_movement.presets.yesterday') }}</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '7days'])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">{{ __('inventory_movement.presets.last_7_days') }}</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '30days'])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">{{ __('inventory_movement.presets.last_30_days') }}</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'this_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'this_month' || (!request('preset') && !request('from_date')) ? 'active' : '' }}">{{ __('inventory_movement.presets.this_month') }}</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'last_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'last_month' ? 'active' : '' }}">{{ __('inventory_movement.presets.last_month') }}</a>
                            </div>
                        </div>
                    @endif

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="from_date">{{ __('inventory_movement.filter.from_date') }}</label>
                                <input type="text" name="from_date" id="from_date" class="filter-input datepicker-input"
                                    value="{{ $from_date }}" autocomplete="off" placeholder="{{ __('inventory_movement.filter.placeholder_date') }}">
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_date">{{ __('inventory_movement.filter.to_date') }}</label>
                                <input type="text" name="to_date" id="to_date" class="filter-input datepicker-input"
                                    value="{{ $to_date }}" autocomplete="off" placeholder="{{ __('inventory_movement.filter.placeholder_date') }}">
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label for="year">{{ __('inventory_movement.filter.year') }}</label>
                                <select name="year" id="year" class="filter-select">
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="from_month">{{ __('inventory_movement.filter.from_month') }}</label>
                                <select name="from_month" id="from_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $from_month === $m ? 'selected' : '' }}>
                                            {{ __('inventory_movement.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_month">{{ __('inventory_movement.filter.to_month') }}</label>
                                <select name="to_month" id="to_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $to_month === $m ? 'selected' : '' }}>
                                            {{ __('inventory_movement.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label for="shop_id">{{ __('inventory_movement.filter.shop') }}</label>
                            <select name="shop_id" id="shop_id" class="filter-select">
                                <option value="">{{ __('inventory_movement.filter.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="category_id">{{ __('inventory_movement.filter.category') }}</label>
                            <select name="category_id" id="category_id" class="filter-select">
                                <option value="">{{ __('inventory_movement.filter.all_categories') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="product_id">{{ __('inventory_movement.filter.product') }}</label>
                            <select name="product_id" id="product_id" class="filter-select">
                                <option value="">{{ __('inventory_movement.filter.all_products') }}</option>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="movement_type">{{ __('inventory_movement.filter.movement_type') }}</label>
                            <select name="movement_type" id="movement_type" class="filter-select">
                                @foreach ($movementTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('movement_type', 'all') == $key ? 'selected' : '' }}>
                                        {{ __('inventory_movement.filter.movement_types.' . $key) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="request_by">{{ __('inventory_movement.filter.staff') }}</label>
                            <select name="request_by" id="request_by" class="filter-select">
                                <option value="">{{ __('inventory_movement.filter.all_staff') }}</option>
                                @foreach ($staffUsers as $stf)
                                    <option value="{{ $stf->id }}" {{ request('request_by') == $stf->id ? 'selected' : '' }}>
                                        {{ $stf->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="search">{{ __('inventory_movement.filter.keyword_search') }}</label>
                            <input type="text" name="search" id="search" class="filter-input"
                                value="{{ request('search') }}" placeholder="{{ __('inventory_movement.filter.placeholder_search') }}">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class='bx bx-search'></i>
                                <span>{{ __('inventory_movement.button.filter') }}</span>
                            </button>
                            <a href="{{ route($viewMode === 'daily' ? 'admin-report-inventory-movement-daily' : 'admin-report-inventory-movement-monthly') }}"
                                class="btn-filter-reset">
                                <i class='bx bx-reset'></i>
                                <span>{{ __('inventory_movement.button.reset') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <!-- 1. Total Stock In -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success">
                        <i class='bx bx-archive-in'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.total_stock_in') }}</div>
                        <div class="kpi-value text-success">+{{ number_format($summary['total_stock_in_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_stock_in_count'] ?? 0) }} {{ __('inventory_movement.kpi.inflow_records') }}</div>
                    </div>
                </div>

                <!-- 2. Total Stock Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger">
                        <i class='bx bx-archive-out'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.total_stock_out') }}</div>
                        <div class="kpi-value text-danger">-{{ number_format($summary['total_stock_out_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_stock_out_count'] ?? 0) }} {{ __('inventory_movement.kpi.outflow_records') }}</div>
                    </div>
                </div>

                <!-- 3. POS Sales Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary">
                        <i class='bx bx-cart'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.pos_sales_out') }}</div>
                        <div class="kpi-value text-primary">{{ number_format($summary['total_sales_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_sales_count'] ?? 0) }} {{ __('inventory_movement.kpi.sales_orders') }}</div>
                    </div>
                </div>

                <!-- 4. Internal / Wastage Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap warning">
                        <i class='bx bx-trash-alt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.internal_wastage') }}</div>
                        <div class="kpi-value" style="color: #d97706;">{{ number_format($summary['total_internal_out_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ __('inventory_movement.kpi.internal_sub') }}</div>
                    </div>
                </div>

                <!-- 5. Stock Transfers -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap purple">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.stock_transfers') }}</div>
                        <div class="kpi-value" style="color: #7c3aed;">{{ number_format($summary['total_transfer_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_transfer_count'] ?? 0) }} {{ __('inventory_movement.kpi.branch_transfers') }}</div>
                    </div>
                </div>

                <!-- 6. Net Movement Balance -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap teal">
                        <i class='bx bx-trending-up'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('inventory_movement.kpi.net_stock_movement') }}</div>
                        <div class="kpi-value {{ ($summary['net_movement'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($summary['net_movement'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['net_movement'] ?? 0) }}
                        </div>
                        <div class="kpi-sub">{{ number_format($summary['distinct_products_count'] ?? 0) }} {{ __('inventory_movement.kpi.active_products') }}</div>
                    </div>
                </div>
            </div>

            <!-- Visual Analytics Chart Card -->
            <div class="report-chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class='bx bx-line-chart'></i>
                        <span>{{ $viewMode === 'daily' ? __('inventory_movement.chart.daily_trends') : __('inventory_movement.chart.monthly_trends') }}</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="inventoryMovementChart"></canvas>
                </div>
            </div>

            <!-- Movement Breakdown Data Table -->
            <div class="report-table-card">
                <div class="table-header-bar">
                    <div class="table-title">
                        <i class='bx bx-table'></i>
                        <span>{{ $viewMode === 'daily' ? __('inventory_movement.table.daily_breakdown') : __('inventory_movement.table.monthly_breakdown') }}</span>
                        <span class="table-count-badge">{{ count($rows) }} {{ $viewMode === 'daily' ? __('inventory_movement.table.days') : __('inventory_movement.table.months') }}</span>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    @if ($viewMode === 'daily')
                        <table class="movement-data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">{{ __('inventory_movement.table.no') }}</th>
                                    <th>{{ __('inventory_movement.table.date') }}</th>
                                    <th>{{ __('inventory_movement.table.day_of_week') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.stock_in') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.stock_out') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.pos_sales') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.internal_waste') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.transfers') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.net_movement') }}</th>
                                    <th>{{ __('inventory_movement.table.top_moving_item') }}</th>
                                    <th class="text-center">{{ __('inventory_movement.table.transactions') }}</th>
                                    <th class="text-center" style="width: 100px;">{{ __('inventory_movement.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row->index }}</td>
                                        <td>
                                            <strong style="color: #1e293b;">{{ $row->date_formatted }}</strong>
                                            <small class="text-muted" style="display: block;">{{ $row->date }}</small>
                                        </td>
                                        <td>
                                            @php $dayKey = strtolower($row->day_name); @endphp
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 500;">
                                                {{ __('inventory_movement.days.' . $dayKey) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="qty-badge in">+{{ number_format($row->stock_in_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span class="qty-badge out">-{{ number_format($row->stock_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #2563eb; font-weight: 600;">{{ number_format($row->sales_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #d97706; font-weight: 600;">{{ number_format($row->internal_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #7c3aed; font-weight: 600;">{{ number_format($row->transfer_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span class="movement-badge {{ $row->net_movement >= 0 ? 'stock_in' : 'stock_out' }}">
                                                {{ $row->net_movement >= 0 ? '+' : '' }}{{ number_format($row->net_movement) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small style="font-weight: 500; color: #334155;">{{ $row->top_product }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" style="background: #eff6ff; color: #2563eb; font-weight: 600;">
                                                {{ $row->total_transactions }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" @click="openPeriodDetails('{{ $row->date }}')" class="btn-view-details">
                                                <i class='bx bx-search-alt-2'></i>
                                                <span>{{ __('inventory_movement.button.details') }}</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="empty-placeholder">
                                            <i class='bx bx-cube-alt'></i>
                                            <p>{{ __('inventory_movement.empty.daily_description') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($rows) > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">{{ __('inventory_movement.table.grand_total') }} ({{ count($rows) }} {{ __('inventory_movement.table.days') }})</td>
                                        <td class="text-right text-success">+{{ number_format(collect($rows)->sum('stock_in_qty')) }}</td>
                                        <td class="text-right text-danger">-{{ number_format(collect($rows)->sum('stock_out_qty')) }}</td>
                                        <td class="text-right text-primary">{{ number_format(collect($rows)->sum('sales_out_qty')) }}</td>
                                        <td class="text-right" style="color: #d97706;">{{ number_format(collect($rows)->sum('internal_out_qty')) }}</td>
                                        <td class="text-right" style="color: #7c3aed;">{{ number_format(collect($rows)->sum('transfer_qty')) }}</td>
                                        <td class="text-right {{ collect($rows)->sum('net_movement') >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ collect($rows)->sum('net_movement') >= 0 ? '+' : '' }}{{ number_format(collect($rows)->sum('net_movement')) }}
                                        </td>
                                        <td>---</td>
                                        <td class="text-center">{{ number_format(collect($rows)->sum('total_transactions')) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <!-- Monthly Table -->
                        <table class="movement-data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">{{ __('inventory_movement.table.no') }}</th>
                                    <th>{{ __('inventory_movement.table.month') }}</th>
                                    <th class="text-center">{{ __('inventory_movement.table.active_days') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.stock_in') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.stock_out') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.pos_sales') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.internal_waste') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.transfers') }}</th>
                                    <th class="text-right">{{ __('inventory_movement.table.net_movement') }}</th>
                                    <th>{{ __('inventory_movement.table.top_moving_product') }}</th>
                                    <th>{{ __('inventory_movement.table.primary_branch') }}</th>
                                    <th class="text-center">{{ __('inventory_movement.table.transactions') }}</th>
                                    <th class="text-center" style="width: 120px;">{{ __('inventory_movement.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row->index }}</td>
                                        <td>
                                            @php
                                                $cMonthNum = (int) \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->month;
                                                $cYearNum = \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->year;
                                            @endphp
                                            <strong style="color: #1e293b;">{{ __('inventory_movement.months.' . $cMonthNum) }} {{ $cYearNum }}</strong>
                                            <small class="text-muted" style="display: block;">{{ $row->month_key }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                                {{ $row->active_days }} {{ __('inventory_movement.table.days') }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="qty-badge in">+{{ number_format($row->stock_in_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span class="qty-badge out">-{{ number_format($row->stock_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #2563eb; font-weight: 600;">{{ number_format($row->sales_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #d97706; font-weight: 600;">{{ number_format($row->internal_out_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span style="color: #7c3aed; font-weight: 600;">{{ number_format($row->transfer_qty) }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span class="movement-badge {{ $row->net_movement >= 0 ? 'stock_in' : 'stock_out' }}">
                                                {{ $row->net_movement >= 0 ? '+' : '' }}{{ number_format($row->net_movement) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small style="font-weight: 500; color: #334155;">{{ $row->top_product }}</small>
                                        </td>
                                        <td>
                                            <small style="font-weight: 500; color: #64748b;">{{ $row->top_shop_name }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" style="background: #eff6ff; color: #2563eb; font-weight: 600;">
                                                {{ $row->total_transactions }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div style="display: flex; gap: 4px; justify-content: center;">
                                                <button type="button" @click="openPeriodDetails('{{ $row->month_key }}')" class="btn-view-details" title="{{ __('inventory_movement.button.view_month_ledger_tooltip') }}">
                                                    <i class='bx bx-search-alt-2'></i>
                                                </button>
                                                <a href="{{ route('admin-report-inventory-movement-daily', ['from_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->startOfMonth()->format('Y-m-d'), 'to_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->endOfMonth()->format('Y-m-d')]) }}"
                                                    class="btn-view-details" style="background: #f8fafc; color: #475569; border-color: #cbd5e1;" title="{{ __('inventory_movement.button.view_daily_breakdown_tooltip') }}">
                                                    <i class='bx bx-calendar'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="empty-placeholder">
                                            <i class='bx bx-cube-alt'></i>
                                            <p>{{ __('inventory_movement.empty.monthly_description') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($rows) > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">{{ __('inventory_movement.table.grand_total') }} ({{ count($rows) }} {{ __('inventory_movement.table.months') }})</td>
                                        <td class="text-right text-success">+{{ number_format(collect($rows)->sum('stock_in_qty')) }}</td>
                                        <td class="text-right text-danger">-{{ number_format(collect($rows)->sum('stock_out_qty')) }}</td>
                                        <td class="text-right text-primary">{{ number_format(collect($rows)->sum('sales_out_qty')) }}</td>
                                        <td class="text-right" style="color: #d97706;">{{ number_format(collect($rows)->sum('internal_out_qty')) }}</td>
                                        <td class="text-right" style="color: #7c3aed;">{{ number_format(collect($rows)->sum('transfer_qty')) }}</td>
                                        <td class="text-right {{ collect($rows)->sum('net_movement') >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ collect($rows)->sum('net_movement') >= 0 ? '+' : '' }}{{ number_format(collect($rows)->sum('net_movement')) }}
                                        </td>
                                        <td colspan="2">---</td>
                                        <td class="text-center">{{ number_format(collect($rows)->sum('total_transactions')) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Interactive Drilldown Ledger Modal -->
        <template x-if="showDetailModal">
            <div class="modal-overlay" @click.self="closePeriodDetails()">
                <div class="modal-box">
                    <div class="modal-header">
                        <div class="modal-title">
                            <i class='bx bx-transfer text-primary'></i>
                            <span x-text="periodData ? periodData.period_label + ' - {{ __('inventory_movement.modal.movement_ledger') }}' : '{{ __('inventory_movement.modal.loading_ledger') }}'"></span>
                        </div>
                        <button type="button" @click="closePeriodDetails()" class="modal-close-btn">&times;</button>
                    </div>

                    <div class="modal-body">
                        <template x-if="modalLoading">
                            <div class="empty-placeholder">
                                <i class='bx bx-loader-alt bx-spin'></i>
                                <p>{{ __('inventory_movement.modal.fetching_movements') }}</p>
                            </div>
                        </template>

                        <template x-if="!modalLoading && periodData">
                            <div>
                                <!-- Period Mini KPI Summary -->
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.total_records') }}</span>
                                        <strong x-text="periodData.count"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.total_inflow') }}</span>
                                        <strong class="text-success" x-text="'+' + periodData.total_in"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.total_outflow') }}</span>
                                        <strong class="text-danger" x-text="'-' + periodData.total_out"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.pos_sales') }}</span>
                                        <strong class="text-primary" x-text="periodData.total_sales"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.transfers') }}</span>
                                        <strong style="color: #7c3aed;" x-text="periodData.total_transfer"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('inventory_movement.modal.net_delta') }}</span>
                                        <strong :class="periodData.net_movement >= 0 ? 'text-success' : 'text-danger'"
                                            x-text="(periodData.net_movement >= 0 ? '+' : '') + periodData.net_movement"></strong>
                                    </div>
                                </div>

                                <!-- In-modal Quick Search -->
                                <div style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
                                    <input type="text" x-model="modalSearch" placeholder="{{ __('inventory_movement.modal.search_placeholder') }}"
                                        class="filter-input" style="max-width: 250px; height: 34px; font-size: 12px;">
                                </div>

                                <!-- Detailed Transaction Ledger Table -->
                                <div style="overflow-x: auto;">
                                    <table class="movement-data-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('inventory_movement.modal.table.time') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.product') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.branch') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.type') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.origin_destination') }}</th>
                                                <th class="text-right">{{ __('inventory_movement.modal.table.qty_change') }}</th>
                                                <th class="text-right">{{ __('inventory_movement.modal.table.stock_after') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.processed_by') }}</th>
                                                <th>{{ __('inventory_movement.modal.table.remark_ref') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="item in filteredModalMovements" :key="item.id">
                                                <tr>
                                                    <td>
                                                        <small style="font-weight: 600; color: #475569;" x-text="item.created_at_formatted"></small>
                                                    </td>
                                                    <td>
                                                        <div style="font-weight: 600; color: #1e293b;" x-text="item.product_name"></div>
                                                        <small class="text-muted" x-text="item.category_name + ' • ' + item.uom_name"></small>
                                                    </td>
                                                    <td>
                                                        <span style="font-weight: 500;" x-text="item.shop_name"></span>
                                                    </td>
                                                    <td>
                                                        <span :class="'movement-badge ' + item.movement_type" x-text="item.movement_label"></span>
                                                    </td>
                                                    <td>
                                                        <small style="color: #334155; font-weight: 500;" x-text="item.status === 'stock_in' ? '{{ __('inventory_movement.modal.from_prefix') }}: ' + item.from_title : '{{ __('inventory_movement.modal.to_prefix') }}: ' + item.to_title"></small>
                                                    </td>
                                                    <td class="text-right">
                                                        <span :class="'qty-badge ' + (item.status === 'stock_in' ? 'in' : (item.status === 'stock_transfer' ? 'transfer' : 'out'))"
                                                            x-text="(item.status === 'stock_in' ? '+' : (item.status === 'stock_transfer' ? '⇄ ' : '-')) + item.qty"></span>
                                                    </td>
                                                    <td class="text-right font-weight-bold" x-text="item.current_stock"></td>
                                                    <td>
                                                        <small style="color: #475569;" x-text="item.request_by_name"></small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted" x-text="item.remark"></small>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="filteredModalMovements.length === 0">
                                                <tr>
                                                    <td colspan="9" class="empty-placeholder" style="padding: 20px;">
                                                        <p>{{ __('inventory_movement.modal.no_transactions_match') }}</p>
                                                    </td>
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
            Alpine.data('xInventoryReport', () => ({
                viewMode: '{{ $viewMode }}',
                showDetailModal: false,
                modalLoading: false,
                periodData: null,
                modalSearch: '',
                exportLoading: false,

                get filteredModalMovements() {
                    if (!this.periodData || !this.periodData.movements) return [];
                    if (!this.modalSearch) return this.periodData.movements;

                    const q = this.modalSearch.toLowerCase();
                    return this.periodData.movements.filter(m =>
                        (m.product_name && m.product_name.toLowerCase().includes(q)) ||
                        (m.shop_name && m.shop_name.toLowerCase().includes(q)) ||
                        (m.from_title && m.from_title.toLowerCase().includes(q)) ||
                        (m.to_title && m.to_title.toLowerCase().includes(q)) ||
                        (m.movement_label && m.movement_label.toLowerCase().includes(q)) ||
                        (m.remark && m.remark.toLowerCase().includes(q)) ||
                        (m.request_by_name && m.request_by_name.toLowerCase().includes(q))
                    );
                },

                async openPeriodDetails(period) {
                    this.showDetailModal = true;
                    this.modalLoading = true;
                    this.periodData = null;
                    this.modalSearch = '';

                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        const response = await Axios.get(`{{ url('admin/report/inventory-movement/details') }}/${period}?` + currentParams.toString());
                        this.periodData = response.data;
                    } catch (err) {
                        console.error('Failed to load period movements:', err);
                        alert('{{ __('inventory_movement.modal.error_load') }}');
                        this.showDetailModal = false;
                    } finally {
                        this.modalLoading = false;
                    }
                },

                closePeriodDetails() {
                    this.showDetailModal = false;
                    this.periodData = null;
                    this.modalSearch = '';
                },

                async exportExcel() {
                    this.exportLoading = true;
                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        currentParams.set('view_mode', this.viewMode);

                        const response = await Axios.get(`{{ route('admin-report-inventory-movement-report') }}?` + currentParams.toString());
                        const reportData = response.data;

                        const workbook = new ExcelJS.Workbook();
                        const sheetName = this.viewMode === 'monthly' ? '{{ __('inventory_movement.excel.sheet_monthly') }}' : '{{ __('inventory_movement.excel.sheet_daily') }}';
                        const worksheet = workbook.addWorksheet(sheetName);

                        if (this.viewMode === 'daily') {
                            worksheet.columns = [
                                { header: '{{ __('inventory_movement.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('inventory_movement.excel.date') }}', key: 'date', width: 16 },
                                { header: '{{ __('inventory_movement.excel.day') }}', key: 'day_name', width: 12 },
                                { header: '{{ __('inventory_movement.excel.stock_in') }}', key: 'stock_in_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.stock_out') }}', key: 'stock_out_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.pos_sales') }}', key: 'sales_out_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.internal_waste') }}', key: 'internal_out_qty', width: 22 },
                                { header: '{{ __('inventory_movement.excel.transfers') }}', key: 'transfer_qty', width: 18 },
                                { header: '{{ __('inventory_movement.excel.net_movement') }}', key: 'net_movement', width: 16 },
                                { header: '{{ __('inventory_movement.excel.top_moving_product') }}', key: 'top_product', width: 26 },
                                { header: '{{ __('inventory_movement.excel.total_transactions') }}', key: 'total_transactions', width: 18 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    date: r.date,
                                    day_name: r.day_name,
                                    stock_in_qty: Number(r.stock_in_qty || 0),
                                    stock_out_qty: Number(r.stock_out_qty || 0),
                                    sales_out_qty: Number(r.sales_out_qty || 0),
                                    internal_out_qty: Number(r.internal_out_qty || 0),
                                    transfer_qty: Number(r.transfer_qty || 0),
                                    net_movement: Number(r.net_movement || 0),
                                    top_product: r.top_product,
                                    total_transactions: r.total_transactions,
                                });
                            });
                        } else {
                            worksheet.columns = [
                                { header: '{{ __('inventory_movement.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('inventory_movement.excel.month') }}', key: 'month_name', width: 18 },
                                { header: '{{ __('inventory_movement.excel.active_days') }}', key: 'active_days', width: 14 },
                                { header: '{{ __('inventory_movement.excel.stock_in') }}', key: 'stock_in_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.stock_out') }}', key: 'stock_out_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.pos_sales') }}', key: 'sales_out_qty', width: 16 },
                                { header: '{{ __('inventory_movement.excel.internal_waste') }}', key: 'internal_out_qty', width: 22 },
                                { header: '{{ __('inventory_movement.excel.transfers') }}', key: 'transfer_qty', width: 18 },
                                { header: '{{ __('inventory_movement.excel.net_movement') }}', key: 'net_movement', width: 16 },
                                { header: '{{ __('inventory_movement.excel.top_moving_product') }}', key: 'top_product', width: 26 },
                                { header: '{{ __('inventory_movement.excel.primary_branch') }}', key: 'top_shop_name', width: 20 },
                                { header: '{{ __('inventory_movement.excel.total_transactions') }}', key: 'total_transactions', width: 18 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    month_name: r.month_name,
                                    active_days: r.active_days,
                                    stock_in_qty: Number(r.stock_in_qty || 0),
                                    stock_out_qty: Number(r.stock_out_qty || 0),
                                    sales_out_qty: Number(r.sales_out_qty || 0),
                                    internal_out_qty: Number(r.internal_out_qty || 0),
                                    transfer_qty: Number(r.transfer_qty || 0),
                                    net_movement: Number(r.net_movement || 0),
                                    top_product: r.top_product,
                                    top_shop_name: r.top_shop_name,
                                    total_transactions: r.total_transactions,
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
                        const filename = (this.viewMode === 'monthly' ? '{{ __('inventory_movement.excel.file_monthly_prefix') }}' : '{{ __('inventory_movement.excel.file_daily_prefix') }}') + moment().format('YYYY_MM_DD_HHmmss');
                        saveAs(blob, filename);
                    } catch (err) {
                        console.error('Export failed:', err);
                        alert('{{ __('inventory_movement.excel.export_failed') }}');
                    } finally {
                        this.exportLoading = false;
                    }
                }
            }));

            // Initialize Chart.js
            initMovementChart();
        });

        function initMovementChart() {
            const chartCanvas = document.getElementById('inventoryMovementChart');
            if (!chartCanvas) return;

            const rowsData = @json($rows);
            const viewMode = '{{ $viewMode }}';

            let labels = [];
            let inData = [];
            let outData = [];
            let salesData = [];
            let netData = [];

            // Chronological order for chart (reverse from table desc)
            const chronological = [...rowsData].reverse();

            chronological.forEach(r => {
                labels.push(viewMode === 'daily' ? r.date_formatted : r.month_name);
                inData.push(r.stock_in_qty || 0);
                outData.push(r.stock_out_qty || 0);
                salesData.push(r.sales_out_qty || 0);
                netData.push(r.net_movement || 0);
            });

            const ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '{{ __('inventory_movement.chart.stock_in') }}',
                            data: inData,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('inventory_movement.chart.stock_out') }}',
                            data: outData,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('inventory_movement.chart.pos_sales') }}',
                            data: salesData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('inventory_movement.chart.net_movement') }}',
                            data: netData,
                            type: 'line',
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#8b5cf6',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 12, weight: '600' },
                                boxWidth: 14,
                                padding: 16,
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            padding: 12,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
    </script>
@stop
