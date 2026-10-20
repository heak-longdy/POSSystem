        /* Invoice Sheet Layout (matching invoice_01.png) */
        .pos-invoice-sheet {
            background: #ffffff;
            color: #000000;
            font-family: 'Hanuman', 'Khmer OS Battambang', 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            width: 100%;
            max-width: 790px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .inv-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            padding-bottom: 2px;
            width: 100%;
            box-sizing: border-box;
        }

        .inv-brand-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inv-shop-logo-img {
            max-height: 46px;
            max-width: 100px;
            object-fit: contain;
        }

        .inv-coca-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .coca-script {
            font-family: 'Brush Script MT', 'Playball', 'Pacifico', cursive, sans-serif;
            font-size: 27px;
            font-weight: bold;
            color: #e41d2d;
            letter-spacing: -0.5px;
            line-height: 1;
            transform: skew(-8deg);
            display: inline-block;
        }

        .inv-shop-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .inv-shop-title-kh {
            font-size: 15px;
            font-weight: 700;
            color: #000000;
            margin: 0;
            line-height: 1.25;
        }

        .inv-shop-subtitle-en {
            font-size: 12px;
            font-style: italic;
            color: #222222;
            margin: 0;
        }

        .inv-shop-phone {
            font-size: 11px;
            font-weight: 600;
            color: #000000;
            margin-top: 1px;
        }

        .inv-shop-phone .phone-label {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .inv-official-title-box {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .inv-copy-notice {
            font-size: 11px;
            color: #333333;
            margin-bottom: 1px;
        }

        .inv-main-heading {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .inv-main-heading .khmer-title {
            font-size: 17px;
            font-weight: 700;
            color: #000000;
        }

        .inv-main-heading .en-title {
            font-size: 16px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.5px;
        }

        /* Sub-Header Metadata Row */
        .inv-metadata-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            padding: 2px 0;
            font-size: 11.5px;
            width: 100%;
            box-sizing: border-box;
        }

        .inv-meta-left {
            flex: 1;
            max-width: 58%;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .inv-meta-right {
            flex: 1;
            max-width: 42%;
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 2px;
            align-items: flex-end;
        }

        .inv-meta-item {
            display: flex;
            align-items: baseline;
            gap: 4px;
            color: #000000;
        }

        .inv-meta-item .meta-label {
            font-weight: 600;
            color: #222222;
        }

        .inv-meta-item .meta-value {
            color: #000000;
        }

        .inv-meta-item .inv-number {
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Table Grid */
        .inv-grid-table {
            width: 100% !important;
            max-width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
            margin-bottom: 0 !important;
            table-layout: fixed !important;
            box-sizing: border-box !important;
        }

        .inv-grid-table th,
        .inv-grid-table td {
            border: 1px solid #000000 !important;
            padding: 2px 6px !important;
            box-sizing: border-box !important;
            color: #000000 !important;
        }

        .inv-grid-table thead th {
            background: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-align: center !important;
            height: 25px !important;
        }

        .inv-grid-table .col-num {
            width: 6% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-desc {
            width: 36% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-qty {
            width: 10% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-price {
            width: 14% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-discount {
            width: 16% !important;
            text-align: center !important;
        }

        .inv-grid-table .col-total {
            width: 18% !important;
            text-align: center !important;
        }

        .inv-grid-table tbody tr {
            height: 24px !important;
        }

        .inv-grid-table tbody td.cell-num {
            text-align: center !important;
            font-weight: 600 !important;
        }

        .inv-grid-table tbody td.cell-desc {
            text-align: left !important;
            padding-left: 6px !important;
        }

        .inv-grid-table tbody td.cell-qty {
            text-align: center !important;
        }

        .inv-grid-table tbody td.cell-price,
        .inv-grid-table tbody td.cell-discount {
            text-align: right !important;
            padding-right: 6px !important;
        }

        .inv-grid-table tbody td.cell-total {
            text-align: right !important;
            padding-right: 6px !important;
            font-weight: 600 !important;
        }

        .inv-grid-table tbody tr.inv-empty-row td {
            height: 22px !important;
            padding: 0 4px !important;
        }

        /* Footer: Signatures & Financial Summary */
        .inv-grid-table tfoot td {
            border: 1px solid #000000 !important;
        }

        .inv-foot-signatures-cell {
            vertical-align: top !important;
            padding: 4px 8px !important;
            height: 80px !important;
            width: 66% !important;
        }

        .inv-signatures-wrap {
            display: flex !important;
            justify-content: space-between !important;
            height: 100% !important;
            min-height: 72px !important;
        }

        .inv-signature-col {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: center !important;
        }

        .inv-signature-col.buyer-sig {
            text-align: left !important;
        }

        .inv-signature-col.seller-sig {
            text-align: center !important;
        }

        .inv-signature-col .sig-header {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            display: flex !important;
            align-items: baseline !important;
            gap: 3px !important;
        }

        .inv-signature-col.seller-sig .sig-header {
            justify-content: center !important;
        }

        .inv-signature-col .sig-status-tag {
            font-size: 11px !important;
            font-weight: normal !important;
            color: #333333 !important;
        }

        .inv-signature-col .sig-space {
            flex: 1 !important;
            min-height: 38px !important;
        }

        .inv-signature-col .sig-action-label {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #000000 !important;
            text-align: center !important;
        }

        .inv-foot-calc-label {
            width: 16% !important;
            font-size: 11.5px !important;
            font-weight: 600 !important;
            text-align: center !important;
            vertical-align: middle !important;
            background: #ffffff !important;
            height: 20px !important;
            padding: 2px 4px !important;
        }

        .inv-foot-calc-value {
            width: 18% !important;
            font-size: 11.5px !important;
            text-align: right !important;
            padding-right: 6px !important;
            vertical-align: middle !important;
            font-weight: 600 !important;
            background: #ffffff !important;
            height: 20px !important;
        }



        /* =========================================================
           Template 2: Delivery Slip / Voucher (invoice_02.png)
           ========================================================= */
        .pos-slip-sheet {
            background: #ffffff;
            color: #000000;
            font-family: 'Hanuman', 'Khmer OS Battambang', 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            padding: 4px 8px;
            box-sizing: border-box;
            width: 100%;
        }

        .slip-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .slip-top-left {
            width: 100px;
        }
        .slip-top-center {
            flex: 1;
            text-align: center;
        }
        .slip-title-khmer {
            font-size: 21px;
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
            color: #000000;
            letter-spacing: 0.5px;
        }
        .slip-top-right {
            text-align: right;
            font-size: 10.5px;
            line-height: 1.25;
            color: #000000;
        }
        .slip-meta-text {
            font-size: 10px;
            margin-bottom: 2px;
        }
        .draft-tag {
            font-weight: 700;
        }
        .slip-meta-item {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
        }
        .slip-meta-item .lbl {
            color: #222222;
        }
        .slip-meta-item .val {
            font-weight: 600;
            min-width: 75px;
            text-align: left;
        }

        /* 3-Column Info Matrix */
        .slip-info-matrix {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000000;
            padding: 4px 8px;
            margin-bottom: 4px;
            font-size: 10.5px;
            line-height: 1.35;
            background: #ffffff;
            gap: 8px;
        }
        .slip-info-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .slip-info-col.col-cust {
            flex: 1.25;
        }
        .slip-info-col.col-depot {
            flex: 1.35;
        }
        .slip-info-col.col-dist {
            flex: 0.9;
        }
        .info-row {
            display: flex;
            gap: 4px;
        }
        .info-lbl {
            font-weight: 600;
            min-width: 60px;
            color: #111111;
            flex-shrink: 0;
        }
        .info-val {
            color: #000000;
            word-break: break-word;
        }
        .cust-code {
            font-weight: 700;
            margin-right: 4px;
        }
        .spacer-row {
            visibility: hidden;
        }

        /* Slip Grid Table */
        .slip-grid-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
            font-size: 10.5px !important;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }
        .slip-grid-table th,
        .slip-grid-table td {
            border: 1px solid #000000 !important;
            padding: 2px 3px !important;
            text-align: center !important;
            vertical-align: middle !important;
            color: #000000 !important;
            box-sizing: border-box !important;
        }
        .slip-grid-table thead th {
            font-weight: 600 !important;
            background: #ffffff !important;
            padding: 2px 3px !important;
        }
        .th-sub-row th {
            font-weight: 500 !important;
            font-size: 10px !important;
            padding: 1px 2px !important;
        }
        .th-subtext {
            font-size: 9px !important;
            font-weight: normal !important;
        }

        /* Column widths */
        .th-no, .td-no { width: 4% !important; }
        .th-code, .td-code { width: 8% !important; font-weight: 600 !important; }
        .th-desc, .td-desc { width: 32% !important; text-align: left !important; padding-left: 5px !important; }
        .th-uom, .td-uom { width: 6% !important; }
        .th-qty-group { width: 12% !important; }
        .th-sub-col, .td-cases, .td-cans { width: 6% !important; }
        .th-price, .td-price { width: 9% !important; text-align: right !important; padding-right: 3px !important; }
        .th-disc, .td-disc { width: 10% !important; text-align: right !important; padding-right: 3px !important; }
        .th-net, .td-net { width: 9% !important; text-align: right !important; padding-right: 3px !important; }
        .th-total, .td-total { width: 10% !important; text-align: right !important; padding-right: 3px !important; font-weight: 600 !important; }

        .slip-row td {
            height: 18px !important;
        }
        .slip-empty-row td {
            height: 16px !important;
            background: #ffffff !important;
        }

        /* Table Bottom Bar */
        .slip-table-bottom-bar {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            margin-top: 2px;
        }
        .slip-free-goods-note {
            font-size: 10px;
            font-style: italic;
            color: #111111;
        }
        .slip-total-pay-box {
            display: inline-flex;
            border: 1px solid #000000;
            background: #ffffff;
        }
        .slip-total-pay-box .pay-lbl {
            padding: 2px 8px;
            font-weight: 700;
            font-size: 11px;
            border-right: 1px solid #000000;
        }
        .slip-total-pay-box .pay-val {
            padding: 2px 10px;
            font-weight: 700;
            font-size: 11.5px;
            min-width: 70px;
            text-align: right;
        }

        /* Dual Signatures Section */
        .slip-signatures-section {
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            margin-top: 14px;
            margin-bottom: 8px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-sig-col {
            text-align: center;
            width: 200px;
        }
        .slip-sig-col .sig-title {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 22px;
        }
        .slip-sig-col .sig-underline {
            border-bottom: 1px dotted #000000;
            width: 100%;
            height: 1px;
        }

        /* Footer Disclaimer */
        .slip-footer-disclaimer {
            font-size: 9.5px;
            line-height: 1.25;
            color: #111111;
            text-align: left;
            margin-top: 4px;
        }
