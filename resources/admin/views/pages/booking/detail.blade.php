@extends('admin::shared.layout')

@section('title')
    | {{ __('booking.detail.title') }} #{{ $booking->invoice_number ?: $booking->id }}
@stop

@section('layout')
    @include('admin::shared.header', ['header_name' => __('booking.detail.title')])

    <div class="content-wrapper booking-detail-wrapper" id="bookingDetailApp" x-data="xBookingDetail()" :class="'printing-' + activePrintTemplate" x-cloak>
        <div class="content-body" id="bookingDetailContentBody">
            <div class="booking-detail-page-wrapper">
                <!-- Top Navigation / Breadcrumb -->
                <nav class="detail-breadcrumb">
                    <a href="{{ route('admin-booking-list', 'Pending') }}" class="breadcrumb-link">
                        <i data-feather="calendar"></i>
                        <span>{{ __('booking.title') }}</span>
                    </a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">{{ __('booking.detail.title') }}</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-invoice">#{{ $booking->invoice_number ?: $booking->id }}</span>
                </nav>

                <!-- Main Detail Header Banner -->
                <header class="detail-header-card">
                    <div class="header-left">
                        <div class="invoice-badge-title">
                            <h1 class="invoice-title">#{{ $booking->invoice_number ?: $booking->id }}</h1>
                            @php
                                $paymentStatus = $booking->payment_status ?: 'Pending';
                                $statusClass = match ($paymentStatus) {
                                    'Paid' => 'status-paid',
                                    'Partial' => 'status-partial',
                                    'Cancel' => 'status-cancel',
                                    default => 'status-pending',
                                };
                                $statusLabel = match ($paymentStatus) {
                                    'Paid' => __('booking.status.paid'),
                                    'Partial' => __('booking.status.partial'),
                                    'Cancel' => __('booking.status.canceled'),
                                    default => __('booking.status.pending'),
                                };

                                $customerName = $booking->customer?->name ?: ($booking->customer?->phone ?: __('booking.walk_in_customer'));
                                $customerPhone = $booking->customer?->phone ?: '---';
                                $customerEmail = $booking->customer?->email ?: null;
                                $customerAddress = $booking->customer?->address ?: null;
                                $customerInitials = strtoupper(substr($customerName, 0, 2));
                            @endphp
                            <span class="detail-status-pill {{ $statusClass }}">
                                <span class="status-dot"></span>
                                {{ $statusLabel }}
                            </span>

                            @if ($booking->trashed())
                                <span class="detail-status-pill status-trash">
                                    <i data-feather="trash-2"></i>
                                    {{ __('booking.tab.trash') }}
                                </span>
                            @endif
                        </div>
                        <p class="invoice-subtitle">
                            <span><i data-feather="clock"></i> {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---' }}</span>
                            @if ($booking->shop)
                                <span class="meta-sep">•</span>
                                <span><i data-feather="home"></i> {{ $booking->shop->name }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Action Buttons aligned with System Style Guide -->
                    <div class="header-right-actions">
                        @if ($canAddPayment)
                            <button type="button" class="btn btn-create bg-success btn-system btn-system-success" @click="openPaymentModal = true">
                                <i data-feather="plus-circle"></i>
                                <span>{{ __('booking.detail.add_payment') }}</span>
                            </button>
                        @endif

                        @if ($canEdit)
                            <a href="{{ route('admin-booking-edit', $booking->id) }}" class="btn btn-create bg-primary btn-system btn-system-primary">
                                <i data-feather="edit-2"></i>
                                <span>{{ __('booking.detail.edit_booking') }}</span>
                            </a>
                        @endif

                        <button type="button" class="btn btn-system btn-system-outline" @click="printTemplate1()">
                            <i data-feather="printer"></i>
                            <span>{{ __('booking.detail.print_invoice') }}</span>
                        </button>

                        <button type="button" class="btn btn-system btn-system-outline" @click="printTemplate2()">
                            <i data-feather="printer"></i>
                            <span>{{ __('booking.detail.print_slip') }}</span>
                        </button>

                        @if ($canCancel)
                            <button type="button" class="btn btn-create bg-danger btn-system btn-system-danger" @click="confirmCancel()">
                                <i data-feather="x-circle"></i>
                                <span>{{ __('booking.detail.cancel_booking') }}</span>
                            </button>
                        @endif

                        <a href="{{ route('admin-booking-list', $booking->payment_status ?: 'Pending') }}" class="btn btn-system btn-system-outline btn-system-neutral">
                            <i data-feather="arrow-left"></i>
                            <span>{{ __('booking.detail.back_to_list') }}</span>
                        </a>
                    </div>
                </header>

                <!-- Top 4 KPI Metrics Cards -->
                <section class="detail-kpi-grid">
                    <article class="kpi-card">
                        <div class="kpi-icon-wrap kpi-blue">
                            <i data-feather="dollar-sign"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('booking.detail.total_bill') }}</span>
                            <strong class="kpi-value">${{ number_format((float) ($booking->total_price ?? 0), 2) }}</strong>
                        </div>
                    </article>

                    <article class="kpi-card">
                        <div class="kpi-icon-wrap kpi-green">
                            <i data-feather="check-circle"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('booking.detail.paid_amount') }}</span>
                            <strong class="kpi-value text-success">${{ number_format((float) ($booking->paid_amount ?? 0), 2) }}</strong>
                        </div>
                    </article>

                    <article class="kpi-card">
                        <div class="kpi-icon-wrap {{ (float) ($booking->remaining_amount ?? 0) > 0 ? 'kpi-orange' : 'kpi-gray' }}">
                            <i data-feather="credit-card"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('booking.detail.balance_due') }}</span>
                            <strong class="kpi-value {{ (float) ($booking->remaining_amount ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                ${{ number_format((float) ($booking->remaining_amount ?? 0), 2) }}
                            </strong>
                        </div>
                    </article>

                    <article class="kpi-card">
                        <div class="kpi-icon-wrap kpi-purple">
                            <i data-feather="award"></i>
                        </div>
                        <div class="kpi-info">
                            <span class="kpi-label">{{ __('booking.detail.customer_points') }}</span>
                            <strong class="kpi-value text-primary">+{{ (int) ($booking->total_point ?? 0) }} {{ __('booking.detail.pts') }}</strong>
                        </div>
                    </article>
                </section>

                <!-- Main Content 2-Column Grid (70% Left / 30% Right) -->
                <div class="detail-columns-layout">
                    <!-- Left Main Column (70%) -->
                    <div class="detail-col-main">
                        <!-- Itemized Services & Products Table -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="scissors"></i>
                                    <h2>{{ __('booking.detail.item_details') }}</h2>
                                    <span class="count-badge">{{ $booking->bookingDetail ? $booking->bookingDetail->count() : 0 }}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="detail-data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>{{ __('booking.table.item') }}</th>
                                            <th style="width: 110px;">{{ __('booking.table.type') }}</th>
                                            <th>{{ __('booking.detail.stylist_barber') }}</th>
                                            <th class="text-right">{{ __('booking.detail.unit_price') }}</th>
                                            <th class="text-center">{{ __('booking.detail.quantity') }}</th>
                                            <th class="text-right">{{ __('booking.detail.discount') }}</th>
                                            <th class="text-right">{{ __('booking.detail.line_total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($booking->bookingDetail as $index => $detail)
                                            @php
                                                $isService = $detail->type === 'service';
                                                $itemName = $isService
                                                    ? ($detail->service?->name ?? '---')
                                                    : ($detail->product?->name ?? '---');
                                                $unitPrice = (float) ($detail->price ?? 0);
                                                $qty = (int) ($detail->qty ?: 1);
                                                $discount = (float) ($isService ? ($detail->service_discount ?? 0) : ($detail->product_discount ?? 0));
                                                $lineSubtotal = max(0, ($unitPrice * $qty) - $discount);
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="item-name-cell">
                                                        <strong>{{ $itemName }}</strong>
                                                        @if ($detail->point)
                                                            <small class="point-badge">+{{ $detail->point }} {{ __('booking.detail.pts') }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($isService)
                                                        <span class="type-pill pill-service">{{ __('booking.detail.service') }}</span>
                                                    @else
                                                        <span class="type-pill pill-product">{{ __('booking.detail.product') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $booking->barber?->name ?? '---' }}</td>
                                                <td class="text-right">${{ number_format($unitPrice, 2) }}</td>
                                                <td class="text-center">{{ $qty }}</td>
                                                <td class="text-right">
                                                    @if ($discount > 0)
                                                        <span class="text-danger">-${{ number_format($discount, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">$0.00</span>
                                                    @endif
                                                </td>
                                                <td class="text-right font-weight-bold">${{ number_format($lineSubtotal, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    {{ __('booking.empty_cart.title') }}
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
                                    <h2>{{ __('booking.detail.payment_history') }}</h2>
                                    <span class="count-badge">{{ $booking->payments ? $booking->payments->count() : 0 }}</span>
                                </div>
                                @if ($canAddPayment)
                                    <button type="button" class="btn btn-create bg-success btn-system-sm btn-system-success" @click="openPaymentModal = true">
                                        <i data-feather="plus"></i>
                                        <span>{{ __('booking.detail.add_payment') }}</span>
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="detail-data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>{{ __('booking.detail.transaction_date') }}</th>
                                            <th>{{ __('booking.detail.method') }}</th>
                                            <th class="text-right">{{ __('booking.table.paid') }}</th>
                                            <th>{{ __('booking.detail.cashier') }}</th>
                                            <th>{{ __('booking.table.note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($booking->payments as $pIdx => $pay)
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    {{ __('booking.detail.no_payments') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Booking Remarks / Internal Notes -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="message-square"></i>
                                    <h2>{{ __('booking.detail.remarks') }}</h2>
                                </div>
                            </div>
                            <div class="remark-content-box">
                                @if ($booking->remark)
                                    <p class="remark-text">{{ $booking->remark }}</p>
                                @else
                                    <p class="remark-empty">{{ __('booking.detail.no_remarks') }}</p>
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
                                    <h2>{{ __('booking.detail.customer_profile') }}</h2>
                                </div>
                            </div>
                            <div class="customer-profile-body">
                                @php
                                    $customerName = $booking->customer?->name ?: ($booking->customer?->phone ?: __('booking.walk_in_customer'));
                                    $customerPhone = $booking->customer?->phone ?: '---';
                                    $customerEmail = $booking->customer?->email ?: null;
                                    $customerAddress = $booking->customer?->address ?: null;
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
                                        <span class="info-label"><i data-feather="phone"></i> {{ __('booking.detail.phone') }}:</span>
                                        <span class="info-val">{{ $customerPhone }}</span>
                                    </li>
                                    @if ($customerEmail)
                                        <li>
                                            <span class="info-label"><i data-feather="mail"></i> {{ __('booking.detail.email') }}:</span>
                                            <span class="info-val">{{ $customerEmail }}</span>
                                        </li>
                                    @endif
                                    @if ($customerAddress)
                                        <li>
                                            <span class="info-label"><i data-feather="map-pin"></i> {{ __('booking.detail.address') }}:</span>
                                            <span class="info-val">{{ $customerAddress }}</span>
                                        </li>
                                    @endif
                                    <li>
                                        <span class="info-label"><i data-feather="award"></i> {{ __('booking.detail.customer_points') }}:</span>
                                        <span class="info-val points-tag">{{ $booking->customer?->point ?? 0 }} {{ __('booking.detail.pts') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <!-- Appointment & Shop Details Card -->
                        <section class="detail-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="calendar"></i>
                                    <h2>{{ __('booking.detail.appointment_details') }}</h2>
                                </div>
                            </div>
                            <div class="appointment-body">
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="home"></i> {{ __('booking.detail.shop') }}:</span>
                                    <span class="val font-weight-bold">{{ $booking->shop?->name ?: '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="user-check"></i> {{ __('booking.detail.barber') }}:</span>
                                    <span class="val font-weight-bold">{{ $booking->barber?->name ?: '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="clock"></i> {{ __('booking.detail.appointment_time') }}:</span>
                                    <span class="val">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---' }}</span>
                                </div>
                                <div class="appointment-info-row">
                                    <span class="label"><i data-feather="plus-square"></i> {{ __('booking.detail.created_at') }}:</span>
                                    <span class="val">{{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y, h:i A') : '---' }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Billing Summary Card -->
                        <section class="detail-card billing-card">
                            <div class="detail-card-header">
                                <div class="card-title-group">
                                    <i data-feather="credit-card"></i>
                                    <h2>{{ __('booking.detail.billing_summary') }}</h2>
                                </div>
                            </div>
                            <div class="billing-body">
                                @php
                                    $subtotal = (float) ($booking->total_price ?? 0) + (float) ($booking->total_discount ?? 0);
                                    $discount = (float) ($booking->total_discount ?? 0);
                                    $grandTotal = (float) ($booking->total_price ?? 0);
                                    $paid = (float) ($booking->paid_amount ?? 0);
                                    $remaining = (float) ($booking->remaining_amount ?? 0);
                                @endphp
                                <div class="billing-row">
                                    <span>{{ __('booking.detail.subtotal') }}</span>
                                    <span>${{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="billing-row">
                                    <span>{{ __('booking.detail.discount') }}</span>
                                    <span class="text-danger">- ${{ number_format($discount, 2) }}</span>
                                </div>
                                <div class="billing-divider"></div>
                                <div class="billing-row billing-grand-total">
                                    <span>{{ __('booking.detail.grand_total') }}</span>
                                    <span>${{ number_format($grandTotal, 2) }}</span>
                                </div>
                                <div class="billing-row billing-paid">
                                    <span>{{ __('booking.detail.paid_amount') }}</span>
                                    <span class="text-success">${{ number_format($paid, 2) }}</span>
                                </div>
                                <div class="billing-divider"></div>
                                <div class="billing-row billing-due">
                                    <span>{{ __('booking.detail.balance_due') }}</span>
                                    <span class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format($remaining, 2) }}
                                    </span>
                                </div>

                                @if ($canAddPayment)
                                    <button type="button" class="btn btn-create bg-success btn-system btn-system-success btn-full-width mt-3" @click="openPaymentModal = true">
                                        <i data-feather="check-circle"></i>
                                        <span>{{ __('booking.button.make_payment') }}</span>
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
                                <h3>{{ __('booking.detail.add_payment') }}</h3>
                            </div>
                            <button type="button" class="btn-close-modal" :disabled="paymentSubmitting" @click="openPaymentModal = false">
                                &times;
                            </button>
                        </div>

                        <form @submit.prevent="submitPayment()">
                            <div class="modal-body-custom">
                                <div class="form-group-modal">
                                    <label>{{ __('booking.amount') }} ($) <span class="text-danger">*</span></label>
                                    <div class="input-action-wrap">
                                        <input type="number" step="0.01" min="0.01" max="{{ $booking->remaining_amount }}"
                                            class="modal-input" x-model="paymentForm.amount" required>
                                        <button type="button" class="btn-fill-max" @click="paymentForm.amount = {{ (float) $booking->remaining_amount }}">
                                            {{ __('booking.button.full_balance') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('booking.detail.method') }}</label>
                                    <div class="payment-method-selector">
                                        <label class="method-option" :class="paymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                            <input type="radio" value="Cash" x-model="paymentForm.payment_method">
                                            <i data-feather="dollar-sign"></i>
                                            <span>{{ __('booking.payment.cash') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                            <input type="radio" value="ABA" x-model="paymentForm.payment_method">
                                            <i data-feather="credit-card"></i>
                                            <span>{{ __('booking.payment.aba') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                            <input type="radio" value="Card" x-model="paymentForm.payment_method">
                                            <i data-feather="server"></i>
                                            <span>{{ __('booking.payment.card') }}</span>
                                        </label>
                                        <label class="method-option" :class="paymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                            <input type="radio" value="QR" x-model="paymentForm.payment_method">
                                            <i data-feather="maximize"></i>
                                            <span>{{ __('booking.payment.qr') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('booking.table.pay_date') }}</label>
                                    <input type="datetime-local" class="modal-input" x-model="paymentForm.payment_date">
                                </div>

                                <div class="form-group-modal">
                                    <label>{{ __('booking.note') }}</label>
                                    <textarea class="modal-textarea" rows="3" x-model="paymentForm.note"
                                        placeholder="{{ __('booking.placeholder.note') }}"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer-custom">
                                <button type="button" class="btn btn-system btn-system-outline btn-system-neutral" :disabled="paymentSubmitting" @click="openPaymentModal = false">
                                    <span>{{ __('booking.button.close') }}</span>
                                </button>
                                <button type="submit" class="btn btn-create bg-success btn-system btn-system-success" :disabled="paymentSubmitting">
                                    <span x-show="!paymentSubmitting">{{ __('booking.detail.add_payment') }}</span>
                                    <span x-show="paymentSubmitting">{{ __('booking.button.processing_payment') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- Print Only Invoice View - Template 1 (Matching design in invoice_01.png) -->
                <div class="print-only-invoice print-template-1">
                    @include('admin::pages.booking.invoice-template')
                </div>

                <!-- Print Only Invoice View - Template 2 (Matching design in invoice_02.png) -->
                <div class="print-only-invoice print-template-2">
                    @include('admin::pages.booking.invoice-template-02')
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <style>
        /* Ensure single scroll container matching system setup (No double scroll, smooth scrolling) */
        #content {
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
        }

        #content .header {
            flex-shrink: 0 !important;
            width: 100% !important;
        }

        .content-wrapper.booking-detail-wrapper {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: auto !important;
            max-height: 100% !important;
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            position: relative !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .booking-detail-wrapper .content-body {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: 100% !important;
            width: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 0 !important;
            scroll-behavior: smooth !important;
            -webkit-overflow-scrolling: touch;
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
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 16px;
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

        /* Compact Button (for small cards) */
        .btn-system-sm {
            height: 32px !important;
            padding: 0 12px !important;
            font-size: 12px !important;
            border-radius: 16px !important;
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

        /* Top 4 KPI Grid */
        .detail-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
            align-items: center;
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
            background: none;
            border: none;
            font-size: 24px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
        }
        .btn-close-modal:hover {
            color: #334155;
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



        /* Invoice Sheet Layout (matching invoice_01.png) */
        .pos-invoice-sheet {
            background: #ffffff;
            color: #000000;
            font-family: 'Hanuman', 'Khmer OS Battambang', 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            width: 100%;
            max-width: 790px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .inv-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            padding-bottom: 2px;
            width: 100%;
            box-sizing: border-box;
        }

        .inv-brand-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inv-shop-logo-img {
            max-height: 46px;
            max-width: 100px;
            object-fit: contain;
        }

        .inv-coca-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .coca-script {
            font-family: 'Brush Script MT', 'Playball', 'Pacifico', cursive, sans-serif;
            font-size: 27px;
            font-weight: bold;
            color: #e41d2d;
            letter-spacing: -0.5px;
            line-height: 1;
            transform: skew(-8deg);
            display: inline-block;
        }

        .inv-shop-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .inv-shop-title-kh {
            font-size: 15px;
            font-weight: 700;
            color: #000000;
            margin: 0;
            line-height: 1.25;
        }

        .inv-shop-subtitle-en {
            font-size: 12px;
            font-style: italic;
            color: #222222;
            margin: 0;
        }

        .inv-shop-phone {
            font-size: 11px;
            font-weight: 600;
            color: #000000;
            margin-top: 1px;
        }

        .inv-shop-phone .phone-label {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .inv-official-title-box {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .inv-copy-notice {
            font-size: 11px;
            color: #333333;
            margin-bottom: 1px;
        }

        .inv-main-heading {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .inv-main-heading .khmer-title {
            font-size: 17px;
            font-weight: 700;
            color: #000000;
        }

        .inv-main-heading .en-title {
            font-size: 16px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.5px;
        }

        /* Sub-Header Metadata Row */
        .inv-metadata-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            padding: 2px 0;
            font-size: 11.5px;
            width: 100%;
            box-sizing: border-box;
        }

        .inv-meta-left {
            flex: 1;
            max-width: 58%;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .inv-meta-right {
            flex: 1;
            max-width: 42%;
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 2px;
            align-items: flex-end;
        }

        .inv-meta-item {
            display: flex;
            align-items: baseline;
            gap: 4px;
            color: #000000;
        }

        .inv-meta-item .meta-label {
            font-weight: 600;
            color: #222222;
        }

        .inv-meta-item .meta-value {
            color: #000000;
        }

        .inv-meta-item .inv-number {
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Table Grid */
        .inv-grid-table {
            width: 100% !important;
            max-width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
            margin-bottom: 0 !important;
            table-layout: fixed !important;
            box-sizing: border-box !important;
        }

        .inv-grid-table th,
        .inv-grid-table td {
            border: 1px solid #000000 !important;
            padding: 2px 6px !important;
            box-sizing: border-box !important;
            color: #000000 !important;
        }

        .inv-grid-table thead th {
            background: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-align: center !important;
            height: 25px !important;
        }

        .inv-grid-table .col-num {
            width: 6% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-desc {
            width: 50% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-qty {
            width: 10% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-price {
            width: 16% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-total {
            width: 18% !important;
            text-align: center !important;
        }

        .inv-grid-table tbody tr {
            height: 24px !important;
        }

        .inv-grid-table tbody td.cell-num {
            text-align: center !important;
            font-weight: 600 !important;
        }

        .inv-grid-table tbody td.cell-desc {
            text-align: left !important;
            padding-left: 6px !important;
        }

        .inv-grid-table tbody td.cell-qty {
            text-align: center !important;
        }

        .inv-grid-table tbody td.cell-price {
            text-align: right !important;
            padding-right: 6px !important;
        }

        .inv-grid-table tbody td.cell-total {
            text-align: right !important;
            padding-right: 6px !important;
            font-weight: 600 !important;
        }

        .inv-grid-table tbody tr.inv-empty-row td {
            height: 22px !important;
            padding: 0 4px !important;
        }

        /* Footer: Signatures & Financial Summary */
        .inv-grid-table tfoot td {
            border: 1px solid #000000 !important;
        }

        .inv-foot-signatures-cell {
            vertical-align: top !important;
            padding: 4px 8px !important;
            height: 80px !important;
            width: 66% !important;
        }

        .inv-signatures-wrap {
            display: flex !important;
            justify-content: space-between !important;
            height: 100% !important;
            min-height: 72px !important;
        }

        .inv-signature-col {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: center !important;
        }

        .inv-signature-col.buyer-sig {
            text-align: left !important;
        }

        .inv-signature-col.seller-sig {
            text-align: center !important;
        }

        .inv-signature-col .sig-header {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            display: flex !important;
            align-items: baseline !important;
            gap: 3px !important;
        }

        .inv-signature-col.seller-sig .sig-header {
            justify-content: center !important;
        }

        .inv-signature-col .sig-status-tag {
            font-size: 11px !important;
            font-weight: normal !important;
            color: #333333 !important;
        }

        .inv-signature-col .sig-space {
            flex: 1 !important;
            min-height: 38px !important;
        }

        .inv-signature-col .sig-action-label {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #000000 !important;
            text-align: center !important;
        }

        .inv-foot-calc-label {
            width: 16% !important;
            font-size: 11.5px !important;
            font-weight: 600 !important;
            text-align: center !important;
            vertical-align: middle !important;
            background: #ffffff !important;
            height: 20px !important;
            padding: 2px 4px !important;
        }

        .inv-foot-calc-value {
            width: 18% !important;
            font-size: 11.5px !important;
            text-align: right !important;
            padding-right: 6px !important;
            vertical-align: middle !important;
            font-weight: 600 !important;
            background: #ffffff !important;
            height: 20px !important;
        }



        /* =========================================================
           Template 2: Delivery Slip / Voucher (invoice_02.png)
           ========================================================= */
        .pos-slip-sheet {
            background: #ffffff;
            color: #000000;
            font-family: 'Hanuman', 'Khmer OS Battambang', 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 8px 12px;
            box-sizing: border-box;
            width: 100%;
        }

        .slip-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .slip-top-left {
            width: 120px;
        }
        .slip-top-center {
            flex: 1;
            text-align: center;
        }
        .slip-title-khmer {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            color: #000000;
            letter-spacing: 0.5px;
        }
        .slip-top-right {
            text-align: right;
            font-size: 11.5px;
            line-height: 1.35;
            color: #000000;
        }
        .slip-meta-text {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .draft-tag {
            font-weight: 700;
        }
        .slip-meta-item {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }
        .slip-meta-item .lbl {
            color: #222222;
        }
        .slip-meta-item .val {
            font-weight: 600;
            min-width: 80px;
            text-align: left;
        }

        /* 3-Column Info Matrix */
        .slip-info-matrix {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000000;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 11.5px;
            line-height: 1.45;
            background: #ffffff;
            gap: 12px;
        }
        .slip-info-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .slip-info-col.col-cust {
            flex: 1.25;
        }
        .slip-info-col.col-depot {
            flex: 1.35;
        }
        .slip-info-col.col-dist {
            flex: 0.9;
        }
        .info-row {
            display: flex;
            gap: 6px;
        }
        .info-lbl {
            font-weight: 600;
            min-width: 65px;
            color: #111111;
            flex-shrink: 0;
        }
        .info-val {
            color: #000000;
            word-break: break-word;
        }
        .cust-code {
            font-weight: 700;
            margin-right: 4px;
        }
        .spacer-row {
            visibility: hidden;
        }

        /* Slip Grid Table */
        .slip-grid-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
            font-size: 11px !important;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }
        .slip-grid-table th,
        .slip-grid-table td {
            border: 1px solid #000000 !important;
            padding: 3px 4px !important;
            text-align: center !important;
            vertical-align: middle !important;
            color: #000000 !important;
            box-sizing: border-box !important;
        }
        .slip-grid-table thead th {
            font-weight: 600 !important;
            background: #ffffff !important;
        }
        .th-sub-row th {
            font-weight: 500 !important;
            font-size: 10.5px !important;
        }
        .th-subtext {
            font-size: 9.5px !important;
            font-weight: normal !important;
        }

        /* Column widths */
        .th-no, .td-no { width: 4% !important; }
        .th-code, .td-code { width: 8% !important; font-weight: 600 !important; }
        .th-desc, .td-desc { width: 32% !important; text-align: left !important; padding-left: 6px !important; }
        .th-uom, .td-uom { width: 6% !important; }
        .th-qty-group { width: 12% !important; }
        .th-sub-col, .td-cases, .td-cans { width: 6% !important; }
        .th-price, .td-price { width: 9% !important; text-align: right !important; padding-right: 4px !important; }
        .th-disc, .td-disc { width: 10% !important; text-align: right !important; padding-right: 4px !important; }
        .th-net, .td-net { width: 9% !important; text-align: right !important; padding-right: 4px !important; }
        .th-total, .td-total { width: 10% !important; text-align: right !important; padding-right: 4px !important; font-weight: 600 !important; }

        .slip-row td {
            height: 22px !important;
        }
        .slip-empty-row td {
            height: 20px !important;
            background: #ffffff !important;
        }

        /* Table Bottom Bar */
        .slip-table-bottom-bar {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            margin-top: 3px;
        }
        .slip-free-goods-note {
            font-size: 11px;
            font-style: italic;
            color: #111111;
        }
        .slip-total-pay-box {
            display: inline-flex;
            border: 1px solid #000000;
            background: #ffffff;
        }
        .slip-total-pay-box .pay-lbl {
            padding: 3px 10px;
            font-weight: 700;
            font-size: 11.5px;
            border-right: 1px solid #000000;
        }
        .slip-total-pay-box .pay-val {
            padding: 3px 12px;
            font-weight: 700;
            font-size: 12.5px;
            min-width: 75px;
            text-align: right;
        }

        /* Dual Signatures Section */
        .slip-signatures-section {
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            margin-top: 25px;
            margin-bottom: 18px;
        }
        .slip-sig-col {
            text-align: center;
            width: 220px;
        }
        .slip-sig-col .sig-title {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 40px;
        }
        .slip-sig-col .sig-underline {
            border-bottom: 1px dotted #000000;
            width: 100%;
            height: 1px;
        }

        /* Footer Disclaimer */
        .slip-footer-disclaimer {
            font-size: 10.5px;
            line-height: 1.35;
            color: #111111;
            text-align: left;
            margin-top: 6px;
        }

        /* Print Media Queries */
        .print-only-invoice {
            display: none;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 6mm 8mm;
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
            .booking-detail-page-wrapper {
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
                overflow: visible !important;
                visibility: visible !important;
            }

            .print-only-invoice * {
                visibility: visible !important;
            }

            /* Print template switching */
            #bookingDetailApp.printing-template1 .print-template-1 {
                display: block !important;
            }
            #bookingDetailApp.printing-template1 .print-template-2 {
                display: none !important;
            }
            #bookingDetailApp.printing-template2 .print-template-1 {
                display: none !important;
            }
            #bookingDetailApp.printing-template2 .print-template-2 {
                display: block !important;
            }

            .print-template-2 .pos-slip-sheet {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
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
            }

            .inv-grid-table tr,
            .slip-grid-table tr {
                page-break-inside: avoid !important;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.feather) {
                feather.replace();
            }
        });

        function xBookingDetail() {
            return {
                openPaymentModal: false,
                activePrintTemplate: 'template1',
                paymentSubmitting: false,
                paymentForm: {
                    amount: {{ (float) ($booking->remaining_amount ?? 0) }},
                    payment_method: 'Cash',
                    payment_date: '{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}',
                    note: ''
                },
                init() {
                    this.$nextTick(() => {
                        if (window.feather) {
                            feather.replace();
                        }
                    });

                    // Follow system setup: bind scroll to #content .content-body and #jsScroll
                    const contentBody = document.querySelector("#content .content-body");
                    const contentHeader = document.querySelector("#content");
                    const headerEl = document.querySelector("#content .header");
                    const jsScroll = document.getElementById("jsScroll");

                    if (contentBody) {
                        contentBody.addEventListener("scroll", function () {
                            const st = contentBody.scrollTop;
                            if (st > 10) {
                                contentHeader?.classList.add("isScrolled");
                                headerEl?.classList.add("isScrolled");
                            } else {
                                contentHeader?.classList.remove("isScrolled");
                                headerEl?.classList.remove("isScrolled");
                            }
                        }, { passive: true });

                        jsScroll?.addEventListener("click", function () {
                            contentBody.scrollTo({
                                top: 0,
                                behavior: "smooth"
                            });
                        });
                    }
                },
                async submitPayment() {
                    if (this.paymentSubmitting) return;
                    if (!this.paymentForm.amount || Number(this.paymentForm.amount) <= 0) {
                        alert('Please enter a valid payment amount.');
                        return;
                    }

                    this.paymentSubmitting = true;
                    const url = '{{ route('admin-booking-add-payment', $booking->id) }}';

                    try {
                        const response = await Axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            amount: this.paymentForm.amount,
                            payment_method: this.paymentForm.payment_method,
                            payment_date: this.paymentForm.payment_date,
                            note: this.paymentForm.note
                        });

                        if (response.data && (response.data.message === 'success' || response.status === 200)) {
                            if (window.toastr) {
                                toastr.success('{{ __('booking.message.payment_status_success') }}');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 400);
                        } else {
                            alert(response.data?.error || 'Payment recording failed.');
                            this.paymentSubmitting = false;
                        }
                    } catch (error) {
                        this.paymentSubmitting = false;
                        const errorMsg = error.response?.data?.error ||
                            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                            'Failed to record payment.';
                        alert(errorMsg);
                    }
                },
                printTemplate1() {
                    this.activePrintTemplate = 'template1';
                    setTimeout(() => {
                        window.print();
                    }, 50);
                },
                printTemplate2() {
                    this.activePrintTemplate = 'template2';
                    setTimeout(() => {
                        window.print();
                    }, 50);
                },
                confirmCancel() {
                    const invoiceName = '{{ $booking->invoice_number ?: '#' . $booking->id }}';
                    if (!confirm(`Are you sure you want to cancel booking ${invoiceName}?`)) {
                        return;
                    }

                    const url = '{{ route('admin-booking-cancel', $booking->id) }}';
                    Axios.post(url, {
                        _token: '{{ csrf_token() }}'
                    }).then((res) => {
                        if (res.data.message === 'success' || res.status === 200) {
                            if (window.toastr) {
                                toastr.success('{{ __('booking.message.reject_success') }}');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 400);
                        }
                    }).catch((err) => {
                        const message = err.response?.data?.error ||
                            Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
                            'Failed to cancel booking.';
                        alert(message);
                    });
                }
            };
        }
    </script>
@stop
