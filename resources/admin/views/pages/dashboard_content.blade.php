@php
    $hasFullDataAccess = $hasFullDataAccess ?? (Auth::check() && Auth::user()->hasFullDataAccess());
@endphp
<!-- ================= ROW 1 ================= -->
<div class="dash-grid-row dash-grid-row-1">
    <!-- 4. Orders (Total Price) Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">${{ number_format($totalOrderPrice ?? 0, 2) }}</h3>
                <p class="top-mini-label">@lang('dashboard.orders_total_price')</p>
            </div>
            <div class="grid-icon-box teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <span class="badge-subtle badge-teal-subtle">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                <span>@lang('dashboard.total_sales')</span>
            </span>
        </div>
    </div>

    <!-- 5. Order Pending (Count) Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">{{ number_format($pendingOrderCount ?? 0) }}</h3>
                <p class="top-mini-label">@lang('dashboard.orders_pending')</p>
            </div>
            <div class="grid-icon-box orange">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <span class="badge-subtle badge-orange-subtle">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>@lang('dashboard.pending')</span>
            </span>
        </div>
    </div>

    <!-- 6. Order Remaining Amount Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">${{ number_format($totalOrderRemainingAmount ?? 0, 2) }}</h3>
                <p class="top-mini-label">@lang('dashboard.orders_remaining_amount')</p>
            </div>
            <div class="grid-icon-box coral">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                    <line x1="7" y1="15" x2="7.01" y2="15"></line>
                    <line x1="11" y1="15" x2="13" y2="15"></line>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <a @can('remaining-amount-view') href="{{ Route::has('admin-remaining-amount-list') ? route('admin-remaining-amount-list', 'partial') : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-coral-subtle" style="text-decoration: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ isset($remainingOrderCount) && $remainingOrderCount > 0 ? __('dashboard.remaining_orders', ['count' => $remainingOrderCount]) : __('dashboard.remaining') }}</span>
            </a>
        </div>
    </div>

    @if ($hasFullDataAccess)
        <!-- 7. Products (Count) Card -->
        <div class="dash-card top-mini-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="top-mini-amount">{{ number_format($totalProductCount ?? ($totalProductsCount ?? 0)) }}</h3>
                    <p class="top-mini-label">@lang('dashboard.products_count')</p>
                </div>
                <div class="grid-icon-box purple">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
            </div>
            <div style="margin-top: 14px;">
                <a @can('product-view') href="{{ Route::has('admin-product-list') ? route('admin-product-list', 1) : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-purple-subtle" style="text-decoration: none;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ isset($activeProductCount) && $activeProductCount > 0 ? __('dashboard.active_products', ['count' => $activeProductCount]) : __('dashboard.active') }}</span>
                </a>
            </div>
        </div>
    @else
        <!-- 8. Customer (Count) Card (Slot 4 for Own Records Only) -->
        <div class="dash-card top-mini-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="top-mini-amount">{{ number_format($totalCustomerCount ?? ($totalCustomersCount ?? 0)) }}</h3>
                    <p class="top-mini-label">@lang('dashboard.customers_count')</p>
                </div>
                <div class="grid-icon-box cyan">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <div style="margin-top: 14px;">
                <a @can('customer-view') href="{{ Route::has('admin-customer-list') ? route('admin-customer-list', 1) : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-cyan-subtle" style="text-decoration: none;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ isset($activeCustomerCount) && $activeCustomerCount > 0 ? __('dashboard.active_customers', ['count' => $activeCustomerCount]) : __('dashboard.active') }}</span>
                </a>
            </div>
        </div>
    @endif
</div>

