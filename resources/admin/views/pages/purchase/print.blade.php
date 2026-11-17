<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchase->purchase_number }}</title>
    <style>
        body {
            font-family: 'Hanuman', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            color: #0f172a;
        }

        .print-actions {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-print {
            padding: 8px 18px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-close {
            padding: 8px 16px;
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
        }

        .purchase-voucher {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .voucher-header h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .voucher-meta-info {
            text-align: right;
            font-size: 13px;
            color: #475569;
        }

        .voucher-meta-info strong {
            color: #0f172a;
        }

        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 28px;
        }

        .party-box h4 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 8px 0;
            letter-spacing: 0.5px;
        }

        .party-box p {
            margin: 0 0 4px 0;
            font-size: 13px;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 24px;
        }

        .items-table th {
            background: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #334155;
        }

        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 36px;
        }

        .totals-box {
            width: 320px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #475569;
        }

        .totals-row.grand-total {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            margin-top: 6px;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 60px;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-actions {
                display: none;
            }
            .purchase-voucher {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn-close" onclick="window.close()">Close Window</button>
        <button class="btn-print" onclick="window.print()">Print Document</button>
    </div>

    <div class="purchase-voucher">
        <div class="voucher-header">
            <div>
                <h1>Purchase Order</h1>
                <div style="font-size: 14px; color: #2563eb; font-weight: 700;">
                    PO #: {{ $purchase->purchase_number }}
                </div>
                @if ($purchase->supplier_invoice_no)
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        Vendor Ref: {{ $purchase->supplier_invoice_no }}
                    </div>
                @endif
            </div>

            <div class="voucher-meta-info">
                <div>Date: <strong>{{ $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : '---' }}</strong></div>
                @if ($purchase->delivery_date)
                    <div>Expected Delivery: <strong>{{ $purchase->delivery_date->format('Y-m-d') }}</strong></div>
                @endif
                @if ($purchase->payment_due_date)
                    <div>Payment Due: <strong>{{ $purchase->payment_due_date->format('Y-m-d') }}</strong></div>
                @endif
                <div>Status: <strong>{{ strtoupper($purchase->status) }}</strong></div>
                <div>Payment: <strong>{{ strtoupper($purchase->payment_status) }}</strong></div>
            </div>
        </div>

        <div class="parties-grid">
            <div class="party-box">
                <h4>Vendor / Supplier:</h4>
                <p><strong>{{ $purchase->supplier?->name ?? '---' }}</strong></p>
                @if ($purchase->supplier?->contact_person)
                    <p>Attn: {{ $purchase->supplier->contact_person }}</p>
                @endif
                @if ($purchase->supplier?->phone)
                    <p>Phone: {{ $purchase->supplier->phone }}</p>
                @endif
                @if ($purchase->supplier?->email)
                    <p>Email: {{ $purchase->supplier->email }}</p>
                @endif
                @if ($purchase->supplier?->address)
                    <p>{{ $purchase->supplier->address }}</p>
                @endif
            </div>

            <div class="party-box" style="text-align: right;">
                <h4>Ship To / Branch:</h4>
                <p><strong>{{ $purchase->shop?->name ?? 'Headquarters' }}</strong></p>
                @if ($purchase->shop?->address)
                    <p>{{ $purchase->shop->address }}</p>
                @endif
                @if ($purchase->shop?->phone)
                    <p>Tel: {{ $purchase->shop->phone }}</p>
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Product Description</th>
                    <th>UOM</th>
                    <th style="text-align: right;">Ordered Qty</th>
                    <th style="text-align: right;">Unit Cost ($)</th>
                    <th style="text-align: right;">Discount ($)</th>
                    <th style="text-align: right;">Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $idx => $line)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $line->product?->name }}</strong>
                            @if ($line->product?->code)
                                <span style="font-size: 11px; color: #64748b;">({{ $line->product->code }})</span>
                            @endif
                        </td>
                        <td>{{ $line->uom?->name ?? $line->product?->uom?->name ?? '---' }}</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($line->ordered_qty, 2) }}</td>
                        <td style="text-align: right;">$ {{ number_format($line->unit_cost, 2) }}</td>
                        <td style="text-align: right;">$ {{ number_format($line->discount_amount, 2) }}</td>
                        <td style="text-align: right; font-weight: 700;">$ {{ number_format($line->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-box">
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <strong>$ {{ number_format($purchase->subtotal ?? 0, 2) }}</strong>
                </div>
                @if (($purchase->discount_amount ?? 0) > 0)
                    <div class="totals-row">
                        <span>Discount:</span>
                        <span>- $ {{ number_format($purchase->discount_amount ?? 0, 2) }}</span>
                    </div>
                @endif
                @if (($purchase->tax_amount ?? 0) > 0)
                    <div class="totals-row">
                        <span>Tax / VAT ({{ $purchase->tax_rate ?? 0 }}%):</span>
                        <span>+ $ {{ number_format($purchase->tax_amount ?? 0, 2) }}</span>
                    </div>
                @endif
                @if (($purchase->shipping_cost ?? 0) > 0)
                    <div class="totals-row">
                        <span>Shipping / Freight:</span>
                        <span>+ $ {{ number_format($purchase->shipping_cost ?? 0, 2) }}</span>
                    </div>
                @endif
                <div class="totals-row grand-total">
                    <span>Grand Total:</span>
                    <span>$ {{ number_format($purchase->total_amount ?? 0, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Paid to Date:</span>
                    <span>$ {{ number_format($purchase->paid_amount ?? 0, 2) }}</span>
                </div>
                <div class="totals-row" style="font-weight: 700; color: #dc2626;">
                    <span>Outstanding Due:</span>
                    <span>$ {{ number_format($purchase->remaining_amount ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        @if ($purchase->remark)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; margin-bottom: 24px; font-size: 12.5px;">
                <strong>Terms & Remarks:</strong>
                <p style="margin: 4px 0 0 0; color: #475569;">{{ $purchase->remark }}</p>
            </div>
        @endif

        <div class="signatures-grid">
            <div>
                <div class="sig-line">Prepared By (Purchaser)</div>
            </div>
            <div>
                <div class="sig-line">Authorized Signatory</div>
            </div>
            <div>
                <div class="sig-line">Vendor Acceptance</div>
            </div>
        </div>
    </div>
</body>
</html>
