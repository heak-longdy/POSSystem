@extends('admin::shared.layout')

@section('layout')
    <style>
        /* Unified 13px font-size across page index listing */
        .stock-in-listing-wrapper,
        .stock-in-listing-wrapper .content-body,
        .stock-in-listing-wrapper .table,
        .stock-in-listing-wrapper .table-wrapper {
            font-size: 13px !important;
        }

        /* Tabs font size */
        .stock-in-listing-wrapper .header-tab .tabs a,
        .stock-in-listing-wrapper .header-tab .tabs a span {
            font-size: 13px !important;
        }

        /* Header action buttons */
        .stock-in-listing-wrapper .header-action-button button,
        .stock-in-listing-wrapper .header-action-button button span,
        .stock-in-listing-wrapper .header-action-button a,
        .stock-in-listing-wrapper .header-action-button a span {
            font-size: 13px !important;
        }

        /* Table header row & text */
        .stock-in-listing-wrapper .table-header .row,
        .stock-in-listing-wrapper .table-header .row span {
            font-size: 13px !important;
        }

        /* Table body row & text */
        .stock-in-listing-wrapper .table-body .column,
        .stock-in-listing-wrapper .table-body .column .row,
        .stock-in-listing-wrapper .table-body .column .row span,
        .stock-in-listing-wrapper .table-body .column .row strong,
        .stock-in-listing-wrapper .table-body .column .row a,
        .stock-in-listing-wrapper .table-body .column .row div {
            font-size: 13px !important;
        }

        /* PO number link */
        .stock-in-listing-wrapper .po-number-link,
        .stock-in-listing-wrapper .po-number-link strong {
            font-size: 13px !important;
            color: #2563eb;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .stock-in-listing-wrapper .po-number-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Table subtext (Reference No) */
        .stock-in-listing-wrapper .table-subtext {
            font-size: 13px !important;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Status Badges */
        .stock-in-listing-wrapper .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 5px !important;
            border-radius: 9999px;
            font-size: 13px !important;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            margin: 0 auto;
        }

        .badge-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-ordered { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-partial { background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-received { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-cancelled { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        
        .badge-unpaid { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-paid { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-default { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .action-buttons-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-action-icon:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }
        .btn-action-icon.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }
        .btn-action-icon.text-success:hover {
            background: #ecfdf5;
            color: #059669;
            border-color: #a7f3d0;
        }

        /* Action dropdown menu */
        .stock-in-listing-wrapper .dropdown-menu .dropdown-item,
        .stock-in-listing-wrapper .dropdown-menu .dropdown-item span {
            font-size: 13px !important;
        }

        /* Pagination font size */
        .stock-in-listing-wrapper .paginationLayout2,
        .stock-in-listing-wrapper .paginationLayout2 span,
        .stock-in-listing-wrapper .paginationLayout2 a,
        .stock-in-listing-wrapper .paginationLayout2 label,
        .stock-in-listing-wrapper .paginationLayout2 select,
        .stock-in-listing-wrapper .pagination,
        .stock-in-listing-wrapper .pagination li,
        .stock-in-listing-wrapper .pagination li a,
        .stock-in-listing-wrapper .pagination li span {
            font-size: 13px !important;
        }

        /* Header & Toolbar Layout */
        .stock-in-listing-wrapper .header .header-action-button .filter {
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .stock-in-listing-wrapper .header {
            z-index: 100 !important;
            overflow: visible !important;
        }
        .stock-in-listing-wrapper .header-tab {
            min-width: 0;
            max-width: 100%;
        }
        .stock-in-listing-wrapper .header-tab > *,
        .stock-in-listing-wrapper .header-action-button > * {
            flex-shrink: 0;
        }
        .stock-in-listing-wrapper .header-action-button,
        .stock-in-listing-wrapper .header-action-button .filter {
            overflow: visible !important;
        }

        /* Advanced Filter Button */
        .stock-in-listing-wrapper .advanced-filter-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            z-index: 50;
        }
        .stock-in-listing-wrapper .btn-advanced-filter {
            height: 38px;
            border-radius: 20px;
            border: 1px solid rgba(152, 152, 152, 0.35);
            background: #ffffff;
            color: #475569;
            font-size: 13px !important;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 12px 0 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
        }
        .stock-in-listing-wrapper .btn-advanced-filter span {
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .btn-advanced-filter:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #2563eb;
        }
        .stock-in-listing-wrapper .btn-advanced-filter.active,
        .stock-in-listing-wrapper .btn-advanced-filter.open {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #2563eb;
        }
        .stock-in-listing-wrapper .btn-advanced-filter .advanced-filter-badge {
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            padding: 1px 6px;
            line-height: 1.2;
        }

        /* Advanced Filter Popover */
        .stock-in-listing-wrapper .advanced-filter-popover {
            position: fixed;
            width: 370px;
            max-width: calc(100vw - 16px);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            z-index: 1050;
            padding: 16px;
            box-sizing: border-box;
            text-align: left;
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .filter-popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .stock-in-listing-wrapper .filter-popover-title,
        .stock-in-listing-wrapper .filter-popover-title span {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px !important;
            font-weight: 600;
            color: #1e293b;
        }
        .stock-in-listing-wrapper .filter-popover-title i {
            font-size: 16px;
            color: #2563eb;
        }
        .stock-in-listing-wrapper .filter-popover-count {
            font-size: 11px;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 7px;
            border-radius: 10px;
        }
        .stock-in-listing-wrapper .filter-popover-close {
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.15s ease;
            min-width: auto !important;
            width: 37px !important;
            height: 35px !important;
        }
        .stock-in-listing-wrapper .filter-popover-close i.bx {
            margin-right: 0 !important;
        }
        .stock-in-listing-wrapper .filter-popover-close:hover {
            background: #f1f5f9;
            color: #475569;
        }
        .stock-in-listing-wrapper .filter-field-group {
            margin-bottom: 14px;
        }
        .stock-in-listing-wrapper .filter-field-group label {
            display: block;
            font-size: 13px !important;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .stock-in-listing-wrapper .filter-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stock-in-listing-wrapper .filter-search-input {
            height: 43px;
            width: 100%;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0 12px;
            box-sizing: border-box;
            font-size: 13px !important;
            font-weight: 500;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .stock-in-listing-wrapper .filter-search-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }
        .stock-in-listing-wrapper .filter-search-input::placeholder {
            color: #8892a0 !important;
            font-size: 13px !important;
        }

        /* Filter Popover Date Input */
        .stock-in-listing-wrapper .filter-date-input {
            position: relative !important;
            cursor: pointer;
            width: 100%;
        }
        .stock-in-listing-wrapper .filter-date-input input.filter-input {
            width: 100% !important;
            height: 43px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 7px !important;
            padding: 0 34px 0 12px !important;
            font-size: 13px !important;
            font-weight: 500;
            color: #0f172a !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            outline: none !important;
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .stock-in-listing-wrapper .filter-date-input input.filter-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }
        .stock-in-listing-wrapper .filter-date-input input.filter-input::placeholder {
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .filter-date-input i.bx-calendar {
            position: absolute !important;
            right: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 18px !important;
            color: #64748b !important;
            pointer-events: none !important;
            line-height: 1 !important;
        }

        /* Filter Popover Footer */
        .stock-in-listing-wrapper .filter-popover-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }
        .stock-in-listing-wrapper .btn-popover-reset {
            height: 38px;
            padding: 0 14px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 13px !important;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .stock-in-listing-wrapper .btn-popover-reset span {
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .btn-popover-reset:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .stock-in-listing-wrapper .btn-popover-apply {
            height: 38px;
            padding: 0 16px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 13px !important;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .stock-in-listing-wrapper .btn-popover-apply span {
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .btn-popover-apply:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        /* Select2 inside popover */
        .stock-in-listing-wrapper .select2Group .select2-container .select2-selection--single,
        .stock-in-listing-wrapper .select2Group .select2-container .select2-selection__rendered {
            font-size: 13px !important;
        }
        .stock-in-listing-wrapper .select2-dropdown,
        .stock-in-listing-wrapper .select2-results__option {
            font-size: 13px !important;
        }

        /* Reset / Clear button */
        .btn-clear-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 39px;
            border-radius: 7px;
            background: #fee2e2;
            color: #ef4444;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-left: 2px;
            border: 1px solid #fca5a5;
            align-self: center;
            cursor: pointer;
            flex-shrink: 0;
        }
        .btn-clear-filter:hover {
            background: #fecaca;
            color: #b91c1c;
            transform: scale(1.05);
        }

        /* Scoped UI Datepicker Popup Elevation */
        div#ui-datepicker-div,
        #ui-datepicker-div.stock-in-datepicker-popup {
            z-index: 100001 !important;
        }

        /* Partial Received Modal Styles */
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-dialog-custom {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalFadeInCustom 0.2s ease-out;
        }
        .modal-dialog-custom.modal-dialog-lg {
            max-width: 680px;
        }
        @keyframes modalFadeInCustom {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header-custom {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title-wrap h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }
        .btn-close-modal {
            background: transparent;
            border: none;
            font-size: 22px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
        }
        .btn-close-modal:hover { color: #1e293b; }
        .modal-body-custom {
            padding: 20px;
        }
        .modal-footer-custom {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
    </style>

    @include('admin::shared.header', ['header_name' => __('stock_in.title')])
    <div class="content-wrapper stock-in-listing-wrapper" id="app" x-data="xStockInIndex">
        @php
            $stockInTabQuery = request()->except(['page']);
            $stockInTabUrl = function ($tabStatus) use ($routeName, $stockInTabQuery) {
                $url = route('admin-' . $routeName . '-list', $tabStatus);
                return empty($stockInTabQuery) ? $url : $url . '?' . http_build_query($stockInTabQuery);
            };
        @endphp
        @component('admin::components.listingData', [
            'routeName'        => $routeName,
            'createName'       => __('stock_in.button.create'),
            'createPermission' => 'stock-in-create',
            'filterStatus'     => false,
            'filterView'       => 'admin::pages.inventoryManagement.stockIn.filter',
            'filterData'       => [
                'shop'      => $shop,
                'supplier'  => $supplier,
                'status'    => $status,
                'routeName' => $routeName,
            ],
            'showSearch'       => true,
            'showSearchButton' => false,
            'showTabs'         => true,
            'tabs'             => [
                [
                    'label'  => __('stock_in.tab.all'),
                    'icon'   => 'bx bx-layer',
                    'url'    => $stockInTabUrl('all'),
                    'active' => $status === 'all' || $status === '1',
                ],
                [
                    'label'  => __('stock_in.tab.ordered'),
                    'icon'   => 'bx bx-time-five',
                    'url'    => $stockInTabUrl('ordered'),
                    'active' => $status === 'ordered',
                ],
                [
                    'label'  => __('stock_in.tab.partial_received'),
                    'icon'   => 'bx bx-pie-chart-alt-2',
                    'url'    => $stockInTabUrl('partial_received'),
                    'active' => $status === 'partial_received',
                ],
                [
                    'label'  => __('stock_in.tab.received'),
                    'icon'   => 'bx bx-check-circle',
                    'url'    => $stockInTabUrl('received'),
                    'active' => $status === 'received',
                ],
                [
                    'label'  => __('stock_in.tab.draft'),
                    'icon'   => 'bx bx-file-blank',
                    'url'    => $stockInTabUrl('draft'),
                    'active' => $status === 'draft',
                ],
                [
                    'label'  => __('stock_in.tab.trash'),
                    'icon'   => 'bx bx-trash',
                    'url'    => $stockInTabUrl('trash'),
                    'active' => $status === 'trash',
                ],
            ],
            'exportAction'     => 'excel()',
            'exportUrl'        => $exportUrl,
            'exportLabel'      => __('global.button.export'),
            'data'             => $data,
            'status'           => $status,
            'tbHeader'         => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'supplier_title', 'title' => __('stock_in.table.supplier'), 'class' => 'text left', 'colVal' => 17],
                ['field' => 'shop_title', 'title' => __('stock_in.table.shop'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'created_date', 'title' => __('stock_in.table.date'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'formatted_total_amount', 'title' => __('stock_in.table.total_amount'), 'class' => 'text right font-bold', 'colVal' => 10],
                ['field' => 'formatted_paid_amount', 'title' => __('stock_in.table.paid_amount'), 'class' => 'text right font-semibold text-emerald-600', 'colVal' => 10],
                ['field' => 'formatted_remaining_amount_styled', 'title' => __('stock_in.table.remaining_amount'), 'class' => 'text right', 'colVal' => 10],
                ['field' => 'order_status_badge', 'title' => __('stock_in.table.order_status'), 'class' => 'text center', 'colVal' => 11],
                ['field' => 'payment_status_badge', 'title' => __('stock_in.table.payment_status'), 'class' => 'text center', 'colVal' => 11],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                [
                                    'url' => 'view',
                                    'title' => __('global.action.view'),
                                    'icon' => 'visibility',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'edit',
                                    'title' => __('global.action.edit'),
                                    'icon' => 'edit',
                                    'type' => 'link',
                                    'visible' => ['can_edit' => true],
                                ],
                                [
                                    'url' => 'partial-receive',
                                    'title' => __('stock_in.action.partial_received') ?? 'Partial Received',
                                    'icon' => 'inventory_2',
                                    'type' => 'click',
                                    'handler' => 'openPartialReceiveModal',
                                    'class' => 'text-primary',
                                    'visible' => ['can_receive' => true],
                                ],
                                [
                                    'url' => 'full-receive',
                                    'title' => __('stock_in.action.fully_received') ?? 'Fully Received',
                                    'icon' => 'done_all',
                                    'type' => 'click',
                                    'handler' => 'markAsFullyReceived',
                                    'class' => 'text-success',
                                    'visible' => ['can_receive' => true],
                                ],
                                [
                                    'url' => 'paid',
                                    'title' => __('stock_in.action.mark_as_paid') ?? 'Payment Status (Paid)',
                                    'icon' => 'check_circle',
                                    'type' => 'click',
                                    'handler' => 'markAsPaid',
                                    'class' => 'text-success',
                                    'visible' => ['can_pay' => true],
                                ],
                                [
                                    'url' => 'print',
                                    'title' => __('stock_in.button.print') ?? 'Print PO',
                                    'icon' => 'print',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'delete',
                                    'title' => __('global.action.delete'),
                                    'icon' => 'delete',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                ['url' => 'restore', 'title' => __('global.action.restore'), 'icon' => 'restore'],
                                ['url' => 'destroy', 'title' => __('global.action.destroy'), 'icon' => 'delete_forever', 'class' => 'text-danger'],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent

        {{-- Partial Received Modal --}}
        <div class="modal-backdrop-custom" x-show="showPartialReceiveModal" x-transition.opacity style="display: none;" x-cloak>
            <div class="modal-dialog-custom modal-dialog-lg" @click.outside="if (!submittingPartialReceive) closePartialReceiveModal()">
                <form @submit.prevent="submitPartialReceive()">
                    <div class="modal-header-custom">
                        <div class="modal-title-wrap">
                            <h4 x-text="`{{ __('stock_in.action.partial_received') ?? 'Partial Received' }} - ${selectedPurchase?.purchase_number || (selectedPurchase?.reference_no || '#' + selectedPurchase?.id)}`"></h4>
                            <span class="modal-subtitle">{{ __('stock_in.receiving.subtitle') }}</span>
                        </div>
                        <button type="button" class="btn-close-modal" @click="closePartialReceiveModal()" :disabled="submittingPartialReceive">&times;</button>
                    </div>

                    <div class="modal-body-custom">
                        <div class="modal-meta-bar" style="display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 180px;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">{{ __('stock_in.receiving.received_date') }} <span style="color:#ef4444;">*</span></label>
                                <input type="text" id="partial_received_date" x-model="partialReceiveForm.received_date" class="form-control-custom datepicker-input" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; width: 100%; font-size: 12.5px;" required>
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">{{ __('stock_in.receiving.bill_of_lading') }}</label>
                                <input type="text" x-model="partialReceiveForm.reference_no" placeholder="e.g. Delivery Slip #" class="form-control-custom" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; width: 100%; font-size: 12.5px;">
                            </div>
                        </div>

                        <div class="modal-table-wrap" style="max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;">
                            <table class="modal-table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                                <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
                                    <tr style="border-bottom: 1px solid #e2e8f0; height: 38px;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #475569;">{{ __('stock_in.table.product') }}</th>
                                        <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #475569; width: 90px;">{{ __('stock_in.receiving.qty_ordered') }}</th>
                                        <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #475569; width: 90px;">{{ __('stock_in.receiving.qty_already') }}</th>
                                        <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #dc2626; width: 90px;">{{ __('stock_in.receiving.qty_balance') }}</th>
                                        <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #2563eb; width: 140px;">{{ __('stock_in.receiving.qty_to_receive') }} <span style="color:#ef4444;">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="partialReceiveLoading">
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 24px; color: #64748b;">
                                                <i class='bx bx-loader-alt bx-spin' style="font-size: 20px;"></i> Loading items...
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!partialReceiveLoading && partialReceiveRows.length === 0">
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 24px; color: #64748b;">
                                                All items for this order have already been received.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(row, idx) in partialReceiveRows" :key="row.id">
                                        <tr style="border-bottom: 1px solid #f1f5f9; height: 46px;">
                                            <td style="padding: 8px 12px;">
                                                <strong style="color: #1e293b;" x-text="row.name"></strong>
                                                <div style="font-size: 11.5px; color: #94a3b8;" x-text="row.uom"></div>
                                            </td>
                                            <td style="padding: 8px 12px; text-align: center; color: #64748b;" x-text="row.qty"></td>
                                            <td style="padding: 8px 12px; text-align: center; color: #10b981; font-weight: 600;" x-text="row.qty_received"></td>
                                            <td style="padding: 8px 12px; text-align: center; font-weight: 700; color: #dc2626;" x-text="row.remaining"></td>
                                            <td style="padding: 8px 12px; text-align: center;">
                                                <input type="number" step="any" min="0" :max="row.remaining"
                                                    x-model.number="row.qty_to_receive"
                                                    style="width: 100px; height: 34px; text-align: center; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600; font-size: 13px; color: #1e293b; outline: none;"
                                                    @focus="$event.target.select()">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <button type="button" @click="fillAllPartialRemaining()" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                <i class='bx bx-check-double'></i> {{ __('stock_in.button.receive_all') ?? 'Receive All' }}
                            </button>
                            <button type="button" @click="clearAllPartialReceive()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer;">
                                Clear All
                            </button>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">{{ __('stock_in.receiving.notes') }}</label>
                            <input type="text" x-model="partialReceiveForm.remark" placeholder="Partial delivery notes or batch remarks..." class="form-control-custom" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; width: 100%; font-size: 12.5px;">
                        </div>
                    </div>

                    <div class="modal-footer-custom">
                        <button type="button" class="btn-cancel-custom" @click="closePartialReceiveModal()" :disabled="submittingPartialReceive" style="padding: 8px 16px; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 7px; font-size: 12.5px; font-weight: 600; color: #475569; cursor: pointer;">
                            {{ __('global.button.cancel') }}
                        </button>
                        <button type="submit" class="btn-submit-custom" :disabled="submittingPartialReceive || partialReceiveRows.length === 0" style="padding: 8px 18px; border: none; background: #2563eb; color: #ffffff; border-radius: 7px; font-size: 12.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <i class='bx bx-check'></i>
                            <span x-text="submittingPartialReceive ? 'Receiving...' : @json(__('stock_in.receiving.confirm_receive'))"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Full Size Loading Spinner Overlay --}}
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('stock_in.export_excel') ?? __('global.button.export') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>
    <script>
        function registerStockInIndexAlpine() {
            Alpine.data('xStockInIndex', () => ({
                shopId: @json(request('shop_id') ?? ($shop?->id ?? '')),
                supplierId: @json(request('supplier_id') ?? ($supplier?->id ?? '')),
                exportLoading: false,
                selectedPurchase: null,
                showPartialReceiveModal: false,
                partialReceiveLoading: false,
                submittingPartialReceive: false,
                partialReceiveRows: [],
                partialReceiveForm: {
                    received_date: moment().format('YYYY-MM-DD'),
                    reference_no: '',
                    remark: ''
                },

                init() {
                    this.$nextTick(() => {
                        this.initSelectShop();
                        this.initSelectSupplier();
                        this.initDatepickers();
                    });
                },

                initSelectShop() {
                    const self = this;
                    const $select = $(this.$root).find('#stock_in_filter_shop_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_in.filter.select_shop')),
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) {
                                return { search: param.term };
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return {
                                            text: item?.name ? item.name : item?.phone,
                                            id: item.id
                                        };
                                    })
                                };
                            }
                        }
                    }).on('select2:open', function() {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        } else {
                            const searchField = document.querySelector('.select2-search__field');
                            if (searchField) searchField.focus();
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        self.shopId = $(this).val();
                    });

                    const selectedShop = @json($shop);
                    if (selectedShop?.id) {
                        if (typeof window.$select2Data === 'function') {
                            window.$select2Data($select, selectedShop.id, selectedShop.name || selectedShop.phone);
                        }
                        this.shopId = selectedShop.id;
                    }
                },

                resetShopFilter() {
                    const $select = $(this.$root).find('#stock_in_filter_shop_id');
                    $select.val(null).trigger('change');
                    this.shopId = '';
                },

                initSelectSupplier() {
                    const self = this;
                    const $select = $(this.$root).find('#stock_in_filter_supplier_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_in.filter.select_supplier')),
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-supplier') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) {
                                return { search: param.term };
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return {
                                            text: item.name,
                                            id: item.id
                                        };
                                    })
                                };
                            }
                        }
                    }).on('select2:open', function() {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        } else {
                            const searchField = document.querySelector('.select2-search__field');
                            if (searchField) searchField.focus();
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        self.supplierId = $(this).val();
                    });

                    const selectedSupplier = @json($supplier);
                    if (selectedSupplier?.id) {
                        if (typeof window.$select2Data === 'function') {
                            window.$select2Data($select, selectedSupplier.id, selectedSupplier.name);
                        }
                        this.supplierId = selectedSupplier.id;
                    }
                },

                resetSupplierFilter() {
                    const $select = $(this.$root).find('#stock_in_filter_supplier_id');
                    $select.val(null).trigger('change');
                    this.supplierId = '';
                },

                initDatepickers() {
                    const self = this;
                    const $from = $(this.$root).find('#filter_from_date');
                    const $to = $(this.$root).find('#filter_to_date');

                    if (!$from.length || !$to.length || typeof $.fn.datepicker !== 'function') return;

                    const options = {
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        gotoCurrent: true,
                        yearRange: '-50:+10',
                        beforeShow: function(input, inst) {
                            setTimeout(() => {
                                $('#ui-datepicker-div').addClass('stock-in-datepicker-popup');
                            }, 0);
                        },
                        onClose: function() {
                            $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                        }
                    };

                    if ($from.hasClass('hasDatepicker')) {
                        $from.datepicker('destroy');
                    }
                    if ($to.hasClass('hasDatepicker')) {
                        $to.datepicker('destroy');
                    }

                    $from.datepicker({
                        ...options,
                        onSelect: function(selected) {
                            $to.datepicker('option', 'minDate', selected);
                        }
                    }).on('change', function() {
                        $to.datepicker('option', 'minDate', $from.datepicker('getDate') || null);
                    });

                    $to.datepicker({
                        ...options,
                        onSelect: function(selected) {
                            $from.datepicker('option', 'maxDate', selected);
                        }
                    }).on('change', function() {
                        $from.datepicker('option', 'maxDate', $to.datepicker('getDate') || null);
                    });

                    if ($from.val()) {
                        $to.datepicker('option', 'minDate', $from.datepicker('getDate') || $from.val());
                    }
                    if ($to.val()) {
                        $from.datepicker('option', 'maxDate', $to.datepicker('getDate') || $to.val());
                    }
                },

                resetAdvancedFilters() {
                    $(this.$root).find('input[name="search"]').val('');
                    this.resetSupplierFilter();
                    this.resetShopFilter();
                    $(this.$root).find('select[name="payment_status"]').val('');
                    const $from = $(this.$root).find('#filter_from_date');
                    const $to = $(this.$root).find('#filter_to_date');
                    $from.val('').trigger('change');
                    $to.val('').trigger('change');
                    if ($from.length && $to.length && typeof $.fn.datepicker === 'function') {
                        $to.datepicker('option', 'minDate', null);
                        $from.datepicker('option', 'maxDate', null);
                    }
                    const hasAppliedAdvanced = @json(
                        request()->filled('search') ||
                        request()->filled('supplier_id') ||
                        request()->filled('shop_id') ||
                        request()->filled('payment_status') ||
                        request()->filled('from_date') ||
                        request()->filled('to_date')
                    );
                    if (hasAppliedAdvanced) {
                        const form = this.$el ? this.$el.closest('form.filter') : $(this.$root).find('form.filter')[0];
                        if (form) form.submit();
                    }
                },

                markAsFullyReceived(item) {
                    const poDisplay = item.purchase_number || (item.reference_no || `#${item.id}`);
                    const message = `Are you sure you want to mark Purchase Order "<b>${poDisplay}</b>" as Fully Received?<br><small style="color:#2563eb;font-weight:600;display:block;margin-top:6px;">All remaining ordered items will be added to warehouse stock.</small>`;
                    this.$store.confirmDialog.open({
                        data: {
                            message: message,
                            btnClose: `{{ __('global.cancel') }}`,
                            btnSave: `{{ __('stock_in.action.fully_received') ?? 'Fully Received' }}`,
                            item: item,
                            url: `{{ url('admin/stock-in/full-receive') }}/${item.id}`,
                            typeAction: 'full-receive',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            icon: 'bx bx-check-double',
                            iconStyle: 'font-size: 55px; margin-bottom: 15px; border: 1px solid rgba(37, 99, 235, 0.8); color: rgb(37 99 235); border-radius: 50%; padding: 4px;',
                            btnSaveClass: 'bg-primary',
                            width: "22rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },

                markAsPaid(item) {
                    const poDisplay = item.purchase_number || (item.reference_no || `#${item.id}`);
                    const remainingText = item.formatted_remaining_amount || ('$' + Number(item.remaining_amount || 0).toFixed(2));
                    const message = `Are you sure you want to mark Purchase Order "<b>${poDisplay}</b>" as Paid?<br><small style="color:#10b981;font-weight:600;display:block;margin-top:6px;">Remaining balance to pay: ${remainingText}</small>`;
                    this.$store.confirmDialog.open({
                        data: {
                            message: message,
                            btnClose: `{{ __('global.cancel') }}`,
                            btnSave: `{{ __('stock_in.action.mark_as_paid') ?? 'Payment Status (Paid)' }}`,
                            item: item,
                            url: `{{ url('admin/stock-in/update-payment-status') }}/${item.id}`,
                            postData: {
                                payment_status: 'paid',
                            },
                            typeAction: 'paid',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            icon: 'bx bx-check-circle',
                            iconStyle: 'font-size: 55px; margin-bottom: 15px; border: 1px solid rgba(16, 185, 129, 0.8); color: rgb(16 185 129); border-radius: 50%; padding: 4px;',
                            btnSaveClass: 'bg-success',
                            width: "22rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },

                openPartialReceiveModal(item) {
                    this.selectedPurchase = item;
                    this.partialReceiveForm.received_date = moment().format('YYYY-MM-DD');
                    this.partialReceiveForm.reference_no = '';
                    this.partialReceiveForm.remark = '';
                    this.partialReceiveRows = [];
                    this.showPartialReceiveModal = true;
                    this.partialReceiveLoading = true;

                    if (item.items && Array.isArray(item.items) && item.items.length > 0) {
                        this.populatePartialReceiveRows(item.items);
                        this.partialReceiveLoading = false;
                    } else {
                        fetch(`{{ url('admin/stock-in/items') }}/${item.id}`)
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === 200 && res.items) {
                                    this.populatePartialReceiveRows(res.items);
                                }
                            })
                            .catch(err => {
                                console.error(err);
                            })
                            .finally(() => {
                                this.partialReceiveLoading = false;
                            });
                    }

                    this.$nextTick(() => {
                        const $input = $(this.$root).find('#partial_received_date');
                        if ($input.length && typeof $.fn.datepicker === 'function') {
                            $input.datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                gotoCurrent: true,
                                yearRange: '-50:+10',
                                beforeShow: function() {
                                    setTimeout(() => { $('#ui-datepicker-div').addClass('stock-in-datepicker-popup'); }, 0);
                                },
                                onClose: function() {
                                    $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                                }
                            });
                        }
                    });
                },

                populatePartialReceiveRows(items) {
                    this.partialReceiveRows = items
                        .filter(it => {
                            const rem = Number(it.remaining !== undefined ? it.remaining : (it.remaining_qty !== undefined ? it.remaining_qty : (Number(it.qty) - Number(it.qty_received))));
                            return rem > 0.0001;
                        })
                        .map(it => {
                            const rem = Number(it.remaining !== undefined ? it.remaining : (it.remaining_qty !== undefined ? it.remaining_qty : (Number(it.qty) - Number(it.qty_received))));
                            return {
                                id: it.id,
                                name: it.product_name || (it.product ? it.product.name : '---'),
                                uom: it.uom_title || (it.product && it.product.uom ? it.product.uom.name : ''),
                                qty: Number(it.qty || 0),
                                qty_received: Number(it.qty_received || 0),
                                remaining: rem,
                                qty_to_receive: rem,
                            };
                        });
                },

                fillAllPartialRemaining() {
                    this.partialReceiveRows.forEach(r => {
                        r.qty_to_receive = r.remaining;
                    });
                },

                clearAllPartialReceive() {
                    this.partialReceiveRows.forEach(r => {
                        r.qty_to_receive = 0;
                    });
                },

                closePartialReceiveModal() {
                    this.showPartialReceiveModal = false;
                    this.selectedPurchase = null;
                    this.partialReceiveRows = [];
                },

                async submitPartialReceive() {
                    if (!this.selectedPurchase) return;
                    const itemsToSubmit = this.partialReceiveRows
                        .filter(r => Number(r.qty_to_receive) > 0)
                        .map(r => ({
                            purchase_item_id: r.id,
                            qty_to_receive: Number(r.qty_to_receive)
                        }));

                    if (itemsToSubmit.length === 0) {
                        alert('Please enter at least one quantity greater than 0 to receive.');
                        return;
                    }

                    this.submittingPartialReceive = true;

                    try {
                        const response = await fetch(`{{ url('admin/stock-in/receive') }}/${this.selectedPurchase.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                received_date: this.partialReceiveForm.received_date,
                                reference_no: this.partialReceiveForm.reference_no,
                                remark: this.partialReceiveForm.remark,
                                items: itemsToSubmit
                            })
                        });

                        const data = await response.json();

                        if (response.ok && (data.status === 200 || data.message === 'success')) {
                            this.closePartialReceiveModal();
                            reloadData(`{{ url()->full() }}`);
                        } else {
                            alert(data.error || data.message || 'Failed to record goods receipt.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An unexpected error occurred.');
                    } finally {
                        this.submittingPartialReceive = false;
                    }
                },

                verifyDelete(id, poNumber) {
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Are you sure you want to delete Purchase Order "${poNumber}"? Any received items will be reversed in warehouse stock.`,
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: `{{ __('action_button.delete') }}`,
                            item: { id: id },
                            urlName: `{{ $routeName }}`,
                            typeAction: 'delete',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "22rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },

                verifyRestore(id, poNumber) {
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Restore Purchase Order "${poNumber}"? Received inventory will be re-applied to stock.`,
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: `{{ __('global.action.restore') }}`,
                            item: { id: id },
                            urlName: `{{ $routeName }}`,
                            typeAction: 'restore',
                            digPosition: "posTop",
                            class: "restoreDialog",
                            width: "22rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },

                verifyDestroy(id, poNumber) {
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Permanently destroy Purchase Order "${poNumber}"? This action CANNOT be undone!`,
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: `{{ __('global.action.destroy') }}`,
                            item: { id: id },
                            urlName: `{{ $routeName }}`,
                            typeAction: 'destroy',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "22rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },

                @include('admin::pages.inventoryManagement.partials.excel-request')

                async excel() {
                    this.exportLoading = true;
                    try {
                        const data = await this.requestInventoryExport({
                            status: @json($status),
                            search: $(this.$root).find('input[name="search"]').val() || @json(request('search')),
                            shop_id: $(this.$root).find('select[name="shop_id"]').val() || @json(request('shop_id')),
                            supplier_id: $(this.$root).find('select[name="supplier_id"]').val() || @json(request('supplier_id')),
                            payment_status: $(this.$root).find('select[name="payment_status"]').val() || @json(request('payment_status')),
                            from_date: $(this.$root).find('input[name="from_date"]').val() || @json(request('from_date')),
                            to_date: $(this.$root).find('input[name="to_date"]').val() || @json(request('to_date')),
                        });
                        await this.runExcelExport(data);
                    } catch (error) {
                        this.showInventoryExportError(error);
                    } finally {
                        this.exportLoading = false;
                    }
                },

                async runExcelExport(datas) {
                    let workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet(@json(__('stock_in.excel.sheet_name') ?? 'Purchase Stock In'));
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
                    let lastColumn = dataColumn[dataColumn.length - 1];

                    const style_font_header = {
                        name: "Khmer OS Muol Light",
                        family: 4,
                        size: 16,
                        color: { argb: "538DD5" },
                    };
                    const style_border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                    const align_center = {
                        vertical: "middle",
                        horizontal: "center",
                        wrapText: true,
                    };
                    const align_left = {
                        vertical: "middle",
                        horizontal: "left",
                        wrapText: true,
                    };
                    const align_right = {
                        vertical: "middle",
                        horizontal: "right",
                        wrapText: true,
                    };

                    const titleTopRow = worksheet.addRow([@json(__('stock_in.excel.title') ?? 'PURCHASE STOCK IN REPORT')]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + "1");
                    worksheet.addRow([]);

                    const headers = [
                        @json(__('stock_in.table.no')),
                        @json(__('stock_in.table.po_number')),
                        @json(__('stock_in.table.reference_no')),
                        @json(__('stock_in.table.supplier')),
                        @json(__('stock_in.table.shop')),
                        @json(__('stock_in.table.date')),
                        @json(__('stock_in.table.total_amount')),
                        @json(__('stock_in.table.paid_amount')),
                        @json(__('stock_in.table.remaining_amount')),
                        @json(__('stock_in.table.order_status')),
                        @json(__('stock_in.table.payment_status')),
                        @json(__('stock_in.table.requested_by')),
                    ];

                    const headerRow = worksheet.addRow(headers);
                    headerRow.eachCell((cell) => {
                        cell.fill = {
                            type: "pattern",
                            pattern: "solid",
                            fgColor: { argb: "d4d4d4E5" },
                        };
                        cell.font = {
                            name: "Khmer Moul",
                            size: 11,
                            bold: true,
                        };
                        cell.border = style_border;
                        cell.alignment = align_center;
                    });

                    (datas || []).forEach((item, index) => {
                        const row = worksheet.addRow([
                            index + 1,
                            item.purchase_number || '---',
                            item.reference_no || '',
                            item.supplier_title || item.supplier?.name || '---',
                            item.shop_title || item.shop?.name || '---',
                            item.created_date || '',
                            Number(item.total_amount || 0),
                            Number(item.paid_amount || 0),
                            Number(item.remaining_amount || 0),
                            item.order_status_title || item.order_status || '---',
                            item.payment_status_title || item.payment_status || '---',
                            item.created_by_title || '---',
                        ]);

                        row.eachCell((cell, colNumber) => {
                            cell.border = style_border;
                            cell.font = { name: "Khmer OS System", size: 10 };
                            if ([2, 3, 4, 5, 12].includes(colNumber)) {
                                cell.alignment = align_left;
                            } else if ([7, 8, 9].includes(colNumber)) {
                                cell.alignment = align_right;
                                cell.numFmt = '$#,##0.00';
                            } else {
                                cell.alignment = align_center;
                            }
                        });
                    });

                    worksheet.columns = [
                        { width: 8 },
                        { width: 22 },
                        { width: 18 },
                        { width: 25 },
                        { width: 22 },
                        { width: 15 },
                        { width: 16 },
                        { width: 16 },
                        { width: 16 },
                        { width: 18 },
                        { width: 16 },
                        { width: 18 },
                    ];

                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    saveAs(blob, 'Purchase_Stock_In_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx');
                }
            }));
        }

        if (window.Alpine) {
            registerStockInIndexAlpine();
        } else {
            document.addEventListener('alpine:init', registerStockInIndexAlpine);
        }

        $(document).ready(function() {
            $(document).on('click', '.filter-date-input', function() {
                $(this).find('input').focus();
            });
        });
    </script>
@stop
