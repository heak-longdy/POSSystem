@extends('admin::shared.layout')

@section('layout')
    <style>
        .other-income-report-wrapper {
            padding: 0;
            width: 100%;
            min-width: 0;
            background: #f8fafc;
            min-height: calc(100vh - 60px);
        }
        .other-income-report-wrapper .report-filter-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .other-income-report-wrapper .filter-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .other-income-report-wrapper .preset-badge-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .other-income-report-wrapper .preset-btn {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            text-decoration: none;
            transition: all 0.15s;
        }
        .other-income-report-wrapper .preset-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .other-income-report-wrapper .preset-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            font-weight: 600;
        }
        .other-income-report-wrapper .filter-form-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }
        .other-income-report-wrapper .filter-field-wrap {
            flex: 1 1 180px;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .other-income-report-wrapper .filter-field-wrap label {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin: 0;
        }
        .other-income-report-wrapper .filter-input {
            height: 43px;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            padding: 0 12px;
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
            background: #ffffff;
            width: 100%;
            outline: none;
        }
        .other-income-report-wrapper .filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }
        .other-income-report-wrapper .filter-date-input {
            position: relative;
            cursor: pointer;
        }
        .other-income-report-wrapper .filter-date-input .filter-input {
            padding-right: 36px;
        }
        .other-income-report-wrapper .filter-date-input i.bx-calendar {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 18px;
            pointer-events: none;
        }
        .other-income-report-wrapper .filter-actions-wrap {
            display: flex;
            flex: 0 1 auto;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            height: auto;
            min-height: 43px;
            max-width: 100%;
        }
        .other-income-report-wrapper .filter-actions-wrap > a,
        .other-income-report-wrapper .filter-actions-wrap > button {
            flex-shrink: 0;
            white-space: nowrap;
        }
        .other-income-report-wrapper .btn-filter-search {
            height: 43px;
            padding: 0 16px !important;
            border-radius: 7px !important;
            background: #2563eb;
            color: #ffffff;
            border: none !important;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .other-income-report-wrapper .btn-filter-search:hover {
            background: #1d4ed8;
        }
        .other-income-report-wrapper .btn-filter-reset {
            height: 43px;
            padding: 0 14px;
            border-radius: 7px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-size: 12.5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .other-income-report-wrapper .btn-filter-reset:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        #ui-datepicker-div.other-income-report-datepicker {
            z-index: 9999 !important;
        }

        /* KPI Cards Grid */
        .other-income-report-wrapper .report-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .other-income-report-wrapper .report-kpi-card {
            min-width: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .other-income-report-wrapper .report-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .other-income-report-wrapper .report-kpi-icon.blue { background: #eff6ff; color: #2563eb; }
        .other-income-report-wrapper .report-kpi-icon.green { background: #f0fdf4; color: #16a34a; }
        .other-income-report-wrapper .report-kpi-icon.amber { background: #fffbeb; color: #d97706; }
        .other-income-report-wrapper .report-kpi-icon.purple { background: #faf5ff; color: #9333ea; }
        .other-income-report-wrapper .report-kpi-meta { flex: 1; min-width: 0; }
        .other-income-report-wrapper .report-kpi-meta span.lbl { font-size: 11px; font-weight: 500; color: #64748b; }
        .other-income-report-wrapper .report-kpi-meta h4.val { font-size: 18px; font-weight: 700; color: #0f172a; margin: 2px 0 0 0; }

        /* Report Table Container */
        .other-income-report-wrapper .report-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .other-income-report-wrapper .report-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .other-income-report-wrapper .toggle-group {
            display: flex;
            flex-wrap: wrap;
            background: #f1f5f9;
            border-radius: 7px;
            padding: 3px;
        }
        .other-income-report-wrapper .toggle-group a {
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.15s;
        }
        .other-income-report-wrapper .toggle-group a.active {
            background: #ffffff;
            color: #0f172a;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .other-income-report-wrapper .table-responsive {
            overflow-x: auto;
        }
        .other-income-report-wrapper .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .other-income-report-wrapper .table th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .other-income-report-wrapper .table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .other-income-report-wrapper .table tbody {
            border-top: 0;
        }
        .other-income-report-wrapper .table tbody tr:hover {
            background: #f8fafc;
        }
        .other-income-report-wrapper .btn-excel-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 14px;
            background: #059669;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .other-income-report-wrapper .btn-excel-export svg {
            fill: currentColor;
        }
        .other-income-report-wrapper .payment-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.55);
        }
        .other-income-report-wrapper .payment-modal-card {
            width: 100%;
            max-height: calc(100vh - 32px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .other-income-report-wrapper .payment-modal-header,
        .other-income-report-wrapper .payment-modal-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            background: #f8fafc;
        }
        .other-income-report-wrapper .payment-modal-header {
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }
        .other-income-report-wrapper .payment-modal-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .other-income-report-wrapper .payment-modal-header .close-btn {
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 22px;
            cursor: pointer;
        }
        .other-income-report-wrapper .payment-modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: 20px;
        }
        .other-income-report-wrapper .payment-modal-footer {
            justify-content: flex-end;
            border-top: 1px solid #e2e8f0;
        }
        .other-income-report-wrapper .modal-ledger-summary {
            display: grid;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .other-income-report-wrapper .summary-label,
        .other-income-report-wrapper .summary-val {
            display: block;
        }
        .other-income-report-wrapper .summary-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
        }
        .other-income-report-wrapper .summary-val {
            margin-top: 2px;
            font-size: 18px;
            font-weight: 700;
        }
        .other-income-report-wrapper .cursor-pointer { cursor: pointer; }
    </style>

    @include('admin::shared.header', ['header_name' => __('other_income_report.title')])
    <div class="content-wrapper other-income-report-wrapper" id="otherIncomeReportApp" x-data="xOtherIncomeReport">
        
        {{-- Header Tab Bar --}}
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            @php
                                $dailyParams = array_merge(request()->except(['from_month', 'to_month', 'year', 'page']), ['grouping' => $grouping]);
                                $monthlyParams = array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['grouping' => $grouping]);
                            @endphp
                            <a href="{{ route('admin-report-other-income-daily', $dailyParams) }}"
                                class="{{ $viewMode === 'daily' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-event'></i>
                                {{ __('other_income_report.tab.daily') }}
                            </a>
                            <a href="{{ route('admin-report-other-income-monthly', $monthlyParams) }}"
                                class="{{ $viewMode === 'monthly' ? 'tabActive' : '' }}">
                                <i class='bx bx-calendar-alt'></i>
                                {{ __('other_income_report.tab.monthly') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    @can('report-other-income-excel')
                    <button type="button" @click="excel()" class="btn-excel-export" :disabled="exportLoading">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M252.31-180Q222-180 201-201q-21-21-21-51.31v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46Q780-222 759-201q-21 21-51.31 21H252.31ZM450-664.46l-76.92 76.92q-8.93 8.92-21.19 8.81-12.27-.12-21.58-9.43-8.69-9.3-9-21.07-.31-11.77 9-21.08l124.38-124.38q5.62-5.62 11.85-7.92 6.23-2.31 13.46-2.31t13.46 2.31q6.23 2.3 11.85 7.92l124.38 124.38q8.92 8.92 8.81 20.89-.12 11.96-8.81 21.26-9.31 9.31-21.38 9.62-12.08.31-21.39-9L510-664.46v306q0 12.77-8.62 21.38-8.61 8.62-21.38 8.62t-21.38-8.62q-8.62-8.61-8.62-21.38v-306Z"/></svg>
                        <span x-text="exportLoading ? '{{ __('other_income_report.excel.exporting') }}' : '{{ __('global.button.export') }}'">{{ __('global.button.export') }}</span>
                    </button>
                    @endcan
                    <button type="button" s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>{{ __('global.button.reload') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body" style="padding: 16px 20px 24px 20px;">
            
            {{-- Filter Panel --}}
            <div class="report-filter-panel">
                <form id="otherIncomeFilterForm" method="GET" action="{{ url()->current() }}">
                    <input type="hidden" name="grouping" value="{{ $grouping }}">

                    @if ($viewMode === 'daily')
                        <div class="filter-header-row">
                            <div class="preset-badge-group">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">{{ __('other_income_report.presets.title') }}</span>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => 'today', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === 'today' || (!request()->filled('preset') && !request()->filled('from_date') && !request()->filled('to_date')) ? 'active' : '' }}">{{ __('other_income_report.presets.today') }}</a>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => 'yesterday', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === 'yesterday' ? 'active' : '' }}">{{ __('other_income_report.presets.yesterday') }}</a>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => '7days', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === '7days' ? 'active' : '' }}">{{ __('other_income_report.presets.last_7_days') }}</a>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => '30days', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === '30days' ? 'active' : '' }}">{{ __('other_income_report.presets.last_30_days') }}</a>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => 'this_month', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === 'this_month' ? 'active' : '' }}">{{ __('other_income_report.presets.this_month') }}</a>
                                <a href="{{ route('admin-report-other-income-daily', array_merge(request()->except(['from_date', 'to_date', 'preset', 'page']), ['preset' => 'last_month', 'grouping' => $grouping])) }}"
                                    class="preset-btn {{ request('preset') === 'last_month' ? 'active' : '' }}">{{ __('other_income_report.presets.last_month') }}</a>
                            </div>
                        </div>
                    @endif

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="other-income-from-date">{{ __('other_income_report.filter.from_date') }}</label>
                                <div class="filter-date-input" @click="$refs.fromDate.focus()">
                                    <input type="text" name="from_date" id="other-income-from-date" x-ref="fromDate" class="filter-input"
                                        value="{{ $from_date }}" autocomplete="off" placeholder="{{ __('other_income_report.filter.placeholder_date') }}">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="other-income-to-date">{{ __('other_income_report.filter.to_date') }}</label>
                                <div class="filter-date-input" @click="$refs.toDate.focus()">
                                    <input type="text" name="to_date" id="other-income-to-date" x-ref="toDate" class="filter-input"
                                        value="{{ $to_date }}" autocomplete="off" placeholder="{{ __('other_income_report.filter.placeholder_date') }}">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label for="year">{{ __('other_income_report.filter.year') }}</label>
                                <div class="select2Group">
                                    <select name="year" id="year" class="SelectField select2">
                                        @foreach ($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ (int) $selectedYear === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="from_month">{{ __('other_income_report.filter.from_month') }}</label>
                                <div class="select2Group">
                                    <select name="from_month" id="from_month" class="SelectField select2">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ (int) $from_month === $m ? 'selected' : '' }}>
                                                {{ __('other_income_report.months.' . $m) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="filter-field-wrap">
                                <label for="to_month">{{ __('other_income_report.filter.to_month') }}</label>
                                <div class="select2Group">
                                    <select name="to_month" id="to_month" class="SelectField select2">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ (int) $to_month === $m ? 'selected' : '' }}>
                                                {{ __('other_income_report.months.' . $m) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label for="supplier_id">{{ __('other_income_report.filter.supplier') }}</label>
                            <div class="select2Group">
                                <select name="supplier_id" id="supplier_id" class="SelectField select2">
                                    <option value="">{{ __('other_income_report.filter.all_suppliers') }}</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ (string) request('supplier_id') === (string) $sup->id ? 'selected' : '' }}>
                                            {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="category_id">{{ __('other_income_report.filter.category') }}</label>
                            <div class="select2Group">
                                <select name="category_id" id="category_id" class="SelectField select2">
                                    <option value="">{{ __('other_income_report.filter.all_categories') }}</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="filter-field-wrap">
                            <label for="other-income-search">{{ __('other_income_report.filter.keyword_search') }}</label>
                            <input type="text" name="search" id="other-income-search" class="filter-input"
                                value="{{ request('search') }}" placeholder="{{ __('other_income_report.filter.placeholder_search') }}">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-filter-search">
                                <i class="bx bx-search"></i>
                                <span>{{ __('other_income_report.button.filter') }}</span>
                            </button>
                            <a href="{{ route($viewMode === 'daily' ? 'admin-report-other-income-daily' : 'admin-report-other-income-monthly', ['grouping' => $grouping]) }}" class="btn-filter-reset">
                                <i class="bx bx-reset"></i>
                                <span>{{ __('other_income_report.button.reset') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Summary KPI Cards --}}
            <div class="report-kpi-grid">
                <div class="report-kpi-card">
                    <div class="report-kpi-icon blue"><i class='bx bx-coin-stack'></i></div>
                    <div class="report-kpi-meta">
                        <span class="lbl">{{ __('other_income_report.summary.total_amount') }}</span>
                        <h4 class="val text-primary">${{ number_format($summary['total_amount'] ?? $summary['total_received'] ?? 0, 2) }}</h4>
                    </div>
                </div>
                <div class="report-kpi-card">
                    <div class="report-kpi-icon green"><i class='bx bx-receipt'></i></div>
                    <div class="report-kpi-meta">
                        <span class="lbl">{{ __('other_income_report.summary.total_transactions') }}</span>
                        <h4 class="val text-success">{{ number_format($summary['total_transactions'] ?? 0) }}</h4>
                    </div>
                </div>
                <div class="report-kpi-card">
                    <div class="report-kpi-icon purple"><i class='bx bx-gift'></i></div>
                    <div class="report-kpi-meta">
                        <span class="lbl">{{ __('other_income_report.summary.supplier_bonus') }}</span>
                        <h4 class="val">${{ number_format($summary['supplier_bonus'] ?? 0, 2) }}</h4>
                    </div>
                </div>
                <div class="report-kpi-card">
                    <div class="report-kpi-icon amber"><i class='bx bx-trophy'></i></div>
                    <div class="report-kpi-meta">
                        <span class="lbl">{{ __('other_income_report.summary.top_supplier') }}</span>
                        <h4 class="val text-truncate" style="max-width: 180px;" title="{{ $summary['top_supplier_name'] ?? '' }}">
                            {{ $summary['top_supplier_name'] ?? __('other_income_report.table.na') }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Main Report Data Card --}}
            <div class="report-table-card">
                <div class="report-table-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 font-weight-bold" style="font-size: 14px;">
                            {{ $viewMode === 'daily' ? __('other_income_report.tab.daily') : __('other_income_report.tab.monthly') }}
                        </h5>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="toggle-group">
                            <a href="{{ route(request()->route()?->getName() ?: ($viewMode === 'daily' ? 'admin-report-other-income-daily' : 'admin-report-other-income-monthly'), array_merge(request()->query(), ['grouping' => 'ungrouped', 'page' => 1])) }}"
                                class="{{ $grouping === 'ungrouped' ? 'active' : '' }}">
                                <i class='bx bx-list-ul'></i> {{ __('other_income_report.toggle.ungrouped') }}
                            </a>
                            <a href="{{ route(request()->route()?->getName() ?: ($viewMode === 'daily' ? 'admin-report-other-income-daily' : 'admin-report-other-income-monthly'), array_merge(request()->query(), ['grouping' => 'grouped', 'page' => 1])) }}"
                                class="{{ $grouping === 'grouped' ? 'active' : '' }}">
                                <i class='bx bx-grid-alt'></i> {{ __('other_income_report.toggle.grouped') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Grouped View Table --}}
                @if ($grouping === 'grouped')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>{{ $viewMode === 'daily' ? __('other_income_report.table.date') : __('other_income_report.table.month') }}</th>
                                    <th style="width: 140px;">{{ __('other_income_report.table.transactions') }}</th>
                                    <th style="width: 180px;">{{ __('other_income_report.table.amount') }}</th>
                                    <th>{{ __('other_income_report.table.top_supplier') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr class="cursor-pointer" @click="openDetailsModal('{{ $viewMode === 'daily' ? $row->date : $row->month_key }}')">
                                        <td>{{ $row->index }}</td>
                                        <td>
                                            @if ($viewMode === 'daily')
                                                <strong>{{ $row->date_formatted }}</strong>
                                                <small class="text-muted">({{ $row->day_name }})</small>
                                            @else
                                                <strong>{{ $row->month_name }}</strong>
                                                <small class="text-muted">({{ $row->active_days }} days active)</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $row->transactions_count }}</span></td>
                                        <td class="text-success font-weight-bold">${{ number_format($row->total_amount ?? $row->received_total, 2) }}</td>
                                        <td>{{ $row->top_supplier }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">{{ __('other_income_report.table.empty_grouped') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- Ungrouped View Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>{{ __('other_income_report.table.income_number') }}</th>
                                    <th>{{ __('other_income_report.table.date') }}</th>
                                    <th>{{ __('other_income_report.table.title') }}</th>
                                    <th>{{ __('other_income_report.table.category') }}</th>
                                    <th>{{ __('other_income_report.table.supplier') }}</th>
                                    <th>{{ __('other_income_report.table.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ungroupedRows as $index => $item)
                                    <tr>
                                        <td>{{ $ungroupedRows->firstItem() + $index }}</td>
                                        <td><code>{{ $item->income_number }}</code></td>
                                        <td>{{ $item->income_date_formatted }}</td>
                                        <td><strong>{{ $item->title }}</strong></td>
                                        <td>{{ $item->category_name }}</td>
                                        <td>{{ $item->supplier_name }}</td>
                                        <td class="text-success font-weight-bold">${{ number_format($item->received_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">{{ __('other_income_report.table.empty_ungrouped') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($ungroupedRows && $ungroupedRows->hasPages())
                        <div class="p-3 border-top">
                            {{ $ungroupedRows->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Details Drill-Down Modal --}}
        <template x-if="showDetailsModal">
            <div class="payment-modal-backdrop" @click.self="closeDetailsModal()">
                <div class="payment-modal-card" style="max-width: 800px;">
                    <div class="payment-modal-header">
                        <h4><i class='bx bx-list-check'></i> <span x-text="detailsPeriodLabel"></span></h4>
                        <button type="button" class="close-btn" @click="closeDetailsModal()">&times;</button>
                    </div>
                    <div class="payment-modal-body">
                        <div x-show="detailsLoading" class="text-center py-4">
                            <i class='bx bx-loader-alt bx-spin fs-2 text-primary'></i>
                            <div class="mt-2 text-muted">{{ __('other_income_report.modal.loading') }}</div>
                        </div>

                        <div x-show="!detailsLoading">
                            <div class="modal-ledger-summary mb-3" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="summary-col">
                                    <span class="summary-label">{{ __('other_income_report.summary.total_transactions') }}</span>
                                    <span class="summary-val text-primary" x-text="detailsData.count || 0"></span>
                                </div>
                                <div class="summary-col">
                                    <span class="summary-label">{{ __('other_income_report.table.amount') }}</span>
                                    <span class="summary-val text-success" x-text="'$' + Number(detailsData.total_amount || detailsData.received_total || 0).toFixed(2)"></span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>{{ __('other_income_report.table.income_number') }}</th>
                                            <th>{{ __('other_income_report.table.title') }}</th>
                                            <th>{{ __('other_income_report.table.category') }}</th>
                                            <th>{{ __('other_income_report.table.supplier') }}</th>
                                            <th class="text-end">{{ __('other_income_report.table.amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, idx) in detailsData.incomes" :key="row.id">
                                            <tr>
                                                <td x-text="idx + 1"></td>
                                                <td><code x-text="row.income_number"></code></td>
                                                <td x-text="row.title"></td>
                                                <td x-text="row.category_name"></td>
                                                <td x-text="row.supplier_name"></td>
                                                <td class="text-end text-success font-weight-bold" x-text="'$' + Number(row.received_amount || 0).toFixed(2)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="payment-modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="closeDetailsModal()">{{ __('other_income_report.modal.close') }}</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Full Screen Loading Overlay --}}
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('other_income_report.excel.exporting') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script>
        (function() {
            const registerXReport = () => {
                Alpine.data('xOtherIncomeReport', () => ({
                    exportLoading: false,
                    showDetailsModal: false,
                    detailsLoading: false,
                    detailsPeriodLabel: '',
                    detailsData: { incomes: [] },
                    viewMode: '{{ $viewMode }}',

                    init() {
                        this.$nextTick(() => {
                            this.initDatepickers();
                            this.initDropdowns();
                        });
                    },

                    initDropdowns() {
                        const $root = $(this.$root);
                        if ($root.find('#supplier_id').length) {
                            $root.find('#supplier_id').select2({ width: '100%' });
                        }
                        if ($root.find('#category_id').length) {
                            $root.find('#category_id').select2({ width: '100%' });
                        }
                        if ($root.find('#year').length) {
                            $root.find('#year').select2({ width: '100%' });
                        }
                        if ($root.find('#from_month').length) {
                            $root.find('#from_month').select2({ width: '100%' });
                        }
                        if ($root.find('#to_month').length) {
                            $root.find('#to_month').select2({ width: '100%' });
                        }
                    },

                    initDatepickers() {
                        if (!window.jQuery || !$.fn.datepicker) return;
                        const $from = $(this.$root).find('#other-income-from-date');
                        const $to   = $(this.$root).find('#other-income-to-date');

                        const options = {
                            dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            changeMonth: true,
                            gotoCurrent: true,
                            yearRange: '-10:+10',
                            beforeShow: function(input, instance) {
                                instance.dpDiv.addClass('other-income-report-datepicker');
                            },
                            onClose: function(dateText, instance) {
                                instance.dpDiv.removeClass('other-income-report-datepicker');
                            }
                        };

                        if ($from.length && $to.length) {
                            $from.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $to.datepicker('option', 'minDate', selected);
                                }
                            }).on('change', function() {
                                $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                            });

                            $to.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $from.datepicker('option', 'maxDate', selected);
                                }
                            }).on('change', function() {
                                $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                            });
                        }
                    },

                    async openDetailsModal(period) {
                        this.detailsPeriodLabel = period;
                        this.showDetailsModal = true;
                        this.detailsLoading = true;

                        try {
                            const currentParams = this.getExportParams();
                            const url = `{{ url('admin/report/other-income/details') }}/${period}?` + currentParams.toString();
                            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            const data = await res.json();
                            if (res.ok && data.status === 'success') {
                                this.detailsPeriodLabel = data.period_label || period;
                                this.detailsData = data;
                            }
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.detailsLoading = false;
                        }
                    },

                    closeDetailsModal() {
                        this.showDetailsModal = false;
                    },

                    @include('admin::pages.report.partials.excel-request')

                    getExportParams() {
                        const params = new URLSearchParams(window.location.search);

                        if (window.MDI && Array.isArray(window.MDI.tabs) && window.MDI.activeTabKey) {
                            const currentTab = window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);
                            if (currentTab && currentTab.url && currentTab.url.includes('?')) {
                                const tabParams = new URLSearchParams(currentTab.url.split('?')[1]);
                                for (const [key, value] of tabParams.entries()) {
                                    if (value !== '' && !params.has(key)) {
                                        params.set(key, value);
                                    }
                                }
                            }
                        }

                        const initialQuery = @json(request()->query());
                        if (initialQuery && typeof initialQuery === 'object') {
                            for (const [key, value] of Object.entries(initialQuery)) {
                                if (value !== null && value !== undefined && value !== '' && !params.has(key)) {
                                    params.set(key, value);
                                }
                            }
                        }

                        const form = (this.$root ? this.$root.querySelector('#otherIncomeFilterForm') : null)
                            || document.getElementById('otherIncomeFilterForm');
                        if (form) {
                            const formData = new FormData(form);
                            for (const [key, value] of formData.entries()) {
                                const trimmed = typeof value === 'string' ? value.trim() : value;
                                if (trimmed !== null && trimmed !== undefined && trimmed !== '') {
                                    params.set(key, trimmed);
                                } else if (params.has(key)) {
                                    params.delete(key);
                                }
                            }
                        }

                        params.set('view_mode', this.viewMode);
                        params.delete('page');
                        return params;
                    },

                    async excel() {
                        this.exportLoading = true;
                        try {
                            const currentParams = this.getExportParams();
                            const reportData = await this.requestReportExport(currentParams);
                            await this.runExcelExport(reportData);
                        } catch (err) {
                            this.showReportExportError(err);
                        } finally {
                            this.exportLoading = false;
                        }
                    },

                    async exportExcel() {
                        return this.excel();
                    },

                    async runExcelExport(reportData) {
                        const workbook = new ExcelJS.Workbook();
                        const sheetName = @json(__('other_income_report.excel.sheet_name'));
                        const worksheet = workbook.addWorksheet(sheetName);

                        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);
                        const isGrouped = (reportData?.grouping === 'grouped');

                        if (isGrouped) {
                            worksheet.columns = [
                                { header: @json(__('other_income_report.table.no')), key: 'index', width: 8 },
                                { header: @json($viewMode === 'daily' ? __('other_income_report.table.date') : __('other_income_report.table.month')), key: 'period', width: 22 },
                                { header: @json(__('other_income_report.table.transactions')), key: 'count', width: 16 },
                                { header: @json(__('other_income_report.table.amount')), key: 'total_amount', width: 20 },
                                { header: @json(__('other_income_report.table.top_supplier')), key: 'top_supplier', width: 26 },
                            ];

                            rows.forEach((r, idx) => {
                                worksheet.addRow({
                                    index: idx + 1,
                                    period: r.date_formatted || r.month_name || r.date || r.month_key || '',
                                    count: Number(r.transactions_count || 0),
                                    total_amount: Number(r.total_amount || r.received_total || 0),
                                    top_supplier: r.top_supplier || '---',
                                });
                            });
                        } else {
                            worksheet.columns = [
                                { header: @json(__('other_income_report.table.no')), key: 'index', width: 8 },
                                { header: @json(__('other_income_report.table.income_number')), key: 'income_number', width: 18 },
                                { header: @json(__('other_income_report.table.date')), key: 'income_date', width: 14 },
                                { header: @json(__('other_income_report.table.title')), key: 'title', width: 30 },
                                { header: @json(__('other_income_report.table.category')), key: 'category_name', width: 22 },
                                { header: @json(__('other_income_report.table.supplier')), key: 'supplier_name', width: 24 },
                                { header: @json(__('other_income_report.table.amount')), key: 'amount', width: 18 },
                                { header: @json(__('other_income_report.table.created_by')), key: 'created_by_name', width: 18 },
                            ];

                            rows.forEach((r, idx) => {
                                worksheet.addRow({
                                    index: idx + 1,
                                    income_number: r.income_number || '',
                                    income_date: r.income_date || '',
                                    title: r.title || '',
                                    category_name: r.category_name || '---',
                                    supplier_name: r.supplier_name || '---',
                                    amount: Number(r.received_amount || 0),
                                    created_by_name: r.created_by_name || '---',
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
                        const filename = 'Other_Income_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx';
                        saveAs(blob, filename);
                    }
                }));
            };

            if (window.Alpine) {
                registerXReport();
            } else {
                document.addEventListener('alpine:init', registerXReport);
            }
        })();
    </script>
@stop
