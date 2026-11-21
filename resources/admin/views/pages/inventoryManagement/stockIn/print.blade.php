<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchase->purchase_number }}</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Khmer OS System", sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
            padding: 24px;
        }

        .print-actions-bar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-print-now {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: #2563eb;
            color: #ffffff;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-print-now:hover { background: #1d4ed8; }
        .btn-close-print {
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
        }

        .po-paper {
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 36px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .po-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 22px;
        }
        .po-shop-info h2 {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .po-shop-info p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        .po-doc-title {
            text-align: right;
        }
        .po-doc-title h1 {
            font-size: 22px;
            font-weight: 900;
            color: #2563eb;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .po-num-badge {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .po-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
        }
        .meta-block h4 {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .meta-block strong {
            font-size: 14px;
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
        }
        .meta-block p {
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
        }

        .po-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 12.5px;
        }
        .po-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            padding: 10px 12px;
            text-align: left;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11.5px;
            text-transform: uppercase;
        }
        .po-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .po-summary-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .po-summary-box {
            width: 280px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12.5px;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            color: #64748b;
        }
        .summary-line strong {
            color: #1e293b;
        }
        .summary-line.grand-total {
            border-top: 2px solid #1e293b;
            padding-top: 8px;
            margin-top: 4px;
            font-size: 14.5px;
            font-weight: 800;
            color: #1e293b;
        }
        .summary-line.grand-total span {
            color: #2563eb;
        }

        .po-signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 50px;
            padding-top: 20px;
            text-align: center;
        }
        .sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sig-line {
            width: 150px;
            border-bottom: 1px dashed #94a3b8;
            margin-bottom: 8px;
            height: 40px;
        }
        .sig-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
        }
        .sig-sub {
            font-size: 10px;
            color: #94a3b8;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-actions-bar {
                display: none !important;
            }
            .po-paper {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions-bar">
        <a href="javascript:window.close()" class="btn-close-print">&larr; Close Window</a>
        <button type="button" class="btn-print-now" onclick="window.print()">
            <i class='bx bx-printer'></i> Print Document
        </button>
    </div>

    <div class="po-paper">
        {{-- Header --}}
        <div class="po-header">
            <div class="po-shop-info">
                <h2>{{ $purchase->shop?->name ?? 'POS System' }}</h2>
                <p>{{ $purchase->shop?->address ?? '' }}</p>
                @if ($purchase->shop?->phone)
                    <p>Tel: {{ $purchase->shop->phone }}</p>
                @endif
            </div>

            <div class="po-doc-title">
                <h1>PURCHASE ORDER</h1>
                <div class="po-num-badge">{{ $purchase->purchase_number }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Date: {{ $purchase->created_date }}</div>
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="po-meta-grid">
            <div class="meta-block">
                <h4>Vendor / Supplier:</h4>
                <strong>{{ $purchase->supplier?->name ?? '---' }}</strong>
                @if ($purchase->supplier?->phone)
                    <p>Tel: {{ $purchase->supplier->phone }}</p>
                @endif
                @if ($purchase->supplier?->address)
                    <p>{{ $purchase->supplier->address }}</p>
                @endif
            </div>

            <div class="meta-block">
                <h4>Ship / Deliver To:</h4>
                <strong>{{ $purchase->shop?->name ?? 'Main Warehouse' }}</strong>
                <p>{{ $purchase->shop?->address ?? '' }}</p>
                @if ($purchase->expected_delivery_date)
                    <p style="margin-top: 4px;"><strong>Expected Date:</strong> {{ \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('d/M/Y') }}</p>
                @endif
                @if ($purchase->reference_no)
                    <p><strong>Ref / Bill #:</strong> {{ $purchase->reference_no }}</p>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <table class="po-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>Product Description</th>
                    <th style="width: 80px; text-align: center;">UOM</th>
                    <th style="width: 100px; text-align: right;">Unit Cost</th>
                    <th style="width: 90px; text-align: center;">Qty</th>
                    <th style="width: 110px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if ($item->remark)
                                <div style="font-size: 11px; color: #64748b;">{{ $item->remark }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->uom_title ?: '---' }}</td>
                        <td style="text-align: right;">{{ $item->formatted_unit_cost }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $item->qty }}</td>
                        <td style="text-align: right; font-weight: 700;">{{ $item->formatted_subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Financial Summary --}}
        <div class="po-summary-wrap">
            <div class="po-summary-box">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <strong>${{ number_format($purchase->subtotal, 2) }}</strong>
                </div>
                @if ($purchase->discount_amount > 0)
                    <div class="summary-line">
                        <span>Discount:</span>
                        <strong>-${{ number_format($purchase->discount_amount, 2) }}</strong>
                    </div>
                @endif
                @if ($purchase->tax_amount > 0)
                    <div class="summary-line">
                        <span>Tax:</span>
                        <strong>+${{ number_format($purchase->tax_amount, 2) }}</strong>
                    </div>
                @endif
                @if ($purchase->shipping_cost > 0)
                    <div class="summary-line">
                        <span>Shipping / Freight:</span>
                        <strong>+${{ number_format($purchase->shipping_cost, 2) }}</strong>
                    </div>
                @endif
                <div class="summary-line grand-total">
                    <span>Grand Total:</span>
                    <span>{{ $purchase->formatted_total_amount }}</span>
                </div>
                <div class="summary-line" style="margin-top: 6px; font-size: 11.5px;">
                    <span>Paid Amount:</span>
                    <strong style="color: #059669;">{{ $purchase->formatted_paid_amount }}</strong>
                </div>
                <div class="summary-line" style="font-size: 11.5px;">
                    <span>Balance Due:</span>
                    <strong style="color: {{ $purchase->remaining_amount > 0 ? '#dc2626' : '#64748b' }};">{{ $purchase->formatted_remaining_amount }}</strong>
                </div>
            </div>
        </div>

        @if ($purchase->remark)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 24px; font-size: 12px;">
                <strong style="display: block; margin-bottom: 2px;">General Notes / Instructions:</strong>
                <span style="color: #475569;">{{ $purchase->remark }}</span>
            </div>
        @endif

        {{-- Signatures --}}
        <div class="po-signatures">
            <div class="sig-col">
                <div class="sig-line"></div>
                <span class="sig-label">Prepared By</span>
                <span class="sig-sub">{{ $purchase->created_by_title }}</span>
            </div>

            <div class="sig-col">
                <div class="sig-line"></div>
                <span class="sig-label">Authorized Signature</span>
                <span class="sig-sub">Management Approval</span>
            </div>

            <div class="sig-col">
                <div class="sig-line"></div>
                <span class="sig-label">Vendor Acceptance</span>
                <span class="sig-sub">{{ $purchase->supplier_title }}</span>
            </div>
        </div>
    </div>
</body>
</html>
