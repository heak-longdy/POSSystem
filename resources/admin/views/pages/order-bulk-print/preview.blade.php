@extends('admin::shared.layout')

@php
    $isInvoice = ($documentType ?? 'invoice') === 'invoice';
    $docTitle = $isInvoice 
        ? __('order_bulk_print.preview.title_invoice') 
        : __('order_bulk_print.preview.title_delivery_slip');
    $headerTitle = $docTitle . ' (' . ($totalCount ?? 0) . ')';
@endphp

@section('title')
    | {{ $headerTitle }}
@stop

@section('layout')
    @include('admin::shared.header', ['header_name' => $headerTitle])

    <style>
        @include('admin::pages.order.partials.print-document-styles')

        /* Preview Screen Layout: Natural outer page scroll, NO internal scrollbar */
        #content:has(.bulk-preview-wrapper),
        #workspace-viewport:has(.bulk-preview-wrapper),
        .workspace-tab-pane:has(.bulk-preview-wrapper),
        .workspace-tab-pane.active:has(.bulk-preview-wrapper) {
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: visible !important;
        }

        .bulk-preview-wrapper {
            background-color: #f1f5f9;
            min-height: calc(100vh - 120px);
            padding: 16px 20px 60px 20px;
            box-sizing: border-box;
            display: block;
            width: 100%;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: visible !important;
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
            background: #475569;
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.7;
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
            }

            /* Force page break between orders (except the first) */
            .bulk-preview-item + .bulk-preview-item {
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
        @if (!empty($error) || empty($items))
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
                        <span class="toolbar-badge">
                            {{ __('order_bulk_print.preview.meta_docs', ['count' => $totalCount]) }}
                        </span>
                        <span class="toolbar-badge" style="background: #334155;">
                            {{ $locale === 'km' ? __('order_bulk_print.selection.khmer') : __('order_bulk_print.selection.english') }}
                        </span>
                    </div>

                    <div class="toolbar-meta">
                        <span>{{ __('order_bulk_print.preview.prepared', ['date' => now()->format('Y-m-d H:i')]) }}</span>
                        <span>·</span>
                        <span>{{ __('order_bulk_print.preview.paper_size') }}</span>
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

                    <button type="button" class="btn-toolbar btn-print-all" :disabled="!isReady" @click="printAll()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        {{ __('order_bulk_print.preview.print_all') }}
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

            <!-- Document Stack: Natural outer page scroll, using div (not main) to prevent #content main scroll rules -->
            <div class="preview-stack-container" :style="scale !== 1 ? 'transform: scale(' + scale + '); transform-origin: top center;' : ''">
                @foreach ($items as $index => $item)
                    @php
                        $order = $item['order'];
                        $invoiceNo = $order->invoice_number ?: sprintf('%05d', $order->id);
                    @endphp
                    <section class="bulk-preview-item">
                        <!-- Screen-only separator between documents -->
                        <div class="document-separator no-print">
                            <span>
                                {{ __('order_bulk_print.preview.doc_separator', [
                                    'current' => $index + 1,
                                    'total' => $totalCount,
                                    'invoice' => $invoiceNo,
                                ]) }}
                            </span>
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
                                    'customerName' => $item['customerName'],
                                    'customerPhone' => $item['customerPhone'],
                                    'customerAddress' => $item['customerAddress'],
                                    'printAreaId' => $item['printAreaId'],
                                ])
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function orderBulkPrintPreview() {
            return {
                scale: 1,
                isReady: false,

                adjustZoom(delta) {
                    this.scale = Math.min(1.5, Math.max(0.6, Math.round((this.scale + delta) * 10) / 10));
                },

                resetZoom() {
                    this.scale = 1;
                },

                init() {
                    this.$nextTick(() => {
                        this.removeInternalScroll();
                        this.checkAssetsReady();
                    });
                },

                removeInternalScroll() {
                    // Remove any height or overflow restrictions on ancestors to ensure 100% outer window scroll
                    const activePane = this.$el.closest('.workspace-tab-pane');
                    if (activePane) {
                        activePane.style.setProperty('height', 'auto', 'important');
                        activePane.style.setProperty('max-height', 'none', 'important');
                        activePane.style.setProperty('overflow', 'visible', 'important');
                        activePane.style.setProperty('overflow-y', 'visible', 'important');
                    }
                    const viewport = document.getElementById('workspace-viewport');
                    if (viewport) {
                        viewport.style.setProperty('height', 'auto', 'important');
                        viewport.style.setProperty('max-height', 'none', 'important');
                        viewport.style.setProperty('overflow', 'visible', 'important');
                        viewport.style.setProperty('overflow-y', 'visible', 'important');
                    }
                    const content = document.getElementById('content');
                    if (content) {
                        content.style.setProperty('height', 'auto', 'important');
                        content.style.setProperty('max-height', 'none', 'important');
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

                        this.scale = savedScale;
                        if (activePane) {
                            activePane.style.setProperty('display', 'flex', 'important');
                            activePane.style.setProperty('height', 'auto', 'important');
                            activePane.style.setProperty('max-height', 'none', 'important');
                            activePane.style.setProperty('overflow', 'visible', 'important');
                        }
                        if (viewport) {
                            viewport.style.setProperty('display', 'flex', 'important');
                            viewport.style.setProperty('height', 'auto', 'important');
                            viewport.style.setProperty('max-height', 'none', 'important');
                            viewport.style.setProperty('overflow', 'visible', 'important');
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
