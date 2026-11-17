@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('purchase.form.title.view')])

    <style>
        .purchase-view-wrapper {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .purchase-view-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }

        .purchase-view-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .purchase-view-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .view-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .view-card-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .view-card-header h3 {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .view-card-body {
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
        }

        .info-box h4 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin: 0 0 12px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #334155;
        }

        .info-line .label {
            color: #64748b;
        }

        .info-line .val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Line items table */
        .view-table-wrapper {
            overflow-x: auto;
        }

        .view-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .view-table thead th {
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

        .view-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .view-table tbody tr:hover {
            background: #f8fafc;
        }

        .summary-totals-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            max-width: 400px;
            margin-left: auto;
        }

        .summary-totals-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: #475569;
            border-bottom: 1px dashed #e2e8f0;
        }

        .summary-totals-line.grand-total {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: none;
            padding-top: 14px;
            margin-top: 6px;
            border-top: 2px solid #cbd5e1;
        }

        .btn-action-primary {
            height: 40px;
            padding: 0 16px;
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
            transition: background 0.15s ease;
        }

        .btn-action-primary:hover { background: #1d4ed8; color: #fff; }

        .btn-action-outline {
            height: 40px;
            padding: 0 14px;
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
            transition: all 0.15s ease;
        }

        .btn-action-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge-draft { background: #f1f5f9; color: #475569; }
        .status-badge-ordered { background: #e0e7ff; color: #4338ca; }
        .status-badge-partial-received { background: #fef3c7; color: #b45309; }
        .status-badge-received { background: #dcfce7; color: #15803d; }
        .status-badge-cancelled { background: #fee2e2; color: #b91c1c; }

        .payment-badge-unpaid { background: #fee2e2; color: #b91c1c; }
        .payment-badge-partial { background: #fef3c7; color: #b45309; }
        .payment-badge-paid { background: #dcfce7; color: #15803d; }
    </style>

    <div class="purchase-view-wrapper" x-data="xPurchaseView">
        {{-- View Header --}}
        <div class="purchase-view-header">
            <div class="purchase-view-title">
                <h2>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 'all') !!}" style="cursor: pointer;"></i>
                    <span>Purchase: {{ $purchase->purchase_number }}</span>
                    <span class="badge {{ $purchase->status_badge['class'] }}">{{ $purchase->status_badge['label'] }}</span>
                    <span class="badge {{ $purchase->payment_status_badge['class'] }}">{{ $purchase->payment_status_badge['label'] }}</span>
                </h2>
                <div style="font-size: 13px; color: #64748b;">
                    Created on {{ $purchase->purchase_date ? $purchase->purchase_date->format('M d, Y') : '---' }} by <strong>{{ $purchase->createdBy?->name ?? 'Admin' }}</strong>
                </div>
            </div>

            <div class="purchase-view-actions">
                <a href="{{ route('admin-purchase-print', $purchase->id) }}" target="_blank" class="btn-action-outline">
                    <i class='bx bx-printer'></i>
                    <span>Print Order</span>
                </a>

                @if ($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                    @can('purchase-update')
                        <a href="{{ route('admin-purchase-edit', $purchase->id) }}" class="btn-action-outline">
                            <i class='bx bx-edit-alt'></i>
                            <span>Edit PO</span>
                        </a>
                    @endcan

                    @can('purchase-receive')
                        <button type="button" @click="showReceiveDialog = true" class="btn-action-primary" style="background: #059669;">
                            <i class='bx bx-package'></i>
                            <span>Receive Goods</span>
                        </button>
                    @endcan
                @endif

                @if ($purchase->remaining_amount > 0 && $purchase->status !== 'cancelled')
                    @can('purchase-payment')
                        <button type="button" @click="showPaymentDialog = true" class="btn-action-primary">
                            <i class='bx bx-credit-card'></i>
                            <span>Record Payment</span>
                        </button>
                    @endcan
                @endif

                @if ($purchase->status !== 'cancelled')
                    @can('purchase-delete')
                        <form action="{{ route('admin-purchase-cancel', $purchase->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this purchase order? If goods were received, stock on hand will be safely reversed.');"
                              style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-action-outline" style="color: #dc2626; border-color: #fca5a5;">
                                <i class='bx bx-x-circle'></i>
                                <span>Cancel Order</span>
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Info Cards Grid --}}
        <div class="info-grid" style="margin-bottom: 20px;">
            {{-- Supplier Box --}}
            <div class="info-box">
                <h4><i class='bx bx-building'></i> Supplier Information</h4>
                <div class="info-line">
                    <span class="label">Supplier Name:</span>
                    <span class="val">{{ $purchase->supplier?->name ?? '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Contact Person:</span>
                    <span class="val">{{ $purchase->supplier?->contact_person ?? '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Phone:</span>
                    <span class="val">{{ $purchase->supplier?->phone ?? '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Email:</span>
                    <span class="val">{{ $purchase->supplier?->email ?? '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Vendor Invoice Ref:</span>
                    <span class="val">{{ $purchase->supplier_invoice_no ?? '---' }}</span>
                </div>
            </div>

            {{-- Shop / Delivery Box --}}
            <div class="info-box">
                <h4><i class='bx bx-store-alt'></i> Receiving Shop & Logistics</h4>
                <div class="info-line">
                    <span class="label">Receiving Shop:</span>
                    <span class="val">{{ $purchase->shop?->name ?? '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Purchase Date:</span>
                    <span class="val">{{ $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Expected Delivery:</span>
                    <span class="val">{{ $purchase->delivery_date ? $purchase->delivery_date->format('Y-m-d') : '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Payment Due Date:</span>
                    <span class="val">{{ $purchase->payment_due_date ? $purchase->payment_due_date->format('Y-m-d') : '---' }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Received Timestamp:</span>
                    <span class="val">{{ $purchase->received_at ? $purchase->received_at->format('Y-m-d H:i') : 'Not Yet' }}</span>
                </div>
            </div>

            {{-- Payment & AP Summary Box --}}
            <div class="info-box">
                <h4><i class='bx bx-wallet'></i> Financial & Payment Summary</h4>
                <div class="info-line">
                    <span class="label">Total Invoice Value:</span>
                    <span class="val" style="font-size: 15px;">{{ $purchase->formatted_total }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Amount Paid:</span>
                    <span class="val" style="color: #059669;">{{ $purchase->formatted_paid }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Remaining Balance (AP):</span>
                    <span class="val" style="color: #dc2626; font-size: 15px;">{{ $purchase->formatted_remaining }}</span>
                </div>
                <div class="info-line">
                    <span class="label">Payment Status:</span>
                    <span class="badge {{ $purchase->payment_status_badge['class'] }}">{{ $purchase->payment_status_badge['label'] }}</span>
                </div>
            </div>
        </div>

        {{-- Line Items Card --}}
        <div class="view-card">
            <div class="view-card-header">
                <h3><i class='bx bx-list-check'></i> Purchased Line Items ({{ $purchase->items->count() }})</h3>
            </div>
            <div class="view-card-body" style="padding: 0;">
                <div class="view-table-wrapper">
                    <table class="view-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('purchase.table.product') }}</th>
                                <th>{{ __('purchase.table.uom') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.ordered_qty') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.received_qty') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.pending_qty') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.unit_cost') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.discount') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.tax') }}</th>
                                <th style="text-align: right;">{{ __('purchase.table.subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $idx => $line)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <strong>{{ $line->product?->name ?? '---' }}</strong>
                                        @if ($line->product?->code)
                                            <span style="font-size: 11px; color: #64748b;">({{ $line->product->code }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $line->uom?->name ?? $line->product?->uom?->name ?? '---' }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format($line->ordered_qty, 2) }}</td>
                                    <td style="text-align: right; color: #059669; font-weight: 600;">{{ number_format($line->received_qty, 2) }}</td>
                                    <td style="text-align: right; color: {{ $line->pending_qty > 0 ? '#d97706' : '#64748b' }};">
                                        {{ number_format($line->pending_qty, 2) }}
                                    </td>
                                    <td style="text-align: right;">{{ $line->formatted_unit_cost }}</td>
                                    <td style="text-align: right;">$ {{ number_format($line->discount_amount, 2) }}</td>
                                    <td style="text-align: right;">$ {{ number_format($line->tax_amount, 2) }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ $line->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals Summary --}}
                <div style="padding: 20px;">
                    <div class="summary-totals-card">
                        <div class="summary-totals-line">
                            <span>Subtotal:</span>
                            <strong>$ {{ number_format($purchase->subtotal ?? 0, 2) }}</strong>
                        </div>
                        <div class="summary-totals-line">
                            <span>Discount:</span>
                            <span style="color: #dc2626;">- $ {{ number_format($purchase->discount_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-totals-line">
                            <span>Tax ({{ $purchase->tax_rate ?? 0 }}%):</span>
                            <span>+ $ {{ number_format($purchase->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-totals-line">
                            <span>Shipping / Freight:</span>
                            <span>+ $ {{ number_format($purchase->shipping_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-totals-line grand-total">
                            <span>Grand Total:</span>
                            <span style="color: #2563eb;">{{ $purchase->formatted_total }}</span>
                        </div>
                        <div class="summary-totals-line">
                            <span>Amount Paid:</span>
                            <span style="color: #059669;">{{ $purchase->formatted_paid }}</span>
                        </div>
                        <div class="summary-totals-line" style="border-bottom: none;">
                            <span style="font-weight: 600;">Balance Due (AP):</span>
                            <strong style="color: #dc2626; font-size: 15px;">{{ $purchase->formatted_remaining }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History Card --}}
        <div class="view-card">
            <div class="view-card-header">
                <h3><i class='bx bx-history'></i> Supplier Payment Transactions ({{ $purchase->payments->count() }})</h3>
                @if ($purchase->remaining_amount > 0 && $purchase->status !== 'cancelled')
                    @can('purchase-payment')
                        <button type="button" @click="showPaymentDialog = true" class="btn-action-primary" style="height: 32px; font-size: 12px;">
                            <i class='bx bx-plus'></i>
                            <span>Add Payment</span>
                        </button>
                    @endcan
                @endif
            </div>
            <div class="view-card-body" style="padding: 0;">
                <div class="view-table-wrapper">
                    <table class="view-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Amount ($)</th>
                                <th>Method</th>
                                <th>Reference / Cheque #</th>
                                <th>Slip</th>
                                <th>Recorded By</th>
                                <th>Note</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchase->payments as $pidx => $pay)
                                <tr>
                                    <td>{{ $pidx + 1 }}</td>
                                    <td><strong>{{ $pay->payment_number }}</strong></td>
                                    <td>{{ $pay->payment_date ? $pay->payment_date->format('Y-m-d') : '---' }}</td>
                                    <td><strong style="color: #059669;">{{ $pay->formatted_amount }}</strong></td>
                                    <td>{{ $pay->payment_method }}</td>
                                    <td>{{ $pay->reference_no ?? '---' }}</td>
                                    <td>
                                        @if ($pay->receipt_path)
                                            <a href="{{ asset($pay->receipt_path) }}" target="_blank" style="color: #2563eb; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class='bx bx-paperclip'></i> View Slip
                                            </a>
                                        @else
                                            <span style="color: #94a3b8;">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $pay->createdBy?->name ?? '---' }}</td>
                                    <td>{{ $pay->note ?? '---' }}</td>
                                    <td style="text-align: right;">
                                        @can('purchase-payment')
                                            <form action="{{ route('admin-purchase-destroy-payment', $pay->id) }}" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this payment record? The purchase balance will be recalculated.');"
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-table-icon text-danger" title="Delete Payment">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 24px; color: #94a3b8;">
                                        No payments recorded for this purchase order yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Goods Receive Modal --}}
        <div class="modal-overlay" x-show="showReceiveDialog" style="display: none;" @click.self="showReceiveDialog = false">
            <div class="modal-card" style="max-width: 600px;">
                <form action="{{ route('admin-purchase-receive', $purchase->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3>Receive Goods into Inventory</h3>
                        <button type="button" @click="showReceiveDialog = false" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 13px; color: #475569; margin: 0 0 16px 0;">
                            Verify the delivered quantities below. Received stock will be added to <strong>{{ $purchase->shop?->name }}</strong>.
                        </p>

                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <table class="view-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Ordered</th>
                                        <th>Already Received</th>
                                        <th style="width: 140px;">Receive Now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->product?->name }}</strong>
                                            </td>
                                            <td>{{ number_format($item->ordered_qty, 2) }}</td>
                                            <td>{{ number_format($item->received_qty, 2) }}</td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="{{ $item->pending_qty }}"
                                                       name="received_qty[{{ $item->id }}]"
                                                       value="{{ $item->pending_qty }}"
                                                       style="width: 100%; height: 34px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px;">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showReceiveDialog = false" class="btn-action-outline">Cancel</button>
                        <button type="submit" class="btn-action-primary" style="background: #059669;">
                            <i class='bx bx-check'></i>
                            <span>Process Receiving</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Payment Modal --}}
        <div class="modal-overlay" x-show="showPaymentDialog" style="display: none;" @click.self="showPaymentDialog = false">
            <div class="modal-card">
                <form action="{{ route('admin-purchase-store-payment', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h3>Record Payment ({{ $purchase->purchase_number }})</h3>
                        <button type="button" @click="showPaymentDialog = false" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-size: 12px; font-weight: 600;">REMAINING DUE:</span>
                            <strong style="color: #dc2626; font-size: 14px;">{{ $purchase->formatted_remaining }}</strong>
                        </div>

                        <div class="form-row-field" style="margin-bottom: 14px;">
                            <label>Payment Amount ($) <span style="color: #dc2626;">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="{{ $purchase->remaining_amount }}"
                                   name="amount" value="{{ $purchase->remaining_amount }}" required>
                        </div>

                        <div class="form-row-field" style="margin-bottom: 14px;">
                            <label>Payment Date <span style="color: #dc2626;">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-row-field" style="margin-bottom: 14px;">
                            <label>Payment Method <span style="color: #dc2626;">*</span></label>
                            <select name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="ABA Pay">ABA Pay</option>
                                <option value="Wing">Wing</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-row-field" style="margin-bottom: 14px;">
                            <label>Reference No / Slip #</label>
                            <input type="text" name="reference_no" placeholder="Bank transaction ID or Cheque #">
                        </div>

                        <div class="form-row-field" style="margin-bottom: 14px;">
                            <label>Attachment Slip (Image/PDF)</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" style="padding: 7px 12px;">
                        </div>

                        <div class="form-row-field">
                            <label>Note / Remarks</label>
                            <textarea name="note" rows="2" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; outline: none;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showPaymentDialog = false" class="btn-action-outline">Cancel</button>
                        <button type="submit" class="btn-action-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        (function() {
            const registerXPurchaseView = () => {
                Alpine.data('xPurchaseView', () => ({
                    showReceiveDialog: false,
                    showPaymentDialog: false,
                }));
            };

            if (window.Alpine) {
                registerXPurchaseView();
            } else {
                document.addEventListener('alpine:init', registerXPurchaseView);
            }
        })();
    </script>
@stop
