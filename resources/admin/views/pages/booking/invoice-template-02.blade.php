@php
    $shopName = $booking->shop?->name ?: 'ER-MLP1-TBK(PHE SUYHORTH)';
    $shopPhone = $booking->shop?->phone ?: '092 989 928/088 666 6367';
    $shopAddress = $booking->shop?->address ?: 'TBONG KHMUM';
    $shopCity = $booking->shop?->city ?: 'ត្បូងឃ្មុំ';

    $cName = $customerName ?? ($booking->customer?->name ?: ($booking->customer?->phone ?: __('booking.walk_in_customer')));
    $cPhone = $customerPhone ?? ($booking->customer?->phone ?: '012399252');
    $cAddress = $customerAddress ?? ($booking->customer?->address ?: 'N/A ST 07 ផ្ទះជិត Mokwath Viheato');
    $customerCode = $booking->customer?->code ?: ($booking->customer?->id ? sprintf('%010d', $booking->customer->id) : '0200042419');

    $invoiceNo = $booking->invoice_number ?: sprintf('%05d', $booking->id);
    $bDate = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date) : now();
    $deliveryDate = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->addDays(2) : now()->addDays(2);
    $driverName = $booking->barber?->name ?: ($booking->payments->first()?->createdBy?->name ?: 'សាខាអ្នកដឹក');

    $details = $booking->bookingDetail ?: collect();
    $minRows = 6;
    $emptyRowsCount = max(0, $minRows - $details->count());
@endphp

<div class="pos-slip-sheet" id="posSlipPrintArea">
    <!-- Header Top Section -->
    <div class="slip-top-row">
        <div class="slip-top-left"></div>
        <div class="slip-top-center">
            <h1 class="slip-title-khmer">បង្កាន់ដៃ</h1>
        </div>
        <div class="slip-top-right">
            <div class="slip-meta-text">{{ $bDate->format('n/j/y') }} {{ $bDate->format('g:iA') }} <span class="draft-tag">(DRAFT)</span> OEB</div>
            <div class="slip-meta-item"><span class="lbl">Inv. No.</span> <strong class="val">{{ $invoiceNo }}</strong></div>
            <div class="slip-meta-item"><span class="lbl">ថ្ងៃចេញ</span> <span class="val">{{ $bDate->format('d/m/Y') }}</span></div>
            <div class="slip-meta-item"><span class="lbl">ថ្ងៃដឹក</span> <span class="val">{{ $deliveryDate->format('d/m/Y') }}</span></div>
        </div>
    </div>

    <!-- 3-Column Info Matrix (Customer, Depot/Shop, District/Branch) -->
    <div class="slip-info-matrix">
        <!-- Customer Info -->
        <div class="slip-info-col col-cust">
            <div class="info-row">
                <span class="info-lbl">ឈ្មោះ:</span>
                <span class="info-val"><span class="cust-code">{{ $customerCode }}</span> {{ $cName }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">អា/ដ្ឋាន</span>
                <span class="info-val">{{ $cAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">Tel :</span>
                <span class="info-val">{{ $cPhone }}</span>
            </div>
        </div>

        <!-- Depot / Shop Info -->
        <div class="slip-info-col col-depot">
            <div class="info-row">
                <span class="info-lbl">ឈ្មោះដេប៉ូ</span>
                <strong class="info-val">{{ $shopName }}</strong>
            </div>
            <div class="info-row">
                <span class="info-lbl">អា/ដ្ឋាន</span>
                <span class="info-val">{{ $shopAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">Tel :</span>
                <span class="info-val">{{ $shopPhone }}</span>
            </div>
        </div>

        <!-- District & Delivery Branch Info -->
        <div class="slip-info-col col-dist">
            <div class="info-row">
                <span class="info-lbl">ស្រុករាជធានី</span>
                <span class="info-val">{{ $shopCity }}</span>
            </div>
            <div class="info-row spacer-row">
                <span class="info-lbl">&nbsp;</span>
                <span class="info-val">&nbsp;</span>
            </div>
            <div class="info-row">
                <span class="info-lbl">សាខាអ្នកដឹក</span>
                <span class="info-val">{{ $driverName }}</span>
            </div>
        </div>
    </div>

    <!-- Main Slip Table with 10 Columns Matching invoice_02.png -->
    <table class="slip-grid-table">
        <thead>
            <tr>
                <th rowspan="2" class="th-no">No</th>
                <th rowspan="2" class="th-code">លេខកូដ</th>
                <th rowspan="2" class="th-desc">ឈ្មោះផលិតផល</th>
                <th rowspan="2" class="th-uom">ខ្នាត</th>
                <th colspan="2" class="th-qty-group">បរិមាណ</th>
                <th colspan="2" class="th-qty-group">បរិមាណដឹក</th>
                <th rowspan="2" class="th-price">ថ្លៃដើម($)</th>
                <th rowspan="2" class="th-disc">
                    តម្លៃបញ្ចុះ($)<br>
                    <span class="th-subtext">កញ្ចប់បន្ថែម($)</span>
                </th>
                <th rowspan="2" class="th-net">
                    គិតគិតថ្លៃ<br>
                    <span class="th-subtext">កេស កប</span>
                </th>
                <th rowspan="2" class="th-total">តម្លៃសរុប($)</th>
            </tr>
            <tr class="th-sub-row">
                <th class="th-sub-col">កេស</th>
                <th class="th-sub-col">កប</th>
                <th class="th-sub-col">កេស</th>
                <th class="th-sub-col">កប</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $detail)
                @php
                    $isService = $detail->type === 'service';
                    $itemName = $isService ? ($detail->service?->name ?? '---') : ($detail->product?->name ?? '---');
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
                    <td class="td-desc">កូកាកូឡាដប ១២x២៥០ml</td>
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
            <span class="slip-free-goods-note">(*) : ផលិតផលមិនគិតថ្លៃ</span>
        </div>
        <div class="slip-bottom-right">
            <div class="slip-total-pay-box">
                <span class="pay-lbl">ចំនួនទឹកប្រាក់ត្រូវបង់ ($)</span>
                <strong class="pay-val">{{ number_format((float) ($booking->total_price ?? 0), 2) }}</strong>
            </div>
        </div>
    </div>

    <!-- Dual Signatures Section -->
    <div class="slip-signatures-section">
        <div class="slip-sig-col">
            <div class="sig-title">ហត្ថលេខាអ្នកដឹក</div>
            <div class="sig-underline"></div>
        </div>
        <div class="slip-sig-col">
            <div class="sig-title">ហត្ថលេខាអ្នកតំណាង</div>
            <div class="sig-underline"></div>
        </div>
    </div>

    <!-- Footer Disclaimer Note -->
    <div class="slip-footer-disclaimer">
        សម្គាល់: រាល់ការខូចខាតទំនិញនិងបញ្ហាសេវាផ្សេងៗដែលមានបញ្ហា បុគ្គលិកនៅទីនោះត្រូវរាយការណ៍ជូនក្រុមហ៊ុនឱ្យបានឆាប់រហ័ស តាមរយៈលេខទូរស័ព្ទខាងលើ។
    </div>
</div>
