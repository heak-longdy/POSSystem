@extends('admin::shared.layout')

@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])

    <div class="content-wrapper po-detail-container" x-data="xPurchaseDetail">
        {{-- Top Navigation & Title Bar --}}
        <div class="po-detail-header-bar">
            <div class="po-detail-title-group">
                <a href="{!! route('admin-' . $routeName . '-list', 'all') !!}" class="btn-back-circle" title="Back to orders">
                    <i class='bx bx-arrow-back'></i>
                </a>
                <div>
                    <div class="po-number-title">
                        <h2>{{ $purchase->purchase_number }}</h2>
                        <span class="status-badge {{ match($purchase->order_status) {
                            'draft' => 'badge-draft',
                            'ordered' => 'badge-ordered',
                            'partial_received' => 'badge-partial',
                            'received' => 'badge-received',
                            'cancelled' => 'badge-cancelled',
                            default => 'badge-default'
                        } }}">
                            {{ $purchase->order_status_title }}
                        </span>
                        <span class="status-badge {{ match($purchase->payment_status) {
                            'unpaid' => 'badge-unpaid',
                            'partial' => 'badge-partial',
                            'paid' => 'badge-paid',
                            default => 'badge-default'
                        } }}">
                            {{ $purchase->payment_status_title }}
                        </span>
                    </div>
                    <span class="po-meta-subtitle">
                        Created on {{ $purchase->created_date }} by {{ $purchase->created_by_title }}
                        @if ($purchase->reference_no)
                            &bull; Ref: <strong>{{ $purchase->reference_no }}</strong>
                        @endif
                    </span>
                </div>
            </div>

            <div class="po-detail-actions">
                @if ($purchase->order_status !== 'received' && $purchase->order_status !== 'cancelled' && !$purchase->trashed())
                    <button type="button" class="btn-po-action btn-receive" @click="openReceiveModal()">
                        <i class='bx bx-package'></i>
                        <span>{{ __('stock_in.button.receive_goods') }}</span>
                    </button>
                @endif

                @if ($purchase->payment_status !== 'paid' && $purchase->order_status !== 'cancelled' && !$purchase->trashed())
                    <button type="button" class="btn-po-action btn-payment" @click="openPaymentModal()">
                        <i class='bx bx-credit-card-front'></i>
                        <span>{{ __('stock_in.button.add_payment') }}</span>
                    </button>
                @endif

                <a href="{!! route('admin-' . $routeName . '-print', $purchase->id) !!}" target="_blank" class="btn-po-action btn-print">
                    <i class='bx bx-printer'></i>
                    <span>{{ __('stock_in.button.print') }}</span>
                </a>

                @if (!$purchase->trashed())
                    <a href="{!! route('admin-' . $routeName . '-edit', $purchase->id) !!}" class="btn-po-action btn-edit">
                        <i class='bx bx-edit-alt'></i>
                        <span>{{ __('global.action.edit') }}</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Financial & KPI Highlights --}}
        <div class="po-kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">{{ __('stock_in.table.total_amount') }}</span>
                <span class="kpi-value text-slate-800">{{ $purchase->formatted_total_amount }}</span>
                <span class="kpi-sub">Subtotal: ${{ number_format($purchase->subtotal, 2) }}</span>
            </div>

            <div class="kpi-card">
                <span class="kpi-label">{{ __('stock_in.table.paid_amount') }}</span>
                <span class="kpi-value text-emerald-600">{{ $purchase->formatted_paid_amount }}</span>
                <span class="kpi-sub">{{ $purchase->payments->count() }} transaction(s)</span>
            </div>

            <div class="kpi-card">
                <span class="kpi-label">{{ __('stock_in.table.remaining_amount') }}</span>
                <span class="kpi-value {{ $purchase->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                    {{ $purchase->formatted_remaining_amount }}
                </span>
                <span class="kpi-sub">{{ $purchase->payment_status_title }}</span>
            </div>

            <div class="kpi-card">
                @php
                    $totalOrd = (float) $purchase->items->sum('qty');
                    $totalRec = (float) $purchase->items->sum('qty_received');
                    $pct = $totalOrd > 0 ? min(100, round(($totalRec / $totalOrd) * 100)) : 0;
                @endphp
                <span class="kpi-label">Receiving Fulfillment</span>
                <span class="kpi-value text-blue-600">{{ $pct }}%</span>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: {{ $pct }}%;"></div>
                </div>
                <span class="kpi-sub">{{ $totalRec }} of {{ $totalOrd }} units received</span>
            </div>
        </div>

        {{-- Vendor & Warehouse Meta Cards --}}
        <div class="po-info-grid">
            <div class="info-card">
                <div class="info-card-header">
                    <i class='bx bx-store-alt'></i>
                    <h4>{{ __('stock_in.form.supplier') }}</h4>
                </div>
                <div class="info-card-body">
                    <div class="info-title">{{ $purchase->supplier?->name ?? '---' }}</div>
                    @if ($purchase->supplier?->phone)
                        <div class="info-item"><i class='bx bx-phone'></i> {{ $purchase->supplier->phone }}</div>
                    @endif
                    @if ($purchase->supplier?->address)
                        <div class="info-item"><i class='bx bx-map'></i> {{ $purchase->supplier->address }}</div>
                    @endif
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <i class='bx bx-building-house'></i>
                    <h4>{{ __('stock_in.form.shop') }}</h4>
                </div>
                <div class="info-card-body">
                    <div class="info-title">{{ $purchase->shop?->name ?? '---' }}</div>
                    @if ($purchase->shop?->phone)
                        <div class="info-item"><i class='bx bx-phone'></i> {{ $purchase->shop->phone }}</div>
                    @endif
                    @if ($purchase->shop?->address)
                        <div class="info-item"><i class='bx bx-map'></i> {{ $purchase->shop->address }}</div>
                    @endif
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <i class='bx bx-calendar'></i>
                    <h4>Dates & Tracking</h4>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span>Order Date:</span>
                        <strong>{{ $purchase->created_date }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Expected Delivery:</span>
                        <strong>{{ $purchase->expected_delivery_date ? \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('d/M/Y') : 'Not set' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Vendor Ref:</span>
                        <strong>{{ $purchase->reference_no ?: 'None' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabbed Section (Items, Goods Receipts, Payments) --}}
        <div class="po-tabs-card">
            <div class="po-tabs-nav">
                <button type="button" class="tab-btn" :class="{ 'is-active': activeTab === 'items' }" @click="activeTab = 'items'">
                    <i class='bx bx-list-ul'></i>
                    <span>{{ __('stock_in.form.items') }} ({{ $purchase->items->count() }})</span>
                </button>
                <button type="button" class="tab-btn" :class="{ 'is-active': activeTab === 'receipts' }" @click="activeTab = 'receipts'">
                    <i class='bx bx-receipt'></i>
                    <span>{{ __('stock_in.payment.receipt_history') }} ({{ $purchase->receipts->count() }})</span>
                </button>
                <button type="button" class="tab-btn" :class="{ 'is-active': activeTab === 'payments' }" @click="activeTab = 'payments'">
                    <i class='bx bx-dollar-circle'></i>
                    <span>{{ __('stock_in.payment.history_title') }} ({{ $purchase->payments->count() }})</span>
                </button>
            </div>

            {{-- Tab 1: Order Items Table --}}
            <div class="po-tab-content" x-show="activeTab === 'items'">
                <div class="table-container">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>{{ __('stock_in.table.product') }}</th>
                                <th style="text-align: right;">{{ __('stock_in.table.unit_cost') }}</th>
                                <th style="text-align: center;">{{ __('stock_in.table.qty') }}</th>
                                <th style="text-align: center;">{{ __('stock_in.table.qty_received') }}</th>
                                <th style="text-align: center;">{{ __('stock_in.table.qty_remaining') }}</th>
                                <th style="text-align: right;">{{ __('stock_in.table.subtotal') }}</th>
                                <th>{{ __('stock_in.table.remark') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $idx => $item)
                                <tr>
                                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="product-cell">
                                            <img src="{{ $item->product_image }}" onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                            <div>
                                                <strong>{{ $item->product_name }}</strong>
                                                <div class="table-subtext">{{ $item->category_title }} &bull; {{ $item->uom_title }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right; font-weight: 600;">{{ $item->formatted_unit_cost }}</td>
                                    <td style="text-align: center; font-weight: 700;">{{ $item->qty }}</td>
                                    <td style="text-align: center;">
                                        <span class="badge-rec {{ $item->is_fully_received ? 'is-full' : ($item->qty_received > 0 ? 'is-part' : 'is-none') }}">
                                            {{ $item->qty_received }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: {{ $item->remaining_qty > 0 ? '#dc2626' : '#64748b' }};">
                                        {{ $item->remaining_qty }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #1e293b;">
                                        {{ $item->formatted_subtotal }}
                                    </td>
                                    <td style="color: #64748b;">{{ $item->remark ?: '---' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right; font-weight: 600;">{{ __('stock_in.form.subtotal') }}:</td>
                                <td style="text-align: right; font-weight: 700;">${{ number_format($purchase->subtotal, 2) }}</td>
                                <td></td>
                            </tr>
                            @if ($purchase->discount_amount > 0)
                                <tr>
                                    <td colspan="6" style="text-align: right; font-weight: 600; color: #059669;">{{ __('stock_in.form.discount') }}:</td>
                                    <td style="text-align: right; font-weight: 700; color: #059669;">-${{ number_format($purchase->discount_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                            @if ($purchase->tax_amount > 0)
                                <tr>
                                    <td colspan="6" style="text-align: right; font-weight: 600;">{{ __('stock_in.form.tax') }}:</td>
                                    <td style="text-align: right; font-weight: 700;">+${{ number_format($purchase->tax_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                            @if ($purchase->shipping_cost > 0)
                                <tr>
                                    <td colspan="6" style="text-align: right; font-weight: 600;">{{ __('stock_in.form.shipping') }}:</td>
                                    <td style="text-align: right; font-weight: 700;">+${{ number_format($purchase->shipping_cost, 2) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                            <tr class="total-foot-row">
                                <td colspan="6" style="text-align: right; font-weight: 800; font-size: 15px;">{{ __('stock_in.form.grand_total') }}:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 16px; color: #2563eb;">{{ $purchase->formatted_total_amount }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Tab 2: Goods Receipts Log --}}
            <div class="po-tab-content" x-show="activeTab === 'receipts'">
                @if ($purchase->receipts->count() > 0)
                    <div class="receipts-timeline">
                        @foreach ($purchase->receipts as $rec)
                            <div class="receipt-item-card">
                                <div class="receipt-header">
                                    <div class="receipt-header-left">
                                        <span class="receipt-icon"><i class='bx bx-check-shield'></i></span>
                                        <div>
                                            <strong>{{ $rec->receipt_number }}</strong>
                                            <span class="table-subtext">Received on {{ $rec->formatted_date }} by {{ $rec->received_by_title }}</span>
                                        </div>
                                    </div>
                                    @if ($rec->reference_no)
                                        <div class="receipt-ref-tag">
                                            Waybill: {{ $rec->reference_no }}
                                        </div>
                                    @endif
                                </div>
                                <div class="receipt-items-list">
                                    <table class="receipt-inner-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('stock_in.table.product') }}</th>
                                                <th style="text-align: right;">Quantity Received</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rec->items as $ri)
                                                <tr>
                                                    <td>{{ $ri->product_name }}</td>
                                                    <td style="text-align: right; font-weight: 700; color: #059669;">+{{ $ri->qty_received }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($rec->remark)
                                    <div class="receipt-note"><i class='bx bx-comment-detail'></i> {{ $rec->remark }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-tab-state">
                        <i class='bx bx-box'></i>
                        <span>No goods received yet. Click "Receive Goods" above to record delivery.</span>
                    </div>
                @endif
            </div>

            {{-- Tab 3: Payment History Ledger --}}
            <div class="po-tab-content" x-show="activeTab === 'payments'">
                @if ($purchase->payments->count() > 0)
                    <div class="table-container">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Payment #</th>
                                    <th>{{ __('stock_in.payment.date') }}</th>
                                    <th style="text-align: right;">{{ __('stock_in.payment.amount_to_pay') }}</th>
                                    <th>{{ __('stock_in.payment.method') }}</th>
                                    <th>{{ __('stock_in.payment.ref_no') }}</th>
                                    <th>{{ __('stock_in.payment.slip') }}</th>
                                    <th>Recorded By</th>
                                    @if (!$purchase->trashed())
                                        <th style="width: 50px; text-align: center;">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->payments as $pidx => $pay)
                                    <tr>
                                        <td style="text-align: center;">{{ $pidx + 1 }}</td>
                                        <td><strong>{{ $pay->payment_number }}</strong></td>
                                        <td>{{ $pay->formatted_date }}</td>
                                        <td style="text-align: right; font-weight: 700; color: #059669;">{{ $pay->formatted_amount }}</td>
                                        <td><span class="badge-pay-method">{{ $pay->payment_method_title }}</span></td>
                                        <td>{{ $pay->reference_no ?: '---' }}</td>
                                        <td>
                                            @if ($pay->receipt_url)
                                                <a href="{{ $pay->receipt_url }}" target="_blank" class="receipt-link">
                                                    <i class='bx bx-paperclip'></i> View Slip
                                                </a>
                                            @else
                                                <span class="text-slate-400">---</span>
                                            @endif
                                        </td>
                                        <td>{{ $pay->created_by_title }}</td>
                                        @if (!$purchase->trashed())
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-void-pay" @click="voidPayment({{ $pay->id }}, '{{ $pay->payment_number }}')" title="Void Payment">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-tab-state">
                        <i class='bx bx-credit-card'></i>
                        <span>No payments recorded for this order yet.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL 1: Receive Goods Modal --}}
        <div class="modal-backdrop" x-show="showReceiveModal" x-cloak>
            <div class="modal-dialog modal-lg" @click.outside="showReceiveModal = false">
                <form action="{!! route('admin-' . $routeName . '-receive', $purchase->id) !!}" method="POST" @submit="submittingReceive = true">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <div>
                            <h4>{{ __('stock_in.receiving.modal_title') }}</h4>
                            <span class="modal-subtitle">{{ __('stock_in.receiving.subtitle') }}</span>
                        </div>
                        <button type="button" class="btn-close-modal" @click="showReceiveModal = false">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row-2" style="margin-bottom: 18px;">
                            <div class="form-row">
                                <label>{{ __('stock_in.receiving.received_date') }}<span class="req">*</span></label>
                                <input type="text" name="received_date" id="modal_received_date" class="datepicker-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-row">
                                <label>{{ __('stock_in.receiving.bill_of_lading') }}</label>
                                <input type="text" name="reference_no" placeholder="e.g. Delivery Slip #">
                            </div>
                        </div>

                        <div class="modal-table-wrap">
                            <table class="modal-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('stock_in.table.product') }}</th>
                                        <th style="text-align: center;">{{ __('stock_in.receiving.qty_ordered') }}</th>
                                        <th style="text-align: center;">{{ __('stock_in.receiving.qty_already') }}</th>
                                        <th style="text-align: center;">{{ __('stock_in.receiving.qty_balance') }}</th>
                                        <th style="width: 140px; text-align: center;">{{ __('stock_in.receiving.qty_to_receive') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, idx) in receiveRows" :key="row.id">
                                        <tr>
                                            <td>
                                                <input type="hidden" :name="`items[${idx}][purchase_item_id]`" :value="row.id">
                                                <strong x-text="row.name"></strong>
                                                <div class="table-subtext" x-text="row.uom"></div>
                                            </td>
                                            <td style="text-align: center;" x-text="row.qty"></td>
                                            <td style="text-align: center;" x-text="row.qty_received"></td>
                                            <td style="text-align: center; font-weight: 700; color: #dc2626;" x-text="row.remaining"></td>
                                            <td style="text-align: center;">
                                                <input type="number" step="any" min="0" :max="row.remaining"
                                                    :name="`items[${idx}][qty_to_receive]`"
                                                    x-model.number="row.qty_to_receive"
                                                    class="input-qty-receive">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="modal-quick-actions">
                            <button type="button" class="btn-receive-all" @click="receiveAllRemaining()">
                                <i class='bx bx-check-double'></i>
                                <span>{{ __('stock_in.button.receive_all') }}</span>
                            </button>
                        </div>

                        <div class="form-row" style="margin-top: 14px;">
                            <label>{{ __('stock_in.receiving.notes') }}</label>
                            <input type="text" name="remark" placeholder="Delivery notes or batch remarks...">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" @click="showReceiveModal = false">{{ __('global.button.cancel') }}</button>
                        <button type="submit" class="btn-modal-submit" :disabled="submittingReceive">
                            <i class='bx bx-check'></i>
                            <span x-text="submittingReceive ? 'Receiving...' : @json(__('stock_in.receiving.confirm_receive'))"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Add Payment Modal --}}
        <div class="modal-backdrop" x-show="showPaymentModal" x-cloak>
            <div class="modal-dialog modal-md" @click.outside="showPaymentModal = false">
                <form action="{!! route('admin-' . $routeName . '-payment', $purchase->id) !!}" method="POST" enctype="multipart/form-data" @submit="submittingPayment = true">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <div>
                            <h4>{{ __('stock_in.payment.modal_title') }}</h4>
                            <span class="modal-subtitle">{{ __('stock_in.payment.subtitle') }}</span>
                        </div>
                        <button type="button" class="btn-close-modal" @click="showPaymentModal = false">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="payment-balance-banner">
                            <span>{{ __('stock_in.payment.remaining_due') }}:</span>
                            <strong>${{ number_format($purchase->remaining_amount, 2) }}</strong>
                        </div>

                        <div class="row-2">
                            <div class="form-row">
                                <label>{{ __('stock_in.payment.amount_to_pay') }}<span class="req">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ $purchase->remaining_amount }}" required>
                            </div>
                            <div class="form-row">
                                <label>{{ __('stock_in.payment.date') }}<span class="req">*</span></label>
                                <input type="text" name="payment_date" id="modal_payment_date" class="datepicker-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row-2">
                            <div class="form-row">
                                <label>{{ __('stock_in.payment.method') }}<span class="req">*</span></label>
                                <select name="payment_method" class="filter-select" style="width: 100%;" required>
                                    <option value="cash">{{ __('stock_in.payment_method.cash') }}</option>
                                    <option value="aba">ABA Pay</option>
                                    <option value="bank_transfer">{{ __('stock_in.payment_method.bank_transfer') }}</option>
                                    <option value="cheque">{{ __('stock_in.payment_method.cheque') }}</option>
                                    <option value="other">{{ __('stock_in.payment_method.other') }}</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label>{{ __('stock_in.payment.ref_no') }}</label>
                                <input type="text" name="reference_no" placeholder="e.g. Bank Ref # / Cheque #">
                            </div>
                        </div>

                        <div class="form-row">
                            <label>{{ __('stock_in.payment.slip') }}</label>
                            <input type="file" name="payment_receipt" accept="image/*,application/pdf">
                        </div>

                        <div class="form-row">
                            <label>{{ __('stock_in.payment.note') }}</label>
                            <input type="text" name="note" placeholder="Payment notes...">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" @click="showPaymentModal = false">{{ __('global.button.cancel') }}</button>
                        <button type="submit" class="btn-modal-submit" :disabled="submittingPayment">
                            <i class='bx bx-check'></i>
                            <span x-text="submittingPayment ? 'Processing...' : @json(__('stock_in.payment.confirm_payment'))"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('script')
    <style>
        .po-detail-container {
            max-width: 80rem;
            margin: 0 auto;
            padding: 24px;
        }

        .po-detail-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .po-detail-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .btn-back-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-back-circle:hover { background: #e2e8f0; color: #1e293b; }
        .po-number-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .po-number-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
        }
        .po-meta-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 4px;
            display: block;
        }

        .po-detail-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-po-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-receive { background: #2563eb; color: #ffffff; }
        .btn-receive:hover { background: #1d4ed8; }
        .btn-payment { background: #059669; color: #ffffff; }
        .btn-payment:hover { background: #047857; }
        .btn-print { background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; }
        .btn-print:hover { background: #f1f5f9; }
        .btn-edit { background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; }
        .btn-edit:hover { background: #f1f5f9; }

        /* KPI Highlights */
        .po-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .kpi-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; }
        .kpi-value { display: block; font-size: 22px; font-weight: 800; margin: 6px 0; }
        .kpi-sub { font-size: 12px; color: #94a3b8; }
        .progress-bar-wrap {
            height: 6px;
            border-radius: 9999px;
            background: #e2e8f0;
            overflow: hidden;
            margin: 6px 0;
        }
        .progress-bar-fill {
            height: 100%;
            background: #2563eb;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }

        /* Info Grid */
        .po-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }
        .info-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .info-card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-title { font-size: 14.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .info-item { font-size: 12.5px; color: #64748b; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
        .info-row { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px; color: #64748b; }
        .info-row strong { color: #1e293b; }

        /* Tabs Card */
        .po-tabs-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }
        .po-tabs-nav {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .tab-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 22px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tab-btn:hover { color: #1e293b; }
        .tab-btn.is-active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: #ffffff;
        }

        .po-tab-content { padding: 22px; }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .detail-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .detail-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .product-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-cell img {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            object-fit: cover;
        }
        .badge-rec {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-rec.is-full { background: #ecfdf5; color: #059669; }
        .badge-rec.is-part { background: #fffbeb; color: #d97706; }
        .badge-rec.is-none { background: #f1f5f9; color: #94a3b8; }

        .total-foot-row td {
            border-top: 2px solid #e2e8f0;
            padding-top: 14px;
        }

        /* Receipts Timeline */
        .receipts-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .receipt-item-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            background: #f8fafc;
        }
        .receipt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .receipt-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .receipt-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .receipt-ref-tag {
            font-size: 11.5px;
            padding: 4px 10px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .receipt-inner-table {
            width: 100%;
            background: #ffffff;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .receipt-inner-table th, .receipt-inner-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .receipt-note {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-pay-method {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 600;
        }
        .receipt-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .receipt-link:hover { text-decoration: underline; }
        .btn-void-pay {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #fee2e2;
            background: #fff5f5;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .empty-tab-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
        }
        .empty-tab-state i { font-size: 40px; }

        /* Modals */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        .modal-dialog {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-lg { max-width: 680px; }
        .modal-md { max-width: 520px; }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-header h4 { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; }
        .modal-subtitle { font-size: 12px; color: #64748b; }
        .btn-close-modal {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #94a3b8;
            cursor: pointer;
        }
        .modal-body { padding: 22px; }
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 22px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            border-radius: 0 0 14px 14px;
        }
        .btn-modal-cancel {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-modal-submit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            background: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-modal-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .modal-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .modal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .modal-table th {
            background: #f8fafc;
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #475569;
            text-align: left;
        }
        .modal-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .input-qty-receive {
            width: 80px;
            height: 32px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            outline: none;
        }
        .btn-receive-all {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #2563eb;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
        }

        .payment-balance-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            margin-bottom: 18px;
            color: #991b1b;
            font-size: 13.5px;
        }
        .payment-balance-banner strong { font-size: 16px; }

        .ui-datepicker.stock-in-datepicker-popup {
            z-index: 10001 !important;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xPurchaseDetail', () => ({
                activeTab: 'items',
                showReceiveModal: false,
                showPaymentModal: false,
                submittingReceive: false,
                submittingPayment: false,
                receiveRows: [
                    @foreach ($purchase->items as $item)
                        @if ($item->remaining_qty > 0)
                            {
                                id: {{ $item->id }},
                                name: @json($item->product_name),
                                uom: @json($item->uom_title),
                                qty: {{ $item->qty }},
                                qty_received: {{ $item->qty_received }},
                                remaining: {{ $item->remaining_qty }},
                                qty_to_receive: {{ $item->remaining_qty }},
                            },
                        @endif
                    @endforeach
                ],

                openReceiveModal() {
                    this.showReceiveModal = true;
                    this.$nextTick(() => {
                        const $input = $('#modal_received_date');
                        if ($input.length && typeof $.fn.datepicker === 'function') {
                            $input.datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                gotoCurrent: true,
                                yearRange: '-50:+10',
                                beforeShow: function() {
                                    setTimeout(() => { $('#ui-datepicker-div').addClass('stock-in-datepicker-popup'); }, 0);
                                },
                                onClose: function() {
                                    $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                                }
                            });
                        }
                    });
                },

                receiveAllRemaining() {
                    this.receiveRows.forEach(r => {
                        r.qty_to_receive = r.remaining;
                    });
                },

                openPaymentModal() {
                    this.showPaymentModal = true;
                    this.$nextTick(() => {
                        const $input = $('#modal_payment_date');
                        if ($input.length && typeof $.fn.datepicker === 'function') {
                            $input.datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                gotoCurrent: true,
                                yearRange: '-50:+10',
                                beforeShow: function() {
                                    setTimeout(() => { $('#ui-datepicker-div').addClass('stock-in-datepicker-popup'); }, 0);
                                },
                                onClose: function() {
                                    $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                                }
                            });
                        }
                    });
                },

                voidPayment(paymentId, paymentNumber) {
                    if (!confirm(`Are you sure you want to void payment "${paymentNumber}"? Remaining balance will be restored.`)) {
                        return;
                    }

                    fetch(`/admin/stock-in/payment/delete/${paymentId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 200) {
                            window.location.reload();
                        } else {
                            alert(res.message || 'Failed to void payment');
                        }
                    })
                    .catch(() => alert('Network error while voiding payment'));
                }
            }));
        });
    </script>
@stop
