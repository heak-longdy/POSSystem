@extends('admin::shared.layout')
@section('layout')
    @include('admin::pages.report.customerRemaining.styles')
    @include('admin::shared.header', ['header_name' => __('customer_remaining_report.details_title')])
    <div class="content-wrapper customer-remaining-report" x-data="{}"
        @workspace:tab-activated.window="if ($el.closest('.workspace-tab-pane')?.id === 'tab-pane-' + $event.detail.key) { window.MDI?.refreshTab($event.detail.key); }">
        @include('admin::pages.report.customerRemaining.header', ['detail' => true])
        <div class="content-body" style="padding: 14px 20px 24px 20px;">
            <div class="report-filter-panel report-customer-summary">
                <div>
                    <h3><i class="bx bx-user"></i> {{ $customer->name ?: __('customer_remaining_report.unnamed_customer', ['id' => $customer->id]) }}</h3>
                    <p class="report-description">{{ $customer->phone ?: '—' }}@if ($customer->address) · {{ $customer->address }}@endif</p>
                    @if ($customer->trashed())
                        <span class="method-tag">{{ __('customer_remaining_report.archived') }}</span>
                    @endif
                </div>
                <div class="report-current-balance">
                    <div class="kpi-icon-wrap danger"><i class="bx bx-wallet"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.current_balance') }}</div>
                        <div class="kpi-value text-danger">${{ number_format($history['current'] / 100, 2) }}</div>
                        <div class="kpi-sub">{{ __('customer_remaining_report.current_note') }}</div>
                    </div>
                </div>
            </div>
            @if ($errors->any())
                <div class="report-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="report-filter-panel">
                <form method="GET" action="{{ route('admin-report-customer-remaining-amount-details', $customer->id) }}">
                    <div class="filter-form-grid">
                        <div class="filter-field-wrap">
                            <label for="cr-from">{{ __('customer_remaining_report.from_date') }}</label>
                            <input id="cr-from" type="date" name="from_date" class="filter-input" value="{{ $filters['from_date'] ?? '' }}">
                        </div>
                        <div class="filter-field-wrap">
                            <label for="cr-to">{{ __('customer_remaining_report.to_date') }}</label>
                            <input id="cr-to" type="date" name="to_date" class="filter-input" value="{{ $filters['to_date'] ?? '' }}">
                        </div>
                        <div class="filter-field-wrap">
                            <label for="cr-detail-shop">{{ __('customer_remaining_report.shop') }}</label>
                            <select id="cr-detail-shop" name="shop_id" class="filter-select">
                                <option value="">{{ __('customer_remaining_report.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? '') == $shop->id)>{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions-wrap">
                            <button class="btn btn-filter-search" type="submit">
                                <i class="bx bx-search"></i>
                                <span>{{ __('customer_remaining_report.filter') }}</span>
                            </button>
                            <a class="btn btn-filter-reset" href="{{ route('admin-report-customer-remaining-amount-details', $customer->id) }}">
                                <i class="bx bx-reset"></i>
                                <span>{{ __('customer_remaining_report.reset') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon-wrap indigo"><i class="bx bx-wallet"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.opening_balance') }}</div>
                        <div class="kpi-value">${{ number_format($history['opening'] / 100, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap primary"><i class="bx bx-trending-up"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.increases') }}</div>
                        <div class="kpi-value text-primary">${{ number_format($history['increases'] / 100, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap success"><i class="bx bx-trending-down"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.decreases') }}</div>
                        <div class="kpi-value text-success">${{ number_format($history['decreases'] / 100, 2) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon-wrap danger"><i class="bx bx-time"></i></div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('customer_remaining_report.closing_balance') }}</div>
                        <div class="kpi-value text-danger">${{ number_format($history['closing'] / 100, 2) }}</div>
                    </div>
                </div>
            </div>
            <p class="report-description">{{ __('customer_remaining_report.period_note') }}</p>

            <div class="report-table-card">
                <div class="table-custom-header">
                    <h4><i class="bx bx-history"></i> {{ __('customer_remaining_report.details_title') }}</h4>
                    <span class="report-count">{{ __('customer_remaining_report.invoice_results', ['from' => $invoices->firstItem() ?? 0, 'to' => $invoices->lastItem() ?? 0, 'total' => number_format($invoices->total())]) }}</span>
                </div>
                <div class="table-responsive-custom">
                    <table class="report-data-table">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center" style="width: 45px;">{{ __('sales_report.table.no') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.date') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.type') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.status') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.amount') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.change') }}</th>
                                <th scope="col" class="text-right">{{ __('customer_remaining_report.running_balance') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.method') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.recorded_by') }}</th>
                                <th scope="col">{{ __('customer_remaining_report.note') }}</th>
                            </tr>
                        </thead>
                        @forelse ($invoices as $invoice)
                            <tbody>
                                <tr class="invoice-group-heading">
                                    <th colspan="10" scope="rowgroup">
                                        <div class="invoice-group-title">
                                            <span>
                                                <i class="bx bx-receipt"></i> {{ __('customer_remaining_report.invoice') }}:
                                                @can('order-view')
                                                    <a class="report-link" href="{{ route('admin-remaining-amount-detail', $invoice['order_id']) }}">{{ $invoice['invoice'] }}</a>
                                                @else
                                                    {{ $invoice['invoice'] }}
                                                @endcan
                                                @php
                                                    $invStatus = strtolower($invoice['payment_status'] ?? 'pending');
                                                    $invStatusClass = match($invStatus) {
                                                        'paid' => 'paid',
                                                        'partial' => 'partial',
                                                        'cancel', 'canceled' => 'cancel',
                                                        default => 'pending',
                                                    };
                                                    $invStatusLabel = __('customer_remaining_report.status_' . $invStatusClass);
                                                @endphp
                                                <span class="status-badge {{ $invStatusClass }}" style="margin-left: 6px;">{{ $invStatusLabel }}</span>
                                            </span>
                                            <span class="report-count">{{ __('customer_remaining_report.shop') }}: {{ $invoice['shop'] ?: '—' }}</span>
                                        </div>
                                        <div class="invoice-group-totals">
                                            <span>{{ __('customer_remaining_report.total_amount') }}: <strong>${{ number_format($invoice['total_amount'] / 100, 2) }}</strong></span>
                                            <span>{{ __('customer_remaining_report.paid_amount') }}: <strong>${{ number_format($invoice['paid_amount'] / 100, 2) }}</strong></span>
                                            <span>{{ __('customer_remaining_report.invoice_current_balance') }}: <strong>${{ number_format($invoice['current'] / 100, 2) }}</strong></span>
                                        </div>
                                    </th>
                                </tr>
                                <tr class="invoice-balance-row">
                                    <td colspan="6">{{ __('customer_remaining_report.opening_balance') }}</td>
                                    <td class="text-right">${{ number_format($invoice['opening'] / 100, 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                                @foreach ($invoice['rows'] as $transaction)
                                    @php
                                        $txStatus = strtolower($transaction['status'] ?? $transaction['payment_status'] ?? 'pending');
                                        $txStatusClass = match ($txStatus) {
                                            'paid' => 'paid',
                                            'partial' => 'partial',
                                            'cancel', 'canceled' => 'cancel',
                                            default => 'pending',
                                        };
                                        $txStatusLabel = __('customer_remaining_report.status_' . $txStatusClass);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="report-nowrap">{{ $transaction['date']->format('d M Y, H:i') }}</td>
                                        <td>
                                            <span class="status-badge {{ $transaction['type'] === 'order' ? 'today' : ($transaction['type'] === 'payment' ? 'paid' : 'partial') }}">
                                                {{ __('customer_remaining_report.' . $transaction['type']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $txStatusClass }}">
                                                {{ $txStatusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-right">${{ number_format(abs($transaction['amount']) / 100, 2) }}</td>
                                        <td class="text-right font-weight-bold {{ $transaction['change'] > 0 ? 'text-danger' : ($transaction['change'] < 0 ? 'text-success' : 'text-muted') }}">{{ $transaction['change'] > 0 ? '+' : ($transaction['change'] < 0 ? '−' : '') }}${{ number_format(abs($transaction['change']) / 100, 2) }}</td>
                                        <td class="text-right {{ $transaction['invoice_balance'] > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">${{ number_format($transaction['invoice_balance'] / 100, 2) }}</td>
                                        <td>
                                            @if ($transaction['method'])
                                                <span class="method-tag">{{ $transaction['method'] }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $transaction['created_by'] ?: '—' }}</td>
                                        <td class="report-note-cell">{{ $transaction['note'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="invoice-balance-row">
                                    <td colspan="6">{{ __('customer_remaining_report.closing_balance') }}</td>
                                    <td class="text-right {{ $invoice['closing'] > 0 ? 'text-danger' : 'text-muted' }}">${{ number_format($invoice['closing'] / 100, 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="10" class="empty-placeholder">
                                        <i class="bx bx-receipt"></i>
                                        <p>{{ __('customer_remaining_report.no_transactions') }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
                @include('admin::pages.report.customerRemaining.pagination', ['paginator' => $invoices])
            </div>
            <p class="report-note">{{ __('customer_remaining_report.history_note') }}</p>
            <p class="report-note">{{ __('customer_remaining_report.scope_note') }}</p>
        </div>
    </div>
@endsection
