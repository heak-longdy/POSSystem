@extends('admin::shared.layout')
@section('layout')
    @include('admin::pages.report.customerRemaining.styles')
    @include('admin::shared.header', ['header_name' => __('customer_remaining_report.title')])
    <div class="content-wrapper customer-remaining-report" x-data="xCustomerRemainingReport"
        @workspace:tab-activated.window="if ($el.closest('.workspace-tab-pane')?.id === 'tab-pane-' + $event.detail.key) { window.MDI?.refreshTab($event.detail.key); }">
        @include('admin::pages.report.customerRemaining.header', ['detail' => false])
        <div class="content-body" style="padding: 14px 20px 24px 20px;">
            @if ($errors->any())
                <div class="report-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="report-filter-panel">
                <form id="cr-filter-form" method="GET" action="{{ route('admin-report-customer-remaining-amount-index') }}">
                    <div class="filter-form-grid">
                        <div class="filter-field-wrap">
                            <label for="cr-customer">{{ __('customer_remaining_report.customer') }}</label>
                            <div class="select2Group">
                                <select id="cr-customer" name="customer_id" class="filter-select select2">
                                    <option value="">{{ __('customer_remaining_report.all_customers') }}</option>
                                    @foreach ($customerList as $cust)
                                        <option value="{{ $cust->id }}" @selected(($filters['customer_id'] ?? '') == $cust->id || (($filters['search'] ?? '') != '' && ($filters['search'] == $cust->id || $filters['search'] == $cust->phone || $filters['search'] == $cust->name)))>
                                            {{ $cust->name ?: __('customer_remaining_report.unnamed_customer', ['id' => $cust->id]) }}{{ $cust->phone ? ' (' . $cust->phone . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="select2Reset {{ !empty($filters['customer_id']) ? 'is-visible' : '' }}" id="cr-customer-reset" x-show="customerId" @click.stop="resetCustomer()" title="{{ __('global.button.reset') ?? 'Reset' }}" style="{{ !empty($filters['customer_id']) ? 'display: flex;' : 'display: none;' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="filter-field-wrap">
                            <label for="cr-shop">{{ __('customer_remaining_report.shop') }}</label>
                            <div class="select2Group">
                                <select id="cr-shop" name="shop_id" class="filter-select select2">
                                    <option value="">{{ __('customer_remaining_report.all_shops') }}</option>
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? '') == $shop->id)>{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                                <div class="select2Reset {{ !empty($filters['shop_id']) ? 'is-visible' : '' }}" id="cr-shop-reset" x-show="shopId" @click.stop="resetShop()" title="{{ __('global.button.reset') ?? 'Reset' }}" style="{{ !empty($filters['shop_id']) ? 'display: flex;' : 'display: none;' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="filter-actions-wrap">
                            <button class="btn btn-filter-search" type="submit">
                                <i class="bx bx-search"></i>
                                <span>{{ __('customer_remaining_report.filter') }}</span>
                            </button>
                            <a class="btn btn-filter-reset" href="{{ route('admin-report-customer-remaining-amount-index') }}">
                                <i class="bx bx-reset"></i>
                                <span>{{ __('customer_remaining_report.reset') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon-wrap indigo"><i class="bx bx-group"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.customer_count') }}</div>
                        <div class="kpi-value">{{ number_format($summary->customer_count) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary"><i class="bx bx-dollar-circle"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.total_amount') }}</div>
                        <div class="kpi-value text-primary">${{ number_format($summary->total_amount, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success"><i class="bx bx-check-shield"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.paid_amount') }}</div>
                        <div class="kpi-value text-success">${{ number_format($summary->paid_amount, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger"><i class="bx bx-time"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.remaining_amount') }}</div>
                        <div class="kpi-value text-danger">${{ number_format($summary->remaining_amount, 2) }}</div>
                    </div>
                </div>
            </div>
            <p class="report-description">{{ __('customer_remaining_report.description') }}</p>

            <div class="report-table-card">
                <div class="table-custom-header">
                    <h4><i class="bx bx-wallet"></i> {{ __('customer_remaining_report.title') }}</h4>
                    <span class="report-count">{{ __('customer_remaining_report.results', ['from' => $customers->firstItem() ?? 0, 'to' => $customers->lastItem() ?? 0, 'total' => number_format($customers->total())]) }}</span>
                </div>
                <div class="table-responsive-custom">
                    <table class="report-data-table">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center" style="width: 45px;">{{ __('sales_report.table.no') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.customer') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.phone') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.shop') }}</th>
                                <th scope="col" class="text-center">{{ __('customer_remaining_report.orders') }}</th>
                                <th scope="col" class="text-center">{{ __('customer_remaining_report.outstanding_orders') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.total_amount') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.paid_amount') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.remaining_amount') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.last_order') }}</th>
                                <th scope="col" class="text-center">{{ __('customer_remaining_report.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="text-center">{{ $customers->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="report-name">{{ $customer->name ?: __('customer_remaining_report.unnamed_customer', ['id' => $customer->id]) }}</div>
                                        @if ($customer->deleted_at)
                                            <span class="method-tag">{{ __('customer_remaining_report.archived') }}</span>
                                        @endif
                                    </td>
                                    <td class="report-nowrap">{{ $customer->phone ?: '—' }}</td>
                                    <td>{{ $customerShops->get($customer->id) ?: '—' }}</td>
                                    <td class="text-center">{{ number_format($customer->order_count) }}</td>
                                    <td class="text-center">{{ number_format($customer->outstanding_orders) }}</td>
                                    <td class="text-right text-primary font-weight-bold">${{ number_format($customer->total_amount, 2) }}</td>
                                    <td class="text-right text-success font-weight-bold">${{ number_format($customer->paid_amount, 2) }}</td>
                                    <td class="text-right {{ $customer->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">${{ number_format($customer->remaining_amount, 2) }}</td>
                                    <td class="report-nowrap">{{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : '—' }}</td>
                                    <td class="text-center">
                                        <a class="btn-drilldown" href="{{ route('admin-report-customer-remaining-amount-details', ['customer' => $customer->id, 'shop_id' => $filters['shop_id'] ?? null]) }}">
                                            <i class="bx bx-detail"></i>
                                            <span>{{ __('customer_remaining_report.view_details') }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="empty-placeholder">
                                        <i class="bx bx-user-x"></i>
                                        <p>{{ __('customer_remaining_report.no_customers') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($customers->count())
                            <tfoot>
                                <tr>
                                    <td colspan="6">{{ __('sales_report.table.total_summary') }}</td>
                                    <td class="text-right text-primary">${{ number_format($summary->total_amount, 2) }}</td>
                                    <td class="text-right text-success">${{ number_format($summary->paid_amount, 2) }}</td>
                                    <td class="text-right text-danger">${{ number_format($summary->remaining_amount, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                @include('admin::pages.report.customerRemaining.pagination', ['paginator' => $customers])
            </div>
            <p class="report-note">{{ __('customer_remaining_report.scope_note') }}</p>
        </div>
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('order.export_excel') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script>
        (function() {
            function registerCustomerRemainingAlpine() {
                if (window.Alpine) {
                    window.Alpine.data('xCustomerRemainingReport', () => ({
                        customerId: '{{ $filters['customer_id'] ?? '' }}',
                        shopId: '{{ $filters['shop_id'] ?? '' }}',
                        exportLoading: false,

                        init() {
                            this.$nextTick(() => {
                                if (typeof window.initCustomerRemainingFilters === 'function') {
                                    window.initCustomerRemainingFilters(this.$el, this);
                                }
                            });
                        },
                        resetCustomer() {
                            this.customerId = '';
                            const $cust = $(this.$root).find('#cr-customer');
                            if ($cust.length) {
                                $cust.val('').trigger('change');
                            }
                            $(this.$root).find('#cr-customer-reset').removeClass('is-visible').css('display', 'none').hide();
                        },
                        resetShop() {
                            this.shopId = '';
                            const $shop = $(this.$root).find('#cr-shop');
                            if ($shop.length) {
                                $shop.val('').trigger('change');
                            }
                            $(this.$root).find('#cr-shop-reset').removeClass('is-visible').css('display', 'none').hide();
                        },

                        @include('admin::pages.report.partials.excel-request')

                        getExportParams() {
                            // 1. Start with top-level browser query (for direct navigation and automated tests)
                            const params = new URLSearchParams(window.location.search);

                            // 2. Capture active MDI workspace tab query if running inside the MDI tab manager
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

                            // 3. Merge server-rendered initial query parameters
                            const initialQuery = @json($filters ?? []);
                            if (initialQuery && typeof initialQuery === 'object') {
                                for (const [key, value] of Object.entries(initialQuery)) {
                                    if (value !== null && value !== undefined && value !== '' && !params.has(key)) {
                                        params.set(key, value);
                                    }
                                }
                            }

                            // 4. Capture current values from the tab's active filter form DOM controls
                            const form = (this.$root ? this.$root.querySelector('#cr-filter-form') : null)
                                || document.getElementById('cr-filter-form');
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

                            // 5. Sync tracked filter values in Alpine component state
                            if (this.customerId && String(this.customerId).trim() !== '') {
                                params.set('customer_id', String(this.customerId).trim());
                            } else {
                                params.delete('customer_id');
                            }

                            if (this.shopId && String(this.shopId).trim() !== '') {
                                params.set('shop_id', String(this.shopId).trim());
                            } else {
                                params.delete('shop_id');
                            }

                            // 6. CRITICAL: Remove pagination parameter so export retrieves the complete matching dataset
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
                            const sheetName = '{{ __('customer_remaining_report.excel.sheet_name') }}';
                            const worksheet = workbook.addWorksheet(sheetName);

                            const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

                            worksheet.columns = [
                                { header: '{{ __('customer_remaining_report.excel.no') }}', key: 'index', width: 8 },
                                { header: '{{ __('customer_remaining_report.excel.customer') }}', key: 'customer_name', width: 24 },
                                { header: '{{ __('customer_remaining_report.excel.phone') }}', key: 'phone', width: 16 },
                                { header: '{{ __('customer_remaining_report.excel.shop') }}', key: 'shop_name', width: 22 },
                                { header: '{{ __('customer_remaining_report.excel.orders') }}', key: 'order_count', width: 14 },
                                { header: '{{ __('customer_remaining_report.excel.outstanding_orders') }}', key: 'outstanding_orders', width: 18 },
                                { header: '{{ __('customer_remaining_report.excel.total_amount') }}', key: 'total_amount', width: 18 },
                                { header: '{{ __('customer_remaining_report.excel.paid_amount') }}', key: 'paid_amount', width: 18 },
                                { header: '{{ __('customer_remaining_report.excel.remaining_amount') }}', key: 'remaining_amount', width: 18 },
                                { header: '{{ __('customer_remaining_report.excel.last_order') }}', key: 'last_order_date', width: 18 },
                            ];

                            rows.forEach((r, idx) => {
                                worksheet.addRow({
                                    index: r.index ?? (idx + 1),
                                    customer_name: r.customer_name ?? '',
                                    phone: r.phone ?? '—',
                                    shop_name: r.shop_name ?? '—',
                                    order_count: Number(r.order_count || 0),
                                    outstanding_orders: Number(r.outstanding_orders || 0),
                                    total_amount: Number(r.total_amount || 0),
                                    paid_amount: Number(r.paid_amount || 0),
                                    remaining_amount: Number(r.remaining_amount || 0),
                                    last_order_date: r.last_order_date_formatted || r.last_order_date || '—',
                                });
                            });

                            worksheet.getColumn('order_count').numFmt = '#,##0';
                            worksheet.getColumn('outstanding_orders').numFmt = '#,##0';
                            worksheet.getColumn('total_amount').numFmt = '$#,##0.00';
                            worksheet.getColumn('paid_amount').numFmt = '$#,##0.00';
                            worksheet.getColumn('remaining_amount').numFmt = '$#,##0.00';

                            worksheet.getColumn('index').alignment = { horizontal: 'center' };
                            worksheet.getColumn('order_count').alignment = { horizontal: 'center' };
                            worksheet.getColumn('outstanding_orders').alignment = { horizontal: 'center' };
                            worksheet.getColumn('total_amount').alignment = { horizontal: 'right' };
                            worksheet.getColumn('paid_amount').alignment = { horizontal: 'right' };
                            worksheet.getColumn('remaining_amount').alignment = { horizontal: 'right' };
                            worksheet.getColumn('last_order_date').alignment = { horizontal: 'center' };

                            worksheet.getRow(1).font = { bold: true };
                            worksheet.getRow(1).fill = {
                                type: 'pattern',
                                pattern: 'solid',
                                fgColor: { argb: 'FFE2E8F0' }
                            };

                            if (rows.length > 0) {
                                const summary = reportData?.summary;
                                const totalRow = worksheet.addRow({
                                    index: '',
                                    customer_name: '{{ __('sales_report.table.total_summary') }}',
                                    phone: '',
                                    shop_name: '',
                                    order_count: '',
                                    outstanding_orders: '',
                                    total_amount: summary ? Number(summary.total_amount || 0) : rows.reduce((s, r) => s + Number(r.total_amount || 0), 0),
                                    paid_amount: summary ? Number(summary.paid_amount || 0) : rows.reduce((s, r) => s + Number(r.paid_amount || 0), 0),
                                    remaining_amount: summary ? Number(summary.remaining_amount || 0) : rows.reduce((s, r) => s + Number(r.remaining_amount || 0), 0),
                                    last_order_date: '',
                                });
                                totalRow.font = { bold: true };
                                totalRow.fill = {
                                    type: 'pattern',
                                    pattern: 'solid',
                                    fgColor: { argb: 'FFF8FAFC' }
                                };
                            }

                            const buffer = await workbook.xlsx.writeBuffer();
                            const blob = new Blob([buffer], {
                                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            });
                            const filename = '{{ __('customer_remaining_report.excel.file_prefix') }}' + moment().format('YYYY_MM_DD_HHmmss');
                            saveAs(blob, filename);
                        }
                    }));
                }
            }

            if (window.Alpine) {
                registerCustomerRemainingAlpine();
            } else {
                document.addEventListener('alpine:init', registerCustomerRemainingAlpine);
            }

            window.initCustomerRemainingFilters = function(scope, alpineVm) {
                const $container = scope ? $(scope) : $('.customer-remaining-report');
                const $customer = $container.find('#cr-customer');
                const $shop = $container.find('#cr-shop');

                function syncState() {
                    const cVal = $customer.val();
                    const sVal = $shop.val();
                    const hasCustomer = Boolean(cVal && String(cVal).trim() !== '');
                    const hasShop = Boolean(sVal && String(sVal).trim() !== '');

                    if (alpineVm) {
                        alpineVm.customerId = hasCustomer ? String(cVal) : '';
                        alpineVm.shopId = hasShop ? String(sVal) : '';
                    }

                    const $cReset = $container.find('#cr-customer-reset');
                    const $sReset = $container.find('#cr-shop-reset');

                    if (hasCustomer) {
                        $cReset.addClass('is-visible').css('display', 'flex').show();
                    } else {
                        $cReset.removeClass('is-visible').css('display', 'none').hide();
                    }

                    if (hasShop) {
                        $sReset.addClass('is-visible').css('display', 'flex').show();
                    } else {
                        $sReset.removeClass('is-visible').css('display', 'none').hide();
                    }
                }

                if (window.jQuery && $.fn.select2) {
                    if ($customer.length && !$customer.hasClass('select2-hidden-accessible')) {
                        $customer.select2({
                            placeholder: @json(__('customer_remaining_report.all_customers')),
                            allowClear: false,
                            width: '100%'
                        }).on('select2:open', function() {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            } else {
                                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                                if (searchField) searchField.focus();
                            }
                        }).on('change select2:select select2:clear select2:unselect', function() {
                            syncState();
                        });
                    }

                    if ($shop.length && !$shop.hasClass('select2-hidden-accessible')) {
                        $shop.select2({
                            placeholder: @json(__('customer_remaining_report.all_shops')),
                            allowClear: false,
                            width: '100%'
                        }).on('select2:open', function() {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            } else {
                                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                                if (searchField) searchField.focus();
                            }
                        }).on('change select2:select select2:clear select2:unselect', function() {
                            syncState();
                        });
                    }
                }

                $container.find('#cr-customer-reset').off('click.crReset').on('click.crReset', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $customer.val('').trigger('change');
                    if (alpineVm) {
                        alpineVm.customerId = '';
                    }
                    syncState();
                });

                $container.find('#cr-shop-reset').off('click.crReset').on('click.crReset', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $shop.val('').trigger('change');
                    if (alpineVm) {
                        alpineVm.shopId = '';
                    }
                    syncState();
                });

                syncState();
            };
        })();
    </script>
@stop
