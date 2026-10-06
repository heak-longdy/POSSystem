{{-- Screen-only refinements, scoped to Order Details to preserve invoice styling. --}}
@media screen {
    .order-details-refresh {
        --detail-ink: #192b45;
        --detail-muted: #69788e;
        --detail-border: #e5ebf2;
        --detail-blue: #2877c9;
        --detail-green: #168454;
        --detail-red: #ce4253;
        container: order-details / inline-size;
        background: #f5f7fb;
    }

    .order-details-refresh .booking-detail-page-wrapper {
        width: 75%;
        max-width: 1600px;
        padding: 26px 32px 48px;
        color: var(--detail-ink);
        line-height: 1.6;
    }

    .order-details-refresh .detail-breadcrumb {
        flex-wrap: wrap;
        gap: 10px !important;
        margin-bottom: 20px;
        font-size: 12px;
    }

    .order-details-refresh .detail-breadcrumb a {
        color: var(--detail-blue);
        gap: 7px;
    }

    .order-details-refresh .detail-header-card {
        display: block;
        padding: 0;
        margin-bottom: 20px;
        border: 1px solid var(--detail-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 20px -12px #1c355c26;
        overflow: hidden;
    }

    .order-details-refresh .detail-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px 24px;
        padding: 24px 26px;
    }

    .order-details-refresh .header-left {
        min-width: 0;
    }

    .order-details-refresh .detail-eyebrow {
        margin: 0 0 6px;
        color: var(--detail-muted);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .04em;
    }

    .order-details-refresh .invoice-badge-title {
        gap: 10px 14px;
    }

    .order-details-refresh .invoice-title {
        color: var(--detail-ink);
        font-size: clamp(24px, 2.4vw, 32px);
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.6px;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .invoice-subtitle {
        flex-wrap: wrap;
        gap: 6px 12px;
        margin-top: 10px;
        color: var(--detail-muted);
        line-height: 1.6;
    }

    .order-details-refresh .invoice-subtitle > span:not(.meta-sep) {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .order-details-refresh .invoice-subtitle svg {
        flex-shrink: 0;
    }

    .order-details-refresh .detail-status-pill {
        padding: 5px 11px;
        font-size: 12px;
        line-height: 1.5;
        border: 1px solid currentColor;
        border-color: #00000008;
    }

    .order-details-refresh .status-pending {
        background: #fff5db;
        color: #97620d;
    }

    .order-details-refresh .header-right-actions,
    .order-details-refresh .detail-print-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .order-details-refresh .detail-header-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px 20px;
        padding: 15px 26px;
        border-top: 1px solid var(--detail-border);
        background: #fbfcfe;
    }

    .order-details-refresh .btn-system,
    .order-details-refresh .btn-system-sm {
        height: auto !important;
        min-height: 40px;
        padding: 10px 15px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        line-height: 1.5 !important;
        white-space: normal !important;
        text-align: center;
        justify-content: center;
        box-shadow: none !important;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease !important;
    }

    .order-details-refresh .btn-system span,
    .order-details-refresh .btn-system-sm span {
        font-size: inherit !important;
        line-height: 1.5 !important;
    }

    .order-details-refresh .btn-system svg,
    .order-details-refresh .btn-system-sm svg {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0;
    }

    .order-details-refresh .btn.btn-system-success {
        background: var(--detail-green) !important;
        border: 1px solid var(--detail-green) !important;
        color: #fff !important;
    }

    .order-details-refresh .btn.btn-system-success:hover:not(:disabled) {
        background: #116b43 !important;
        border-color: #116b43 !important;
    }

    .order-details-refresh .btn.btn-system-primary {
        background: #eef5fd !important;
        border: 1px solid #d6e5f7 !important;
        color: #2468ae !important;
    }

    .order-details-refresh .btn.btn-system-primary:hover:not(:disabled) {
        background: #dfedfc !important;
        border-color: #a9caef !important;
        color: #1c5591 !important;
    }

    .order-details-refresh .btn.btn-system-danger {
        background: #fff4f4 !important;
        border: 1px solid #f4dadd !important;
        color: #bf3548 !important;
    }

    .order-details-refresh .btn.btn-system-danger:hover:not(:disabled) {
        background: #ffe7eb !important;
        border-color: #e6a9b2 !important;
        color: #a52b3c !important;
    }

    .order-details-refresh .btn-system-outline {
        background: #fff !important;
        border: 1px solid #dce4ed !important;
        color: #42536b !important;
    }

    .order-details-refresh .btn-system-outline:hover:not(:disabled) {
        background: #edf3fa !important;
        border-color: #bdcde0 !important;
        color: var(--detail-ink) !important;
    }

    .order-details-refresh .detail-header-tools > .btn-system-neutral {
        background: transparent !important;
        border-color: transparent !important;
    }

    .order-details-refresh :is(button, a, input, textarea, [tabindex]):focus-visible {
        outline: 3px solid #78ace3;
        outline-offset: 3px;
    }

    .order-details-refresh button:disabled {
        opacity: .6;
        cursor: not-allowed !important;
    }

    .order-details-refresh .print-lang-switch-box {
        height: auto;
        min-height: 40px;
        padding: 0;
        gap: 10px;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .order-details-refresh .print-lang-label {
        color: var(--detail-muted);
        font-size: 12px;
    }

    .order-details-refresh .print-lang-label span {
        display: inline;
    }

    .order-details-refresh .print-lang-segmented {
        padding: 3px;
        gap: 3px;
        border: 1px solid #e1e8f1;
        border-radius: 30px;
        background: #edf1f7;
    }

    .order-details-refresh .print-lang-btn {
        height: auto;
        min-height: 30px;
        padding: 5px 10px;
        border-radius: 6px;
        color: #58677c !important;
        line-height: 1.5;
    }

    .order-details-refresh .print-lang-btn.is-active {
        background: #fff;
        color: #2468ae !important;
        border-radius: 30px !important;
        box-shadow: 0 1px 4px #233b6026;
    }

    .order-details-refresh .detail-kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .order-details-refresh .kpi-card {
        position: relative;
        flex-direction: row-reverse;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px;
        border: 1px solid var(--detail-border);
        border-radius: 14px;
        box-shadow: 0 3px 12px -8px #203d6626;
        transition: none;
    }

    .order-details-refresh .kpi-card:hover {
        transform: none;
    }

    .order-details-refresh .kpi-card:first-child {
        border-color: #223c60;
        background: linear-gradient(110deg, #1c304e, #284970);
    }

    .order-details-refresh .kpi-card:first-child .kpi-label {
        color: #c3d4e9;
    }

    .order-details-refresh .kpi-card:first-child .kpi-value {
        color: #fff;
    }

    .order-details-refresh .kpi-info {
        min-width: 0;
    }

    .order-details-refresh .kpi-label {
        font-size: 12px;
        color: var(--detail-muted);
        text-transform: none;
        letter-spacing: .01em;
    }

    .order-details-refresh .kpi-value {
        margin-top: 6px;
        color: var(--detail-ink);
        font-size: clamp(24px, 2.3vw, 30px);
        line-height: 1.25;
        letter-spacing: -.6px;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .kpi-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 13px;
    }

    .order-details-refresh .kpi-blue {
        background: #ffffff14;
        color: #c4dffc;
        border: 1px solid #ffffff1a;
    }

    .order-details-refresh .kpi-green {
        background: #eaf7f0;
        color: var(--detail-green);
    }

    .order-details-refresh .kpi-orange {
        background: #fff2e7;
        color: #c8792c;
    }

    .order-details-refresh .detail-columns-layout {
        grid-template-columns: minmax(0, 1.9fr) minmax(320px, 1fr);
        gap: 22px;
    }

    .order-details-refresh .detail-col-main,
    .order-details-refresh .detail-col-sidebar {
        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
    }

    .order-details-refresh .detail-card {
        min-width: 0;
        margin: 0;
        border: 1px solid var(--detail-border);
        border-radius: 14px;
        box-shadow: 0 3px 12px -8px #203d6626;
    }

    .order-details-refresh .detail-card-header {
        min-height: 72px;
        padding: 17px 20px;
        gap: 12px;
        flex-wrap: wrap;
        border-color: var(--detail-border);
        background: #fff;
    }

    .order-details-refresh .card-title-group {
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .order-details-refresh .card-title-group > svg {
        width: 34px;
        height: 34px;
        padding: 8px;
        border-radius: 9px;
        flex-shrink: 0;
        color: var(--detail-blue);
        background: #f0f5fc;
    }

    .order-details-refresh .card-title-group h2 {
        color: var(--detail-ink);
        font-size: 14px;
        line-height: 1.6;
    }

    .order-details-refresh .count-badge {
        padding: 2px 8px;
        min-width: 24px;
        flex-shrink: 0;
        border-radius: 6px;
        background: #edf2f8;
        color: #62748d;
        font-size: 11px;
        text-align: center;
    }

    .order-details-refresh .detail-card-header .btn-system-sm {
        min-height: 34px;
        padding: 7px 10px !important;
        font-size: 12px !important;
        background: #eaf7f0 !important;
        border: 1px solid #d5eddf !important;
        color: #187848 !important;
    }

    .order-details-refresh .detail-card-header .btn-system-sm:hover:not(:disabled) {
        background: #d9f0e3 !important;
    }

    .order-details-refresh .table-responsive {
        overflow-x: auto;
        overscroll-behavior-x: contain;
    }

    .order-details-refresh .detail-data-table {
        min-width: 600px;
        font-size: 13px;
        line-height: 1.65;
        font-variant-numeric: tabular-nums;
    }

    .order-details-refresh .detail-data-table th {
        padding: 12px 16px;
        background: #f7f9fc;
        color: #738198;
        border-bottom-color: var(--detail-border);
        font-size: 12px;
        font-weight: 600;
    }

    .order-details-refresh .detail-data-table td {
        padding: 18px 16px;
        color: #46566d;
        border-bottom-color: #edf1f6;
    }

    .order-details-refresh .detail-data-table :is(th, td):first-child {
        padding-left: 22px;
        color: #8491a4;
    }

    .order-details-refresh .detail-data-table :is(th, td):last-child {
        padding-right: 22px;
    }

    .order-details-refresh .detail-data-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .order-details-refresh .detail-data-table tr:hover td {
        background: #f8fafd;
    }

    .order-details-refresh .detail-data-table .text-right {
        text-align: right;
        white-space: nowrap;
    }

    .order-details-refresh .detail-data-table .text-center {
        text-align: center;
    }

    .order-details-refresh .detail-products-table th:nth-child(2) {
        width: 40%;
    }

    .order-details-refresh .item-name-cell {
        min-width: 170px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .item-name-cell strong,
    .order-details-refresh .detail-data-table .font-weight-bold {
        color: var(--detail-ink);
        font-weight: 600;
    }

    .order-details-refresh .detail-payments-table td:nth-child(2) {
        min-width: 150px;
    }

    .order-details-refresh .detail-payments-table td:nth-child(6) {
        min-width: 110px;
        max-width: 240px;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .payment-chip {
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
    }

    .order-details-refresh .detail-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 10px 20px 14px;
    }

    .order-details-refresh .empty-state-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border: 1px solid #e5ebf4;
        border-radius: 14px;
        background: #f5f8fc;
        color: #91a4bd;
    }

    .order-details-refresh .empty-state-icon svg {
        width: 21px;
        height: 21px;
    }

    .order-details-refresh .detail-empty-state p {
        margin: 0;
        color: var(--detail-muted);
        font-size: 13px;
    }

    .order-details-refresh .remark-content-box {
        padding: 20px;
    }

    .order-details-refresh .remark-text {
        padding: 14px 16px;
        border-left-color: #94b8e1;
        background: #f6f9fd;
        font-size: 13px;
        line-height: 1.85;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .remark-empty {
        padding: 14px 16px;
        border: 1px dashed #dfe6ef;
        border-radius: 8px;
        background: #fafbfd;
        color: var(--detail-muted);
        font-style: normal;
    }

    .order-details-refresh .customer-profile-body,
    .order-details-refresh .appointment-body,
    .order-details-refresh .billing-body {
        padding: 20px;
    }

    .order-details-refresh .customer-avatar-header {
        gap: 12px;
        padding-bottom: 18px;
        margin-bottom: 16px;
        border-bottom: 1px solid #edf1f6;
    }

    .order-details-refresh .customer-avatar-circle {
        width: 48px;
        height: 48px;
        border: 1px solid #dbe8fa;
        border-radius: 14px;
        background: #edf4ff;
        color: #3477bf;
        font-size: 16px;
    }

    .order-details-refresh .customer-title-info {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .customer-title-info h3 {
        margin-bottom: 3px;
        color: var(--detail-ink);
        font-size: 15px;
        line-height: 1.7;
    }

    .order-details-refresh .customer-info-list {
        gap: 15px;
    }

    .order-details-refresh .customer-info-list li,
    .order-details-refresh .appointment-info-row {
        display: grid;
        grid-template-columns: minmax(100px, .9fr) minmax(0, 1.25fr);
        align-items: start;
        gap: 8px 16px;
        font-size: 12px;
        line-height: 1.8;
    }

    .order-details-refresh .info-label,
    .order-details-refresh .appointment-info-row .label {
        align-items: flex-start;
        gap: 7px;
        color: var(--detail-muted);
    }

    .order-details-refresh .info-label svg,
    .order-details-refresh .appointment-info-row .label svg {
        flex-shrink: 0;
        margin-top: 4px;
        color: #8b9bb2;
    }

    .order-details-refresh .customer-info-list .info-val,
    .order-details-refresh .appointment-info-row .val {
        color: #354760;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .order-details-refresh .appointment-body {
        gap: 0;
    }

    .order-details-refresh .appointment-info-row {
        padding: 11px 0;
        border-bottom: 1px solid #edf1f6;
    }

    .order-details-refresh .appointment-info-row:first-child {
        padding-top: 0;
    }

    .order-details-refresh .appointment-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .order-details-refresh .billing-card {
        border-color: #d8e3f0;
    }

    .order-details-refresh .billing-card .detail-card-header {
        background: #f9fbfe;
    }

    .order-details-refresh .billing-body {
        gap: 14px;
    }

    .order-details-refresh .billing-row {
        gap: 16px;
        color: #62718a;
        line-height: 1.6;
        font-variant-numeric: tabular-nums;
    }

    .order-details-refresh .billing-row > span:last-child {
        flex-shrink: 0;
        color: #334762;
        font-weight: 600;
        text-align: right;
    }

    .order-details-refresh .billing-divider {
        background: var(--detail-border);
        margin: 2px 0;
    }

    .order-details-refresh .billing-grand-total {
        color: var(--detail-ink);
        font-size: 14px;
    }

    .order-details-refresh .billing-due {
        padding: 14px;
        border: 1px solid #e5ebf4;
        border-radius: 10px;
        background: #f4f7fc;
        color: var(--detail-ink);
        font-size: 14px;
    }

    .order-details-refresh .billing-due > span:last-child {
        font-size: 22px;
        letter-spacing: -.4px;
    }

    .order-details-refresh .billing-body .btn-full-width {
        min-height: 44px;
        margin-top: 2px !important;
    }

    .order-details-refresh :is(.detail-kpi-grid, .detail-card) .text-success {
        color: var(--detail-green) !important;
    }

    .order-details-refresh :is(.detail-kpi-grid, .detail-card) .text-danger {
        color: var(--detail-red) !important;
    }

    .order-details-refresh :is(.detail-kpi-grid, .detail-card) .text-muted {
        color: var(--detail-muted) !important;
    }

    .order-details-refresh .modal-dialog-custom {
        max-height: calc(100dvh - 40px);
        overflow-y: auto;
        border-radius: 16px;
    }

    .order-details-refresh .input-action-wrap .modal-input {
        min-width: 0;
    }

    @container order-details (max-width: 1100px) {
        .order-details-refresh .booking-detail-page-wrapper {
            padding: 22px 24px 40px;
        }

        .order-details-refresh .detail-columns-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .order-details-refresh .detail-col-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
        }

        .order-details-refresh .billing-card {
            grid-column: 2;
            grid-row: 1 / span 2;
        }

        .order-details-refresh .kpi-card {
            padding: 20px;
        }
    }

    @container order-details (max-width: 700px) {
        .order-details-refresh .booking-detail-page-wrapper {
            padding: 18px 16px 32px;
        }

        .order-details-refresh .detail-header-top {
            padding: 20px;
        }

        .order-details-refresh .header-right-actions {
            width: 100%;
        }

        .order-details-refresh .header-right-actions .btn {
            flex: 1 1 auto;
        }

        .order-details-refresh .detail-header-tools {
            padding: 14px 20px;
        }

        .order-details-refresh .detail-print-actions {
            width: 100%;
            align-items: stretch;
        }

        .order-details-refresh .print-lang-switch-box {
            flex-wrap: wrap;
            width: 100%;
            justify-content: space-between;
        }

        .order-details-refresh .detail-print-actions > .btn {
            flex: 1 1 140px;
        }

        .order-details-refresh .detail-header-tools > .btn {
            width: 100%;
        }

        .order-details-refresh .detail-kpi-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .order-details-refresh .kpi-card {
            padding: 16px 20px;
        }

        .order-details-refresh .kpi-value {
            margin-top: 4px;
            font-size: 26px;
        }

        .order-details-refresh .detail-col-sidebar {
            display: flex;
            align-items: stretch;
        }

        .order-details-refresh .detail-empty-state {
            width: calc(100cqi - 80px);
            padding-inline: 0;
        }

        .order-details-refresh .detail-columns-layout,
        .order-details-refresh .detail-col-main,
        .order-details-refresh .detail-col-sidebar {
            gap: 18px;
        }

        .order-details-refresh .detail-card-header {
            padding: 16px;
        }

        .order-details-refresh .detail-card-header:has(.btn-system-sm) .card-title-group {
            flex-basis: 100%;
        }

        .order-details-refresh .customer-profile-body,
        .order-details-refresh .appointment-body,
        .order-details-refresh .billing-body,
        .order-details-refresh .remark-content-box {
            padding: 18px;
        }

        .order-details-refresh .customer-info-list li,
        .order-details-refresh .appointment-info-row {
            grid-template-columns: minmax(90px, .9fr) minmax(0, 1.25fr);
            column-gap: 12px;
        }

        .order-details-refresh .payment-method-selector {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .order-details-refresh .content-body {
            scroll-behavior: auto !important;
        }

        .order-details-refresh .btn-system,
        .order-details-refresh .btn-system-sm {
            transition: none !important;
        }

        .order-details-refresh .modal-dialog-custom {
            animation: none;
        }
    }
}
