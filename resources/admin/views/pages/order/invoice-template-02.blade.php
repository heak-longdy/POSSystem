@php
    $printLocale = $printLocale ?? app()->getLocale();
    $isKm = $printLocale === 'km';
    $order = $order ?? null;

    $shopName = $order?->shop?->name ?: '---';
    $shopPhone = $order?->shop?->phone ?: '---';
    $shopAddress = $order?->shop?->address ?: '---';
    $shopCity = $order?->shop?->city ?: '---';

    $cName = $customerName ?? ($order?->customer?->name ?: ($order?->customer?->phone ?: __('order.walk_in_customer', [], $printLocale)));
    $cPhone = $customerPhone ?? ($order?->customer?->phone ?: '---');
    $cAddress = $customerAddress ?? ($order?->customer?->address ?: '---');
    $customerCode = trim((string) ($order?->customer?->code ?? ''));

    $invoiceNo = $order?->invoice_number ?: ($order?->id ? sprintf('%05d', $order->id) : '---');
    $orderDate = $order?->order_date ? \Carbon\Carbon::parse($order->order_date) : now();
    $deliveryDate = $order?->delivery_date
        ? \Carbon\Carbon::parse($order->delivery_date)
        : null;
    $creatorPhone = $creatorPhone ?? ($order?->createdBy?->phone ?: '---');

    $details = $order?->orderDetails ?: collect();
    $invoicePreviousBalance = $invoicePreviousBalance ?? (float) ($order?->previous_balance ?? 0);
    $printAreaId = $printAreaId ?? "posSlipPrintArea_{$printLocale}";
    $latestPaymentRecord = $latestPaymentRecord ?? ($order && method_exists($order, 'getLatestPaymentRecord') ? $order->getLatestPaymentRecord() : null);
    $latestPaymentNote = trim((string) ($latestPaymentNote ?? ($latestPaymentRecord?->note ?? '')));
@endphp

<style>
.trHeight{
    height: 30px !important;
}
</style>