@if ($hasFullDataAccess)
<!-- ================= ROW 2: USER & STAFF COUNTS ================= -->
<div class="dash-grid-row dash-grid-row-secondary">

    <!-- 8. Customer (Count) Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">{{ number_format($totalCustomerCount ?? ($totalCustomersCount ?? 0)) }}</h3>
                <p class="top-mini-label">@lang('dashboard.customers_count')</p>
            </div>
            <div class="grid-icon-box cyan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <a @can('customer-view') href="{{ Route::has('admin-customer-list') ? route('admin-customer-list', 1) : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-cyan-subtle" style="text-decoration: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ isset($activeCustomerCount) && $activeCustomerCount > 0 ? __('dashboard.active_customers', ['count' => $activeCustomerCount]) : __('dashboard.active') }}</span>
            </a>
        </div>
    </div>

    <!-- 9. Staff (Count) Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">{{ number_format($totalStaffCount ?? 0) }}</h3>
                <p class="top-mini-label">@lang('dashboard.staff_count')</p>
            </div>
            <div class="grid-icon-box emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <polyline points="16 11 18 13 22 9"></polyline>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <a @can('staff-view') href="{{ Route::has('admin-staff-list') ? route('admin-staff-list', 1) : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-emerald-subtle" style="text-decoration: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ isset($activeStaffCount) && $activeStaffCount > 0 ? __('dashboard.active_staff', ['count' => $activeStaffCount]) : __('dashboard.active') }}</span>
            </a>
        </div>
    </div>

    <!-- 10. User (Count) Card -->
    <div class="dash-card top-mini-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 class="top-mini-amount">{{ number_format($totalUserCount ?? 0) }}</h3>
                <p class="top-mini-label">@lang('dashboard.user_count')</p>
            </div>
            <div class="grid-icon-box indigo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <a @can('user-view') href="{{ Route::has('admin-user-list') ? route('admin-user-list', 1) : 'javascript:void(0);' }}" @endcan class="badge-subtle badge-indigo-subtle" style="text-decoration: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ isset($activeUserCount) && $activeUserCount > 0 ? __('dashboard.active_users', ['count' => $activeUserCount]) : __('dashboard.active') }}</span>
            </a>
        </div>
    </div>

</div>
@endif


