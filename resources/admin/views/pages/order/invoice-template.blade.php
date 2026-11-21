@php
    $printLocale = $printLocale ?? app()->getLocale();
    $isKm = $printLocale === 'km';
    $order = $order ?? null;

    $shopName = $order?->shop?->name ?: 'N/A';
    $shopSub = $order?->shop?->nick_name ?: '';
    $shopPhone = $order?->shop?->phone ?: 'N/A';
    $shopAddress = $order?->shop?->address ?: 'N/A';

    $cName = $customerName ?? ($order?->customer?->name ?: ($order?->customer?->phone ?: __('order.walk_in_customer', [], $printLocale)));
    $cPhone = $customerPhone ?? ($order?->customer?->phone ?: 'N/A');
    $cAddress = $customerAddress ?? ($order?->customer?->address ?: 'N/A');

    $invoiceNo = $order?->invoice_number ?: ($order?->id ? sprintf('%05d', $order->id) : 'N/A');
    $orderDate = $order?->order_date ? \Carbon\Carbon::parse($order->order_date) : now();
    $cashierName = $order?->createdBy?->name ?: ($order?->payments?->first()?->createdBy?->name ?: ($order?->barber?->name ?: 'N/A'));

    $paymentStatus = $order?->payment_status ?: 'Pending';
    $buyerStatusNote = match ($paymentStatus) {
        'Paid' => __('order.invoice.status_paid', [], $printLocale),
        'Partial' => __('order.invoice.status_partial', [], $printLocale),
        default => __('order.invoice.status_unpaid', [], $printLocale),
    };

    $details = $order?->orderDetails ?: collect();
    $minRows = 0;
    $emptyRowsCount = max(0, $minRows - $details->count());
    $invoicePreviousBalance = $invoicePreviousBalance ?? (float) ($order?->previous_balance ?? 0);
    $printAreaId = $printAreaId ?? "posInvoicePrintArea_{$printLocale}";
@endphp

<style>
.trHeight{
    height: 30px !important;
}
.inv-shop-phone {
    font-size: 11.5px !important;
}
.inv-copy-notice {
    font-size: 11.5px !important;
}
.inv-signature-col .sig-status-tag {
    font-size: 11.5px !important;
}
.inv-signature-col .sig-action-label {
    font-size: 11.5px !important;
}
</style>

