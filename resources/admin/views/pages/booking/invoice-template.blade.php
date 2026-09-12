@php
    $shopName = $booking->shop?->name ?: 'រ៉ុង ស៊ុយ ហ័រ ( ផែ ស៊ុយហ័រ )';
    $shopSub = $booking->shop?->nick_name ?: ($booking->shop?->name ? '' : 'phe suy horh');
    $shopPhone = $booking->shop?->phone ?: '092 98 99 28 / 096 088 6666 367';
    $shopAddress = $booking->shop?->address ?: 'ផ្លូវជាតិលេខ៤ សង្កាត់ស្នោរ ក្រុងសំរោង ខេត្តតាកែវ';

    $cName = $customerName ?? ($booking->customer?->name ?: ($booking->customer?->phone ?: __('booking.walk_in_customer')));
    $cPhone = $customerPhone ?? ($booking->customer?->phone ?: '---');
    $cAddress = $customerAddress ?? ($booking->customer?->address ?: $shopAddress);

    $invoiceNo = $booking->invoice_number ?: sprintf('%05d', $booking->id);
    $bDate = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date) : now();
    $cashierName = $booking->barber?->name ?: ($booking->payments->first()?->createdBy?->name ?: '113');

    $paymentStatus = $booking->payment_status ?: 'Pending';
    $buyerStatusNote = match ($paymentStatus) {
        'Paid' => '(បានទូទាត់)',
        'Partial' => '(ទូទាត់ខ្លះ)',
        default => '(មិនទាន់ទូទាត់)',
    };

    $details = $booking->bookingDetail ?: collect();
    $minRows = 7;
    $emptyRowsCount = max(0, $minRows - $details->count());
@endphp

<div class="pos-invoice-sheet" id="posInvoicePrintArea">
    <!-- Invoice Header Block -->
    <div class="inv-header-row">
        <!-- Left: Logo & Company Name -->
        <div class="inv-brand-box">
            @if (!empty($booking->shop?->image_url))
                <img src="{{ $booking->shop->image_url }}" alt="{{ $shopName }}" class="inv-shop-logo-img">
            @else
                <div class="inv-coca-logo">
                    <span class="coca-script">Coca-Cola</span>
                </div>
            @endif

            <div class="inv-shop-text">
                <h2 class="inv-shop-title-kh">{{ $shopName }}</h2>
                @if ($shopSub)
                    <div class="inv-shop-subtitle-en">{{ $shopSub }}</div>
                @endif
                <div class="inv-shop-phone">
                    <span class="phone-label">THE :</span> {{ $shopPhone }}
                </div>
            </div>
        </div>

        <!-- Right: Official Invoice Title -->
        <div class="inv-official-title-box">
            <div class="inv-copy-notice">វិក្កយបត្រ ( ចម្លង ឬ ដើម )</div>
            <div class="inv-main-heading">
                <span class="khmer-title">វិក្កយបត្រ</span>
                <span class="en-title">INVOICE</span>
            </div>
        </div>
    </div>

    <!-- Customer & Invoice Metadata Bar -->
    <div class="inv-metadata-row">
        <div class="inv-meta-left">
            <div class="inv-meta-item">
                <span class="meta-label">លក់ជូន:</span>
                <strong class="meta-value">{{ $cName }}</strong>
                @if ($cPhone && $cPhone !== '---')
                    <span class="meta-phone">({{ $cPhone }})</span>
                @endif
            </div>
            <div class="inv-meta-item meta-address-line">
                <span class="meta-label">អាសយដ្ឋាន:</span>
                <span class="meta-value">{{ $cAddress }}</span>
            </div>
        </div>

        <div class="inv-meta-right">
            <div class="inv-meta-item">
                <span class="meta-label">ID No:</span>
                <strong class="meta-value inv-number">{{ $invoiceNo }}</strong>
            </div>
            <div class="inv-meta-item">
                <span class="meta-label">ត្រូវជូន</span>
                <span class="meta-value">
                    ថ្ងៃទី{{ $bDate->format('d') }}ខែ{{ $bDate->format('m') }}ឆ្នាំ {{ $bDate->format('Y') }}
                </span>
            </div>
            <div class="inv-meta-item">
                <span class="meta-label">កុំព្យូទ័រ:</span>
                <span class="meta-value">{{ $cashierName }}</span>
            </div>
        </div>
    </div>

    <!-- Invoice Grid Table -->
    <table class="inv-grid-table">
        <thead>
            <tr>
                <th class="col-num">ល.រ</th>
                <th class="col-desc">បរិយាយ</th>
                <th class="col-qty">ចំនួន</th>
                <th class="col-price">តម្លៃរាយ</th>
                <th class="col-total">តម្លៃសរុប</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $detail)
                @php
                    $isService = $detail->type === 'service';
                    $itemName = $isService ? ($detail->service?->name ?? '---') : ($detail->product?->name ?? '---');
                    $unitPrice = (float) ($detail->price ?? 0);
                    $qty = (int) ($detail->qty ?: 1);
                    $disc = (float) ($isService ? ($detail->service_discount ?? 0) : ($detail->product_discount ?? 0));
                    $lineTotal = max(0, ($unitPrice * $qty) - $disc);
                @endphp
                <tr class="inv-item-row">
                    <td class="cell-num">{{ $index + 1 }}</td>
                    <td class="cell-desc">
                        <span class="item-name">{{ $itemName }}</span>
                    </td>
                    <td class="cell-qty">{{ $qty }}</td>
                    <td class="cell-price">{{ number_format($unitPrice, 2) }}</td>
                    <td class="cell-total">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr class="inv-item-row">
                    <td class="cell-num">1</td>
                    <td class="cell-desc">
                        <span class="item-name">កូកា ដប 380ml</span>
                    </td>
                    <td class="cell-qty">50</td>
                    <td class="cell-price">2.26</td>
                    <td class="cell-total">113.00</td>
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
            <tr>
                <td colspan="3" rowspan="4" class="inv-foot-signatures-cell">
                    <div class="inv-signatures-wrap">
                        <div class="inv-signature-col buyer-sig">
                            <div class="sig-header">
                                <span class="sig-title">អ្នកទិញ/Buyer</span>
                                <span class="sig-status-tag">{{ $buyerStatusNote }}</span>
                            </div>
                            <div class="sig-space"></div>
                            <div class="sig-action-label">ស្នាមមេដៃ</div>
                        </div>

                        <div class="inv-signature-col seller-sig">
                            <div class="sig-header">
                                <span class="sig-title">អ្នកលក់/Seller</span>
                            </div>
                            <div class="sig-space"></div>
                            <div class="sig-action-label">ហត្ថលេខា</div>
                        </div>
                    </div>
                </td>
                <td class="inv-foot-calc-label">នៅខ្វះមុន</td>
                <td class="inv-foot-calc-value">0.00</td>
            </tr>
            <tr>
                <td class="inv-foot-calc-label font-bold">សរុប</td>
                <td class="inv-foot-calc-value font-bold">
                    {{ number_format((float) ($booking->total_price ?? 0), 2) }}
                </td>
            </tr>
            <tr>
                <td class="inv-foot-calc-label">អោយ</td>
                <td class="inv-foot-calc-value">
                    {{ (float) ($booking->paid_amount ?? 0) > 0 ? number_format((float) $booking->paid_amount, 2) : '' }}
                </td>
            </tr>
            <tr>
                <td class="inv-foot-calc-label">នៅខ្វះ</td>
                <td class="inv-foot-calc-value">
                    {{ (float) ($booking->remaining_amount ?? 0) > 0 ? number_format((float) $booking->remaining_amount, 2) : '0.00' }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