@if ($hasFullDataAccess)
<!-- ================= ROW 4 ================= -->
<div class="dash-grid-row dash-grid-row-4">

    <!-- 12. Recent Transactions -->
    <div class="dash-card">
        <div class="table-header-flex" style="margin-bottom: 8px;">
            <h3 class="dash-card-title">@lang('dashboard.recent_transactions')</h3>
            @if (Route::has('admin-order-list'))
                <a @can('order-view') href="{{ route('admin-order-list') }}" @endcan class="timeline-view-all-link" title="@lang('dashboard.view_all_transactions')">
                    <span>@lang('dashboard.view_all_transactions')</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @endif
        </div>

        <div class="timeline-container">
            @forelse ($recentTransactions ?? [] as $tx)
                @php
                    $createdCarbon = $tx->created_at ? \Carbon\Carbon::parse($tx->created_at) : null;
                    $orderCarbon = $tx->order_date ? \Carbon\Carbon::parse($tx->order_date) : null;

                    // Detect if created_at or order_date has real hour/minute
                    $hasCreatedTime = $createdCarbon && $createdCarbon->format('H:i') !== '00:00';
                    $hasOrderTime = $orderCarbon && $orderCarbon->format('H:i') !== '00:00';

                    $timeObj = null;
                    if ($hasCreatedTime) {
                        $timeObj = $createdCarbon;
                    } elseif ($hasOrderTime) {
                        $timeObj = $orderCarbon;
                    }

                    $dateObj = $orderCarbon ?: $createdCarbon;
                    $isToday = $dateObj ? $dateObj->isToday() : false;

                    if ($timeObj) {
                        $timeFormatted = $timeObj->format('h:i a');
                        $dateFormatted = $isToday ? __('dashboard.today') : ($dateObj ? $dateObj->format('d M') : '');
                    } else {
                        // Avoid showing repetitive 12:00 am when only date was recorded
                        $timeFormatted = $dateObj ? ($isToday ? __('dashboard.today') : $dateObj->format('d M')) : '--';
                        $dateFormatted = $dateObj ? $dateObj->format('Y') : '';
                    }

                    $status = $tx->payment_status ?: 'Pending';
                    $circleColor = match ($status) {
                        'Paid' => 'circle-teal',
                        'Partial' => 'circle-cyan',
                        'Cancel' => 'circle-coral',
                        default => 'circle-orange',
                    };

                    $statusBadgeClass = match ($status) {
                        'Paid' => 'status-badge-paid',
                        'Partial' => 'status-badge-partial',
                        'Cancel' => 'status-badge-cancel',
                        default => 'status-badge-pending',
                    };

                    $statusLabel = match ($status) {
                        'Paid' => __('dashboard.paid'),
                        'Partial' => __('dashboard.partial'),
                        'Cancel' => __('dashboard.cancel'),
                        default => __('dashboard.pending'),
                    };

                    $rawCustomer = $tx->customer?->name ?: ($tx->customer?->phone ?: null);
                    $customerDisplay = $rawCustomer ?: __('dashboard.walk_in_customer');
                    $invoiceNumber = $tx->invoice_number ? '#' . $tx->invoice_number : '#' . $tx->id;
                    $orderUrl = Route::has('admin-order-detail') ? route('admin-order-detail', $tx->id) : (Route::has('admin-order-edit') ? route('admin-order-edit', $tx->id) : '#');
                @endphp
                <div class="timeline-row">
                    <div class="timeline-time-col">
                        <span class="timeline-time">{{ $timeFormatted }}</span>
                        @if ($dateFormatted)
                            <span class="timeline-date-sub">{{ $dateFormatted }}</span>
                        @endif
                    </div>
                    <div class="timeline-indicator">
                        <div class="timeline-circle {{ $circleColor }}"></div>
                        <div class="timeline-line"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title-row">
                            @if ($status === 'Paid')
                                {!! __('dashboard.payment_received_from', [
                                    'name' => '<span class="tx-name">' . e($customerDisplay) . '</span>',
                                    'amount' => '<span class="tx-amount tx-amount-paid">$' . number_format($tx->total_price, 2) . '</span>'
                                ]) !!}
                            @elseif ($status === 'Partial')
                                {!! __('dashboard.partial_payment_from', [
                                    'name' => '<span class="tx-name">' . e($customerDisplay) . '</span>',
                                    'amount' => '<span class="tx-amount tx-amount-partial">$' . number_format($tx->paid_amount, 2) . '</span>'
                                ]) !!}
                            @elseif ($status === 'Cancel')
                                @lang('dashboard.order_cancelled') - <span class="tx-amount tx-amount-cancel">${{ number_format($tx->total_price, 2) }}</span>
                            @else
                                @lang('dashboard.new_sale_recorded')
                                @if ($rawCustomer)
                                    - <span class="tx-name">{{ $rawCustomer }}</span>
                                @endif
                                - <span class="tx-amount">${{ number_format($tx->total_price, 2) }}</span>
                            @endif
                        </div>
                        <div class="timeline-meta-bar">
                            <a @can('order-view') href="{{ $orderUrl }}" @endcan class="timeline-order-pill">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                {{ $invoiceNumber }}
                            </a>
                            <span class="timeline-status-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                            @if (!empty($tx->shop?->name))
                                <span class="timeline-shop-tag">&bull; {{ $tx->shop->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="timeline-empty-state">
                    <div class="empty-icon-circle">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <p>@lang('dashboard.no_recent_transactions')</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 13. Product Performance -->
    <div class="dash-card">
        <div class="table-header-flex">
            <h3 class="dash-card-title">@lang('dashboard.product_performance')</h3>
            <select class="select-month-dropdown" data-product-month aria-label="{{ __('dashboard.product_performance') }}">
                <option value="all" {{ ($selectedProductMonth ?? 'all') === 'all' ? 'selected' : '' }}>@lang('dashboard.all_time')</option>
                @foreach ($productPerformanceMonths ?? [] as $m)
                    <option value="{{ $m['value'] }}" {{ ($selectedProductMonth ?? '') === $m['value'] ? 'selected' : '' }}>
                        {{ $m['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="product-table-wrapper">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>@lang('dashboard.table.product')</th>
                        <th>@lang('dashboard.table.progress')</th>
                        <th>@lang('dashboard.table.priority')</th>
                        <th>@lang('dashboard.table.budget')</th>
                        <th>@lang('dashboard.table.chart')</th>
                    </tr>
                </thead>
                <tbody data-product-performance-rows>
                    @include('admin::pages.dashboard_product_performance_rows')
                </tbody>
            </table>
        </div>
    </div>

</div>
@endif