<div class="pos-invoice-sheet" id="{{ $printAreaId }}">
    <!-- Invoice Header Block -->
    <div class="inv-header-row">
        <!-- Left: Logo & Company Name -->
        <div class="inv-brand-box">
            @if (!empty($order?->shop?->image_url))
                <img src="{{ $order->shop->image_url }}" alt="{{ $shopName }}" class="inv-shop-logo-img"
                    style="display: none;" onload="this.style.display = '';" onerror="this.style.display = 'none';">
            @endif

            <div class="inv-shop-text">
                <h2 class="inv-shop-title-kh">{{ $shopName }}</h2>
                @if ($shopSub)
                    <div class="inv-shop-subtitle-en">{{ $shopSub }}</div>
                @endif
                @if ($shopPhone && $shopPhone !== 'N/A')
                    <div class="inv-shop-phone">
                        <span class="phone-label">{{ __('order.invoice.tel', [], $printLocale) }}</span> {{ $shopPhone }}
                    </div>
                @else
                    <div class="inv-shop-phone">
                        <span class="phone-label">{{ __('order.invoice.tel', [], $printLocale) }}</span> N/A
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Official Invoice Title -->
        <div class="inv-official-title-box">
            <div class="inv-copy-notice">{{ __('order.invoice.invoice_copy_original', [], $printLocale) }}</div>
            <div class="inv-main-heading">
                @if ($isKm)
                    <span class="khmer-title">វិក្កយបត្រ</span>
                    <span class="en-title">INVOICE</span>
                @else
                    <span class="en-title">INVOICE</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Customer & Invoice Metadata Bar -->
    <div class="inv-metadata-row">
        <div class="inv-meta-left">
            <div class="inv-meta-item">
                <span class="meta-label">{{ __('order.invoice.customer', [], $printLocale) }}</span>
                <strong class="meta-value">{{ $cName ?: 'N/A' }}</strong>
                @if ($cPhone && $cPhone !== '---' && $cPhone !== 'N/A')
                    <span class="meta-phone">({{ $cPhone }})</span>
                @else
                    <span class="meta-phone">(N/A)</span>
                @endif
            </div>
            <div class="inv-meta-item meta-address-line">
                <span class="meta-label">{{ __('order.invoice.address', [], $printLocale) }}</span>
                <span class="meta-value">{{ $cAddress ?: 'N/A' }}</span>
            </div>
        </div>

        <div class="inv-meta-right">
            <div class="inv-meta-item">
                <span class="meta-label">{{ __('order.invoice.invoice_no', [], $printLocale) }}</span>
                <strong class="meta-value inv-number">{{ $invoiceNo }}</strong>
            </div>
            <div class="inv-meta-item">
                <span class="meta-label">{{ __('order.invoice.date', [], $printLocale) }}</span>
                <span class="meta-value">
                    @if ($isKm)
                        ថ្ងៃទី{{ $orderDate->format('d') }}ខែ{{ $orderDate->format('m') }}ឆ្នាំ {{ $orderDate->format('Y') }}
                    @else
                        {{ $orderDate->format('d/m/Y') }}
                    @endif
                </span>
            </div>
            <div class="inv-meta-item">
                <span class="meta-label">{{ __('order.invoice.operator', [], $printLocale) }}</span>
                <span class="meta-value">{{ $cashierName }}</span>
            </div>
        </div>
    </div>

    <!-- Invoice Grid Table -->
    <table class="inv-grid-table">
        <thead>
            <tr>
                <th class="col-num">{{ __('order.invoice.col_no', [], $printLocale) }}</th>
                <th class="col-desc">{{ __('order.invoice.col_desc', [], $printLocale) }}</th>
                <th class="col-qty">{{ __('order.invoice.col_qty', [], $printLocale) }}</th>
                <th class="col-price">{{ __('order.invoice.col_price', [], $printLocale) }}</th>
                <th class="col-total">{{ __('order.invoice.col_total', [], $printLocale) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $detail)
                @php
                    $isService = $detail->type === 'service';
                    $rawItemName = $isService ? ($detail->service?->name ?? 'N/A') : ($detail->product?->name ?? 'N/A');
                    if (is_string($rawItemName) && str_starts_with(trim($rawItemName), '{')) {
                        $decoded = json_decode($rawItemName, true);
                        $itemName = $decoded[$printLocale] ?? ($decoded['km'] ?? ($decoded['en'] ?? $rawItemName));
                    } else {
                        $itemName = $rawItemName;
                    }
                    $itemName = $itemName ?: 'N/A';
                    $isBonus = (bool) ($detail->is_bonus ?? false);
                    $qty = \App\Models\OrderDetail::formatQuantity($detail->qty);
                    $unitPrice = $isBonus ? 0.00 : (float) ($detail->unit_price ?? ($detail->price ?? 0));
                    $line = app(\App\Services\OrderPricing::class)->line($detail);
                @endphp
                <tr class="inv-item-row {{ $isBonus ? 'inv-item-row-bonus' : '' }}">
                    <td class="cell-num">{{ $index + 1 }}</td>
                    <td class="cell-desc">
                        <span class="item-name">{{ $itemName }}</span>
                        @if ($isBonus)
                            <span class="slip-bonus-badge" style="display: inline-block; font-weight: 700; color: #047857; margin-left: 4px; font-size: 10px;">
                                ({{ __('order.bonus_free_tag', [], $printLocale) }})
                            </span>
                        @endif
                    </td>
                    <td class="cell-qty">{{ $qty }}</td>
                    <td class="cell-price">${{ number_format($unitPrice, 2) }}</td>
                    <td class="cell-total">${{ number_format($line['total'], 2) }}</td>
                </tr>
            @empty
                <tr class="inv-item-row">
                    <td colspan="5" class="cell-empty" style="text-align: center; color: #64748b; padding: 12px 6px;">
                        {{ __('order.empty_cart.title', [], $printLocale) }}
                    </td>
                </tr>
            @endforelse

            {{-- Fill empty rows to preserve fixed dot-matrix paper proportions like invoice_01.png --}}
            @for ($i = 0; $i < $emptyRowsCount; $i++)
                <tr class="inv-empty-row">
                    <td class="cell-num">&nbsp;</td>
                    <td class="cell-desc">&nbsp;</td>
                    <td class="cell-qty">&nbsp;</td>
                    <td class="cell-price">&nbsp;</td>
                    <td class="cell-total">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <!-- Dual Signatures (Cols 1-3) & Financial Summary (Cols 4-5) -->
            <tr class="trHeight">
                <td colspan="3" rowspan="4" class="inv-foot-signatures-cell">
                    <div class="inv-signatures-wrap">
                        <div class="inv-signature-col buyer-sig">
                            <div class="sig-header">
                                <span class="sig-title">{{ __('order.invoice.buyer_title', [], $printLocale) }}</span>
                                <span class="sig-status-tag">{{ $buyerStatusNote }}</span>
                            </div>
                            <div class="sig-space"></div>
                            <div class="sig-action-label">{{ __('order.invoice.signature', [], $printLocale) }}</div>
                        </div>

                        <div class="inv-signature-col seller-sig">
                            <div class="sig-header">
                                <span class="sig-title">{{ __('order.invoice.seller_title', [], $printLocale) }}</span>
                            </div>
                            <div class="sig-space"></div>
                            <div class="sig-action-label">{{ __('order.invoice.signature', [], $printLocale) }}</div>
                        </div>
                    </div>
                </td>
                <td class="inv-foot-calc-label">{{ __('order.invoice.prev_balance', [], $printLocale) }}</td>
                <td class="inv-foot-calc-value">${{ number_format((float) ($invoicePreviousBalance ?? 0), 2) }}</td>
            </tr>
            <tr class="trHeight">
                <td class="inv-foot-calc-label font-bold">{{ __('order.invoice.subtotal', [], $printLocale) }}</td>
                <td class="inv-foot-calc-value font-bold">
                    ${{ number_format((float) ($order?->total_price ?? 0) + (float) ($invoicePreviousBalance ?? 0), 2) }}
                </td>
            </tr>
            <tr class="trHeight">
                <td class="inv-foot-calc-label">{{ __('order.invoice.paid', [], $printLocale) }}</td>
                <td class="inv-foot-calc-value">
                    {{-- {{ (float) ($order->paid_amount ?? 0) > 0 ? '$' . number_format((float) $order->paid_amount, 2) : '' }} --}}
                </td>
            </tr>
            <tr class="trHeight">
                <td class="inv-foot-calc-label">{{ __('order.invoice.balance_due', [], $printLocale) }}</td>
                <td class="inv-foot-calc-value">
                    {{-- ${{ number_format(max(0, (float) ($order->remaining_amount ?? 0)), 2) }} --}}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
