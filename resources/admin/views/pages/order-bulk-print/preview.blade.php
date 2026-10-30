@extends('admin::shared.layout')

@php
    $isConsolidated = ($documentType ?? '') === 'consolidated';
    $isInvoice = ($documentType ?? 'invoice') === 'invoice';
    $isDeliverySlip = ($documentType ?? '') === 'delivery_slip';
    $hasSidebar = $isInvoice || $isDeliverySlip;

    if ($isConsolidated) {
        $docTitle = __('order_bulk_print.preview.title_consolidated');
    } elseif ($isInvoice) {
        $docTitle = __('order_bulk_print.preview.title_invoice');
    } else {
        $docTitle = __('order_bulk_print.preview.title_delivery_slip');
    }
    $headerTitle = $docTitle . ' (' . ($totalCount ?? 0) . ')';
    $sidebarTitle = $isDeliverySlip
        ? __('order_bulk_print.preview.delivery_slip_list')
        : __('order_bulk_print.preview.invoice_list');
    $selectToPrintMsg = $isDeliverySlip
        ? __('order_bulk_print.preview.select_to_print_slip')
        : __('order_bulk_print.preview.select_to_print');
@endphp

@section('title')
    | {{ $headerTitle }}
@stop

@section('layout')
    @include('admin::shared.header', ['header_name' => $headerTitle])

    <style>
        @include('admin::pages.order.partials.print-document-styles')

        /* 
         * Preview Screen Layout & Full-Height Background
         * Ensure uniform light gray background (#f1f5f9) extends to the very bottom of the page,
         * with seamless flex stretching and natural outer page scrolling.
         */
        #content:has(.workspace-tab-pane.active .bulk-preview-wrapper),
        #content:has(.bulk-preview-wrapper),
        .container-wrapper:has(.workspace-tab-pane.active .bulk-preview-wrapper),
        .container-wrapper:has(.bulk-preview-wrapper) {
            background-color: #f1f5f9 !important;
        }

        #workspace-viewport:has(.workspace-tab-pane.active .bulk-preview-wrapper),
        #workspace-viewport:has(.bulk-preview-wrapper) {
            background-color: #f1f5f9 !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: visible !important;
        }

        .workspace-tab-pane.active:has(.bulk-preview-wrapper),
        .workspace-tab-pane:has(> #bulkPrintPreviewApp),
        .workspace-tab-pane:has(.bulk-preview-wrapper) {
            background-color: #f1f5f9 !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 100% !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: visible !important;
        }

        .bulk-preview-wrapper {
            background-color: #f1f5f9;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100%;
            padding: 16px 20px 60px 20px;
            box-sizing: border-box;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: visible !important;
        }

        @media screen and (max-width: 768px) {
            .bulk-preview-wrapper {
                padding: 12px 14px 40px 14px;
            }

            .preview-toolbar {
                top: 0;
                padding: 10px 14px;
                gap: 12px;
            }

            .toolbar-info {
                gap: 8px;
            }

            .toolbar-title {
                font-size: 13.5px;
            }

            .toolbar-controls {
                width: 100%;
                justify-content: flex-end;
            }
        }

        @media screen and (max-width: 576px) {
            .bulk-preview-wrapper {
                padding: 10px 8px 30px 8px;
            }

            .preview-toolbar {
                border-radius: 8px;
                padding: 10px 12px;
            }

            .toolbar-controls {
                flex-wrap: wrap;
                gap: 8px;
            }

            .btn-toolbar {
                flex: 1 1 auto;
                justify-content: center;
            }
        }

        /* Top Breadcrumb Navigation */
        .preview-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 14px;
        }

        .preview-breadcrumb .breadcrumb-link {
            color: #2563eb;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .preview-breadcrumb .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .preview-breadcrumb .breadcrumb-sep {
            color: #94a3b8;
        }

        .preview-breadcrumb .breadcrumb-current {
            color: #0f172a;
            font-weight: 600;
        }

        /* Sticky Preview Toolbar */
        .preview-toolbar {
            position: sticky;
            top: 45px;
            z-index: 95;
            background: #1e293b;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .toolbar-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-badge {
            background: #2563eb;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 12px;
        }

        .toolbar-meta {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
            padding: 4px 12px;
            border-radius: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .status-indicator.ready {
            color: #4ade80;
            background: rgba(74, 222, 128, 0.15);
        }

        .spinner {
            width: 12px;
            height: 12px;
            border: 2px solid rgba(56, 189, 248, 0.3);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .toolbar-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-controls {
            display: inline-flex;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 7px;
            padding: 2px;
            gap: 2px;
        }

        .btn-zoom {
            background: transparent;
            color: #cbd5e1;
            border: none;
            padding: 4px 10px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.15s ease;
            min-width: auto !important;
        }

        .btn-zoom:hover {
            background: #334155;
            color: #ffffff;
        }

        .btn-toolbar {
            height: 36px;
            padding: 0 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-print-all {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-print-all:hover:not(:disabled) {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-print-all:disabled {
            background: #475569 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
            opacity: 0.7 !important;
        }

        .btn-close-preview {
            background: #334155;
            color: #ffffff;
        }

        .btn-close-preview:hover {
            background: #475569;
            color: #ffffff;
        }

        /* Document Stack Viewport: Natural unconstrained flow */
        .preview-stack-container {
            max-width: 820px;
            width: 100%;
            margin: 0 auto;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            transform-origin: top center;
            transition: transform 0.2s ease;
        }

        .bulk-preview-item {
            margin-bottom: 28px;
            scroll-margin-top: 130px;
            content-visibility: auto;
            contain-intrinsic-size: 800px 520px;
        }

        .bulk-preview-item.bulk-invoice-item.is-active .paper-sheet-box {
            border-color: #93c5fd;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .bulk-invoice-item.is-excluded .paper-sheet-box {
            opacity: 0.65;
            border-style: dashed;
            transition: opacity 0.2s ease, border-style 0.2s ease;
        }

        .document-separator {
            font-size: 11.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .document-separator-title {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .document-separator-status {
            font-size: 11px;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
            padding: 2px 8px;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            line-height: 1.4;
        }

        .document-separator-status.is-included {
            color: #15803d;
            background: #dcfce7;
        }

        .document-separator-status.is-excluded {
            color: #b45309;
            background: #fef3c7;
        }

        .btn-view-order-detail {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
            color: #2563eb;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            line-height: 1.4;
        }

        .btn-view-order-detail:hover {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: #93c5fd;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
        }

        .btn-view-order-detail:active {
            transform: translateY(0);
        }

        .btn-view-order-detail svg {
            flex-shrink: 0;
            width: 13px;
            height: 13px;
            stroke-width: 2;
        }

        .document-separator-line {
            flex: 1;
            height: 1px;
            background: #cbd5e1;
            margin-left: 12px;
        }

        .paper-sheet-box {
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #cbd5e1;
            padding: 16px 20px;
            box-sizing: border-box;
        }

        /* Error Card */
        .preview-error-card {
            max-width: 550px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .preview-error-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .preview-error-card p {
            font-size: 13.5px;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Invoice navigation stays outside the unchanged printable sheets. */
        .bulk-invoice-layout {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .bulk-invoice-sidebar {
            position: sticky;
            top: 125px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 145px);
            z-index: 90;
        }

        .bulk-invoice-sidebar-header {
            flex-shrink: 0;
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .bulk-invoice-sidebar-header h2 {
            margin: 0 0 6px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }

        .bulk-invoice-count {
            color: #64748b;
            font-size: 12px;
        }

        .bulk-invoice-count strong {
            color: #2563eb;
        }

        .bulk-invoice-select-all {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 12px 16px;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }

        .bulk-invoice-sidebar input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin: 0;
            flex: 0 0 16px;
            accent-color: #2563eb;
            cursor: pointer;
        }

        .bulk-invoice-list {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 6px;
        }

        .bulk-invoice-row {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 10px;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .bulk-invoice-row:hover {
            background: #f8fafc;
        }

        .bulk-invoice-row.is-active {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .bulk-invoice-row.is-active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 5px;
            bottom: 5px;
            width: 3px;
            background: #2563eb;
            border-radius: 3px 0 0 3px;
        }

        .bulk-invoice-sidebar .bulk-invoice-row .bulk-invoice-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
            min-width: 0;
            padding: 11px 10px 11px 0;
            border: 0;
            background: transparent;
            color: #334155;
            text-align: left;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.4;
            white-space: normal;
            cursor: pointer;
        }

        .bulk-invoice-link span {
            overflow-wrap: anywhere;
        }

        .bulk-invoice-link svg {
            flex: 0 0 14px;
            color: #94a3b8;
            transition: transform 0.15s ease;
        }

        .bulk-invoice-row.is-active .bulk-invoice-link,
        .bulk-invoice-row.is-active svg {
            color: #2563eb;
        }

        .bulk-invoice-sidebar input:focus-visible,
        .bulk-invoice-link:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }

        .bulk-invoice-empty {
            flex-shrink: 0;
            margin: 0;
            padding: 12px 16px;
            border-top: 1px solid #e2e8f0;
            color: #b45309;
            font-size: 12px;
        }

        .bulk-invoice-main {
            min-width: 0;
        }

        @media screen {
            .bulk-invoice-viewport {
                overflow-x: auto;
                padding: 2px 4px 12px;
            }
        }

        @media screen and (min-width: 1440px) {
            .bulk-invoice-layout {
                grid-template-columns: 220px minmax(0, 820px) 220px;
                justify-content: center;
            }
        }

        @media screen and (max-width: 900px) {
            .bulk-invoice-layout {
                grid-template-columns: minmax(0, 1fr);
                gap: 16px;
            }

            .bulk-invoice-sidebar {
                position: static;
                max-height: none;
            }

            .bulk-invoice-list {
                max-height: 180px;
            }

            .bulk-invoice-viewport .preview-stack-container {
                min-width: 740px;
                transform-origin: top left !important;
            }
        }

        /* Print Media Setup */
        @media print {
            @page {
                size: A5 landscape;
                margin: 4mm 6mm;
            }

            *, *::before, *::after {
                box-sizing: border-box !important;
            }

            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Explicitly hide sidebars, headers, tab bar, toolbars, buttons, dialogs, and inactive panes */
            #sidebar,
            .sidebar,
            .header,
            #content > .header,
            .navHeader,
            .header-name-meta,
            #workspace-tab-bar,
            .mdi-workspace-tabs-container,
            .workspace-tab-bar,
            .mdi-tooltip-popup,
            .mdi-context-menu,
            .mdi-overflow-dropdown,
            .mdi-tab-launcher-modal,
            .preview-breadcrumb,
            .preview-toolbar,
            .document-separator,
            .btn-view-order-detail,
            .bulk-invoice-sidebar,
            .no-print,
            #jsScroll,
            .scroll,
            .dialog,
            #file-manager-popup-container,
            #selectOption,
            #logoutModal,
            #verifyModal,
            .iziToast-wrapper,
            .iziToast,
            #workspace-viewport > :not(.workspace-tab-pane.active),
            #workspace-viewport > .workspace-tab-pane:not(.active),
            #workspace-viewport > [id^="tab-pane-"]:not(.active),
            .workspace-tab-pane:not(.active),
            #tab-pane-initial:not(.active),
            nav,
            footer {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                max-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                overflow: hidden !important;
            }

            /* Remove layout offsets and scrollbars from all ancestor containers */
            .container,
            .container-wrapper,
            .content,
            #content,
            .content-wrapper,
            .workspace-viewport,
            #workspace-viewport,
            .workspace-tab-pane.active,
            .bulk-preview-wrapper {
                display: block !important;
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                max-height: none !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
                float: none !important;
                background: #ffffff !important;
            }

            ::-webkit-scrollbar,
            *::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            .preview-stack-container {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                transform: none !important;
                transform-origin: unset !important;
            }

            .bulk-preview-item {
                display: block !important;
                position: relative !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
                content-visibility: visible !important;
            }

            /* Force page break between orders (except the first) */
            .bulk-preview-item + .bulk-preview-item {
                page-break-before: always !important;
                break-before: page !important;
            }

            .bulk-invoice-layout,
            .bulk-invoice-main,
            .bulk-invoice-viewport {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }

            .bulk-preview-item.bulk-invoice-item {
                page-break-before: auto !important;
                break-before: auto !important;
            }

            .bulk-preview-item.bulk-invoice-item:not(.is-selected) {
                display: none !important;
            }

            .bulk-invoice-item.is-selected ~ .bulk-invoice-item.is-selected {
                page-break-before: always !important;
                break-before: page !important;
            }

            .paper-sheet-box {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: #ffffff !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
            }

            .pos-invoice-sheet,
            .pos-slip-sheet {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background: #ffffff !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
            }

            .inv-grid-table,
            .slip-grid-table {
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .inv-grid-table tr,
            .slip-grid-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>

    <div class="bulk-preview-wrapper" id="bulkPrintPreviewApp" x-data="orderBulkPrintPreview()" x-cloak>
        @if (!empty($error) || (!$isConsolidated && empty($items)) || ($isConsolidated && !isset($consolidatedProducts)))
            <div class="preview-error-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px auto; display: block;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h3>{{ __('order_bulk_print.validation.order_ids_invalid') }}</h3>
                <p>{{ $error ?? __('order_bulk_print.validation.no_orders_selected') }}</p>
                <button type="button" class="btn-toolbar btn-close-preview" @click="closeTab()" style="margin: 0 auto;">
                    {{ __('order_bulk_print.preview.close') }}
                </button>
            </div>
        @else
            <!-- Breadcrumb Navigation -->
            <div class="preview-breadcrumb no-print">
                <a href="{{ route('admin-order-bulk-print-index') }}" class="breadcrumb-link s-click-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>{{ __('order_bulk_print.title') }}</span>
                </a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">{{ $docTitle }}</span>
            </div>

            <!-- Sticky Toolbar -->
            <header class="preview-toolbar no-print">
                <div class="toolbar-info">
                    <div class="toolbar-title">
                        <span>{{ $docTitle }}</span>
                        <span class="toolbar-badge"
                            @if ($hasSidebar)
                                x-text="@js(__('order_bulk_print.preview.meta_docs', ['count' => ':count'])).replace(':count', selectedInvoiceIds.length)"
                            @endif>
                            {{ __('order_bulk_print.preview.meta_docs', ['count' => $totalCount]) }}
                        </span>
                        @if ($isConsolidated)
                            <span class="toolbar-badge" style="background: #0284c7;">
                                {{ $totalProducts ?? 0 }} {{ __('order_bulk_print.preview.products') }}
                            </span>
                            <span class="toolbar-badge" style="background: #10b981;">
                                {{ number_format($overallQuantity ?? 0) }} {{ __('order_bulk_print.preview.total_quantity') }}
                            </span>
                        @endif
                        <span class="toolbar-badge" style="background: #334155;">
                            {{ $locale === 'km' ? __('order_bulk_print.selection.khmer') : __('order_bulk_print.selection.english') }}
                        </span>
                    </div>

                    <div class="toolbar-meta">
                        <span>{{ __('order_bulk_print.preview.prepared', ['date' => now()->format('Y-m-d H:i')]) }}</span>
                        <span>·</span>
                        <span>{{ $isConsolidated ? 'Paper: A4 Portrait' : __('order_bulk_print.preview.paper_size') }}</span>
                    </div>

                    <div class="status-indicator" :class="{ 'ready': isReady }">
                        <template x-if="!isReady">
                            <div class="spinner"></div>
                        </template>
                        <template x-if="isReady">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </template>
                        <span x-text="isReady ? '{{ __('order_bulk_print.preview.ready') }}' : '{{ __('order_bulk_print.preview.preparing') }}'"></span>
                    </div>
                </div>

                <div class="toolbar-controls">
                    <div class="zoom-controls">
                        <button type="button" class="btn-zoom" @click="adjustZoom(-0.1)" title="{{ __('order_bulk_print.preview.zoom_out') }}">−</button>
                        <button type="button" class="btn-zoom" @click="resetZoom()" title="{{ __('order_bulk_print.preview.fit_width') }}" x-text="Math.round(scale * 100) + '%'"></button>
                        <button type="button" class="btn-zoom" @click="adjustZoom(0.1)" title="{{ __('order_bulk_print.preview.zoom_in') }}">+</button>
                    </div>

                    <button type="button" class="btn-toolbar btn-print-all" :disabled="!canPrint" @click="printAll()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        {{ __('order_bulk_print.preview.print_all') }}
                    </button>

                    <button type="button" class="btn-toolbar btn-close-preview" @click="refreshPreview()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                        {{ __('global.button.refresh') }}
                    </button>

                    <button type="button" class="btn-toolbar btn-close-preview" @click="closeTab()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        {{ __('order_bulk_print.preview.close') }}
                    </button>
                </div>
            </header>

            @if ($hasSidebar)
                <div class="bulk-invoice-layout">
                    <aside class="bulk-invoice-sidebar no-print" aria-label="{{ $sidebarTitle }}">
                        <div class="bulk-invoice-sidebar-header">
                            <h2>{{ $sidebarTitle }}</h2>
                            <div class="bulk-invoice-count" aria-live="polite" aria-atomic="true">
                                <strong x-text="selectedInvoiceIds.length"></strong> / {{ count($items) }}
                                {{ __('order_bulk_print.preview.selected') }}
                            </div>
                        </div>
                        <label class="bulk-invoice-select-all">
                            <input type="checkbox" :checked="allInvoicesSelected"
                                x-effect="$el.indeterminate = selectedInvoiceIds.length > 0 && !allInvoicesSelected"
                                @change="toggleAllInvoices()">
                            <span x-text="allInvoicesSelected ? @js(__('order_bulk_print.preview.deselect_all')) : @js(__('order_bulk_print.preview.select_all'))"></span>
                        </label>
                        <div class="bulk-invoice-list">
                            @foreach ($items as $item)
                                @php
                                    $listOrder = $item['order'];
                                    $listInvoiceNo = $listOrder->invoice_number ?: sprintf('%05d', $listOrder->id);
                                @endphp
                                <div class="bulk-invoice-row" data-invoice-row="{{ $listOrder->id }}"
                                    :class="{ 'is-active': activeInvoiceId === '{{ $listOrder->id }}' }">
                                    <input type="checkbox" value="{{ $listOrder->id }}"
                                        aria-label="{{ __('order_bulk_print.preview.include_invoice', ['invoice' => $listInvoiceNo]) }}"
                                        :checked="selectedInvoiceIds.includes('{{ $listOrder->id }}')"
                                        @change="toggleInvoice('{{ $listOrder->id }}', $event.target.checked)">
                                    <button type="button" class="bulk-invoice-link" @click="scrollToInvoice('{{ $listOrder->id }}')"
                                        :aria-current="activeInvoiceId === '{{ $listOrder->id }}' ? 'true' : null">
                                        <span>{{ $listInvoiceNo }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <polyline points="9 5 16 12 9 19"></polyline>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <p class="bulk-invoice-empty" x-show="selectedInvoiceIds.length === 0" role="status">
                            {{ $selectToPrintMsg }}
                        </p>
                    </aside>
                    <div class="bulk-invoice-main">
                        <div class="bulk-invoice-viewport">
            @endif

            <!-- Document Stack: Natural outer page scroll, using div (not main) to prevent #content main scroll rules -->
            <div class="preview-stack-container" :style="scale !== 1 ? 'transform: scale(' + scale + '); transform-origin: top center;' : ''">
                @if ($isConsolidated)
                    <section class="bulk-preview-item" style="margin-bottom: 0;">
                        <div class="paper-sheet-box" style="padding: 0 !important; background: transparent !important; border: none !important; box-shadow: none !important;">
                            @include('admin::pages.order-bulk-print.partials.consolidated-template', [
                                'orders' => $orders,
                                'consolidatedProducts' => $consolidatedProducts ?? [],
                                'overallQuantity' => $overallQuantity ?? 0,
                                'totalProducts' => $totalProducts ?? 0,
                                'delLocation' => $delLocation ?? '',
                                'delDate' => $delDate ?? '',
                                'driverName' => $driverName ?? '',
                                'stopsCount' => $stopsCount ?? ($orders->count() ?? 0),
                                'documentNo' => $documentNo ?? '',
                                'reportDate' => $reportDate ?? now()->format('m/d/y'),
                                'reportTime' => $reportTime ?? now()->format('g:iA'),
                                'printLocale' => $locale ?? 'en',
                            ])
                        </div>
                    </section>
                @else
                    @foreach ($items as $index => $item)
                        @php
                            $order = $item['order'];
                            $invoiceNo = $order->invoice_number ?: sprintf('%05d', $order->id);
                        @endphp
                        <section class="bulk-preview-item{{ $hasSidebar ? ' bulk-invoice-item' : '' }}"
                            @if ($hasSidebar)
                                data-invoice-id="{{ $order->id }}"
                                :class="{
                                    'is-active': activeInvoiceId === '{{ $order->id }}',
                                    'is-selected': selectedInvoiceIds.includes('{{ $order->id }}'),
                                    'is-excluded': !selectedInvoiceIds.includes('{{ $order->id }}')
                                }"
                            @endif>
                            <!-- Screen-only separator between documents -->
                            <div class="document-separator no-print">
                                <div class="document-separator-title">
                                    <span>
                                        {{ __('order_bulk_print.preview.doc_separator', [
                                            'current' => $index + 1,
                                            'total' => $totalCount,
                                            'invoice' => $invoiceNo,
                                        ]) }}
                                    </span>
                                    @if ($hasSidebar)
                                        <span class="document-separator-status"
                                            :class="selectedInvoiceIds.includes('{{ $order->id }}') ? 'is-included' : 'is-excluded'"
                                            x-text="selectedInvoiceIds.includes('{{ $order->id }}') ? @js(__('order_bulk_print.preview.included_in_print')) : @js(__('order_bulk_print.preview.excluded_from_print'))">
                                        </span>
                                        @if (!auth()->check() || auth()->user()->can('order-view'))
                                            <a href="{{ route('admin-order-detail', $order->id) }}"
                                                @click="openOrderDetail($event, '{{ route('admin-order-detail', $order->id) }}', '{{ $invoiceNo }}')"
                                                class="btn-view-order-detail no-print"
                                                title="{{ __('order_bulk_print.preview.view_detail') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                <span>{{ __('order_bulk_print.preview.view_detail') }}</span>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                                <div class="document-separator-line"></div>
                            </div>

                            <!-- Printed Paper Sheet -->
                            <div class="paper-sheet-box">
                                @if ($isInvoice)
                                    @include('admin::pages.order.invoice-template', [
                                        'order' => $order,
                                        'printLocale' => $item['printLocale'],
                                        'invoicePreviousBalance' => $item['invoicePreviousBalance'],
                                        'customerName' => $item['customerName'],
                                        'customerPhone' => $item['customerPhone'],
                                        'customerAddress' => $item['customerAddress'],
                                        'printAreaId' => $item['printAreaId'],
                                    ])
                                @else
                                    @include('admin::pages.order.invoice-template-02', [
                                        'order' => $order,
                                        'printLocale' => $item['printLocale'],
                                        'invoicePreviousBalance' => $item['invoicePreviousBalance'],
                                        'customerName' => $item['customerName'],
                                        'customerPhone' => $item['customerPhone'],
                                        'customerAddress' => $item['customerAddress'],
                                        'printAreaId' => $item['printAreaId'],
                                    ])
                                @endif
                            </div>
                        </section>
                    @endforeach
                @endif
            </div>
            @if ($hasSidebar)
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <script>
        function orderBulkPrintPreview() {
            return {
                scale: 1,
                isReady: false,
                isPrinting: false,
                invoiceIds: [],
                selectedInvoiceIds: [],
                activeInvoiceId: null,
                isProgrammaticScroll: false,
                ticking: false,
                itemElements: null,
                scrollTimeout: null,
                _scrollHandler: null,

                get allInvoicesSelected() {
                    return this.invoiceIds.length > 0 && this.selectedInvoiceIds.length === this.invoiceIds.length;
                },

                get canPrint() {
                    return this.isReady && !this.isPrinting && (this.invoiceIds.length === 0 || this.selectedInvoiceIds.length > 0);
                },

                toggleInvoice(id, checked) {
                    if (!this.invoiceIds.includes(id)) return;
                    this.selectedInvoiceIds = this.selectedInvoiceIds.filter(invoiceId => invoiceId !== id);
                    if (checked) this.selectedInvoiceIds.push(id);
                },

                toggleAllInvoices() {
                    this.selectedInvoiceIds = this.allInvoicesSelected ? [] : [...this.invoiceIds];
                },

                scrollToInvoice(id) {
                    if (!this.invoiceIds.includes(id)) return;
                    this.activeInvoiceId = id;

                    const docEl = this.$root.querySelector(`.bulk-preview-item[data-invoice-id="${id}"]`);
                    if (docEl) {
                        this.isProgrammaticScroll = true;

                        try {
                            docEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } catch (e) {
                            const rect = docEl.getBoundingClientRect();
                            const topOffset = window.pageYOffset + rect.top - 125;
                            window.scrollTo({ top: topOffset, behavior: 'smooth' });
                        }

                        this.scrollSidebarToActive();

                        if (this.scrollTimeout) clearTimeout(this.scrollTimeout);
                        this.scrollTimeout = setTimeout(() => {
                            this.isProgrammaticScroll = false;
                        }, 700);
                    }
                },

                viewInvoice(id) {
                    this.scrollToInvoice(id);
                },

                openOrderDetail(e, url, invoiceNo) {
                    if (e && (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0)) {
                        return;
                    }
                    if (e && typeof e.preventDefault === 'function') {
                        e.preventDefault();
                    }
                    const tabTitle = invoiceNo ? (@js(__('order.detail.title')) + ' #' + invoiceNo) : @js(__('order.detail.title'));
                    if (window.MDI && typeof window.MDI.openUrlInTab === 'function') {
                        window.MDI.openUrlInTab(url, tabTitle, 'bx bx-show');
                    } else {
                        window.open(url, '_blank');
                    }
                },

                scrollSidebarToActive() {
                    this.$nextTick(() => {
                        const row = this.$root.querySelector(`.bulk-invoice-row[data-invoice-row="${this.activeInvoiceId}"]`);
                        const list = this.$root.querySelector('.bulk-invoice-list');
                        if (!row || !list) return;

                        const rowBounds = row.getBoundingClientRect();
                        const listBounds = list.getBoundingClientRect();

                        if (rowBounds.top < listBounds.top) {
                            list.scrollTop -= (listBounds.top - rowBounds.top) + 8;
                        } else if (rowBounds.bottom > listBounds.bottom) {
                            list.scrollTop += (rowBounds.bottom - listBounds.bottom) + 8;
                        }
                    });
                },

                setupScrollSpy() {
                    this.itemElements = null;
                    this.ticking = false;

                    this._scrollHandler = () => {
                        if (this.isProgrammaticScroll || this.ticking) return;
                        this.ticking = true;
                        requestAnimationFrame(() => {
                            this.updateActiveOnScroll();
                            this.ticking = false;
                        });
                    };

                    window.addEventListener('scroll', this._scrollHandler, { passive: true, capture: true });
                },

                updateActiveOnScroll() {
                    if (this.isProgrammaticScroll) return;

                    if (!this.itemElements || this.itemElements.length === 0) {
                        this.itemElements = Array.from(this.$root.querySelectorAll('.bulk-preview-item[data-invoice-id]'));
                    }
                    if (this.itemElements.length === 0) return;

                    const offset = 180;
                    let currentId = null;

                    for (let i = 0; i < this.itemElements.length; i++) {
                        const rect = this.itemElements[i].getBoundingClientRect();
                        if (rect.top <= offset) {
                            currentId = this.itemElements[i].dataset.invoiceId;
                        } else {
                            break;
                        }
                    }

                    if (!currentId && this.itemElements.length > 0) {
                        currentId = this.itemElements[0].dataset.invoiceId;
                    }

                    if (currentId && currentId !== this.activeInvoiceId) {
                        this.activeInvoiceId = currentId;
                        this.scrollSidebarToActive();
                    }
                },

                adjustZoom(delta) {
                    this.scale = Math.min(1.5, Math.max(0.6, Math.round((this.scale + delta) * 10) / 10));
                },

                resetZoom() {
                    this.scale = 1;
                },

                init() {
                    // Read this preview's documents locally; other MDI tabs may use the same component.
                    this.invoiceIds = Array.from(this.$root.querySelectorAll('.bulk-invoice-item'), item => item.dataset.invoiceId);
                    this.selectedInvoiceIds = [...this.invoiceIds];
                    this.activeInvoiceId = this.invoiceIds[0] ?? null;
                    this.$nextTick(() => {
                        this.removeInternalScroll();
                        this.checkAssetsReady();
                        this.setupScrollSpy();
                    });

                    if (typeof this.$cleanup === 'function') {
                        this.$cleanup(() => {
                            if (this._scrollHandler) {
                                window.removeEventListener('scroll', this._scrollHandler, { capture: true });
                            }
                            if (this.scrollTimeout) clearTimeout(this.scrollTimeout);
                        });
                    }
                },

                removeInternalScroll() {
                    // Ensure active tab pane and ancestors flex cleanly to 100% full height with uniform light gray background
                    const activePane = this.$el.closest('.workspace-tab-pane');
                    if (activePane) {
                        activePane.style.setProperty('background-color', '#f1f5f9', 'important');
                        activePane.style.setProperty('display', 'flex', 'important');
                        activePane.style.setProperty('flex-direction', 'column', 'important');
                        activePane.style.setProperty('flex', '1 1 auto', 'important');
                        activePane.style.setProperty('min-height', '100%', 'important');
                        activePane.style.setProperty('overflow', 'visible', 'important');
                        activePane.style.setProperty('overflow-y', 'visible', 'important');
                    }
                    const viewport = document.getElementById('workspace-viewport');
                    if (viewport) {
                        viewport.style.setProperty('background-color', '#f1f5f9', 'important');
                        viewport.style.setProperty('overflow', 'visible', 'important');
                        viewport.style.setProperty('overflow-y', 'visible', 'important');
                    }
                    const content = document.getElementById('content');
                    if (content) {
                        content.style.setProperty('background-color', '#f1f5f9', 'important');
                        content.style.setProperty('overflow', 'visible', 'important');
                        content.style.setProperty('overflow-y', 'visible', 'important');
                    }
                },

                async checkAssetsReady() {
                    try {
                        if (document.fonts && document.fonts.ready) {
                            await document.fonts.ready;
                        }
                        const container = this.$el;
                        const images = Array.from(container.querySelectorAll('img'));
                        await Promise.all(images.map(img => {
                            if (img.complete) return Promise.resolve();
                            return new Promise(resolve => {
                                img.onload = resolve;
                                img.onerror = resolve;
                            });
                        }));
                    } catch (e) {
                        console.warn('Asset readiness warning:', e);
                    }
                    this.isReady = true;
                },

                printAll() {
                    if (!this.canPrint) return;
                    this.isPrinting = true;
                    // Temporarily reset interactive zoom so CSS transform does not block paged media page breaks
                    const savedScale = this.scale;
                    this.scale = 1;

                    const activePane = this.$el.closest('.workspace-tab-pane');
                    const viewport = document.getElementById('workspace-viewport');
                    const siblingsToRestore = [];

                    // Explicitly isolate print by hiding all other tab panes in #workspace-viewport
                    if (activePane && activePane.parentElement) {
                        Array.from(activePane.parentElement.children).forEach(child => {
                            if (child !== activePane && child.nodeType === 1) {
                                siblingsToRestore.push({
                                    el: child,
                                    prevDisplay: child.style.display,
                                    prevPriority: child.style.getPropertyPriority('display')
                                });
                                child.style.setProperty('display', 'none', 'important');
                            }
                        });
                    }

                    // Convert flex ancestors to block so CSS page breaks (break-before: page) work across all browsers
                    if (activePane) {
                        activePane.style.setProperty('display', 'block', 'important');
                        activePane.style.setProperty('height', 'auto', 'important');
                        activePane.style.setProperty('max-height', 'none', 'important');
                        activePane.style.setProperty('overflow', 'visible', 'important');
                    }
                    if (viewport) {
                        viewport.style.setProperty('display', 'block', 'important');
                        viewport.style.setProperty('height', 'auto', 'important');
                        viewport.style.setProperty('max-height', 'none', 'important');
                        viewport.style.setProperty('overflow', 'visible', 'important');
                    }

                    let cleanedUp = false;
                    const cleanup = () => {
                        if (cleanedUp) return;
                        cleanedUp = true;

                        this.isPrinting = false;
                        this.scale = savedScale;
                        if (activePane) {
                            activePane.style.setProperty('display', 'flex', 'important');
                            activePane.style.setProperty('flex-direction', 'column', 'important');
                            activePane.style.setProperty('flex', '1 1 auto', 'important');
                            activePane.style.setProperty('min-height', '100%', 'important');
                            activePane.style.setProperty('background-color', '#f1f5f9', 'important');
                            activePane.style.setProperty('overflow', 'visible', 'important');
                            activePane.style.setProperty('overflow-y', 'visible', 'important');
                        }
                        if (viewport) {
                            viewport.style.setProperty('display', 'flex', 'important');
                            viewport.style.setProperty('flex-direction', 'column', 'important');
                            viewport.style.setProperty('flex', '1 1 auto', 'important');
                            viewport.style.setProperty('background-color', '#f1f5f9', 'important');
                            viewport.style.setProperty('overflow', 'visible', 'important');
                            viewport.style.setProperty('overflow-y', 'visible', 'important');
                        }
                        siblingsToRestore.forEach(item => {
                            if (item.prevDisplay) {
                                item.el.style.setProperty('display', item.prevDisplay, item.prevPriority);
                            } else {
                                item.el.style.removeProperty('display');
                            }
                        });
                    };

                    this.$nextTick(() => {
                        setTimeout(() => {
                            window.addEventListener('afterprint', cleanup, { once: true });
                            window.print();
                            // Fallback in case afterprint does not fire
                            setTimeout(cleanup, 500);
                        }, 80);
                    });
                },

                refreshPreview() {
                    const tabKey = this.$root.closest('.workspace-tab-pane')?.getAttribute('data-tab-key');

                    if (tabKey && window.MDI && typeof window.MDI.refreshTab === 'function') {
                        window.MDI.refreshTab(tabKey);
                        return;
                    }

                    window.location.reload();
                },

                closeTab() {
                    // Reuse system's Core Standard MDI tab management
                    if (window.MDI && typeof window.MDI.closeTab === 'function') {
                        const tabKey = this.$root.closest('.workspace-tab-pane')?.getAttribute('data-tab-key');
                        const currentTab = (tabKey && window.MDI.tabs.find(t => t.key === tabKey))
                            || window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);

                        if (currentTab) {
                            window.MDI.closeTab(currentTab);
                            return;
                        }
                    }

                    // Fallback when accessed directly outside MDI
                    if (window.history.length > 1) {
                        window.history.back();
                    } else {
                        window.location.href = '{{ route('admin-order-bulk-print-index') }}';
                    }
                }
            };
        }
    </script>
@stop
