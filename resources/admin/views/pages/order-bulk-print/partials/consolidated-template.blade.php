@php
    $consolidatedProducts = $consolidatedProducts ?? [];
    $overallQuantity = $overallQuantity ?? 0;
    $stopsCount = $stopsCount ?? 0;
    $delLocation = $delLocation ?? 'ER-MLP1-TBK(PHE SUYHORTH)';
    $delDate = $delDate ?? now()->format('d/m/Y');
    $driverName = $driverName ?? '';
    $documentNo = $documentNo ?? '';
    $reportDate = $reportDate ?? now()->format('n/j/y');
    $reportTime = $reportTime ?? now()->format('g:iA');
    $totalWeight = $totalWeight ?? '1,264';
@endphp

<style>
    .load-report-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        background-color: transparent;
    }

    .load-sheet-box {
        background: #ffffff !important;
        width: 100% !important;
        max-width: 820px !important;
        margin: 0 auto !important;
        padding: 36px 40px 50px 40px !important;
        box-sizing: border-box !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        font-family: Arial, "Helvetica Neue", Helvetica, "Khmer OS Battambang", sans-serif !important;
        font-size: 11px !important;
        color: #000000 !important;
        line-height: 1.4 !important;
    }

    /* 3-Column Header Section matching reference image */
    .load-header-grid {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        width: 100% !important;
        margin-bottom: 14px !important;
        font-size: 11px !important;
        line-height: 1.45 !important;
    }

    .load-hdr-left {
        width: 33% !important;
        text-align: left !important;
    }

    .load-hdr-center {
        width: 34% !important;
        text-align: center !important;
    }

    .load-hdr-right {
        width: 33% !important;
        text-align: right !important;
    }

    .load-hdr-title {
        font-size: 15px !important;
        font-weight: 700 !important;
        margin-bottom: 8px !important;
        letter-spacing: 0.2px !important;
        color: #000000 !important;
    }

    .load-hdr-line {
        margin-bottom: 3px !important;
        white-space: nowrap !important;
    }

    .load-hdr-lbl {
        font-weight: 500 !important;
    }

    .load-hdr-val {
        font-weight: 500 !important;
    }

    .load-loc-wrap {
        white-space: normal !important;
        word-break: break-word !important;
    }

    .load-page-info {
        display: flex !important;
        justify-content: flex-end !important;
        gap: 16px !important;
        margin-bottom: 2px !important;
    }

    .load-pld-stamp {
        font-weight: 700 !important;
        margin-bottom: 4px !important;
    }

    /* Table Section with exact black borders */
    .load-table-wrap {
        width: 100% !important;
        margin-top: 10px !important;
        margin-bottom: 8px !important;
    }

    .load-report-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #000000 !important;
        font-size: 11px !important;
        background: #ffffff !important;
    }

    .load-report-table th,
    .load-report-table td {
        border: 1px solid #000000 !important;
        padding: 4px 6px !important;
        color: #000000 !important;
        line-height: 1.35 !important;
    }

    .load-report-table th {
        background-color: #ffffff !important;
        font-weight: 700 !important;
        text-align: center !important;
        font-size: 11.5px !important;
        padding: 5px 6px !important;
    }

    .load-report-table td.col-art-no {
        text-align: center !important;
        font-family: inherit !important;
        white-space: nowrap !important;
        width: 14% !important;
    }

    .load-report-table td.col-art-name {
        text-align: left !important;
        width: 50% !important;
    }

    .load-report-table td.col-uom {
        text-align: center !important;
        width: 12% !important;
    }

    .load-report-table td.col-units {
        text-align: center !important;
        font-weight: 500 !important;
        width: 12% !important;
    }

    .load-report-table td.col-subunits {
        text-align: center !important;
        width: 12% !important;
    }

    .load-report-table td.col-empty {
        text-align: center !important;
        padding: 20px !important;
        color: #64748b !important;
    }

    /* Summary Row Under Table */
    .load-delivered-bar {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        width: 100% !important;
        margin-top: 10px !important;
        margin-bottom: 35px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #000000 !important;
    }

    .load-delivered-lbl {
        margin-right: 25px !important;
    }

    .load-delivered-qty {
        width: 12% !important;
        text-align: center !important;
    }

    .load-delivered-spacer {
        width: 12% !important;
    }

    /* Footer Checklist & Signatures */
    .load-footer-signatures {
        margin-top: 40px !important;
        font-size: 11px !important;
        color: #000000 !important;
    }

    .load-footer-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        margin-bottom: 24px !important;
    }

    .load-footer-field {
        display: flex !important;
        align-items: flex-end !important;
        width: 46% !important;
    }

    .load-footer-lbl {
        font-weight: 500 !important;
        white-space: nowrap !important;
        margin-right: 6px !important;
    }

    .load-footer-dots {
        flex: 1 !important;
        border-bottom: 1px dotted #555555 !important;
        height: 12px !important;
    }

    /* Print Overrides */
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }

        .load-sheet-box {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .load-report-table {
            border: 1px solid #000000 !important;
        }

        .load-report-table th,
        .load-report-table td {
            border: 1px solid #000000 !important;
        }

        .load-report-table thead {
            display: table-header-group !important;
        }

        .load-report-table tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="load-report-wrapper" id="consolidatedReportArea">
    <div class="load-sheet-box">
        <!-- 3-Column Header Section matching reference image -->
        <div class="load-header-grid">
            <!-- Left Column -->
            <div class="load-hdr-left">
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Document No.:</span>
                    <span class="load-hdr-val">{{ $documentNo }}</span>
                </div>
                <div class="load-hdr-line load-loc-wrap">
                    <span class="load-hdr-lbl">Del Location:</span>
                    <span class="load-hdr-val">{{ $delLocation }}</span>
                </div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Del Date :</span>
                    <span class="load-hdr-val">{{ $delDate }}</span>
                </div>
            </div>

            <!-- Center Column -->
            <div class="load-hdr-center">
                <div class="load-hdr-title">Load Report (Draft)</div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Driver :</span>
                    <span class="load-hdr-val">{{ $driverName }}</span>
                </div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Vehicle :</span>
                    <span class="load-hdr-val">na</span>
                </div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Load No. :</span>
                    <span class="load-hdr-val">na</span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="load-hdr-right">
                <div class="load-page-info">
                    <span>{{ $reportDate }}</span>
                    <span>{{ $reportTime }}</span>
                    <span>Page 1 of 1</span>
                </div>
                <div class="load-pld-stamp">PLD</div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">Weight :</span>
                    <span class="load-hdr-val">{{ $totalWeight }}</span>
                </div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">No. of Stops :</span>
                    <span class="load-hdr-val">{{ $stopsCount }}</span>
                </div>
                <div class="load-hdr-line">
                    <span class="load-hdr-lbl">No. of Helpers :</span>
                    <span class="load-hdr-val">na</span>
                </div>
            </div>
        </div>

        <!-- Table Section with exact black borders -->
        <div class="load-table-wrap">
            <table class="load-report-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Article No.</th>
                        <th style="width: 50%;">Article Name</th>
                        <th style="width: 12%;">UOM</th>
                        <th style="width: 12%;">Units</th>
                        <th style="width: 12%;">Subunits</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consolidatedProducts as $prod)
                        <tr>
                            <td class="col-art-no">{{ $prod['product_code'] }}</td>
                            <td class="col-art-name">{{ $prod['product_name'] }}</td>
                            <td class="col-uom">{{ $prod['uom_name'] }}</td>
                            <td class="col-units">{{ number_format($prod['total_qty']) }}</td>
                            <td class="col-subunits">0</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="col-empty">
                                {{ __('order_bulk_print.preview.no_products_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Row: Total for Delivered Articles aligned with Units -->
        <div class="load-delivered-bar">
            <span class="load-delivered-lbl">Total for Delivered Articles :</span>
            <span class="load-delivered-qty">{{ number_format($overallQuantity) }}</span>
            <span class="load-delivered-spacer"></span>
        </div>

        <!-- Bottom Signatures and Checklist (Exact match to reference photo) -->
        <div class="load-footer-signatures">
            <div class="load-footer-row">
                <div class="load-footer-field">
                    <span class="load-footer-lbl">Pallets Used :</span>
                    <span class="load-footer-dots"></span>
                </div>
                <div class="load-footer-field">
                    <span class="load-footer-lbl">Hooks Used :</span>
                    <span class="load-footer-dots"></span>
                </div>
            </div>

            <div class="load-footer-row">
                <div class="load-footer-field">
                    <span class="load-footer-lbl">Driver :</span>
                    <span class="load-footer-dots"></span>
                </div>
                <div class="load-footer-field">
                    <span class="load-footer-lbl">Checker :</span>
                    <span class="load-footer-dots"></span>
                </div>
            </div>
        </div>
    </div>
</div>
