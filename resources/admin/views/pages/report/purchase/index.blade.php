@extends('admin::shared.layout')

@section('layout')
    <style>
        .purchase-report-wrapper {
            padding: 0;
            width: 100%;
            min-width: 0;
            background: #f8fafc;
            min-height: calc(100vh - 60px);
        }

        .purchase-report-wrapper .report-filter-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .purchase-report-wrapper .filter-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .purchase-report-wrapper .preset-badge-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .purchase-report-wrapper .preset-btn {
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

        .purchase-report-wrapper .preset-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .purchase-report-wrapper .preset-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            font-weight: 600;
        }

        .purchase-report-wrapper .filter-form-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .purchase-report-wrapper .filter-field-wrap {
            flex: 1 1 180px;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .purchase-report-wrapper .filter-field-wrap label {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin: 0;
        }

        .purchase-report-wrapper .filter-input {
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
            box-sizing: border-box;
        }

        .purchase-report-wrapper .filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .purchase-report-wrapper .filter-date-input {
            position: relative;
            cursor: pointer;
        }

        .purchase-report-wrapper .filter-date-input .filter-input {
            padding-right: 36px;
        }

        .purchase-report-wrapper .filter-date-input i.bx-calendar {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 18px;
            pointer-events: none;
        }

        .purchase-report-wrapper .filter-actions-wrap {
            display: flex;
            flex: 0 1 auto;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            height: auto;
            min-height: 43px;
        }

        .purchase-report-wrapper .btn-submit-filter {
            height: 43px;
            padding: 0 18px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .purchase-report-wrapper .btn-reset-filter {
            height: 43px;
            padding: 0 16px;
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .purchase-report-wrapper .btn-excel-export {
            height: 43px;
            padding: 0 16px;
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .purchase-report-wrapper .btn-excel-export:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* KPI cards */
        .purchase-report-wrapper .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .purchase-report-wrapper .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .purchase-report-wrapper .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .purchase-report-wrapper .kpi-icon.blue { background: #eff6ff; color: #2563eb; }
        .purchase-report-wrapper .kpi-icon.indigo { background: #e0e7ff; color: #4f46e5; }
        .purchase-report-wrapper .kpi-icon.emerald { background: #ecfdf5; color: #059669; }
        .purchase-report-wrapper .kpi-icon.rose { background: #fff1f2; color: #e11d48; }

        .purchase-report-wrapper .kpi-content h5 {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin: 0 0 4px 0;
        }

        .purchase-report-wrapper .kpi-content .num {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Report table */
        .purchase-report-wrapper .report-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .purchase-report-wrapper .table-toolbar {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .purchase-report-wrapper .table-toolbar h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .purchase-report-wrapper .toggle-pill-group {
            display: inline-flex;
            background: #e2e8f0;
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }

        .purchase-report-wrapper .toggle-pill-btn {
            border: none;
            background: transparent;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }

        .purchase-report-wrapper .toggle-pill-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .purchase-report-wrapper .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .purchase-report-wrapper .report-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        .purchase-report-wrapper .report-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .purchase-report-wrapper .report-table tbody tr:hover {
            background: #f8fafc;
        }
    </style>

    <div class="purchase-report-wrapper" x-data="xPurchaseReport">
        @include('admin::shared.header', ['header_name' => __('purchase_report.title')])

        <div style="padding: 16px 20px;">
            {{-- Mode Selector Tabs: Daily / Monthly --}}
            <div class="purchase-report-nav" style="display: flex; gap: 8px; border-bottom: 1px solid #e2e8f0; margin-bottom: 18px;">
                <a href="{{ route('admin-' . $routeName . '-daily', request()->except('page')) }}"
                   style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $viewMode === 'daily' ? '#2563eb' : 'transparent' }}; color: {{ $viewMode === 'daily' ? '#2563eb' : '#64748b' }};">
                    <i class='bx bx-calendar'></i> {{ __('purchase_report.tab.daily') }}
                </a>
                <a href="{{ route('admin-' . $routeName . '-monthly', request()->except('page')) }}"
                   style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $viewMode === 'monthly' ? '#2563eb' : 'transparent' }}; color: {{ $viewMode === 'monthly' ? '#2563eb' : '#64748b' }};">
                    <i class='bx bx-calendar-event'></i> {{ __('purchase_report.tab.monthly') }}
                </a>
            </div>

            {{-- Filter Panel --}}
            <div class="report-filter-panel">
                <form id="purchaseReportForm" method="GET" action="{{ route('admin-' . $routeName . '-' . $viewMode) }}">
                    <input type="hidden" name="grouping" value="{{ $grouping }}">

                    <div class="filter-header-row">
                        <div class="preset-badge-group">
                            <span style="font-size: 11.5px; font-weight: 600; color: #64748b;">{{ __('purchase_report.presets.title') }}</span>
                            <button type="button" @click="setPreset('today')" class="preset-btn">{{ __('purchase_report.presets.today') }}</button>
                            <button type="button" @click="setPreset('yesterday')" class="preset-btn">{{ __('purchase_report.presets.yesterday') }}</button>
                            <button type="button" @click="setPreset('last_7_days')" class="preset-btn">{{ __('purchase_report.presets.last_7_days') }}</button>
                            <button type="button" @click="setPreset('last_30_days')" class="preset-btn">{{ __('purchase_report.presets.last_30_days') }}</button>
                            <button type="button" @click="setPreset('this_month')" class="preset-btn">{{ __('purchase_report.presets.this_month') }}</button>
                            <button type="button" @click="setPreset('last_month')" class="preset-btn">{{ __('purchase_report.presets.last_month') }}</button>
                        </div>
                    </div>

                    <div class="filter-form-grid">
                        @if ($viewMode === 'daily')
                            <div class="filter-field-wrap">
                                <label for="report-from-date">{{ __('purchase_report.filter.from_date') }}</label>
                                <div class="filter-date-input" @click="$refs.fromDate.focus()">
                                    <input type="text" name="from_date" id="report-from-date"
                                           x-ref="fromDate" class="filter-input" value="{{ $from_date }}"
                                           autocomplete="off" placeholder="YYYY-MM-DD">
                                    <i class='bx bx-calendar'></i>
                                </div>
                            </div>

                            <div class="filter-field-wrap">
                                <label for="report-to-date">{{ __('purchase_report.filter.to_date') }}</label>
                                <div class="filter-date-input" @click="$refs.toDate.focus()">
                                    <input type="text" name="to_date" id="report-to-date"
                                           x-ref="toDate" class="filter-input" value="{{ $to_date }}"
                                           autocomplete="off" placeholder="YYYY-MM-DD">
                                    <i class='bx bx-calendar'></i>
                                </div>
                            </div>
                        @else
                            <div class="filter-field-wrap">
                                <label>{{ __('purchase_report.filter.year') }}</label>
                                <select name="year" class="filter-input">
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label>{{ __('purchase_report.filter.from_month') }}</label>
                                <select name="from_month" class="filter-input">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ ($from_month ?? 1) == $m ? 'selected' : '' }}>Month {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-field-wrap">
                                <label>{{ __('purchase_report.filter.to_month') }}</label>
                                <select name="to_month" class="filter-input">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ ($to_month ?? date('m')) == $m ? 'selected' : '' }}>Month {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="filter-field-wrap">
                            <label>{{ __('purchase_report.filter.supplier') }}</label>
                            <select name="supplier_id" class="filter-input">
                                <option value="">{{ __('purchase_report.filter.all_suppliers') }}</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ ($filters['supplier_id'] ?? '') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label>{{ __('purchase_report.filter.shop') }}</label>
                            <select name="shop_id" class="filter-input">
                                <option value="">{{ __('purchase_report.filter.all_shops') }}</option>
                                @foreach ($shops as $shp)
                                    <option value="{{ $shp->id }}" {{ ($filters['shop_id'] ?? '') == $shp->id ? 'selected' : '' }}>
                                        {{ $shp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label>{{ __('purchase_report.filter.payment_status') }}</label>
                            <select name="payment_status" class="filter-input">
                                <option value="all">{{ __('purchase_report.filter.all_payment_statuses') }}</option>
                                <option value="unpaid" {{ ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                            </select>
                        </div>

                        <div class="filter-field-wrap">
                            <label>{{ __('purchase_report.filter.keyword_search') }}</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="filter-input" placeholder="{{ __('purchase_report.filter.placeholder_search') }}">
                        </div>

                        <div class="filter-actions-wrap">
                            <button type="submit" class="btn-submit-filter">
                                <i class='bx bx-filter-alt'></i>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route('admin-' . $routeName . '-' . $viewMode) }}" class="btn-reset-filter">
                                <i class='bx bx-reset'></i>
                                <span>Reset</span>
                            </a>

                            @can('report-purchase-excel')
                                <button type="button" @click="excel()" class="btn-excel-export" :disabled="exportLoading">
                                    <i class='bx bx-download' x-show="!exportLoading"></i>
                                    <i class='bx bx-loader-alt bx-spin' x-show="exportLoading" style="display: none;"></i>
                                    <span x-text="exportLoading ? '{{ __('purchase_report.excel.exporting') }}' : 'Export Excel'">Export Excel</span>
                                </button>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>

            {{-- KPI Summary Row --}}
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon blue"><i class='bx bx-cart-download'></i></div>
                    <div class="kpi-content">
                        <h5>{{ __('purchase_report.kpi.total_purchases') }}</h5>
                        <div class="num">{{ number_format($summary['total_purchases'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon indigo"><i class='bx bx-receipt'></i></div>
                    <div class="kpi-content">
                        <h5>{{ __('purchase_report.kpi.total_volume') }}</h5>
                        <div class="num">$ {{ number_format($summary['total_volume'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon emerald"><i class='bx bx-check-shield'></i></div>
                    <div class="kpi-content">
                        <h5>{{ __('purchase_report.kpi.total_paid') }}</h5>
                        <div class="num">$ {{ number_format($summary['total_paid'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon rose"><i class='bx bx-time-five'></i></div>
                    <div class="kpi-content">
                        <h5>{{ __('purchase_report.kpi.total_outstanding') }}</h5>
                        <div class="num">$ {{ number_format($summary['total_outstanding'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Report Table Card --}}
            <div class="report-table-card">
                <div class="table-toolbar">
                    <h4>{{ $grouping === 'grouped' ? 'Grouped Summary Breakdown' : 'Individual Purchase Transactions' }}</h4>
                    <div class="toggle-pill-group">
                        <a href="{{ route('admin-' . $routeName . '-' . $viewMode, array_merge(request()->except('grouping', 'page'), ['grouping' => 'grouped'])) }}"
                           class="toggle-pill-btn {{ $grouping === 'grouped' ? 'active' : '' }}">
                            Grouped
                        </a>
                        <a href="{{ route('admin-' . $routeName . '-' . $viewMode, array_merge(request()->except('grouping', 'page'), ['grouping' => 'ungrouped'])) }}"
                           class="toggle-pill-btn {{ $grouping === 'ungrouped' ? 'active' : '' }}">
                            Ungrouped
                        </a>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    @if ($grouping === 'grouped')
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $viewMode === 'monthly' ? 'Month' : 'Date' }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.purchases_count') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.total_amount') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.paid_amount') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.remaining_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $viewMode === 'monthly' ? $row->month : $row->date }}</strong></td>
                                        <td style="text-align: right;">{{ number_format($row->count) }}</td>
                                        <td style="text-align: right; font-weight: 700;">$ {{ number_format($row->total_amount, 2) }}</td>
                                        <td style="text-align: right; color: #059669;">$ {{ number_format($row->paid_amount, 2) }}</td>
                                        <td style="text-align: right; color: #dc2626; font-weight: 600;">$ {{ number_format($row->remaining_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 24px; color: #94a3b8;">
                                            No records found for the selected period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('purchase_report.table.purchase_number') }}</th>
                                    <th>{{ __('purchase_report.table.supplier') }}</th>
                                    <th>{{ __('purchase_report.table.shop') }}</th>
                                    <th>{{ __('purchase_report.table.date') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.total_amount') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.paid_amount') }}</th>
                                    <th style="text-align: right;">{{ __('purchase_report.table.remaining_amount') }}</th>
                                    <th>{{ __('purchase_report.table.status') }}</th>
                                    <th>{{ __('purchase_report.table.payment_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ungroupedRows as $index => $item)
                                    <tr>
                                        <td>{{ $ungroupedRows->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('admin-purchase-view', $item->id) }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">
                                                {{ $item->purchase_number }}
                                            </a>
                                        </td>
                                        <td><strong>{{ $item->supplier?->name ?? '---' }}</strong></td>
                                        <td>{{ $item->shop?->name ?? '---' }}</td>
                                        <td>{{ $item->purchase_date ? $item->purchase_date->format('Y-m-d') : '---' }}</td>
                                        <td style="text-align: right; font-weight: 700;">{{ $item->formatted_total }}</td>
                                        <td style="text-align: right; color: #059669;">{{ $item->formatted_paid }}</td>
                                        <td style="text-align: right; color: {{ $item->remaining_amount > 0 ? '#dc2626' : '#64748b' }}; font-weight: 600;">
                                            {{ $item->formatted_remaining }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->status_badge['class'] }}">
                                                {{ $item->status_badge['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->payment_status_badge['class'] }}">
                                                {{ $item->payment_status_badge['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 24px; color: #94a3b8;">
                                            No purchase records found matching your filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if ($ungroupedRows->hasPages())
                            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0;">
                                {{ $ungroupedRows->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Full Screen Loading Spinner Overlay for Excel Export --}}
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('purchase_report.excel.exporting') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script>
        (function() {
            const registerXPurchaseReport = () => {
                Alpine.data('xPurchaseReport', () => ({
                    exportLoading: false,

                    init() {
                        this.$nextTick(() => {
                            this.initDatepickers();
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

                        const $from = $(this.$refs.fromDate);
                        const $to = $(this.$refs.toDate);

                        if ($from.length) {
                            $from.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $to.datepicker('option', 'minDate', selected);
                                }
                            }).on('change', function() {
                                $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                            });
                        }

                        if ($to.length) {
                            $to.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $from.datepicker('option', 'maxDate', selected);
                                }
                            }).on('change', function() {
                                $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                            });
                        }

                        if ($from.val()) $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                        if ($to.val()) $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                    },

                    setPreset(type) {
                        const now = new Date();
                        let from = new Date();
                        let to = new Date();

                        switch (type) {
                            case 'today':
                                break;
                            case 'yesterday':
                                from.setDate(now.getDate() - 1);
                                to.setDate(now.getDate() - 1);
                                break;
                            case 'last_7_days':
                                from.setDate(now.getDate() - 6);
                                break;
                            case 'last_30_days':
                                from.setDate(now.getDate() - 29);
                                break;
                            case 'this_month':
                                from = new Date(now.getFullYear(), now.getMonth(), 1);
                                break;
                            case 'last_month':
                                from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                                to = new Date(now.getFullYear(), now.getMonth(), 0);
                                break;
                        }

                        const formatDate = (d) => {
                            const year = d.getFullYear();
                            const month = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            return `${year}-${month}-${day}`;
                        };

                        if (this.$refs.fromDate) $(this.$refs.fromDate).val(formatDate(from)).trigger('change');
                        if (this.$refs.toDate) $(this.$refs.toDate).val(formatDate(to)).trigger('change');

                        const form = document.getElementById('purchaseReportForm');
                        if (form) form.submit();
                    },

                    getExportParams() {
                        const params = new URLSearchParams(window.location.search);

                        if (window.MDI && Array.isArray(window.MDI.tabs) && window.MDI.activeTabKey) {
                            const activeTab = window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);
                            if (activeTab && activeTab.url && activeTab.url.includes('?')) {
                                const tabParams = new URLSearchParams(activeTab.url.split('?')[1]);
                                tabParams.forEach((value, key) => params.set(key, value));
                            }
                        }

                        const form = document.getElementById('purchaseReportForm');
                        if (form) {
                            const formData = new FormData(form);
                            formData.forEach((value, key) => {
                                if (value !== null && value !== '') {
                                    params.set(key, value);
                                }
                            });
                        }

                        params.set('viewMode', '{{ $viewMode }}');
                        params.set('grouping', '{{ $grouping }}');
                        params.delete('page');
                        return params;
                    },

                    async excel() {
                        if (this.exportLoading) return;
                        this.exportLoading = true;

                        try {
                            const params = this.getExportParams();
                            const url = '{{ route("admin-report-purchase-report") }}?' + params.toString();
                            const response = await axios.get(url);
                            const data = response.data;

                            if (data && data.status === 'success' && Array.isArray(data.rows)) {
                                await this.runExcelExport(data.rows, data.grouping, data.viewMode);
                            } else {
                                alert('No records found for export.');
                            }
                        } catch (e) {
                            console.error('Export error:', e);
                            alert('An error occurred during export.');
                        } finally {
                            this.exportLoading = false;
                        }
                    },

                    async runExcelExport(rows, grouping, viewMode) {
                        const workbook = new ExcelJS.Workbook();
                        workbook.creator = 'POS System';
                        workbook.created = new Date();

                        const worksheet = workbook.addWorksheet('Purchase_Report', {
                            views: [{ showGridLines: true }]
                        });

                        const isGrouped = (grouping === 'grouped');

                        if (isGrouped) {
                            worksheet.columns = [
                                { header: 'Nº', key: 'no', width: 8 },
                                { header: viewMode === 'monthly' ? 'Month' : 'Date', key: 'period', width: 18 },
                                { header: 'Total Purchase Count', key: 'count', width: 22 },
                                { header: 'Total Amount ($)', key: 'total', width: 20 },
                                { header: 'Total Paid ($)', key: 'paid', width: 20 },
                                { header: 'Outstanding Balance ($)', key: 'balance', width: 22 },
                            ];
                        } else {
                            worksheet.columns = [
                                { header: 'Nº', key: 'no', width: 8 },
                                { header: 'PO Number', key: 'purchase_number', width: 22 },
                                { header: 'Supplier', key: 'supplier', width: 26 },
                                { header: 'Shop', key: 'shop', width: 22 },
                                { header: 'Date', key: 'date', width: 14 },
                                { header: 'Total Amount ($)', key: 'total', width: 18 },
                                { header: 'Paid Amount ($)', key: 'paid', width: 16 },
                                { header: 'Balance ($)', key: 'balance', width: 16 },
                                { header: 'Status', key: 'status', width: 16 },
                                { header: 'Payment Status', key: 'payment_status', width: 16 },
                            ];
                        }

                        const headerRow = worksheet.getRow(1);
                        headerRow.height = 28;
                        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
                        headerRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FF1E293B' }
                        };
                        headerRow.alignment = { vertical: 'middle', horizontal: 'center' };

                        let sumTotal = 0;
                        let sumPaid = 0;
                        let sumBalance = 0;

                        rows.forEach((r, idx) => {
                            const tot = Number(r.total_amount || 0);
                            const pd = Number(r.paid_amount || 0);
                            const bal = Number(r.remaining_amount || Math.max(0, tot - pd));

                            sumTotal += tot;
                            sumPaid += pd;
                            sumBalance += bal;

                            let rowData;
                            if (isGrouped) {
                                rowData = {
                                    no: idx + 1,
                                    period: viewMode === 'monthly' ? r.month : r.date,
                                    count: Number(r.count || 0),
                                    total: tot,
                                    paid: pd,
                                    balance: bal,
                                };
                            } else {
                                rowData = {
                                    no: idx + 1,
                                    purchase_number: r.purchase_number || '',
                                    supplier: r.supplier?.name || '',
                                    shop: r.shop?.name || '',
                                    date: r.purchase_date ? r.purchase_date.substring(0, 10) : '',
                                    total: tot,
                                    paid: pd,
                                    balance: bal,
                                    status: (r.status || '').toUpperCase(),
                                    payment_status: (r.payment_status || '').toUpperCase(),
                                };
                            }

                            const row = worksheet.addRow(rowData);
                            row.height = 22;
                            row.alignment = { vertical: 'middle' };

                            ['total', 'paid', 'balance'].forEach(col => {
                                row.getCell(col).numFmt = '$#,##0.00';
                            });
                        });

                        // Summary row
                        const totalRowData = isGrouped
                            ? { no: '', period: 'TOTAL', total: sumTotal, paid: sumPaid, balance: sumBalance }
                            : { no: '', purchase_number: 'TOTAL', total: sumTotal, paid: sumPaid, balance: sumBalance };

                        const totalRow = worksheet.addRow(totalRowData);
                        totalRow.height = 26;
                        totalRow.font = { bold: true };
                        totalRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FFF1F5F9' }
                        };
                        ['total', 'paid', 'balance'].forEach(col => {
                            totalRow.getCell(col).numFmt = '$#,##0.00';
                        });

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                        saveAs(blob, 'Purchase_Report_' + new Date().toISOString().slice(0, 10) + '.xlsx');
                    }
                }));
            };

            if (window.Alpine) {
                registerXPurchaseReport();
            } else {
                document.addEventListener('alpine:init', registerXPurchaseReport);
            }
        })();
    </script>
@stop
