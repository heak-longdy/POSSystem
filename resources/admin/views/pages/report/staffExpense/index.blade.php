@extends('admin::shared.layout')
@section('layout')
    <style>
        .staff-expense-report-wrapper {
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

        .kpi-icon-wrap.primary { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .kpi-icon-wrap.indigo  { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
        .kpi-icon-wrap.teal    { background: rgba(20, 184, 166, 0.12); color: #0d9488; }
        .kpi-icon-wrap.success { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .kpi-icon-wrap.danger  { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
        .kpi-icon-wrap.purple  { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
        .kpi-icon-wrap.amber   { background: rgba(245, 158, 11, 0.12); color: #d97706; }

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

        .expense-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .expense-data-table th {
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

        .expense-data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .expense-data-table tr:hover td {
            background-color: #f8fafc;
        }

        .expense-data-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            border-top: 2px solid #cbd5e1;
            padding: 14px 16px;
        }

        /* Type Badges */
        .badge-salary    { background: #e0f2fe; color: #0284c7; }
        .badge-bonus     { background: #dcfce7; color: #15803d; }
        .badge-deduction { background: #fee2e2; color: #b91c1c; }
        .badge-other     { background: #f1f5f9; color: #475569; }

        .currency-pos { color: #059669; font-weight: 600; }
        .currency-neg { color: #dc2626; font-weight: 600; }
        .currency-net { color: #2563eb; font-weight: 700; }

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
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
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

    @include('admin::shared.header', ['header_name' => __('staff_expense_report.title')])
    <div class="content-wrapper staff-expense-report-wrapper" id="staffExpenseReportApp" x-data="xStaffExpenseReport">
        <!-- Tab Bar Header -->
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            @php
                                $currentParams = request()->query();
                            @endphp
                            <a href="{{ route('admin-report-staff-expense-daily', $currentParams) }}"
                                class="{{ $viewMode === 'daily' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-event'></i>
                                {{ __('staff_expense_report.tab.daily') }}
                            </a>
                            <a href="{{ route('admin-report-staff-expense-monthly', $currentParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {{ __('staff_expense_report.tab.monthly') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button type="button" @click="exportExcel()" class="btn-excel-export" :disabled="exportLoading">
                        <i class='bx bx-download'></i>
                        <span x-text="exportLoading ? '{{ __('staff_expense_report.excel.exporting') }}' : '{{ __('staff_expense_report.button.export_excel') }}'">{{ __('staff_expense_report.button.export_excel') }}</span>
                    </button>
                    <button type="button" s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>{{ __('staff_expense_report.button.reload') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body" style="padding: 14px 20px 24px 20px;">
            <!-- Filter Panel -->
            <div class="report-filter-panel">
                <form id="expenseFilterForm" method="GET" action="{{ url()->current() }}">
                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">{{ __('staff_expense_report.presets.title') }}</span>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'today'])) }}"
                                    class="preset-btn {{ request('preset') === 'today' ? 'active' : '' }}">{{ __('staff_expense_report.presets.today') }}</a>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'yesterday'])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">{{ __('staff_expense_report.presets.yesterday') }}</a>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '7days'])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">{{ __('staff_expense_report.presets.last_7_days') }}</a>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => '30days'])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">{{ __('staff_expense_report.presets.last_30_days') }}</a>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'this_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'this_month' || (!request('preset') && !request('from_date')) ? 'active' : '' }}">{{ __('staff_expense_report.presets.this_month') }}</a>
                                <a href="{{ route('admin-report-staff-expense-daily', array_merge(request()->except(['from_date', 'to_date', 'preset']), ['preset' => 'last_month'])) }}"
                                    class="preset-btn {{ request('preset') === 'last_month' ? 'active' : '' }}">{{ __('staff_expense_report.presets.last_month') }}</a>
                            </div>
                        </div>
                    @endif

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="from_date">{{ __('staff_expense_report.filter.from_date') }}</label>
                                <input type="text" name="from_date" id="from_date" class="filter-input datepicker-input"
                                    value="{{ $from_date }}" autocomplete="off" placeholder="{{ __('staff_expense_report.filter.placeholder_date') }}">
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_date">{{ __('staff_expense_report.filter.to_date') }}</label>
                                <input type="text" name="to_date" id="to_date" class="filter-input datepicker-input"
                                    value="{{ $to_date }}" autocomplete="off" placeholder="{{ __('staff_expense_report.filter.placeholder_date') }}">
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label for="year">{{ __('staff_expense_report.filter.year') }}</label>
                                <select name="year" id="year" class="filter-select">
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="from_month">{{ __('staff_expense_report.filter.from_month') }}</label>
                                <select name="from_month" id="from_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $from_month === $m ? 'selected' : '' }}>
                                            {{ __('staff_expense_report.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_month">{{ __('staff_expense_report.filter.to_month') }}</label>
                                <select name="to_month" id="to_month" class="filter-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $to_month === $m ? 'selected' : '' }}>
                                            {{ __('staff_expense_report.months.' . $m) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label for="staff_id">{{ __('staff_expense_report.filter.staff') }}</label>
                            <select name="staff_id" id="staff_id" class="filter-select">
                                <option value="">{{ __('staff_expense_report.filter.all_staff') }}</option>
                                @foreach ($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ (string) ($filters['staff_id'] ?? '') === (string) $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} {{ $staff->phone_number ? '(' . $staff->phone_number . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="shop_id">{{ __('staff_expense_report.filter.shop') }}</label>
                            <select name="shop_id" id="shop_id" class="filter-select">
                                <option value="">{{ __('staff_expense_report.filter.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ (string) ($filters['shop_id'] ?? '') === (string) $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="type">{{ __('staff_expense_report.filter.expense_type') }}</label>
                            <select name="type" id="type" class="filter-select">
                                <option value="all">{{ __('staff_expense_report.filter.all_types') }}</option>
                                @foreach ($expenseTypes as $typeOption)
                                    <option value="{{ $typeOption->value }}" {{ ($filters['type'] ?? '') === $typeOption->value ? 'selected' : '' }}>
                                        {{ __('staff_expense_report.types.' . strtolower($typeOption->value)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="search">{{ __('staff_expense_report.filter.keyword_search') }}</label>
                            <input type="text" name="search" id="search" class="filter-input"
                                value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('staff_expense_report.filter.placeholder_search') }}">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class='bx bx-filter-alt'></i>
                                <span>{{ __('staff_expense_report.button.filter') }}</span>
                            </button>
                            <a href="{{ $viewMode === 'daily' ? route('admin-report-staff-expense-daily') : route('admin-report-staff-expense-monthly') }}"
                                class="btn-filter-reset" title="{{ __('staff_expense_report.button.reset_tooltip') }}">
                                <i class='bx bx-reset'></i>
                                <span>{{ __('staff_expense_report.button.reset') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="kpi-grid">
                <!-- Net Total Expense -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary">
                        <i class='bx bx-wallet'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.net_total_expense') }}</div>
                        <div class="kpi-value text-primary">${{ number_format($summary['net_total'], 2) }}</div>
                        <div class="kpi-sub">{{ __('staff_expense_report.kpi.net_sub') }}</div>
                    </div>
                </div>

                <!-- Gross Additions -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap indigo">
                        <i class='bx bx-layer-plus'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.gross_additions') }}</div>
                        <div class="kpi-value">${{ number_format($summary['gross_total'], 2) }}</div>
                        <div class="kpi-sub">{{ __('staff_expense_report.kpi.gross_sub') }}</div>
                    </div>
                </div>

                <!-- Total Salary -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap teal">
                        <i class='bx bx-user-check'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.total_salary') }}</div>
                        <div class="kpi-value">${{ number_format($summary['salary_total'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['type_counts']['Salary'] ?? 0 }} {{ __('staff_expense_report.kpi.salary_records') }}</div>
                    </div>
                </div>

                <!-- Total Bonus -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success">
                        <i class='bx bx-gift'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.total_bonus') }}</div>
                        <div class="kpi-value text-success">${{ number_format($summary['bonus_total'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['type_counts']['Bonus'] ?? 0 }} {{ __('staff_expense_report.kpi.bonus_records') }}</div>
                    </div>
                </div>

                <!-- Total Deductions -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger">
                        <i class='bx bx-minus-circle'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.total_deductions') }}</div>
                        <div class="kpi-value text-danger">-${{ number_format($summary['deduction_total'], 2) }}</div>
                        <div class="kpi-sub">{{ $summary['type_counts']['Deduction'] ?? 0 }} {{ __('staff_expense_report.kpi.deduction_records') }}</div>
                    </div>
                </div>

                <!-- Total Records & Staff -->
                <div class="kpi-card">
                    <div class="kpi-icon-wrap purple">
                        <i class='bx bx-group'></i>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('staff_expense_report.kpi.transactions_staff') }}</div>
                        <div class="kpi-value">{{ number_format($summary['total_transactions']) }}</div>
                        <div class="kpi-sub">{{ $summary['distinct_staff_count'] }} {{ __('staff_expense_report.kpi.active_staff_members') }}</div>
                    </div>
                </div>
            </div>

            <!-- Visual Analytics Chart Card -->
            <div class="report-chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class='bx bx-line-chart' style="color: #2563eb; font-size: 18px;"></i>
                        <span>{{ $viewMode === 'daily' ? __('staff_expense_report.chart.daily_trend') : __('staff_expense_report.chart.monthly_trend') }}</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="staffExpenseChart"></canvas>
                </div>
            </div>

            <!-- Breakdown Data Table Card -->
            <div class="report-table-card">
                <div class="table-header-bar">
                    <div class="table-title">
                        <i class='bx bx-table' style="color: #64748b;"></i>
                        <span>{{ $viewMode === 'daily' ? __('staff_expense_report.table.daily_breakdown') : __('staff_expense_report.table.monthly_breakdown') }}</span>
                        <span class="table-count-badge">{{ $rows->count() }} {{ $viewMode === 'daily' ? __('staff_expense_report.table.days') : __('staff_expense_report.table.months') }}</span>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="expense-data-table">
                        <thead>
                            @if ($viewMode === 'daily')
                                <tr>
                                    <th style="width: 50px; text-align: center;">{{ __('staff_expense_report.table.no') }}</th>
                                    <th>{{ __('staff_expense_report.table.date') }}</th>
                                    <th>{{ __('staff_expense_report.table.day') }}</th>
                                    <th style="text-align: center;">{{ __('staff_expense_report.table.records') }}</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.salary') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.bonus') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.deductions') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.other') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.gross_total') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.net_expense') }} ($)</th>
                                    <th>{{ __('staff_expense_report.table.top_staff_branch') }}</th>
                                    <th style="text-align: center; width: 110px;">{{ __('staff_expense_report.table.action') }}</th>
                                </tr>
                            @else
                                <tr>
                                    <th style="width: 50px; text-align: center;">{{ __('staff_expense_report.table.no') }}</th>
                                    <th>{{ __('staff_expense_report.table.month') }}</th>
                                    <th style="text-align: center;">{{ __('staff_expense_report.table.active_days') }}</th>
                                    <th style="text-align: center;">{{ __('staff_expense_report.table.records') }}</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.salary') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.bonus') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.deductions') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.other') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.gross_total') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.net_expense') }} ($)</th>
                                    <th style="text-align: right;">{{ __('staff_expense_report.table.avg_daily') }} ($)</th>
                                    <th>{{ __('staff_expense_report.table.top_staff') }}</th>
                                    <th style="text-align: center; width: 110px;">{{ __('staff_expense_report.table.action') }}</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @if ($viewMode === 'daily')
                                    <tr style="{{ $row->is_today ? 'background-color: #f0fdf4;' : ($row->is_weekend ? 'background-color: #f8fafc;' : '') }}">
                                        <td style="text-align: center; color: #94a3b8;">{{ $row->index }}</td>
                                        <td>
                                            <strong style="color: #0f172a;">{{ $row->date_formatted }}</strong>
                                            @if ($row->is_today)
                                                <span class="badge bg-success" style="font-size: 10px; margin-left: 4px;">{{ __('staff_expense_report.badge.today') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="color: {{ $row->is_weekend ? '#dc2626' : '#64748b' }}; font-weight: 500;">
                                                {{ $row->day_name }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="table-count-badge">{{ $row->transactions_count }}</span>
                                        </td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->salary_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->bonus_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-neg">-${{ number_format($row->deduction_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->other_total, 2) }}</td>
                                        <td style="text-align: right; font-weight: 600; color: #475569;">${{ number_format($row->gross_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-net">${{ number_format($row->net_total, 2) }}</td>
                                        <td>
                                            <div style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ $row->top_staff }}</div>
                                            <small style="color: #64748b;">{{ $row->top_shop }}</small>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-view-details" @click="openPeriodDetails('{{ $row->date }}')">
                                                <i class='bx bx-show'></i>
                                                <span>{{ __('staff_expense_report.button.details') }}</span>
                                            </button>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="text-align: center; color: #94a3b8;">{{ $row->index }}</td>
                                        <td>
                                            @php
                                                $cMonthNum = (int) \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->month;
                                                $cYearNum = \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->year;
                                            @endphp
                                            <strong style="color: #0f172a;">{{ __('staff_expense_report.months.' . $cMonthNum) }} {{ $cYearNum }}</strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <span style="font-weight: 600; color: #475569;">{{ $row->active_days }} {{ __('staff_expense_report.table.days') }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="table-count-badge">{{ $row->transactions_count }}</span>
                                        </td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->salary_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->bonus_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-neg">-${{ number_format($row->deduction_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($row->other_total, 2) }}</td>
                                        <td style="text-align: right; font-weight: 600; color: #475569;">${{ number_format($row->gross_total, 2) }}</td>
                                        <td style="text-align: right;" class="currency-net">${{ number_format($row->net_total, 2) }}</td>
                                        <td style="text-align: right; font-weight: 500; color: #64748b;">${{ number_format($row->avg_daily_expense, 2) }}</td>
                                        <td>
                                            <div style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ $row->top_staff }}</div>
                                            <small style="color: #64748b;">{{ $row->top_shop }}</small>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-view-details" @click="openPeriodDetails('{{ $row->month_key }}')">
                                                <i class='bx bx-show'></i>
                                                <span>{{ __('staff_expense_report.button.details') }}</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="{{ $viewMode === 'daily' ? 12 : 13 }}" class="empty-placeholder">
                                        <i class='bx bx-receipt'></i>
                                        <h4>{{ __('staff_expense_report.empty.title') }}</h4>
                                        <p>{{ __('staff_expense_report.empty.description') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                @if ($viewMode === 'daily')
                                    <tr>
                                        <td colspan="3" style="text-align: right; text-transform: uppercase;">{{ __('staff_expense_report.table.grand_total') }}:</td>
                                        <td style="text-align: center;"><span class="table-count-badge">{{ number_format($summary['total_transactions']) }}</span></td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['salary_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['bonus_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-neg">-${{ number_format($summary['deduction_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['other_total'], 2) }}</td>
                                        <td style="text-align: right; font-weight: 700;">${{ number_format($summary['gross_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-net">${{ number_format($summary['net_total'], 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="3" style="text-align: right; text-transform: uppercase;">{{ __('staff_expense_report.table.grand_total') }}:</td>
                                        <td style="text-align: center;"><span class="table-count-badge">{{ number_format($summary['total_transactions']) }}</span></td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['salary_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['bonus_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-neg">-${{ number_format($summary['deduction_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-pos">${{ number_format($summary['other_total'], 2) }}</td>
                                        <td style="text-align: right; font-weight: 700;">${{ number_format($summary['gross_total'], 2) }}</td>
                                        <td style="text-align: right;" class="currency-net">${{ number_format($summary['net_total'], 2) }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                @endif
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Period Details Drilldown Modal -->
        <template x-if="showDetailModal">
            <div class="modal-overlay" @click.self="closePeriodDetails()">
                <div class="modal-box">
                    <div class="modal-header">
                        <div class="modal-title">
                            <i class='bx bx-list-check' style="color: #2563eb;"></i>
                            <span>{{ __('staff_expense_report.modal.period_expenses') }}: </span>
                            <span style="color: #2563eb;" x-text="periodData ? periodData.period_label : '{{ __('staff_expense_report.modal.loading') }}'"></span>
                        </div>
                        <button type="button" class="modal-close-btn" @click="closePeriodDetails()">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Loading State -->
                        <template x-if="modalLoading">
                            <div class="empty-placeholder">
                                <i class='bx bx-loader-alt bx-spin' style="font-size: 40px; color: #2563eb;"></i>
                                <p style="margin-top: 10px; font-weight: 500;">{{ __('staff_expense_report.modal.loading') }}</p>
                            </div>
                        </template>

                        <!-- Content Loaded -->
                        <template x-if="!modalLoading && periodData">
                            <div>
                                <!-- Period Summary Cards -->
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.net_expense') }}</span>
                                        <strong style="color: #2563eb;" x-text="'$' + Number(periodData.net_total || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.salary') }}</span>
                                        <strong style="color: #059669;" x-text="'$' + Number(periodData.salary_total || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.bonus') }}</span>
                                        <strong style="color: #059669;" x-text="'$' + Number(periodData.bonus_total || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.deductions') }}</span>
                                        <strong style="color: #dc2626;" x-text="'-$' + Number(periodData.deduction_total || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.other_expense') }}</span>
                                        <strong style="color: #475569;" x-text="'$' + Number(periodData.other_total || 0).toFixed(2)"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('staff_expense_report.modal.total_records') }}</span>
                                        <strong x-text="periodData.count"></strong>
                                    </div>
                                </div>

                                <!-- Quick Search inside Modal -->
                                <div style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                    <input type="text" x-model="modalSearch" class="filter-input"
                                        placeholder="{{ __('staff_expense_report.modal.search_placeholder') }}"
                                        style="max-width: 320px; height: 34px; font-size: 12px;">
                                    <span style="font-size: 12px; color: #64748b;"
                                        x-text="'{{ __('staff_expense_report.modal.showing') }} ' + filteredModalExpenses.length + ' {{ __('staff_expense_report.modal.of') }} ' + periodData.count + ' {{ __('staff_expense_report.modal.transactions') }}'"></span>
                                </div>

                                <!-- Itemized Expense Table -->
                                <div style="overflow-x: auto; max-height: 440px; border: 1px solid #edf2f7; border-radius: 8px;">
                                    <table class="expense-data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 45px; text-align: center;">{{ __('staff_expense_report.modal.table.id') }}</th>
                                                <th>{{ __('staff_expense_report.modal.table.date') }}</th>
                                                <th>{{ __('staff_expense_report.modal.table.staff') }}</th>
                                                <th>{{ __('staff_expense_report.modal.table.shop') }}</th>
                                                <th style="text-align: center;">{{ __('staff_expense_report.modal.table.type') }}</th>
                                                <th style="text-align: right;">{{ __('staff_expense_report.modal.table.amount') }}</th>
                                                <th>{{ __('staff_expense_report.modal.table.description') }}</th>
                                                <th>{{ __('staff_expense_report.modal.table.created_by') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="item in filteredModalExpenses" :key="item.id">
                                                <tr>
                                                    <td style="text-align: center; color: #94a3b8; font-size: 11px;" x-text="'#' + item.id"></td>
                                                    <td style="white-space: nowrap;">
                                                        <strong style="color: #1e293b;" x-text="item.expense_date_formatted"></strong>
                                                    </td>
                                                    <td>
                                                        <div style="font-weight: 600; color: #0f172a;" x-text="item.staff_name"></div>
                                                        <small style="color: #64748b;" x-text="item.position_name + (item.staff_phone !== '---' ? ' • ' + item.staff_phone : '')"></small>
                                                    </td>
                                                    <td>
                                                        <span style="font-size: 12px; color: #475569;" x-text="item.shop_name"></span>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <span :class="item.badge_class" x-text="item.type_label"></span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <strong :class="item.is_deduction ? 'currency-neg' : 'currency-pos'" x-text="item.amount_formatted"></strong>
                                                    </td>
                                                    <td>
                                                        <span style="font-size: 12px; color: #334155;" x-text="item.description"></span>
                                                    </td>
                                                    <td>
                                                        <small style="color: #64748b;" x-text="item.created_by_name"></small>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="filteredModalExpenses.length === 0">
                                                <tr>
                                                    <td colspan="8" class="empty-placeholder" style="padding: 24px;">
                                                        <p>{{ __('staff_expense_report.modal.no_matching') }}</p>
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
    <!-- CDN & Local Assets for Charts & Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>

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
            Alpine.data('xStaffExpenseReport', () => ({
                viewMode: '{{ $viewMode }}',
                showDetailModal: false,
                modalLoading: false,
                periodData: null,
                modalSearch: '',
                exportLoading: false,

                get filteredModalExpenses() {
                    if (!this.periodData || !this.periodData.expenses) return [];
                    if (!this.modalSearch) return this.periodData.expenses;

                    const q = this.modalSearch.toLowerCase();
                    return this.periodData.expenses.filter(e =>
                        (e.staff_name && e.staff_name.toLowerCase().includes(q)) ||
                        (e.position_name && e.position_name.toLowerCase().includes(q)) ||
                        (e.shop_name && e.shop_name.toLowerCase().includes(q)) ||
                        (e.type_label && e.type_label.toLowerCase().includes(q)) ||
                        (e.description && e.description.toLowerCase().includes(q)) ||
                        (e.created_by_name && e.created_by_name.toLowerCase().includes(q)) ||
                        (e.amount_formatted && e.amount_formatted.includes(q))
                    );
                },

                async openPeriodDetails(period) {
                    this.showDetailModal = true;
                    this.modalLoading = true;
                    this.periodData = null;
                    this.modalSearch = '';

                    try {
                        const currentParams = new URLSearchParams(window.location.search);
                        const response = await Axios.get(`{{ url('admin/report/staff-expense/details') }}/${period}?` + currentParams.toString());
                        this.periodData = response.data;
                    } catch (err) {
                        console.error('Failed to load period expenses:', err);
                        alert('{{ __('staff_expense_report.modal.load_error') }}');
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

                        const response = await Axios.get(`{{ route('admin-report-staff-expense-report') }}?` + currentParams.toString());
                        const reportData = response.data;

                        const workbook = new ExcelJS.Workbook();
                        const sheetName = this.viewMode === 'monthly' ? '{{ __('staff_expense_report.excel.sheet_monthly') }}' : '{{ __('staff_expense_report.excel.sheet_daily') }}';
                        const worksheet = workbook.addWorksheet(sheetName);

                        if (this.viewMode === 'daily') {
                            worksheet.columns = [
                                { header: '{{ __('staff_expense_report.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('staff_expense_report.excel.date') }}', key: 'date', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.day') }}', key: 'day_name', width: 10 },
                                { header: '{{ __('staff_expense_report.excel.records_count') }}', key: 'transactions_count', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.salary') }} ($)', key: 'salary_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.bonus') }} ($)', key: 'bonus_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.deductions') }} ($)', key: 'deduction_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.other') }} ($)', key: 'other_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.gross_total') }} ($)', key: 'gross_total', width: 18 },
                                { header: '{{ __('staff_expense_report.excel.net_expense') }} ($)', key: 'net_total', width: 18 },
                                { header: '{{ __('staff_expense_report.excel.top_staff') }}', key: 'top_staff', width: 26 },
                                { header: '{{ __('staff_expense_report.excel.top_branch') }}', key: 'top_shop', width: 20 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    date: r.date,
                                    day_name: r.day_name,
                                    transactions_count: r.transactions_count,
                                    salary_total: Number(r.salary_total || 0),
                                    bonus_total: Number(r.bonus_total || 0),
                                    deduction_total: Number(r.deduction_total || 0),
                                    other_total: Number(r.other_total || 0),
                                    gross_total: Number(r.gross_total || 0),
                                    net_total: Number(r.net_total || 0),
                                    top_staff: r.top_staff,
                                    top_shop: r.top_shop,
                                });
                            });
                        } else {
                            worksheet.columns = [
                                { header: '{{ __('staff_expense_report.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('staff_expense_report.excel.month') }}', key: 'month_name', width: 18 },
                                { header: '{{ __('staff_expense_report.excel.active_days') }}', key: 'active_days', width: 14 },
                                { header: '{{ __('staff_expense_report.excel.records_count') }}', key: 'transactions_count', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.salary') }} ($)', key: 'salary_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.bonus') }} ($)', key: 'bonus_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.deductions') }} ($)', key: 'deduction_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.other') }} ($)', key: 'other_total', width: 16 },
                                { header: '{{ __('staff_expense_report.excel.gross_total') }} ($)', key: 'gross_total', width: 18 },
                                { header: '{{ __('staff_expense_report.excel.net_expense') }} ($)', key: 'net_total', width: 18 },
                                { header: '{{ __('staff_expense_report.excel.avg_daily') }} ($)', key: 'avg_daily_expense', width: 20 },
                                { header: '{{ __('staff_expense_report.excel.top_staff') }}', key: 'top_staff', width: 26 },
                                { header: '{{ __('staff_expense_report.excel.top_branch') }}', key: 'top_shop', width: 20 },
                            ];

                            reportData.rows.forEach((r) => {
                                worksheet.addRow({
                                    index: r.index,
                                    month_name: r.month_name,
                                    active_days: r.active_days,
                                    transactions_count: r.transactions_count,
                                    salary_total: Number(r.salary_total || 0),
                                    bonus_total: Number(r.bonus_total || 0),
                                    deduction_total: Number(r.deduction_total || 0),
                                    other_total: Number(r.other_total || 0),
                                    gross_total: Number(r.gross_total || 0),
                                    net_total: Number(r.net_total || 0),
                                    avg_daily_expense: Number(r.avg_daily_expense || 0),
                                    top_staff: r.top_staff,
                                    top_shop: r.top_shop,
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
                        const filename = (this.viewMode === 'monthly' ? '{{ __('staff_expense_report.excel.file_monthly_prefix') }}' : '{{ __('staff_expense_report.excel.file_daily_prefix') }}') + moment().format('YYYY_MM_DD_HHmmss');
                        saveAs(blob, filename);
                    } catch (err) {
                        console.error('Export failed:', err);
                        alert('{{ __('staff_expense_report.excel.export_failed') }}');
                    } finally {
                        this.exportLoading = false;
                    }
                }
            }));

            // Initialize Chart.js
            initExpenseChart();
        });

        function initExpenseChart() {
            const chartCanvas = document.getElementById('staffExpenseChart');
            if (!chartCanvas) return;

            const rowsData = @json($rows);
            const viewMode = '{{ $viewMode }}';

            let labels = [];
            let salaryData = [];
            let bonusData = [];
            let otherData = [];
            let deductionData = [];
            let netData = [];

            // Chronological order for chart (reverse from table desc)
            const chronological = [...rowsData].reverse();

            chronological.forEach(r => {
                labels.push(viewMode === 'daily' ? r.date_formatted : r.month_name);
                salaryData.push(r.salary_total || 0);
                bonusData.push(r.bonus_total || 0);
                otherData.push(r.other_total || 0);
                deductionData.push(r.deduction_total || 0);
                netData.push(r.net_total || 0);
            });

            const ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '{{ __('staff_expense_report.chart.salary') }} ($)',
                            data: salaryData,
                            backgroundColor: 'rgba(20, 184, 166, 0.7)',
                            borderColor: '#0d9488',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('staff_expense_report.chart.bonus') }} ($)',
                            data: bonusData,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('staff_expense_report.chart.other') }} ($)',
                            data: otherData,
                            backgroundColor: 'rgba(100, 116, 139, 0.7)',
                            borderColor: '#64748b',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('staff_expense_report.chart.deductions') }} ($)',
                            data: deductionData,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __('staff_expense_report.chart.net_expense') }} ($)',
                            data: netData,
                            type: 'line',
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2.5,
                            fill: false,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#2563eb',
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
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '$' + Number(context.parsed.y).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                    return label;
                                }
                            }
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
                            ticks: {
                                font: { size: 11 },
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@stop
