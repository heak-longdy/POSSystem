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
            padding: 20px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
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
            height: 280px;
            width: 100%;
        }

        /* Data Table Card */
        .report-table-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-header-bar {
            padding: 16px 20px;
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

    <div class="content-wrapper inventory-report-wrapper" id="inventoryReportApp" x-data="xInventoryReport">
        <!-- Main Header -->
        <div class="header box-shadow-bottom">
            @include('admin::shared.header', ['header_name' => 'Inventory Movement Report'])
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
                                {!! \App\Support\Language::translatedValue(['en' => 'Daily Movement Report', 'km' => 'របាយការណ៍បម្រែបម្រួលប្រចាំថ្ងៃ']) !!}
                            </a>
                            <a href="{{ route('admin-report-inventory-movement-monthly', $currentParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {!! \App\Support\Language::translatedValue(['en' => 'Monthly Movement Report', 'km' => 'របាយការណ៍បម្រែបម្រួលប្រចាំខែ']) !!}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button type="button" @click="exportExcel()" class="btn-excel-export" :disabled="exportLoading">
                        <i class='bx bx-download'></i>
                        <span x-text="exportLoading ? 'Exporting...' : 'Export Excel'">Export Excel</span>
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
                <form id="inventoryFilterForm" method="GET" action="{{ url()->current() }}">
                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">Quick Presets:</span>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'today'])) }}"
                                    class="preset-btn {{ request('preset') === 'today' ? 'active' : '' }}">Today</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'yesterday'])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">Yesterday</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '7days'])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">Last 7 Days</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '30days'])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">Last 30 Days</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'this_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'this_month' || (!request('preset') && !request('from_date')) ? 'active' : '' }}">This Month</a>
                                <a href="{{ route('admin-report-inventory-movement-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'last_month'])) }}"
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
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="filter-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="product_id">Product</label>
                            <select name="product_id" id="product_id" class="filter-select">
                                <option value="">All Products</option>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="movement_type">Movement Type</label>
                            <select name="movement_type" id="movement_type" class="filter-select">
                                @foreach ($movementTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('movement_type', 'all') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="request_by">Staff / Requester</label>
                            <select name="request_by" id="request_by" class="filter-select">
                                <option value="">All Staff</option>
                                @foreach ($staffUsers as $stf)
                                    <option value="{{ $stf->id }}" {{ request('request_by') == $stf->id ? 'selected' : '' }}>
                                        {{ $stf->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="search">Keyword Search</label>
                            <input type="text" name="search" id="search" class="filter-input"
                                value="{{ request('search') }}" placeholder="Product, shop, remark...">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class='bx bx-search'></i>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route($viewMode === 'daily' ? 'admin-report-inventory-movement-daily' : 'admin-report-inventory-movement-monthly') }}"
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
                <!-- 1. Total Stock In -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success">
                        <i class='bx bx-archive-in'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Stock In</div>
                        <div class="kpi-value text-success">+{{ number_format($summary['total_stock_in_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_stock_in_count'] ?? 0) }} inflow records</div>
                    </div>
                </div>

                <!-- 2. Total Stock Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger">
                        <i class='bx bx-archive-out'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Total Stock Out</div>
                        <div class="kpi-value text-danger">-{{ number_format($summary['total_stock_out_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_stock_out_count'] ?? 0) }} outflow records</div>
                    </div>
                </div>

                <!-- 3. POS Sales Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary">
                        <i class='bx bx-cart'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">POS Sales Out</div>
                        <div class="kpi-value text-primary">{{ number_format($summary['total_sales_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_sales_count'] ?? 0) }} sales orders</div>
                    </div>
                </div>

                <!-- 4. Internal / Wastage Out -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap warning">
                        <i class='bx bx-trash-alt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Internal / Wastage</div>
                        <div class="kpi-value" style="color: #d97706;">{{ number_format($summary['total_internal_out_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">Loss, damage & usage</div>
                    </div>
                </div>

                <!-- 5. Stock Transfers -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap purple">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Stock Transfers</div>
                        <div class="kpi-value" style="color: #7c3aed;">{{ number_format($summary['total_transfer_qty'] ?? 0) }}</div>
                        <div class="kpi-sub">{{ number_format($summary['total_transfer_count'] ?? 0) }} branch transfers</div>
                    </div>
                </div>

                <!-- 6. Net Movement Balance -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap teal">
                        <i class='bx bx-trending-up'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">Net Stock Movement</div>
                        <div class="kpi-value {{ ($summary['net_movement'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($summary['net_movement'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['net_movement'] ?? 0) }}
                        </div>
                        <div class="kpi-sub">{{ number_format($summary['distinct_products_count'] ?? 0) }} active products</div>
                    </div>
                </div>
            </div>

            <!-- Visual Analytics Chart Card -->
            <div class="report-chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class='bx bx-line-chart'></i>
                        <span>{{ $viewMode === 'daily' ? 'Daily Inventory Flow Trends' : 'Monthly Inventory Flow Trends' }}</span>
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
                        <span>{{ $viewMode === 'daily' ? 'Daily Movement Ledger Breakdown' : 'Monthly Movement Summary Breakdown' }}</span>
                        <span class="table-count-badge">{{ count($rows) }} {{ $viewMode === 'daily' ? 'Days' : 'Months' }}</span>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    @if ($viewMode === 'daily')
                        <table class="movement-data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Nº</th>
                                    <th>Date</th>
                                    <th>Day of Week</th>
                                    <th class="text-right">Stock In (+)</th>
                                    <th class="text-right">Stock Out (-)</th>
                                    <th class="text-right">POS Sales</th>
                                    <th class="text-right">Internal / Waste</th>
                                    <th class="text-right">Transfers</th>
                                    <th class="text-right">Net Movement</th>
                                    <th>Top Moving Item</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
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
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 500;">
                                                {{ $row->day_name }}
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
                                                <span>Details</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="empty-placeholder">
                                            <i class='bx bx-cube-alt'></i>
                                            <p>No inventory movement records found for the selected date range and filters.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($rows) > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Grand Total ({{ count($rows) }} Days)</td>
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
                                    <th style="width: 50px;">Nº</th>
                                    <th>Month</th>
                                    <th class="text-center">Active Days</th>
                                    <th class="text-right">Stock In (+)</th>
                                    <th class="text-right">Stock Out (-)</th>
                                    <th class="text-right">POS Sales</th>
                                    <th class="text-right">Internal / Waste</th>
                                    <th class="text-right">Transfers</th>
                                    <th class="text-right">Net Movement</th>
                                    <th>Top Moving Product</th>
                                    <th>Primary Branch</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row->index }}</td>
                                        <td>
                                            <strong style="color: #1e293b;">{{ $row->month_name }}</strong>
                                            <small class="text-muted" style="display: block;">{{ $row->month_key }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                                {{ $row->active_days }} days
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
                                                <button type="button" @click="openPeriodDetails('{{ $row->month_key }}')" class="btn-view-details" title="View Month Ledger">
                                                    <i class='bx bx-search-alt-2'></i>
                                                </button>
                                                <a href="{{ route('admin-report-inventory-movement-daily', ['from_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->startOfMonth()->format('Y-m-d'), 'to_date' => Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->endOfMonth()->format('Y-m-d')]) }}"
                                                    class="btn-view-details" style="background: #f8fafc; color: #475569; border-color: #cbd5e1;" title="View Daily Breakdown">
                                                    <i class='bx bx-calendar'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="empty-placeholder">
                                            <i class='bx bx-cube-alt'></i>
                                            <p>No inventory movement records found for the selected year and months.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($rows) > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Grand Total ({{ count($rows) }} Months)</td>
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
                            <span x-text="periodData ? periodData.period_label + ' - Movement Ledger' : 'Loading Ledger...'"></span>
                        </div>
                        <button type="button" @click="closePeriodDetails()" class="modal-close-btn">&times;</button>
                    </div>

                    <div class="modal-body">
                        <template x-if="modalLoading">
                            <div class="empty-placeholder">
                                <i class='bx bx-loader-alt bx-spin'></i>
                                <p>Fetching detailed itemized movements...</p>
                            </div>
                        </template>

                        <template x-if="!modalLoading && periodData">
                            <div>
                                <!-- Period Mini KPI Summary -->
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>Total Records</span>
                                        <strong x-text="periodData.count"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Total Inflow</span>
                                        <strong class="text-success" x-text="'+' + periodData.total_in"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Total Outflow</span>
                                        <strong class="text-danger" x-text="'-' + periodData.total_out"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>POS Sales</span>
                                        <strong class="text-primary" x-text="periodData.total_sales"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Transfers</span>
                                        <strong style="color: #7c3aed;" x-text="periodData.total_transfer"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>Net Delta</span>
                                        <strong :class="periodData.net_movement >= 0 ? 'text-success' : 'text-danger'"
                                            x-text="(periodData.net_movement >= 0 ? '+' : '') + periodData.net_movement"></strong>
                                    </div>
                                </div>

                                <!-- In-modal Quick Search -->
                                <div style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
                                    <input type="text" x-model="modalSearch" placeholder="Search within ledger..."
                                        class="filter-input" style="max-width: 250px; height: 34px; font-size: 12px;">
                                </div>

                                <!-- Detailed Transaction Ledger Table -->
                                <div style="overflow-x: auto;">
                                    <table class="movement-data-table">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Product</th>
                                                <th>Branch / Shop</th>
                                                <th>Type</th>
                                                <th>Origin / Destination</th>
                                                <th class="text-right">Qty Change</th>
                                                <th class="text-right">Stock After</th>
                                                <th>Processed By</th>
                                                <th>Remark / Ref</th>
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
                                                        <small style="color: #334155; font-weight: 500;" x-text="item.status === 'stock_in' ? 'From: ' + item.from_title : 'To: ' + item.to_title"></small>
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
                                                        <p>No transactions matching search.</p>
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
                        alert('Could not load period movements. Please try again.');
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
                        const sheetName = this.viewMode === 'monthly' ? 'Monthly Inventory Movement' : 'Daily Inventory Movement';
                        const worksheet = workbook.addWorksheet(sheetName);

                        if (this.viewMode === 'daily') {
                            worksheet.columns = [
                                { header: 'Nº', key: 'index', width: 8 },
                                { header: 'Date', key: 'date', width: 16 },
                                { header: 'Day', key: 'day_name', width: 12 },
                                { header: 'Stock In (+)', key: 'stock_in_qty', width: 16 },
                                { header: 'Stock Out (-)', key: 'stock_out_qty', width: 16 },
                                { header: 'POS Sales Out', key: 'sales_out_qty', width: 16 },
                                { header: 'Internal / Waste Out', key: 'internal_out_qty', width: 22 },
                                { header: 'Stock Transfers', key: 'transfer_qty', width: 18 },
                                { header: 'Net Movement', key: 'net_movement', width: 16 },
                                { header: 'Top Moving Product', key: 'top_product', width: 26 },
                                { header: 'Total Transactions', key: 'total_transactions', width: 18 },
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
                                { header: 'Nº', key: 'index', width: 8 },
                                { header: 'Month', key: 'month_name', width: 18 },
                                { header: 'Active Days', key: 'active_days', width: 14 },
                                { header: 'Stock In (+)', key: 'stock_in_qty', width: 16 },
                                { header: 'Stock Out (-)', key: 'stock_out_qty', width: 16 },
                                { header: 'POS Sales Out', key: 'sales_out_qty', width: 16 },
                                { header: 'Internal / Waste Out', key: 'internal_out_qty', width: 22 },
                                { header: 'Stock Transfers', key: 'transfer_qty', width: 18 },
                                { header: 'Net Movement', key: 'net_movement', width: 16 },
                                { header: 'Top Moving Product', key: 'top_product', width: 26 },
                                { header: 'Primary Branch', key: 'top_shop_name', width: 20 },
                                { header: 'Total Transactions', key: 'total_transactions', width: 18 },
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
                        const filename = (this.viewMode === 'monthly' ? 'Monthly_Inventory_Movement_Report_' : 'Daily_Inventory_Movement_Report_') + moment().format('YYYY_MM_DD_HHmmss');
                        saveAs(blob, filename);
                    } catch (err) {
                        console.error('Export failed:', err);
                        alert('Excel export failed. Please try again.');
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
                            label: 'Stock In (+)',
                            data: inData,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Stock Out (-)',
                            data: outData,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'POS Sales Out',
                            data: salesData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Net Movement',
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
