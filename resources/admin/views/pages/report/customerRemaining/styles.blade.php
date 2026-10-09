{{-- Match the Sales and Order Transaction report styles; scope them to these pages. --}}
<style>
    .customer-remaining-report .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        margin: 0 0 14px 0;
    }

    .customer-remaining-report .kpi-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 12px 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        border: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .customer-remaining-report .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .customer-remaining-report .kpi-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .customer-remaining-report .kpi-icon-wrap.primary {
        background: rgba(59, 130, 246, 0.12); color: #2563eb;
    }

    .customer-remaining-report .kpi-icon-wrap.success {
        background: rgba(16, 185, 129, 0.12); color: #059669;
    }

    .customer-remaining-report .kpi-icon-wrap.warning {
        background: rgba(245, 158, 11, 0.12); color: #d97706;
    }

    .customer-remaining-report .kpi-icon-wrap.danger {
        background: rgba(239, 68, 68, 0.12); color: #dc2626;
    }

    .customer-remaining-report .kpi-icon-wrap.purple {
        background: rgba(139, 92, 246, 0.12); color: #7c3aed;
    }

    .customer-remaining-report .kpi-icon-wrap.indigo {
        background: rgba(99, 102, 241, 0.12); color: #4f46e5;
    }

    .customer-remaining-report .kpi-info {
        flex: 1;
        min-width: 0;
    }

    .customer-remaining-report .kpi-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .customer-remaining-report .kpi-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }

    .customer-remaining-report .kpi-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .customer-remaining-report .report-filter-panel {
        background: #ffffff;
        border-radius: 12px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        margin-bottom: 14px;
    }

    .customer-remaining-report .filter-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        align-items: flex-end;
    }

    .customer-remaining-report .filter-field-wrap label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 5px;
        letter-spacing: 0.3px;
    }

    /* Filter Input & Select - Matching Create Order style setup (43px, 7px radius, #cbd5e1 border) */
    .customer-remaining-report .filter-input,
    .customer-remaining-report .filter-select {
        width: 100%;
        height: 43px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        padding: 0 10px;
        font-size: 12.5px;
        font-weight: 500;
        color: #0f172a;
        background-color: #ffffff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .customer-remaining-report .filter-input:focus,
    .customer-remaining-report .filter-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }

    .customer-remaining-report .filter-actions-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 43px;
    }

    /* Filter Action Buttons - Matching 43px height & 7px radius */
    .customer-remaining-report .btn-filter-search {
        height: 43px !important;
        padding: 0 16px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        border: 1px solid #1d4ed8 !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.15s ease-in-out !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }

    .customer-remaining-report .btn-filter-search:hover {
        background: #1d4ed8 !important;
        border-color: #1e40af !important;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25) !important;
        transform: translateY(-1px);
    }

    .customer-remaining-report .btn-filter-search:active {
        background: #1e40af !important;
        transform: translateY(0);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2) !important;
    }

    .customer-remaining-report .btn-filter-search:focus-visible {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25) !important;
    }

    .customer-remaining-report .btn-filter-search .bx {
        font-size: 18px !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    .customer-remaining-report .btn-filter-reset {
        height: 43px !important;
        padding: 0 14px !important;
        background: #ffffff !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.15s ease-in-out !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }

    .customer-remaining-report .btn-filter-reset:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06) !important;
        transform: translateY(-1px);
    }

    .customer-remaining-report .btn-filter-reset:active {
        background: #e2e8f0 !important;
        transform: translateY(0);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1) !important;
    }

    .customer-remaining-report .btn-filter-reset:focus-visible {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.3) !important;
    }

    .customer-remaining-report .btn-filter-reset .bx {
        font-size: 18px !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    /* Select2 Styling - Exact match to createOrder.blade.php & core setup */
    .customer-remaining-report .filter-field-wrap .select2Group {
        width: 100% !important;
        position: relative !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group select,
    .customer-remaining-report .filter-field-wrap .filter-select {
        width: 100% !important;
        height: 43px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 7px !important;
        padding: 0 10px !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        color: #0f172a !important;
        background: #ffffff !important;
        outline: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2.select2-container {
        width: 100% !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2-container .select2-selection--single {
        height: 43px !important;
        border-radius: 7px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--open .select2-selection--single,
    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--focus .select2-selection--single,
    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        line-height: normal !important;
        padding-left: 0 !important;
        padding-right: 28px !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 8px !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset {
        position: absolute !important;
        top: 50% !important;
        right: 26px !important;
        transform: translateY(-50%) !important;
        cursor: pointer !important;
        z-index: 2 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset[style*="display: none"],
    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset[style*="display:none"],
    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset[x-cloak] {
        display: none !important;
    }

    .customer-remaining-report .filter-field-wrap .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset svg {
        fill: #94a3b8 !important;
        width: 18px !important;
        height: 18px !important;
        background: #ffffff !important;
        border-radius: 50% !important;
        transition: fill 0.15s ease !important;
    }

    .customer-remaining-report .filter-field-wrap .select2Group .select2Reset:hover svg {
        fill: #ef4444 !important;
    }

    .customer-remaining-report .report-table-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .customer-remaining-report .table-custom-header {
        padding: 12px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .customer-remaining-report .table-custom-header h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .customer-remaining-report .table-responsive-custom {
        width: 100%;
        overflow-x: auto;
    }

    .customer-remaining-report .report-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .customer-remaining-report .report-data-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        padding: 12px 14px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .customer-remaining-report .report-data-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }

    .customer-remaining-report .report-data-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .customer-remaining-report .report-data-table tr.row-today {
        background-color: #eff6ff;
    }

    .customer-remaining-report .report-data-table tfoot td {
        background: #f8fafc;
        font-weight: 700;
        color: #0f172a;
        border-top: 2px solid #cbd5e1;
        padding: 14px;
    }

    .customer-remaining-report .text-right {
        text-align: right !important;
    }

    .customer-remaining-report .text-center {
        text-align: center !important;
    }

    .customer-remaining-report .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .customer-remaining-report .status-badge.paid {
        background: #dcfce7; color: #166534;
    }

    .customer-remaining-report .status-badge.partial {
        background: #fef3c7; color: #92400e;
    }

    .customer-remaining-report .status-badge.pending {
        background: #fee2e2; color: #991b1b;
    }

    .customer-remaining-report .status-badge.today {
        background: #dbeafe; color: #1e40af;
    }

    .customer-remaining-report .status-badge.cancel,
    .customer-remaining-report .status-badge.canceled {
        background: #f1f5f9; color: #64748b;
    }

    .customer-remaining-report .method-tag {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
        margin: 1px 2px;
    }

    .customer-remaining-report .btn-drilldown {
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #2563eb !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 5px 10px !important;
        border-radius: 6px !important;
        cursor: pointer;
        display: inline-flex !important;
        align-items: center !important;
        min-width: unset !important;
        gap: 4px;
        transition: all 0.15s ease;
        line-height: 0 !important;
    }

    .customer-remaining-report .btn-drilldown:hover {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }

    .customer-remaining-report .empty-placeholder {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .customer-remaining-report .empty-placeholder i {
        font-size: 48px;
        margin-bottom: 10px;
        color: #cbd5e1;
    }

    .customer-remaining-report { padding: 0; width: 100%; min-width: 0; }
    .customer-remaining-report .report-description,
    .customer-remaining-report .report-note { font-size: 12px; line-height: 1.6; color: #64748b; margin: 0 0 14px; }
    .customer-remaining-report .report-count { font-size: 12px; font-weight: normal; color: #64748b; }
    .customer-remaining-report .report-customer-summary { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; }
    .customer-remaining-report .report-customer-summary h3 { margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #1e293b; }
    .customer-remaining-report .report-customer-summary .report-description { margin: 0; }
    .customer-remaining-report .report-current-balance { display: flex; align-items: center; gap: 14px; }
    .customer-remaining-report .report-error { padding: 12px 16px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b91c1c; margin-bottom: 14px; }
    .customer-remaining-report .report-link { color: #2563eb; font-weight: 600; text-decoration: none; }
    .customer-remaining-report .report-link:hover { text-decoration: underline; }
    .customer-remaining-report .report-name { font-weight: 600; color: #334155; }
    .customer-remaining-report .report-nowrap { white-space: nowrap; }
    .customer-remaining-report .report-data-table .text-right { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .customer-remaining-report .report-data-table .text-primary,
    .customer-remaining-report .kpi-value.text-primary { color: #2563eb; }
    .customer-remaining-report .report-data-table .text-success,
    .customer-remaining-report .kpi-value.text-success { color: #059669; }
    .customer-remaining-report .report-data-table .text-danger,
    .customer-remaining-report .kpi-value.text-danger { color: #dc2626; }
    .customer-remaining-report .report-data-table .text-muted { color: #94a3b8; }
    .customer-remaining-report .report-data-table .font-weight-bold { font-weight: 700; }
    .customer-remaining-report .report-data-table .report-note-cell { min-width: 180px; max-width: 300px; white-space: normal; overflow-wrap: anywhere; }
    .customer-remaining-report .invoice-group-heading th { background: #eff6ff; border-top: 2px solid #cbd5e1; padding: 14px; text-transform: none; white-space: normal; letter-spacing: normal; }
    .customer-remaining-report .invoice-group-title { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 14px; color: #1e293b; }
    .customer-remaining-report .invoice-group-totals { display: flex; flex-wrap: wrap; gap: 8px 24px; margin-top: 8px; font-size: 12px; font-weight: 400; color: #64748b; }
    .customer-remaining-report .invoice-group-totals strong { color: #334155; font-variant-numeric: tabular-nums; }
    .customer-remaining-report .invoice-balance-row td { background: #f8fafc; font-weight: 600; padding: 10px 14px; }
    .customer-remaining-report .btn-drilldown { min-height: 28px; line-height: 1.4 !important; text-decoration: none; white-space: nowrap; }
    .customer-remaining-report .filter-input,
    .customer-remaining-report .filter-select { box-sizing: border-box; }
    .customer-remaining-report a:focus-visible,
    .customer-remaining-report button:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
    @media (max-width: 600px) {
        .customer-remaining-report .kpi-grid { grid-template-columns: 1fr; }
        .customer-remaining-report .filter-form-grid { grid-template-columns: 1fr; }
        .customer-remaining-report .kpi-sub { white-space: normal; }
    }
</style>