<div class="pos-slip-sheet" id="{{ $printAreaId }}">
    <!-- Header Top Section -->
    <div class="slip-top-row">
        <div class="slip-top-left"></div>
        <div class="slip-top-center">
            <h1 class="slip-title-khmer">{{ __('order.invoice.slip_title', [], $printLocale) }}</h1>
        </div>
        <div class="slip-top-right">
            <div class="slip-meta-text">{{ $orderDate->format('n/j/y') }} {{ $orderDate->format('g:iA') }} <span class="draft-tag">{{ __('order.invoice.slip_draft', [], $printLocale) }}</span></div>
            <div class="slip-meta-item">
                <span class="lbl">{{ __('order.invoice.slip_inv_no', [], $printLocale) }}</span> 
                <strong class="val">{{ $invoiceNo }}</strong>
            </div>
            <div class="slip-meta-item">
                <span class="lbl">{{ __('order.invoice.slip_issue_date', [], $printLocale) }}</span> 
                <span class="val">{{ $orderDate->format('d/m/Y') }}</span>
            </div>
            <div class="slip-meta-item">
                <span class="lbl">{{ __('order.invoice.slip_delivery_date', [], $printLocale) }}</span> 
                <span class="val">{{ $deliveryDate ? $deliveryDate->format('d/m/Y') : '---' }}</span>
            </div>
        </div>
    </div>

    <!-- 3-Column Info Matrix (Customer, Depot/Shop, District/Branch) -->
    <div class="slip-info-matrix">
        <!-- Customer Info -->
        <div class="slip-info-col col-cust">
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_cust_name', [], $printLocale) }}</span>
                <span class="info-val"><span class="cust-code">{{ $cName }}@if ($customerCode !== '')&nbsp;({{ $customerCode }})@endif</span></span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_cust_address', [], $printLocale) }}</span>
                <span class="info-val">{{ $cAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.tel', [], $printLocale) }}</span>
                <span class="info-val">{{ $cPhone }}</span>
            </div>
        </div>

        <!-- Depot / Shop Info -->
        <div class="slip-info-col col-depot">
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_depot_name', [], $printLocale) }}</span>
                <strong class="info-val">{{ $shopName }}</strong>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_depot_address', [], $printLocale) }}</span>
                <span class="info-val">{{ $shopAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.tel', [], $printLocale) }}</span>
                <span class="info-val">{{ $shopPhone }}</span>
            </div>
        </div>

        <!-- District & Delivery Branch Info -->
        <div class="slip-info-col col-dist">
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_district', [], $printLocale) }}</span>
                <span class="info-val">{{ $shopCity }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.detail.created_by', [], $printLocale) }}:</span>
                <span class="info-val">{{ $order?->createdBy?->name ?: '---' }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.detail.phone', [], $printLocale) }}:</span>
                <span class="info-val">{{ $creatorPhone }}</span>
            </div>
        </div>
    </div>

    <!-- Main Slip Table with 10 Columns Matching invoice_02.png -->
    <table class="slip-grid-table">
        <thead>
            <tr>
                <th rowspan="2" class="th-no">{{ __('order.invoice.slip_col_no', [], $printLocale) }}</th>
                <th rowspan="2" class="th-code">{{ __('order.invoice.slip_col_code', [], $printLocale) }}</th>
                <th rowspan="2" class="th-desc">{{ __('order.invoice.slip_col_desc', [], $printLocale) }}</th>
                <th rowspan="2" class="th-uom">{{ __('order.invoice.slip_col_uom', [], $printLocale) }}</th>
                <th class="th-qty">{{ __('order.invoice.slip_col_qty', [], $printLocale) }}</th>
                <th class="th-deliv-qty">{{ __('order.invoice.slip_col_deliv_qty', [], $printLocale) }}</th>
                <th rowspan="2" class="th-price">{{ __('order.invoice.slip_col_cost', [], $printLocale) }}</th>
                <th rowspan="2" class="th-disc">
                    {{ __('order.invoice.slip_col_discount', [], $printLocale) }}<br>
                    <span class="th-subtext">{{ __('order.invoice.slip_col_promo', [], $printLocale) }}</span>
                </th>
                <th rowspan="2" class="th-net">
                    {{ __('order.invoice.slip_col_net_price', [], $printLocale) }}<br>
                    <span class="th-subtext">{{ __('order.invoice.slip_col_net_sub', [], $printLocale) }}</span>
                </th>
                <th rowspan="2" class="th-total">{{ __('order.invoice.slip_col_total', [], $printLocale) }}</th>
            </tr>
            <tr class="th-sub-row">
                <th class="th-sub-col th-sub-qty">{{ __('order.invoice.slip_col_case', [], $printLocale) }}</th>
                <th class="th-sub-col th-sub-deliv">{{ __('order.invoice.slip_col_case', [], $printLocale) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $detail)
                @php
                    $isService = $detail->type === 'service';
                    $rawItemName = $isService ? ($detail->service?->name ?? '---') : ($detail->product?->name ?? '---');
                    if (is_string($rawItemName) && str_starts_with(trim($rawItemName), '{')) {
                        $decoded = json_decode($rawItemName, true);
                        $itemName = $decoded[$printLocale] ?? ($decoded['km'] ?? ($decoded['en'] ?? $rawItemName));
                    } else {
                        $itemName = $rawItemName;
                    }
                    $itemCode = $detail->product?->code ?: ($detail->product?->barcode ?: ($detail->product_id ? sprintf('%04d', $detail->product_id) : '---'));
                    $uomName = $detail->product?->uom?->name ?: '---';
                    $price = $detail->price;
                    $unitPrice = (float) ($detail->unit_price ??  0);
                    $productDisc = (float) ($detail->product_discount ?? 0);
                    $qty = (int) ($detail->qty ?: 1);
                    $line = app(\App\Services\OrderPricing::class)->line($detail);
                    $disc = $line['discount'] / max(1, $qty);
                    $netUnitPrice = $unitPrice;
                    $lineTotal = $line['total'];
                @endphp
                <tr class="slip-row">
                    <td class="td-no">{{ $index + 1 }}</td>
                    <td class="td-code">{{ $itemCode }}</td>
                    <td class="td-desc">{{ $itemName }}</td>
                    <td class="td-uom">{{ $uomName }}</td>
                    <td class="td-cases td-qty">{{ $qty }}</td>
                    <td class="td-cases td-deliv-qty">{{ $qty }}</td>
                    <td class="td-price">${{ number_format($price, 2) }}</td>
                    <td class="td-disc">${{ $disc > 0 ? number_format($productDisc, 2) : '0.00' }}</td>
                    <td class="td-net">${{ number_format($netUnitPrice, 2) }}</td>
                    <td class="td-total">${{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr class="slip-row">
                    <td colspan="10" class="slip-empty" style="text-align: center; color: #64748b; padding: 12px 6px;">
                        {{ __('order.empty_cart.title', [], $printLocale) }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bottom Table Extension & Summary -->
    <div class="slip-table-bottom-bar">
        <div class="slip-bottom-left">
            @if ($latestPaymentNote !== '')
                <span class="slip-free-goods-note">
                    <strong>{{ __('order.note', [], $printLocale) }}:</strong> {!! nl2br(e($latestPaymentNote)) !!}
                </span>
            @endif
        </div>
        <div class="slip-bottom-right">
            <table class="slip-calc-table">
                <tbody>
                    <tr class="trHeight">
                        <td class="slip-calc-label inv-foot-calc-label">{{ __('order.invoice.prev_balance', [], $printLocale) }}</td>
                        <td class="slip-calc-value inv-foot-calc-value">${{ number_format((float) ($invoicePreviousBalance ?? 0), 2) }}</td>
                    </tr>
                    <tr class="trHeight">
                        <td class="slip-calc-label inv-foot-calc-label font-bold">{{ __('order.invoice.subtotal', [], $printLocale) }}</td>
                        <td class="slip-calc-value inv-foot-calc-value font-bold">
                            ${{ number_format((float) ($order?->total_price ?? 0) + (float) ($invoicePreviousBalance ?? 0), 2) }}
                        </td>
                    </tr>
                    <tr class="trHeight">
                        <td class="slip-calc-label inv-foot-calc-label">{{ __('order.invoice.paid', [], $printLocale) }}</td>
                        <td class="slip-calc-value inv-foot-calc-value">
                            {{-- {{ (float) ($order->paid_amount ?? 0) > 0 ? '$' . number_format((float) $order->paid_amount, 2) : '' }} --}}
                        </td>
                    </tr>
                    {{-- ${{ number_format(max(0, (float) ($order->remaining_amount ?? 0)), 2) }} --}}
                    <tr class="trHeight">
                        <td class="slip-calc-label inv-foot-calc-label">{{ __('order.invoice.balance_due', [], $printLocale) }}</td>
                        <td class="slip-calc-value inv-foot-calc-value"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dual Signatures Section -->
    <div class="slip-signatures-section">
        <div class="slip-sig-col">
            <div class="sig-title">{{ __('order.invoice.slip_driver_signature', [], $printLocale) }}</div>
            <div class="sig-underline"></div>
        </div>
        <div class="slip-sig-col">
            <div class="sig-title">{{ __('order.invoice.slip_customer_signature', [], $printLocale) }}</div>
            <div class="sig-underline"></div>
        </div>
    </div>

    <!-- Footer Disclaimer Note -->
    <div class="slip-footer-disclaimer">
        {{ __('order.invoice.slip_disclaimer', [], $printLocale) }}
    </div>
</div>
