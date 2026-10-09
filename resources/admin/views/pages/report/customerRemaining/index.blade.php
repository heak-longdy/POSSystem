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
                                    <td colspan="10" class="empty-placeholder">
                                        <i class="bx bx-user-x"></i>
                                        <p>{{ __('customer_remaining_report.no_customers') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($customers->count())
                            <tfoot>
                                <tr>
                                    <td colspan="5">{{ __('sales_report.table.total_summary') }}</td>
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
    </div>
    <script>
        (function() {
            function registerCustomerRemainingAlpine() {
                if (window.Alpine) {
                    window.Alpine.data('xCustomerRemainingReport', () => ({
                        customerId: '{{ $filters['customer_id'] ?? '' }}',
                        shopId: '{{ $filters['shop_id'] ?? '' }}',
                        init() {
                            this.$nextTick(() => {
                                if (typeof window.initCustomerRemainingFilters === 'function') {
                                    window.initCustomerRemainingFilters(this.$el, this);
                                }
                            });
                        },
                        resetCustomer() {
                            this.customerId = '';
                            const $cust = $('#cr-customer');
                            if ($cust.length) {
                                $cust.val('').trigger('change');
                            }
                            $('#cr-customer-reset').removeClass('is-visible').css('display', 'none').hide();
                        },
                        resetShop() {
                            this.shopId = '';
                            const $shop = $('#cr-shop');
                            if ($shop.length) {
                                $shop.val('').trigger('change');
                            }
                            $('#cr-shop-reset').removeClass('is-visible').css('display', 'none').hide();
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

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    window.initCustomerRemainingFilters();
                });
            } else {
                window.initCustomerRemainingFilters();
            }

            window.addEventListener('workspace:tab-activated', function() {
                window.initCustomerRemainingFilters();
            });
        })();
    </script>
@endsection
