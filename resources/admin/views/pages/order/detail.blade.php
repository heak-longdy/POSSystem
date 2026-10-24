@extends('admin::shared.layout')

@section('title')
    | {{ __('order.detail.title') }} #{{ $order->invoice_number ?: $order->id }}
@stop

@section('layout')
    @include('admin::shared.header', ['header_name' => __('order.detail.title')])

    <div class="content-wrapper order-detail-wrapper booking-detail-wrapper order-details-refresh" id="bookingDetailApp" x-data="xOrderDetail()" :class="'printing-' + activePrintTemplate + ' print-lang-' + printLanguage" x-cloak>
        <div class="content-body" id="bookingDetailContentBody">
            <div class="booking-detail-page-wrapper">
                <!-- Top Navigation / Breadcrumb -->
                <div class="detail-breadcrumb">
                    <a href="{{ route('admin-' . ($routeName ?? 'order') . '-list', 'Pending') }}" class="breadcrumb-link">
                        <i data-feather="calendar"></i>
                        <span>{{ __('order.title') }}</span>
                    </a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">{{ __('order.detail.title') }}</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-invoice">#{{ $order->invoice_number ?: $order->id }}</span>
                </div>

                <!-- Main Detail Header Banner -->
                <header class="detail-header-card">
                    <div class="detail-header-top">
                        <div class="header-left">
                            <p class="detail-eyebrow">{{ __('order.detail.title') }}</p>
                            <div class="invoice-badge-title">
                                <h1 class="invoice-title">#{{ $order->invoice_number ?: $order->id }}</h1>
                                @php
                                    $paymentStatus = $order->payment_status ?: 'Pending';
                                    $statusClass = match ($paymentStatus) {
                                        'Paid' => 'status-paid',
                                        'Partial' => 'status-partial',
                                        'Cancel' => 'status-cancel',
                                        default => 'status-pending',
                                    };
                                    $statusLabel = match ($paymentStatus) {
                                        'Paid' => __('order.status.paid'),
                                        'Partial' => __('order.status.partial'),
                                        'Cancel' => __('order.status.rejected'),
                                        default => __('order.status.pending'),
                                    };

                                    $customerName = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer'));
                                    $customerPhone = $order->customer?->phone ?: '---';
                                    $customerEmail = $order->customer?->email ?: null;
                                    $customerAddress = $order->customer?->address ?: null;
                                    $customerInitials = strtoupper(substr($customerName, 0, 2));
                                @endphp
                                <span class="detail-status-pill {{ $statusClass }}">
                                    <span class="status-dot"></span>
                                    {{ $statusLabel }}
                                </span>

                                @if ($order->trashed())
                                    <span class="detail-status-pill status-trash">
                                        <i data-feather="trash-2"></i>
                                        {{ __('order.tab.trash') }}
                                    </span>
                                @endif
                            </div>
                            <p class="invoice-subtitle">
                                <span><i data-feather="clock"></i> {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') : '---' }}</span>
                                @if ($order->shop)
                                    <span class="meta-sep">•</span>
                                    <span><i data-feather="home"></i> {{ $order->shop->name }}</span>
                                @endif
                            </p>
                        </div>

                        <!-- Primary order actions -->
                        <div class="header-right-actions">
                            @if ($canAddPayment)
                                <button type="button" class="btn btn-create bg-success btn-system btn-system-success" :disabled="paymentSubmitting" @click="openPaymentModal = true">
                                    <i data-feather="plus-circle"></i>
                                    <span>{{ __('order.detail.add_payment') }}</span>
                                </button>
                            @endif

                            @if ($canEdit)
                                <a href="{{ route('admin-' . ($routeName ?? 'order') . '-edit', $order->id) }}" class="btn btn-create bg-primary btn-system btn-system-primary">
                                    <i data-feather="edit-2"></i>
                                    <span>{{ __('order.detail.edit_order') }}</span>
                                </a>
                            @endif

                            @if ($canReject ?? $canCancel)
                                <button type="button" class="btn btn-create bg-danger btn-system btn-system-danger" @click="rejectOrder()">
                                    <i data-feather="x-circle"></i>
                                    <span>{{ __('order.action.reject_order') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="detail-header-tools">
                        <div class="detail-print-actions">
                            <!-- Print Language Switcher -->
                            <div class="print-lang-switch-box" title="{{ __('order.invoice.select_language') }}">
                                <span class="print-lang-label">
                                    <i data-feather="globe"></i>
                                    <span>{{ __('order.invoice.print_language') }}:</span>
                                </span>
                                <div class="print-lang-segmented">
                                    <button type="button"
                                            class="print-lang-btn"
                                            :class="{ 'is-active': printLanguage === 'km' }"
                                            @click="setPrintLanguage('km')">
                                        <span class="flag">🇰🇭</span>
                                        <span>{{ __('order.invoice.khmer') }}</span>
                                    </button>
                                    <button type="button"
                                            class="print-lang-btn"
                                            :class="{ 'is-active': printLanguage === 'en' }"
                                            @click="setPrintLanguage('en')">
                                        <span class="flag">🇬🇧</span>
                                        <span>{{ __('order.invoice.english') }}</span>
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-system btn-system-outline" @click="printTemplate1()">
                                <i data-feather="printer"></i>
                                <span>{{ __('order.detail.print_invoice') }}</span>
                            </button>

                            <button type="button" class="btn btn-system btn-system-outline" @click="printTemplate2()">
                                <i data-feather="printer"></i>
                                <span>{{ __('order.detail.print_slip') }}</span>
                            </button>
                        </div>

                        <a href="{{ route('admin-' . ($routeName ?? 'order') . '-list', $order->payment_status ?: 'Pending') }}" class="btn btn-system btn-system-outline btn-system-neutral">
                            <i data-feather="arrow-left"></i>
                            <span>{{ __('order.detail.back_to_list') }}</span>
                        </a>
                    </div>
                </header>

                <!-- Financial overview -->
                <section class="detail-kpi-grid">
                    <article class="kpi-card">
                        <div class="kpi-icon-wrap kpi-blue">
                            <i data-feather="dollar-sign"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('order.detail.total_bill') }}</span>
                            <strong class="kpi-value">${{ number_format((float) ($order->total_price ?? 0), 2) }}</strong>
                        </div>
                    </article>

                    <article class="kpi-card">
                        <div class="kpi-icon-wrap kpi-green">
                            <i data-feather="check-circle"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('order.detail.paid_amount') }}</span>
                            <strong class="kpi-value text-success">${{ number_format((float) ($order->paid_amount ?? 0), 2) }}</strong>
                        </div>
                    </article>

                    <article class="kpi-card">
                        <div class="kpi-icon-wrap {{ $paymentRemainingAmount > 0 ? 'kpi-orange' : 'kpi-gray' }}">
                            <i data-feather="credit-card"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('order.detail.balance_due') }}</span>
                            <strong class="kpi-value {{ $paymentRemainingAmount > 0 ? 'text-danger' : 'text-muted' }}">
                                ${{ number_format($paymentRemainingAmount, 2) }}
                            </strong>
                        </div>
                    </article>
                </section>

                <!-- Order activity and supporting details -->
                <div class="detail-columns-layout">
                    <!-- Left Main Column (70%) -->
                    <div class="detail-col-main">
                        <!-- Ordered Products Table -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="package"></i>
                                    <h2>{{ __('order.detail.item_details') }}</h2>
                                    <span class="count-badge">{{ $order->orderDetails ? $order->orderDetails->count() : 0 }}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="detail-data-table detail-products-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>{{ __('order.table.item') }}</th>
                                            <th class="text-right">{{ __('order.detail.unit_price') }}</th>
                                            <th class="text-center">{{ __('order.detail.quantity') }}</th>
                                            <th class="text-right">{{ __('order.detail.discount') }}</th>
                                            <th class="text-right">{{ __('order.detail.line_total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($order->orderDetails as $index => $detail)
                                            @php
                                                $isService = $detail->type === 'service';
                                                $rawItemName = $isService
                                                    ? ($detail->service?->name ?? '---')
                                                    : ($detail->product?->name ?? '---');
                                                $itemCode = ! $isService ? ($detail->product?->code ?? null) : null;
                                                $itemName = ($itemCode && ! str_starts_with($rawItemName, $itemCode . ' - '))
                                                    ? ($itemCode . ' - ' . $rawItemName)
                                                    : $rawItemName;
                                                $unitPrice = (float) ($detail->price ?? 0);
                                                $qty = (int) ($detail->qty ?: 1);
                                                $line = app(\App\Services\OrderPricing::class)->line($detail);
                                                $discount = $line['discount'];
                                                $lineSubtotal = $line['total'];
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="item-name-cell">
                                                        <strong>{{ $itemName }}</strong>
                                                    </div>
                                                </td>
                                                <td class="text-right">${{ number_format($unitPrice, 2) }}</td>
                                                <td class="text-center">{{ $qty }}</td>
                                                <td class="text-right">
                                                    @if ($discount > 0)
                                                        <span class="text-danger">${{ number_format($discount, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">$0.00</span>
                                                    @endif
                                                </td>
                                                <td class="text-right font-weight-bold">${{ number_format($lineSubtotal, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    {{ __('order.empty_cart.title') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Payment History Audit Log -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="file-text"></i>
                                    <h2>{{ __('order.detail.payment_history') }}</h2>
                                    <span class="count-badge">{{ $order->payments ? $order->payments->count() : 0 }}</span>
                                </div>
                                @if ($canAddPayment)
                                    <button type="button" class="btn btn-create bg-success btn-system-sm btn-system-success" :disabled="paymentSubmitting" @click="openPaymentModal = true">
                                        <i data-feather="plus"></i>
                                        <span>{{ __('order.detail.add_payment') }}</span>
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="detail-data-table detail-payments-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>{{ __('order.detail.transaction_date') }}</th>
                                            <th>{{ __('order.detail.method') }}</th>
                                            <th class="text-right">{{ __('order.table.paid') }}</th>
                                            <th>{{ __('order.detail.cashier') }}</th>
                                            <th>{{ __('order.table.note') }}</th>
                                            <th class="text-center" style="width: 70px;">{{ __('global.table.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($order->payments as $pIdx => $pay)
                                            @php
                                                $method = $pay->payment_method ?: 'Cash';
                                                $methodClass = match(strtolower($method)) {
                                                    'cash' => 'chip-cash',
                                                    'aba' => 'chip-aba',
                                                    'card' => 'chip-card',
                                                    'qr' => 'chip-qr',
                                                    default => 'chip-default'
                                                };
                                            @endphp
                                            <tr>
                                                <td>{{ $pIdx + 1 }}</td>
                                                <td>
                                                    <span>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M Y, h:i A') : ($pay->created_at ? \Carbon\Carbon::parse($pay->created_at)->format('d M Y, h:i A') : '---') }}</span>
                                                </td>
                                                <td>
                                                    <span class="payment-chip {{ $methodClass }}">{{ $method }}</span>
                                                </td>
                                                <td class="text-right font-weight-bold text-success">${{ number_format((float) $pay->amount, 2) }}</td>
                                                <td>{{ $pay->createdBy?->name ?: 'Staff' }}</td>
                                                <td>
                                                    <span class="text-muted">{{ $pay->note ?: '---' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($order->payment_status !== 'Cancel')
                                                        @can('order-delete')
                                                            <button type="button"
                                                                class="btn-action-delete"
                                                                title="{{ __('order.action.delete_payment') }}"
                                                                @click="deletePaymentRecord({{ $pay->id }}, '{{ number_format((float)$pay->amount, 2) }}', '{{ $method }}')">
                                                                <i data-feather="trash-2"></i>
                                                            </button>
                                                        @endcan
                                                    @else
                                                        <span class="text-muted">---</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <div class="detail-empty-state">
                                                        <span class="empty-state-icon" aria-hidden="true"><i data-feather="file-text"></i></span>
                                                        <p>{{ __('order.detail.no_payments') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Order Remarks / Internal Notes -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="message-square"></i>
                                    <h2>{{ __('order.detail.remarks') }}</h2>
                                </div>
                            </div>
                            <div class="remark-content-box">
                                @if ($order->remark)
                                    <p class="remark-text">{{ $order->remark }}</p>
                                @else
                                    <p class="remark-empty">{{ __('order.detail.no_remarks') }}</p>
                                @endif
                            </div>
                        </section>
                    </div>

                    <!-- Right Sidebar Column (30%) -->
                    <div class="detail-col-sidebar">
                        <!-- Customer Profile Card -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="user"></i>
                                    <h2>{{ __('order.detail.customer_profile') }}</h2>
                                </div>
                            </div>
                            <div class="customer-profile-body">
                                @php
                                    $customerName = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer'));
                                    $customerPhone = $order->customer?->phone ?: '---';
                                    $customerEmail = $order->customer?->email ?: null;
                                    $customerAddress = $order->customer?->address ?: null;
                                    $customerInitials = strtoupper(substr($customerName, 0, 2));
                                @endphp
                                <div class="customer-avatar-header">
                                    <div class="customer-avatar-circle">
                                        <span>{{ $customerInitials }}</span>
                                    </div>
                                    <div class="customer-title-info">
                                        <h3>{{ $customerName }}</h3>
                                        <span class="customer-phone">{{ $customerPhone }}</span>
                                    </div>
                                </div>

                                <ul class="customer-info-list">
                                    <li>
                                        <span class="info-label"><i data-feather="phone"></i> {{ __('order.detail.phone') }}:</span>
                                        <span class="info-val">{{ $customerPhone }}</span>
                                    </li>
                                    @if ($customerEmail)
                                        <li>
                                            <span class="info-label"><i data-feather="mail"></i> {{ __('order.detail.email') }}:</span>
                                            <span class="info-val">{{ $customerEmail }}</span>
                                        </li>
                                    @endif
                                    @if ($customerAddress)
                                        <li>
                                            <span class="info-label"><i data-feather="map-pin"></i> {{ __('order.detail.address') }}:</span>
                                            <span class="info-val">{{ $customerAddress }}</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </section>

                        <!-- Appointment & Shop Details Card -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="calendar"></i>
                                    <h2>{{ __('order.detail.appointment_details') }}</h2>
                                </div>
                            </div>
                            <div class="appointment-body">
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="home"></i> {{ __('order.detail.shop') }}:</span>
                                    <span class="val font-weight-bold">{{ $order->shop?->name ?: '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="clock"></i> {{ __('order.detail.appointment_time') }}:</span>
                                    <span class="val">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') : '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="truck"></i> {{ __('order.detail.delivery_date') }}:</span>
                                    <span class="val font-weight-bold">{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') : ($order->order_date ? \Carbon\Carbon::parse($order->order_date)->addDays(2)->format('d M Y') : '---') }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="plus-square"></i> {{ __('order.detail.created_at') }}:</span>
                                    <span class="val">{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') : '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="user"></i> {{ __('order.detail.created_by') }}:</span>
                                    <span class="val">{{ $order->createdBy?->name ?: '---' }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Billing Summary Card -->
                        <section class="detail-card billing-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="credit-card"></i>
                                    <h2>{{ __('order.detail.billing_summary') }}</h2>
                                </div>
                            </div>
                            <div class="billing-body">
                                @php
                                    $commission = (float) ($order->total_commission ?? 0);
                                    $subtotal = (float) ($order->total_price ?? 0) + (float) ($order->total_discount ?? 0) + $commission;
                                    $discount = (float) ($order->total_discount ?? 0);
                                    $grandTotal = (float) ($order->total_price ?? 0);
                                    $paid = (float) ($order->paid_amount ?? 0);
                                    $remaining = $paymentRemainingAmount;
                                @endphp
                                <div class="billing-row">
                                    <span>{{ __('order.detail.subtotal') }}</span>
                                    <span>${{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="billing-row">
                                    <span>{{ __('order.detail.discount') }}</span>
                                    <span class="text-danger">${{ number_format($discount, 2) }}</span>
                                </div>
                                @if ($commission != 0)
                                    <div class="billing-row">
                                        <span>{{ __('order.commission') }}</span>
                                        <span class="text-danger">- ${{ number_format($commission, 2) }}</span>
                                    </div>
                                @endif
                                <div class="billing-divider"></div>
                                <div class="billing-row billing-grand-total">
                                    <span>{{ __('order.detail.grand_total') }}</span>
                                    <span>${{ number_format($grandTotal, 2) }}</span>
                                </div>
                                <div class="billing-row billing-paid">
                                    <span>{{ __('order.detail.paid_amount') }}</span>
                                    <span class="text-success">${{ number_format($paid, 2) }}</span>
                                </div>
                                <div class="billing-divider"></div>
                                <div class="billing-row billing-due">
                                    <span>{{ __('order.detail.balance_due') }}</span>
                                    <span class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format($remaining, 2) }}
                                    </span>
                                </div>

                                @if ($canAddPayment)
                                    <button type="button" class="btn btn-create bg-success btn-system btn-system-success btn-full-width mt-3" :disabled="paymentSubmitting" @click="openPaymentModal = true">
                                        <i data-feather="check-circle"></i>
                                        <span>{{ __('order.button.make_payment') }}</span>
                                    </button>
                                @endif
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Add Payment Modal (Alpine.js) -->
                <div class="modal-backdrop-custom" x-show="openPaymentModal" x-transition.opacity style="display: none;">
                    <div class="modal-dialog-custom" @click.away="if (!paymentSubmitting) openPaymentModal = false">
                        <div class="modal-header-custom">
                            <div class="modal-title-wrap">
                                <i data-feather="plus-circle" class="text-success"></i>
                                <h3>{{ __('order.detail.add_payment') }}</h3>
                            </div>
                            <button type="button" class="btn-close-modal" :disabled="paymentSubmitting" @click="openPaymentModal = false">
                                &times;
                            </button>
                        </div>

                        <form @submit.prevent="submitPayment()" :aria-busy="paymentSubmitting">
                            <fieldset class="modal-body-custom" :disabled="paymentSubmitting" style="border: 0; margin: 0; min-width: 0;">
                                <div class="form-group-modal">
                                    <label>{{ __('order.amount') }} ($) <span class="text-danger">*</span></label>
                                    <div class="input-action-wrap">
                                        <input type="number" step="0.01" min="0.01" max="{{ number_format($paymentRemainingAmount, 2, '.', '') }}"
                                            class="modal-input" x-model="paymentForm.amount" required>
                                        <button type="button" class="btn-fill-max" @click="paymentForm.amount = {{ $paymentRemainingAmount }}">
                                            {{ __('order.button.full_balance') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('order.detail.method') }}</label>
                                    <div class="payment-method-selector">
                                        <label class="method-option" :class="paymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                            <input type="radio" value="Cash" x-model="paymentForm.payment_method">
                                            <i data-feather="dollar-sign"></i>
                                            <span>{{ __('order.payment.cash') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                            <input type="radio" value="ABA" x-model="paymentForm.payment_method">
                                            <i data-feather="credit-card"></i>
                                            <span>{{ __('order.payment.aba') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                            <input type="radio" value="Card" x-model="paymentForm.payment_method">
                                            <i data-feather="server"></i>
                                            <span>{{ __('order.payment.card') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                            <input type="radio" value="QR" x-model="paymentForm.payment_method">
                                            <i data-feather="maximize"></i>
                                            <span>{{ __('order.payment.qr') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('order.table.pay_date') }}</label>
                                    <input type="datetime-local" class="modal-input" x-model="paymentForm.payment_date">
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('order.note') }}</label>
                                    <textarea class="modal-textarea" rows="3" x-model="paymentForm.note"
                                        placeholder="{{ __('order.placeholder.note') }}"></textarea>
                                </div>
                            </fieldset>

                            <div class="modal-footer-custom">
                                <button type="button" class="btn btn-system btn-system-outline btn-system-neutral" :disabled="paymentSubmitting" @click="openPaymentModal = false">
                                    <span>{{ __('order.button.close') }}</span>
                                </button>
                                <button type="submit" class="btn btn-create bg-success btn-system btn-system-success" :disabled="paymentSubmitting" :aria-busy="paymentSubmitting">
                                    <span class="bx bx-loader-alt bx-spin" x-show="paymentSubmitting" style="display: none;" aria-hidden="true"></span>
                                    <span aria-live="polite" x-text="paymentSubmitting ? @js(__('order.button.processing_payment')) : @js(__('order.detail.add_payment'))"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- Print Only Invoice View - Template 1 (Matching design in invoice_01.png) -->
                <div class="print-only-invoice print-template-1 print-lang-km">
                    @include('admin::pages.order.invoice-template', ['printLocale' => 'km'])
                </div>
                <div class="print-only-invoice print-template-1 print-lang-en">
                    @include('admin::pages.order.invoice-template', ['printLocale' => 'en'])
                </div>

                <!-- Print Only Invoice View - Template 2 (Matching design in invoice_02.png) -->
                <div class="print-only-invoice print-template-2 print-lang-km">
                    @include('admin::pages.order.invoice-template-02', ['printLocale' => 'km'])
                </div>
                <div class="print-only-invoice print-template-2 print-lang-en">
                    @include('admin::pages.order.invoice-template-02', ['printLocale' => 'en'])
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <style>
        .content-wrapper.booking-detail-wrapper {
            flex: 1 1 auto !important;
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
            display: flex !important;
            flex-direction: column !important;
            position: relative !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .booking-detail-wrapper .content-body {
            flex: 1 1 auto !important;
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            padding: 0 !important;
        }

        /* Scoped Detail Page Styling using System Tokens */
        .booking-detail-page-wrapper {
            padding: 24px 32px 80px 32px;
            max-width: 1400px;
            margin: 0 auto;
            color: #231f20;
            font-family: inherit;
        }

        /* Breadcrumb */
        .detail-breadcrumb {
            position: static !important;
            top: auto !important;
            left: auto !important;
            z-index: auto !important;
            height: auto !important;
            min-height: unset !important;
            max-height: unset !important;
            padding: 0 !important;
            grid-gap: 8px !important;
            gap: 8px !important;
            display: flex !important;
            align-items: center !important;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 16px;
            background: transparent !important;
        }
        .detail-breadcrumb a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #3C91E6;
            text-decoration: none;
            font-weight: 500;
        }
        .detail-breadcrumb a:hover {
            text-decoration: underline;
        }
        .detail-breadcrumb svg {
            width: 14px;
            height: 14px;
        }
        .detail-breadcrumb .breadcrumb-sep {
            color: #cbd5e1;
        }
        .detail-breadcrumb .breadcrumb-invoice {
            font-weight: 600;
            color: #1e293b;
        }

        /* Header Card */
        .detail-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .invoice-badge-title {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .invoice-subtitle {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #64748b;
        }
        .invoice-subtitle svg {
            width: 14px;
            height: 14px;
            vertical-align: -2px;
        }
        .meta-sep {
            color: #cbd5e1;
        }

        /* Status Pills */
        .detail-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }
        .status-paid {
            background: #dcfce7;
            color: #15803d;
        }
        .status-paid .status-dot {
            background: #16a34a;
        }
        .status-partial {
            background: #e0f2fe;
            color: #0369a1;
        }
        .status-partial .status-dot {
            background: #0284c7;
        }
        .status-pending {
            background: #fef3c7;
            color: #b45309;
        }
        .status-pending .status-dot {
            background: #d97706;
        }
        .status-cancel {
            background: #fee2e2;
            color: #b91c1c;
        }
        .status-cancel .status-dot {
            background: #dc2626;
        }
        .status-trash {
            background: #f1f5f9;
            color: #64748b;
        }
        .status-trash svg {
            width: 12px;
            height: 12px;
        }

        /* System Button Styling (following system button.scss and header.scss) */
        .header-right-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .header-right-actions .btn,
        .btn-system {
            height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            grid-gap: 7px !important;
            gap: 7px !important;
            padding: 0 16px !important;
            border-radius: 20px !important; /* System standard rounded pill from button.scss */
            font-size: 13px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            text-transform: none !important;
            border: unset !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            transition: all 0.2s ease !important;
            line-height: 1 !important;
            border: 1px solid #e2e8f0 !important;
        }

        .header-right-actions .btn svg,
        .btn-system svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 2 !important;
            line-height: 0 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        .header-right-actions .btn span,
        .btn-system span {
            font-size: 13px !important;
            line-height: normal !important;
        }

        /* Success Green Button ($success: #00b74a / button.scss .btn-create) */
        .btn-system-success,
        .header-right-actions .btn.bg-success,
        .header-right-actions .btn-create.bg-success {
            background-color: #00b74a !important;
            color: #ffffff !important;
            border: unset !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.1) !important;
        }
        .btn-system-success:hover:not(:disabled),
        .header-right-actions .btn.bg-success:hover:not(:disabled),
        .header-right-actions .btn-create.bg-success:hover:not(:disabled) {
            background-color: #009e40 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 183, 74, 0.3) !important;
        }

        /* Brand Blue Button ($defaultColor: #3C91E6 / header.scss .btn-create) */
        .btn-system-primary,
        .header-right-actions .btn.bg-primary,
        .header-right-actions .btn-create.bg-primary {
            background-color: #3C91E6 !important;
            color: #ffffff !important;
            border: unset !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.1) !important;
        }
        .btn-system-primary:hover:not(:disabled),
        .header-right-actions .btn.bg-primary:hover:not(:disabled),
        .header-right-actions .btn-create.bg-primary:hover:not(:disabled) {
            background-color: #2b7bc9 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(60, 145, 230, 0.3) !important;
        }

        /* Standard Outline Button (matching .header-tab .header-action-button button) */
        .btn-system-outline,
        .btn-system-neutral {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid rgba(152, 152, 152, 0.25) !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.05) !important;
        }
        .btn-system-outline:hover:not(:disabled),
        .btn-system-neutral:hover:not(:disabled) {
            background-color: #f5f5f5 !important;
            border-color: rgba(152, 152, 152, 0.45) !important;
            color: #111111 !important;
        }

        /* Danger Red Button ($danger: #ff3838 / app.css .bg-danger) */
        .btn-system-danger,
        .header-right-actions .btn.bg-danger,
        .header-right-actions .btn-create.bg-danger {
            background-color: #ff3838 !important;
            color: #ffffff !important;
            border: unset !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.1) !important;
        }
        .btn-system-danger:hover:not(:disabled),
        .header-right-actions .btn.bg-danger:hover:not(:disabled),
        .header-right-actions .btn-create.bg-danger:hover:not(:disabled) {
            background-color: #e02828 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(255, 56, 56, 0.3) !important;
        }

        /* Print Language Switcher */
        .print-lang-switch-box {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            height: 40px;
            padding: 3px 4px 3px 10px;
            gap: 8px;
            box-sizing: border-box;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .print-lang-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        .print-lang-label svg {
            width: 15px;
            height: 15px;
            stroke-width: 2;
            color: #3C91E6;
        }

        .print-lang-segmented {
            display: inline-flex;
            align-items: center;
            background: #e2e8f0;
            border-radius: 16px;
            padding: 2px;
            gap: 2px;
        }

        .print-lang-btn {
            border: none;
            background: transparent;
            border-radius: 14px;
            height: 28px;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            white-space: nowrap;
            line-height: 1;
        }

        .print-lang-btn:hover {
            color: #0f172a;
        }

        .print-lang-btn.is-active {
            background: #3C91E6;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(60, 145, 230, 0.4);
            border-radius: 20px !important;
        }

        .print-lang-btn .flag {
            font-size: 13px;
            line-height: 1;
        }

        @media (max-width: 768px) {
            .print-lang-label span {
                display: none;
            }
            .print-lang-switch-box {
                padding-left: 8px;
            }
        }

        /* Compact Button (for small cards) */
        .btn-system-sm {
            height: 40px !important;
            padding: 0 12px !important;
            font-size: 12px !important;
            border-radius: 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            border: unset !important;
            line-height: 1 !important;
        }
        .btn-system-sm svg {
            width: 14px !important;
            height: 14px !important;
        }
        .btn-full-width {
            width: 100% !important;
        }
        .btn-action-delete {
            width: 30px !important;
            height: 30px !important;
            min-width: unset !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            border: 1px solid #fee2e2 !important;
            background: #fef2f2 !important;
            color: #ef4444 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        .btn-action-delete:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
            transform: scale(1.05);
        }
        .btn-action-delete svg {
            width: 14px !important;
            height: 14px !important;
        }

        /* Top 3 KPI Grid */
        .detail-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: transform 0.15s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
        }
        .kpi-icon-wrap {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon-wrap svg {
            width: 22px;
            height: 22px;
        }
        .kpi-blue {
            background: #eff6ff;
            color: #3C91E6;
        }
        .kpi-green {
            background: #ecfdf5;
            color: #00c753;
        }
        .kpi-orange {
            background: #fff7ed;
            color: #ea580c;
        }
        .kpi-gray {
            background: #f1f5f9;
            color: #94a3b8;
        }
        .kpi-purple {
            background: #faf5ff;
            color: #8b5cf6;
        }
        .kpi-info {
            display: flex;
            flex-direction: column;
        }
        .kpi-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }

        /* 2-Column Layout */
        .detail-columns-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            align-items: start;
        }

        /* Detail Card Base */
        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .detail-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fcfcfd;
        }
        .card-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-title-group svg {
            width: 18px;
            height: 18px;
            color: #3C91E6;
        }
        .card-title-group h2 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .count-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Data Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .detail-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .detail-data-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }
        .detail-data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .detail-data-table tr:hover td {
            background: #fafbfc;
        }
        .item-name-cell {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .point-badge {
            display: inline-block;
            color: #2563eb;
            font-size: 11px;
            font-weight: 600;
        }
        .type-pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }
        .pill-service {
            background: #e0f2fe;
            color: #0284c7;
        }
        .pill-product {
            background: #fef3c7;
            color: #d97706;
        }
        .payment-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .chip-cash {
            background: #dcfce7;
            color: #15803d;
        }
        .chip-aba {
            background: #e0f2fe;
            color: #0284c7;
        }
        .chip-card {
            background: #f3e8ff;
            color: #7e22ce;
        }
        .chip-qr {
            background: #fef3c7;
            color: #b45309;
        }
        .chip-default {
            background: #f1f5f9;
            color: #475569;
        }

        /* Remarks Box */
        .remark-content-box {
            padding: 20px;
        }
        .remark-text {
            margin: 0;
            font-size: 14px;
            color: #334155;
            line-height: 1.6;
            background: #f8fafc;
            border-left: 3px solid #3C91E6;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
        }
        .remark-empty {
            margin: 0;
            font-size: 13px;
            color: #94a3b8;
            font-style: italic;
        }

        /* Customer Profile Card */
        .customer-profile-body {
            padding: 20px;
        }
        .customer-avatar-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .customer-avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3C91E6, #1d4ed8);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .customer-title-info h3 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .customer-phone {
            font-size: 13px;
            color: #64748b;
        }
        .customer-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .customer-info-list li {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 13px;
        }
        .info-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
        }
        .info-label svg {
            width: 14px;
            height: 14px;
        }
        .info-val {
            font-weight: 600;
            color: #1e293b;
            text-align: left;
        }
        .points-tag {
            background: #eff6ff;
            color: #2563eb;
            padding: 2px 8px;
            border-radius: 6px;
        }

        /* Appointment Card */
        .appointment-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .appointment-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .appointment-info-row .label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
        }
        .appointment-info-row .label svg {
            width: 14px;
            height: 14px;
        }
        .appointment-info-row .val {
            color: #1e293b;
            text-align: right;
        }

        /* Billing Summary Card */
        .billing-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .billing-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #475569;
        }
        .billing-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 4px 0;
        }
        .billing-grand-total {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .billing-paid {
            font-weight: 600;
        }
        .billing-due {
            font-size: 16px;
            font-weight: 700;
        }

        /* Modal Styles */
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-dialog-custom {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header-custom {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-title-wrap h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .btn-close-modal {
            background: none !important;
            border: none !important;
            font-size: 24px !important;
            line-height: 1 !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            min-width: 0 !important;
            padding: 0 !important;
        }
        .btn-close-modal:hover {
            color: #334155;
            background: none !important;
        }
        .modal-body-custom {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group-modal label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .modal-input, .modal-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .modal-input:focus, .modal-textarea:focus {
            border-color: #3C91E6;
            box-shadow: 0 0 0 3px rgba(60, 145, 230, 0.15);
        }
        .input-action-wrap {
            display: flex;
            gap: 8px;
        }
        .btn-fill-max {
            background: #ffffff;
            border: 1px solid rgba(152, 152, 152, 0.25);
            border-radius: 20px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 500;
            color: #333333;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .btn-fill-max:hover {
            background: #f5f5f5;
            color: #111111;
        }
        .payment-method-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .method-option {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            transition: all 0.15s;
        }
        .method-option input {
            display: none;
        }
        .method-option svg {
            width: 16px;
            height: 16px;
        }
        .method-option.is-active {
            border-color: #00c753;
            background: #ecfdf5;
            color: #00c753;
        }
        .modal-footer-custom {
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
        }



        @include('admin::pages.order.partials.print-document-styles')

        /* Print Media Queries */
        .print-only-invoice {
            display: none;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 4mm 6mm;
            }

            *, *::before, *::after {
                box-sizing: border-box !important;
            }

            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* 1. Explicitly hide sidebars, headers, app layouts, modals, buttons */
            #sidebar,
            .sidebar,
            .header,
            #content > .header,
            #workspace-tab-bar,
            .mdi-workspace-tabs-container,
            .workspace-tab-bar,
            .mdi-tooltip-popup,
            .mdi-context-menu,
            .mdi-overflow-dropdown,
            .mdi-tab-launcher-modal,
            .detail-breadcrumb,
            .detail-header-card,
            .detail-kpi-grid,
            .detail-columns-layout,
            .payment-modal-backdrop,
            .modal-backdrop-custom,
            #jsScroll,
            .scroll,
            .btn-system,
            .header-right-actions,
            #selectOption,
            #logoutModal,
            .iziToast-wrapper,
            .iziToast,
            nav,
            footer {
                display: none !important;
            }

            /* 2. Remove all layout offsets, widths, heights, paddings, and scrollbars from all ancestors */
            .container,
            .container-wrapper,
            .content,
            #content,
            .content-wrapper,
            #bookingDetailApp,
            .content-body,
            #bookingDetailContentBody,
            .booking-detail-page-wrapper,
            .workspace-viewport,
            #workspace-viewport,
            .workspace-tab-pane,
            #tab-pane-initial {
                display: block !important;
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                max-height: none !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
                float: none !important;
                background: #ffffff !important;
            }

            /* 3. Disable all WebKit scrollbars (completely eradicates the blue bar) */
            ::-webkit-scrollbar,
            *::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            /* 4. Display the invoice sheet cleanly in normal document flow */
            .print-only-invoice {
                display: block !important;
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #ffffff !important;
                box-shadow: none !important;
                border: none !important;
                overflow: hidden !important;
                visibility: visible !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .print-only-invoice * {
                visibility: visible !important;
            }

            /* Print template & language switching */
            .print-only-invoice {
                display: none !important;
            }

            #bookingDetailApp.printing-template1.print-lang-km .print-template-1.print-lang-km {
                display: block !important;
            }
            #bookingDetailApp.printing-template1.print-lang-en .print-template-1.print-lang-en {
                display: block !important;
            }

            #bookingDetailApp.printing-template2.print-lang-km .print-template-2.print-lang-km {
                display: block !important;
            }
            #bookingDetailApp.printing-template2.print-lang-en .print-template-2.print-lang-en {
                display: block !important;
            }

            /* Strict isolation: never allow the inactive template or language to show */
            #bookingDetailApp.printing-template1 .print-template-2,
            #bookingDetailApp.printing-template2 .print-template-1,
            #bookingDetailApp.print-lang-km .print-lang-en,
            #bookingDetailApp.print-lang-en .print-lang-km {
                display: none !important;
            }

            .print-template-2 .pos-slip-sheet {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
                overflow: hidden !important;
            }

            .pos-invoice-sheet {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .inv-grid-table,
            .slip-grid-table {
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .inv-grid-table tr,
            .slip-grid-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .detail-columns-layout {
                grid-template-columns: 1fr;
            }
            .detail-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .booking-detail-page-wrapper {
                padding: 16px;
            }
            .detail-kpi-grid {
                grid-template-columns: 1fr;
            }
            .header-right-actions {
                width: 100%;
            }
            .header-right-actions .btn-system,
            .header-right-actions .btn {
                flex: 1;
            }
        }

        @include('admin::pages.order.detail-styles')
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.feather) {
                feather.replace();
            }
        });

        function xOrderDetail() {
            return {
                openPaymentModal: false,
                activePrintTemplate: 'template1',
                printLanguage: '{{ app()->getLocale() === 'km' ? 'km' : 'en' }}',
                paymentSubmitting: false,
                paymentNoticeKey: 'order-payment-success:{{ $order->id }}',
                paymentForm: {
                    amount: {{ $paymentRemainingAmount }},
                    payment_method: 'Cash',
                    payment_date: '{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}',
                    note: ''
                },
                init() {
                    this.$nextTick(() => {
                        if (window.feather) {
                            feather.replace();
                        }
                        // Consume the notice once when a full-page refresh was needed.
                        try {
                            if (sessionStorage.getItem(this.paymentNoticeKey)) {
                                sessionStorage.removeItem(this.paymentNoticeKey);
                                this.notifyPayment('success', @json(__('order.message.payment_status_success')));
                            }
                        } catch (error) {
                            // Storage may be unavailable; the payment flow must still work.
                        }
                    });
                },
                setPrintLanguage(lang) {
                    this.printLanguage = lang;
                },
                notifyPayment(type, message) {
                    // Reuse the shared admin::components.iziToast configuration.
                    window.iziToast[type]({
                        ...window.property,
                        title: type === 'success' ? 'Success' : 'Error',
                        message: message
                    });
                },
                async submitPayment() {
                    if (this.paymentSubmitting) return;
                    if (!this.paymentForm.amount || Number(this.paymentForm.amount) <= 0) {
                        this.notifyPayment('error', 'Please enter a valid payment amount.');
                        return;
                    }

                    this.paymentSubmitting = true;
                    const url = '{{ route('admin-' . ($routeName ?? 'order') . '-add-payment', $order->id) }}';
                    const tabKey = this.$root.closest('.workspace-tab-pane')?.getAttribute('data-tab-key');
                    let response;

                    try {
                        response = await Axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            amount: this.paymentForm.amount,
                            payment_method: this.paymentForm.payment_method,
                            payment_date: this.paymentForm.payment_date,
                            note: this.paymentForm.note
                        });
                    } catch (error) {
                        this.paymentSubmitting = false;
                        const errorMsg = error.response?.data?.error ||
                            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                            'Failed to record payment.';
                        this.notifyPayment('error', errorMsg);
                        return;
                    }

                    if (!(response.data && (response.data.message === 'success' || response.status === 200))) {
                        this.paymentSubmitting = false;
                        this.notifyPayment('error', response.data?.error || 'Payment recording failed.');
                        return;
                    }

                    this.openPaymentModal = false;
                    const workspace = window.MDI;
                    const tab = workspace?.tabs.find(item => item.key === tabKey);
                    if (tab && typeof workspace.fetchAndMountTab === 'function') {
                        this.notifyPayment('success', @json(__('order.message.payment_status_success')));
                        // Keep the current content visible until its replacement is ready.
                        // Refresh the submitting tab even if the user has switched tabs.
                        this.$nextTick(() => workspace.fetchAndMountTab(tab));
                    } else if (this.$root.isConnected) {
                        try {
                            sessionStorage.setItem(this.paymentNoticeKey, '1');
                        } catch (error) {
                            this.notifyPayment('success', @json(__('order.message.payment_status_success')));
                        }
                        window.location.reload();
                    } else {
                        this.notifyPayment('success', @json(__('order.message.payment_status_success')));
                    }
                },
                printTemplate1(lang = null) {
                    if (lang) this.printLanguage = lang;
                    this.activePrintTemplate = 'template1';
                    const appEl = document.getElementById('bookingDetailApp');
                    if (appEl) {
                        appEl.classList.remove('printing-template2');
                        appEl.classList.add('printing-template1');
                        appEl.classList.remove('print-lang-km', 'print-lang-en');
                        appEl.classList.add('print-lang-' + this.printLanguage);
                    }
                    this.$nextTick(() => {
                        setTimeout(() => {
                            window.print();
                        }, 60);
                    });
                },
                printTemplate2(lang = null) {
                    if (lang) this.printLanguage = lang;
                    this.activePrintTemplate = 'template2';
                    const appEl = document.getElementById('bookingDetailApp');
                    if (appEl) {
                        appEl.classList.remove('printing-template1');
                        appEl.classList.add('printing-template2');
                        appEl.classList.remove('print-lang-km', 'print-lang-en');
                        appEl.classList.add('print-lang-' + this.printLanguage);
                    }
                    this.$nextTick(() => {
                        setTimeout(() => {
                            window.print();
                        }, 60);
                    });
                },
                rejectOrder() {
                    const rejectTemplate = "{{ __('order.confirm.reject', ['invoice' => '__INVOICE__']) }}";
                    const invoiceName = '{{ $order->invoice_number ?: '#' . $order->id }}';
                    this.$store.confirmDialog.open({
                        data: {
                            message: rejectTemplate.replace('__INVOICE__', '<b>' + invoiceName + '</b>'),
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: '{{ __('order.action.reject_order') }}',
                            btnSaveClass: 'bg-danger',
                            item: { id: {{ $order->id }} },
                            url: '{{ route('admin-' . ($routeName ?? 'order') . '-cancel', $order->id) }}',
                            typeAction: 'cancel',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "20rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                window.location.reload();
                            }
                        }
                    });
                },
                confirmCancel() {
                    this.rejectOrder();
                },
                deletePaymentRecord(paymentId, amount, method) {
                    const template = @json(__('order.confirm.delete_payment'));
                    const plainMessage = template
                        .replace(':amount', '$' + amount)
                        .replace(':method', method)
                        .replace(/<[^>]*>/g, '');

                    if (!confirm(plainMessage)) {
                        return;
                    }

                    const url = `{{ url('admin/order/delete-payment') }}/${paymentId}`;
                    Axios.delete(url, {
                        data: { _token: '{{ csrf_token() }}' }
                    }).then((res) => {
                        if (res.data && (res.data.message === 'success' || res.status === 200)) {
                            if (window.toastr) {
                                toastr.success(@json(__('order.message.delete_success')));
                            } else {
                                alert(@json(__('order.message.delete_success')));
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 400);
                        } else {
                            alert(res.data?.error || @json(__('order.message.delete_error')));
                        }
                    }).catch((err) => {
                        const message = err.response?.data?.error ||
                            Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
                            @json(__('order.message.delete_error'));
                        alert(message);
                    });
                }
            };
        }
    </script>
@stop
