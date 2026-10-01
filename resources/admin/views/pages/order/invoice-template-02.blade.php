@php
    $printLocale = $printLocale ?? app()->getLocale();
    $isKm = $printLocale === 'km';
    $order = $order ?? null;

    $shopName = $order->shop?->name ?: 'ER-MLP1-TBK(PHE SUYHORTH)';
    $shopPhone = $order->shop?->phone ?: '092 989 928/088 666 6367';
    $shopAddress = $order->shop?->address ?: 'TBONG KHMUM';
    $shopCity = $order->shop?->city ?: ($isKm ? 'ត្បូងឃ្មុំ' : 'Tbong Khmum');

    $cName = $customerName ?? ($order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer', [], $printLocale)));
    $cPhone = $customerPhone ?? ($order->customer?->phone ?: '012399252');
    $cAddress = $customerAddress ?? ($order->customer?->address ?: 'N/A ST 07 ផ្ទះជិត Mokwath Viheato');
    $customerCode = $order->customer?->code ?: ($order->customer?->id ? sprintf('%010d', $order->customer->id) : '0200042419');

    $invoiceNo = $order->invoice_number ?: sprintf('%05d', $order->id);
    $orderDate = $order->order_date ? \Carbon\Carbon::parse($order->order_date) : now();
    $deliveryDate = $order->delivery_date
        ? \Carbon\Carbon::parse($order->delivery_date)
        : ($order->order_date ? \Carbon\Carbon::parse($order->order_date)->addDays(2) : now()->addDays(2));
    $driverName = $order->barber?->name ?: ($order->payments->first()?->createdBy?->name ?: ($isKm ? 'សាខាអ្នកដឹក' : 'Delivery Branch'));

    $details = $order->orderDetails ?: collect();
    $minRows = 3;
    $emptyRowsCount = max(0, min(3, $minRows - $details->count()));
@endphp

<div class="pos-slip-sheet" id="posSlipPrintArea_{{ $printLocale }}">
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
                <span class="val">{{ $deliveryDate->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- 3-Column Info Matrix (Customer, Depot/Shop, District/Branch) -->
    <div class="slip-info-matrix">
        <!-- Customer Info -->
        <div class="slip-info-col col-cust">
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_cust_name', [], $printLocale) }}</span>
                <span class="info-val"><span class="cust-code">{{ $customerCode }}</span> {{ $cName }}</span>
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
            <div class="info-row spacer-row">
                <span class="info-lbl">&nbsp;</span>
                <span class="info-val">&nbsp;</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">{{ __('order.invoice.slip_delivery_branch', [], $printLocale) }}</span>
                <span class="info-val">{{ $driverName }}</span>
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
                <th colspan="2" class="th-qty-group">{{ __('order.invoice.slip_col_qty', [], $printLocale) }}</th>
                <th colspan="2" class="th-qty-group">{{ __('order.invoice.slip_col_deliv_qty', [], $printLocale) }}</th>
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
                <th class="th-sub-col">{{ __('order.invoice.slip_col_case', [], $printLocale) }}</th>
                <th class="th-sub-col">{{ __('order.invoice.slip_col_can', [], $printLocale) }}</th>
                <th class="th-sub-col">{{ __('order.invoice.slip_col_case', [], $printLocale) }}</th>
                <th class="th-sub-col">{{ __('order.invoice.slip_col_can', [], $printLocale) }}</th>
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
                    $itemCode = $detail->product?->code ?: ($detail->product?->barcode ?: sprintf('%04d', $detail->item_id ?: ($index + 101)));
                    $uomName = $detail->product?->uom?->name ?: '12';
                    $unitPrice = (float) ($detail->price ?? 0);
                    $qty = (int) ($detail->qty ?: 1);
                    $disc = (float) ($isService ? ($detail->service_discount ?? 0) : ($detail->product_discount ?? 0));
                    $netUnitPrice = max(0, $unitPrice - ($qty > 0 ? ($disc / $qty) : 0));
                    $lineTotal = max(0, ($unitPrice * $qty) - $disc);
                @endphp
                <tr class="slip-row">
                    <td class="td-no">{{ $index + 1 }}</td>
                    <td class="td-code">{{ $itemCode }}</td>
                    <td class="td-desc">{{ $itemName }}</td>
                    <td class="td-uom">{{ $uomName }}</td>
                    <td class="td-cases">{{ $qty }}</td>
                    <td class="td-cans">0</td>
                    <td class="td-cases">{{ $qty }}</td>
                    <td class="td-cans">0</td>
                    <td class="td-price">{{ number_format($unitPrice, 2) }}</td>
                    <td class="td-disc">{{ $disc > 0 ? '-' . number_format($disc, 2) : '-0.00' }}</td>
                    <td class="td-net">{{ number_format($netUnitPrice, 2) }}</td>
                    <td class="td-total">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr class="slip-row">
                    <td class="td-no">1</td>
                    <td class="td-code">3801</td>
                    <td class="td-desc">{{ __('order.invoice.sample_coca_12', [], $printLocale) }}</td>
                    <td class="td-uom">12</td>
                    <td class="td-cases">35</td>
                    <td class="td-cans">0</td>
                    <td class="td-cases">35</td>
                    <td class="td-cans">0</td>
                    <td class="td-price">2.40</td>
                    <td class="td-disc">-0.14</td>
                    <td class="td-net">2.26</td>
                    <td class="td-total">79.10</td>
                </tr>
            @endforelse

            {{-- Pad blank rows matching continuous slip layout in invoice_02.png --}}
            @for ($i = 0; $i < $emptyRowsCount; $i++)
                <tr class="slip-empty-row">
                    <td class="td-no">&nbsp;</td>
                    <td class="td-code">&nbsp;</td>
                    <td class="td-desc">&nbsp;</td>
                    <td class="td-uom">&nbsp;</td>
                    <td class="td-cases">&nbsp;</td>
                    <td class="td-cans">&nbsp;</td>
                    <td class="td-cases">&nbsp;</td>
                    <td class="td-cans">&nbsp;</td>
                    <td class="td-price">&nbsp;</td>
                    <td class="td-disc">&nbsp;</td>
                    <td class="td-net">&nbsp;</td>
                    <td class="td-total">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Bottom Table Extension & Summary -->
    <div class="slip-table-bottom-bar">
        <div class="slip-bottom-left">
            <span class="slip-free-goods-note">{{ __('order.invoice.slip_free_goods', [], $printLocale) }}</span>
        </div>
        <div class="slip-bottom-right">
            <div class="slip-total-pay-box">
                <span class="pay-lbl">{{ __('order.invoice.slip_total_pay', [], $printLocale) }}</span>
                <strong class="pay-val">{{ number_format((float) ($order->total_price ?? 0), 2) }}</strong>
            </div>
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
