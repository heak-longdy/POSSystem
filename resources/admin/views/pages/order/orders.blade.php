@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('order.title')])
    <style>
        /* Keep payment labels readable within the listing's horizontal scroll area. */
        .order-listing-wrapper .table-wrapper {
            min-width: 1280px;
        }
        .order-listing-wrapper .table-header > .row,
        .order-listing-wrapper .table-body > .column > .row {
            flex-shrink: 1;
        }
        .order-listing-wrapper .table .row.order-payment-status {
            flex: 0 0 180px;
        }
        .order-listing-wrapper .table-body .column .row.order-payment-status > span {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            padding-right: 20px;
            overflow: visible;
        }
        .order-listing-wrapper .table-body .column .row.order-payment-status .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 3px 10px;
            margin: 0 !important;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            line-height: 18px;
            white-space: nowrap;
            word-break: normal;
        }
        .order-listing-wrapper .table-body .column .row.order-payment-status .text-muted {
            display: block;
            font-size: 11px;
            line-height: 16px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .order-listing-wrapper .header .header-action-button .filter {
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .order-listing-wrapper .filter-search-input {
            height: 43px;
            width: 100%;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0 12px;
            box-sizing: border-box;
            font-size: 12.5px;
            font-weight: 500;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .order-listing-wrapper .filter-search-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }
        .order-listing-wrapper .filter-search-input::placeholder {
            color: #8892a0 !important;
        }

        /* Keep the toolbar's shared horizontal scrolling and scrollbar styles. */
        .order-listing-wrapper .header {
            z-index: 100 !important;
            overflow: visible !important;
        }
        .order-listing-wrapper .header-tab {
            min-width: 0;
            max-width: 100%;
        }
        .order-listing-wrapper .header-tab > *,
        .order-listing-wrapper .header-action-button > * {
            flex-shrink: 0;
        }
        .order-listing-wrapper .header-action-button,
        .order-listing-wrapper .header-action-button .filter {
            overflow: visible !important;
        }

        /* Advanced Filter Button */
        .order-listing-wrapper .advanced-filter-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            z-index: 50;
        }
        .order-listing-wrapper .btn-advanced-filter {
            height: 38px;
            border-radius: 20px;
            border: 1px solid rgba(152, 152, 152, 0.35);
            background: #ffffff;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 12px 0 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
        }
        .order-listing-wrapper .btn-advanced-filter:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #2563eb;
        }
        .order-listing-wrapper .btn-advanced-filter.active,
        .order-listing-wrapper .btn-advanced-filter.open {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #2563eb;
        }
        .order-listing-wrapper .btn-advanced-filter .advanced-filter-badge {
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            padding: 1px 6px;
            line-height: 1.2;
        }

        /* Advanced Filter Popover */
        .order-listing-wrapper .advanced-filter-popover {
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
        }
        .order-listing-wrapper .filter-popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .order-listing-wrapper .filter-popover-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }
        .order-listing-wrapper .filter-popover-title i {
            font-size: 16px;
            color: #2563eb;
        }
        .order-listing-wrapper .filter-popover-count {
            font-size: 11px;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 7px;
            border-radius: 10px;
        }
        .order-listing-wrapper .filter-popover-close {
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
            padding: 4px !important;
            min-width: auto !important;
            width: 37px !important;
            height: 35px !important;
        }
        .order-listing-wrapper .filter-popover-close i.bx{
            margin-right:0 !important;
        }
        .order-listing-wrapper .filter-popover-close:hover {
            background: #f1f5f9;
            color: #475569;
        }
        .order-listing-wrapper .filter-field-group {
            margin-bottom: 14px;
        }
        .order-listing-wrapper .filter-field-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .order-listing-wrapper .filter-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        /* Filter Popover Date Input */
        .order-listing-wrapper .filter-date-input {
            position: relative !important;
            cursor: pointer;
            width: 100%;
        }
        .order-listing-wrapper .filter-date-input input.filter-input {
            width: 100% !important;
            height: 43px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 7px !important;
            padding: 0 34px 0 12px !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            outline: none !important;
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .order-listing-wrapper .filter-date-input input.filter-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }
        .order-listing-wrapper .filter-date-input i.bx-calendar {
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
        .order-listing-wrapper .filter-popover-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }
        .order-listing-wrapper .btn-popover-reset {
            height: 38px;
            padding: 0 14px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .order-listing-wrapper .btn-popover-reset:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .order-listing-wrapper .btn-popover-apply {
            height: 38px;
            padding: 0 16px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .order-listing-wrapper .btn-popover-apply:hover {
            background: #1d4ed8;
        }

        /* Datepicker stacking */
        div#ui-datepicker-div,
        #ui-datepicker-div.order-listing-datepicker {
            z-index: 9999 !important;
        }

        /* Reset / Clear button */
        .btn-clear-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
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

        /* Add Payment Modal Styles */
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
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalFadeInCustom 0.2s ease-out;
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
        .modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .modal-title-wrap svg,
        .modal-title-wrap i {
            width: 22px;
            height: 22px;
            color: #00b74a;
        }
        .modal-title-wrap h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-subtitle-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .btn-close-modal {
            background: none;
            border: none;
            font-size: 24px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
        }
        .btn-close-modal:hover {
            color: #334155;
        }
        .modal-body-custom {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .remaining-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remaining-info-box .info-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
        }
        .remaining-info-box .info-val {
            font-size: 16px;
            font-weight: 700;
            color: #dc2626;
        }
        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group-modal label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .modal-input, .modal-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .modal-input:focus, .modal-textarea:focus {
            border-color: #3C91E6;
            box-shadow: 0 0 0 3px rgba(60, 145, 230, 0.15);
        }
        .input-action-wrap {
            display: flex;
            gap: 8px;
        }
        .btn-fill-max {
            background: #ffffff;
            border: 1px solid rgba(152, 152, 152, 0.25);
            border-radius: 20px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 500;
            color: #333333;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .btn-fill-max:hover {
            background: #f5f5f5;
            color: #111111;
        }
        .payment-method-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .method-option {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            transition: all 0.15s;
            margin: 0;
            background: #ffffff;
        }
        .method-option input {
            display: none;
        }
        .method-option svg,
        .method-option i {
            width: 16px;
            height: 16px;
        }
        .method-option.is-active {
            border-color: #00c753 !important;
            background: #ecfdf5 !important;
            color: #00c753 !important;
        }
        .method-option.is-active svg,
        .method-option.is-active i {
            color: #00c753 !important;
        }
        .modal-footer-custom {
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
        }
        .btn-system {
            height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            grid-gap: 7px !important;
            gap: 7px !important;
            padding: 0 16px !important;
            border-radius: 20px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            text-transform: none !important;
            border: unset !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            transition: all 0.2s ease !important;
            line-height: 1 !important;
        }
        .btn-system svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 2 !important;
            line-height: 0 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        .btn-system span {
            font-size: 13px !important;
            line-height: normal !important;
        }
        .btn-system-success {
            background-color: #00b74a !important;
            color: #ffffff !important;
            border: unset !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.1) !important;
        }
        .btn-system-success:hover:not(:disabled) {
            background-color: #009e40 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 183, 74, 0.3) !important;
        }
        .btn-system-outline,
        .btn-system-neutral {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid rgba(152, 152, 152, 0.25) !important;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.05) !important;
        }
        .btn-system-outline:hover:not(:disabled),
        .btn-system-neutral:hover:not(:disabled) {
            background-color: #f5f5f5 !important;
            border-color: rgba(152, 152, 152, 0.45) !important;
            color: #111111 !important;
        }
    </style>
    <div class="content-wrapper order-listing-wrapper" id="app" x-data="xIndex">
        @php
            $orderTabQuery = request()->except(['page', 'payment_status']);
            $orderTabUrl = function ($tabStatus) use ($orderTabQuery) {
                $url = route('admin-' . ($routeName ?? 'order') . '-list', $tabStatus);

                return empty($orderTabQuery) ? $url : $url . '?' . http_build_query($orderTabQuery);
            };
        @endphp
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('order.button.create'),
            'createPermission' => 'order-create',
            'filterStatus' => false,
            'filterView' => 'admin::pages.order.filter',
            'filterData' => [
                'shop' => $shop,
                'status' => $status,
                'routeName' => $routeName,
                'firstMonthDay' => $firstMonthDay,
                'lastMonthDay' => $lastMonthDay,
            ],
            'showSearch' => true,
            'showSearchButton' => false,
            'showTabs' => true,
            'tabs' => [
                [
                    'label' => __('order.tab.all_outstanding'),
                    'icon' => 'bx bx-list-ul',
                    'url' => $orderTabUrl('all'),
                    'active' => $status === 'all',
                ],
                [
                    'label' => __('order.tab.pending'),
                    'icon' => 'bx bx-time-five',
                    'url' => $orderTabUrl('Pending'),
                    'active' => $status === 'Pending',
                ],
                [
                    'label' => __('order.tab.partial'),
                    'icon' => 'bx bx-credit-card',
                    'url' => $orderTabUrl('Partial'),
                    'active' => $status === 'Partial',
                ],
                [
                    'label' => __('order.tab.paid'),
                    'icon' => 'bx bx-check-circle',
                    'url' => $orderTabUrl('Paid'),
                    'active' => $status === 'Paid',
                ],
                [
                    'label' => __('order.tab.rejected'),
                    'icon' => 'bx bx-x-circle',
                    'url' => $orderTabUrl('Rejected'),
                    'active' => $status === 'Rejected',
                ],
                // [
                //     'label' => __('order.tab.trash'),
                //     'icon' => 'bx bx-trash-alt',
                //     'url' => $orderTabUrl('trash'),
                //     'active' => $status === 'trash',
                // ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.export'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'invoice_title', 'title' => __('order.table.order_id'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'shop_title', 'title' => __('order.table.shop'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'customer_title', 'title' => __('order.table.customer'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'order_items_title', 'title' => __('order.table.products'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'payment_status_title', 'title' => __('order.table.pay_status'), 'class' => 'text left order-payment-status', 'colVal' => 8],
                ['field' => 'total_price_title', 'title' => __('order.table.total'), 'class' => 'text left', 'colVal' => 7],
                ['field' => 'paid_amount_title', 'title' => __('order.table.paid'), 'class' => 'text left', 'colVal' => 6],
                ['field' => 'remaining_amount_title', 'title' => __('order.table.remaining'), 'class' => 'text left', 'colVal' => 6],
                ['field' => 'total_discount_title', 'title' => __('order.table.discount'), 'class' => 'text left', 'colVal' => 6],
                ['field' => 'order_date_title', 'title' => __('order.table.order_date'), 'class' => 'text left', 'colVal' => 10],
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
                                    'url' => 'detail',
                                    'title' => __('order.action.view_detail'),
                                    'icon' => 'visibility',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'edit',
                                    'title' => __('global.action.edit'),
                                    'icon' => 'edit',
                                    'type' => 'link',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
                                [
                                    'url' => 'paid',
                                    'title' => __('order.action.paid'),
                                    'icon' => 'check_circle',
                                    'type' => 'click',
                                    'handler' => 'markAsPaid',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => ['Pending', 'Partial']],
                                ],
                                [
                                    'url' => 'add-payment',
                                    'title' => __('order.action.add_payment'),
                                    'icon' => 'add_circle',
                                    'type' => 'click',
                                    'handler' => 'openAddPaymentModal',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => ['Pending', 'Partial']],
                                ],
                                [
                                    'url' => 'cancel',
                                    'title' => __('order.action.reject'),
                                    'icon' => 'cancel',
                                    'type' => 'click',
                                    'handler' => 'cancelOrder',
                                    'value' => 'Cancel',
                                    'class' => 'text-danger',
                                    'visible' => ['can_reject' => true],
                                ],
                                // [
                                //     'url' => 'delete',
                                //     'title' => __('global.action.delete'),
                                //     'icon' => 'Delete',
                                //     'class' => 'text-danger',
                                //     'visible' => ['payment_status' => 'Pending'],
                                // ],
                            ],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                [
                                    'url' => 'detail',
                                    'title' => __('order.action.view_detail'),
                                    'icon' => 'visibility',
                                    'type' => 'link',
                                ],
                                ['url' => 'restore', 'title' => __('global.action.restore'), 'icon' => 'settings_backup_restore'],
                                ['url' => 'destroy', 'title' => __('global.action.destroy'), 'icon' => 'Delete', 'class' => 'text-danger'],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent

        <!-- Dedicated Add Payment Modal (Similar to Order Detail) -->
        <div class="modal-backdrop-custom" x-show="showAddPaymentModal" x-transition.opacity style="display: none;">
            <div class="modal-dialog-custom" @click.away="if (!addPaymentSubmitting) closeAddPaymentModal()">
                <div class="modal-header-custom">
                    <div class="modal-title-wrap">
                        <i data-feather="plus-circle" class="text-success"></i>
                        <div>
                            <h3>{{ __('order.detail.add_payment') }}</h3>
                            <div class="modal-subtitle-text"
                                x-text="getAddPaymentSubtitle()">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close-modal" :disabled="addPaymentSubmitting" @click="closeAddPaymentModal()">
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitAddPayment()">
                    <fieldset class="modal-body-custom" :disabled="addPaymentSubmitting" style="border: 0; margin: 0; min-width: 0;">
                        <div class="remaining-info-box">
                            <span class="info-label">{{ __('remaining_amount.ledger.remaining_balance') }}</span>
                            <strong class="info-val text-danger" x-text="formatCurrency(addPaymentOrder?.remaining_amount)"></strong>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.amount') }} ($) <span class="text-danger">*</span></label>
                            <div class="input-action-wrap">
                                <input type="number" step="0.01" min="0.01" :max="addPaymentOrder?.remaining_amount"
                                    class="modal-input" x-model="addPaymentForm.amount" required placeholder="0.00">
                                <button type="button" class="btn-fill-max" @click="addPaymentForm.amount = Number(addPaymentOrder?.remaining_amount || 0)">
                                    {{ __('order.button.full_balance') }}
                                </button>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.detail.method') }}</label>
                            <div class="payment-method-selector">
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Cash' ? 'is-active' : ''">
                                    <input type="radio" value="Cash" x-model="addPaymentForm.payment_method">
                                    <i data-feather="dollar-sign"></i>
                                    <span>{{ __('order.payment.cash') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'ABA' ? 'is-active' : ''">
                                    <input type="radio" value="ABA" x-model="addPaymentForm.payment_method">
                                    <i data-feather="credit-card"></i>
                                    <span>{{ __('order.payment.aba') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'Card' ? 'is-active' : ''">
                                    <input type="radio" value="Card" x-model="addPaymentForm.payment_method">
                                    <i data-feather="server"></i>
                                    <span>{{ __('order.payment.card') }}</span>
                                </label>
                                <label class="method-option" :class="addPaymentForm.payment_method === 'QR' ? 'is-active' : ''">
                                    <input type="radio" value="QR" x-model="addPaymentForm.payment_method">
                                    <i data-feather="maximize"></i>
                                    <span>{{ __('order.payment.qr') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.table.pay_date') }}</label>
                            <input type="datetime-local" class="modal-input" x-model="addPaymentForm.payment_date">
                        </div>

                        <div class="form-group-modal">
                            <label>{{ __('order.note') }}</label>
                            <textarea class="modal-textarea" rows="3" x-model="addPaymentForm.note"
                                placeholder="{{ __('order.placeholder.note') }}"></textarea>
                        </div>
                    </fieldset>

                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-system btn-system-outline btn-system-neutral" :disabled="addPaymentSubmitting" @click="closeAddPaymentModal()">
                            <span>{{ __('order.button.close') }}</span>
                        </button>
                        <button type="submit" class="btn btn-create bg-success btn-system btn-system-success" :disabled="addPaymentSubmitting">
                            <span class="bx bx-loader-alt bx-spin" x-show="addPaymentSubmitting" style="display: none;" aria-hidden="true"></span>
                            <span x-show="!addPaymentSubmitting">{{ __('order.detail.add_payment') }}</span>
                            <span x-show="addPaymentSubmitting" style="display: none;">{{ __('order.button.processing_payment') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('order.export_excel') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>
    <script>
        function initOrderDatepickers() {
            const options = {
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+10",
                beforeShow: function(input, instance) {
                    instance.dpDiv.addClass('order-listing-datepicker');
                },
                onClose: function(dateText, instance) {
                    instance.dpDiv.removeClass('order-listing-datepicker');
                }
            };

            const $from = $("#from_date");
            const $to = $("#to_date");

            if ($from.length && $to.length) {
                if ($from.hasClass('hasDatepicker')) {
                    $from.datepicker('destroy');
                }
                if ($to.hasClass('hasDatepicker')) {
                    $to.datepicker('destroy');
                }

                $from.datepicker({
                    ...options,
                    onSelect: function(selected) {
                        $to.datepicker("option", "minDate", selected);
                    }
                }).on('change', function() {
                    $to.datepicker("option", "minDate", $from.datepicker('getDate') || null);
                });

                $to.datepicker({
                    ...options,
                    onSelect: function(selected) {
                        $from.datepicker("option", "maxDate", selected);
                    }
                }).on('change', function() {
                    $from.datepicker("option", "maxDate", $to.datepicker('getDate') || null);
                });

                if ($from.val()) {
                    $to.datepicker("option", "minDate", $from.datepicker('getDate'));
                }
                if ($to.val()) {
                    $from.datepicker("option", "maxDate", $to.datepicker('getDate'));
                }
            }
        }

        $(document).ready(function() {
            initOrderDatepickers();

            $(document).on('click', '.filter-date-input', function() {
                $(this).find('input').focus();
            });
        });
    </script>
    <script>
        function registerOrderListingAlpine() {
            if (typeof Alpine === 'undefined') return;
            Alpine.data('xIndex', () => ({
                exportLoading: false,
                showAddPaymentModal: false,
                addPaymentOrder: null,
                addPaymentSubmitting: false,
                addPaymentForm: {
                    amount: null,
                    payment_method: 'Cash',
                    payment_date: '',
                    note: '',
                },
                walkInCustomerText: @json(__('order.walk_in_customer')),
                formData: {
                    status: @json($status),
                    payment_status: null,
                    shop_id: @json(request('shop_id')),
                    from_date: @json(request('from_date')),
                    to_date: @json(request('to_date')),
                    search: @json(request('search')),
                },
                init() {
                    this.$nextTick(() => {
                        this.fetchSelectShop();
                        initOrderDatepickers();
                        if (window.feather) {
                            feather.replace();
                        }
                    });
                },
                fetchSelectShop() {
                    const self = this;
                    const $select = $(this.$root).find('select[name="shop_id"]');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: '{{ __('order.select_shop') }}',
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : '',
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        if (typeof window.$select2FocusInputSearch === 'function') {
                            window.$select2FocusInputSearch();
                        } else {
                            const searchField = document.querySelector('.select2-search__field');
                            if (searchField) searchField.focus();
                        }
                    }).on('change select2:select select2:clear select2:unselect', function () {
                        self.formData.shop_id = $(this).val();
                    });

                    const shop = @json($shop);
                    if (shop?.id) {
                        $select2Data($select, shop.id, shop.name || shop.phone);
                        this.formData.shop_id = shop.id;
                    }
                },
                resetShopField() {
                    $(this.$root).find('select[name="shop_id"]').val(null).trigger('change');
                    this.formData.shop_id = null;
                },
                resetAdvancedFilters() {
                    $(this.$root).find('input[name="search"]').val('');
                    this.formData.search = '';
                    this.resetShopField();
                    $('#from_date').val('').trigger('change');
                    $('#to_date').val('').trigger('change');
                    if ($("#from_date").length && $("#to_date").length) {
                        $("#to_date").datepicker("option", "minDate", null);
                        $("#from_date").datepicker("option", "maxDate", null);
                    }
                    this.formData.from_date = '';
                    this.formData.to_date = '';
                    const hasAppliedAdvanced = @json(request()->filled('search') || request()->filled('shop_id') || request()->filled('from_date') || request()->filled('to_date'));
                    if (hasAppliedAdvanced) {
                        const form = this.$el.closest('form.filter');
                        if (form) form.submit();
                    }
                },
                fetchSelectBarber() {
                    $(this.$root).find('#barber_id').select2({
                        placeholder: '{{ __('order.select_barber') }}',
                        ajax: {
                            url: '{{ route('admin-select-barber') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: (param) => ({ search: param.term }),
                            processResults: (data) => ({
                                results: $.map(data.data, (item) => ({
                                    text: item?.name ? item.name : '',
                                    id: item.id
                                }))
                            })
                        }
                    }).on('select2:open', () => {
                        window.$select2FocusInputSearch();
                    });
                },
                verifyDialog(data, typeAction, btn) {
                    const confirmTemplate = "{{ __('order.confirm.action', ['action' => '__ACTION__']) }}";
                    this.$store.confirmDialog.open({
                        data: {
                            message: confirmTemplate.replace('__ACTION__', btn),
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: btn,
                            item: data,
                            urlName: `{{ $routeName }}`,
                            typeAction: typeAction,
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "18rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },
                markAsPaid(item) {
                    const confirmTemplate = "{{ __('order.confirm.mark_as_paid', ['invoice' => '__INVOICE__']) }}";
                    let message = confirmTemplate.replace('__INVOICE__', '<b>' + (item?.invoice_title || '') + '</b>');
                    if (item?.payment_status === 'Partial' && item?.remaining_amount_title) {
                        message += `<br><small style="color:#10b981;font-weight:600;display:block;margin-top:6px;">{{ __('order.table.remaining') }}: ${item.remaining_amount_title}</small>`;
                    }
                    this.$store.confirmDialog.open({
                        data: {
                            message: message,
                            btnClose: `{{ __('global.cancel') }}`,
                            btnSave: '{{ __('order.action.paid') }}',
                            item: item,
                            url: `{{ url('admin/order/update-payment-status') }}/${item.id}`,
                            postData: {
                                payment_status: 'Paid',
                            },
                            typeAction: 'paid',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            icon: 'bx bx-check-circle',
                            iconStyle: 'font-size: 55px; margin-bottom: 15px; border: 1px solid rgba(16, 185, 129, 0.8); color: rgb(16 185 129); border-radius: 50%; padding: 4px;',
                            btnSaveClass: 'bg-success',
                            width: "20rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },
                cancelOrder(item) {
                    const rejectTemplate = "{{ __('order.confirm.reject', ['invoice' => '__INVOICE__']) }}";
                    this.$store.confirmDialog.open({
                        data: {
                            message: rejectTemplate.replace('__INVOICE__', '<b>' + (item?.invoice_title || '') + '</b>'),
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: '{{ __('order.action.reject_order') }}',
                            btnSaveClass: 'bg-danger',
                            item: item,
                            url: `{{ url('admin/order/cancel') }}/${item.id}`,
                            typeAction: 'cancel',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "20rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                reloadData(`{{ url()->full() }}`);
                            }
                        }
                    });
                },
                getAddPaymentSubtitle() {
                    const invoice = this.addPaymentOrder?.invoice_number || this.addPaymentOrder?.invoice_title || '';
                    const customer = this.addPaymentOrder?.customer_name
                        || (this.addPaymentOrder?.customer ? (this.addPaymentOrder.customer.name || this.addPaymentOrder.customer.phone) : '')
                        || this.walkInCustomerText;
                    return `${invoice} • ${customer}`;
                },
                openAddPaymentModal(item) {
                    this.addPaymentOrder = item;
                    this.showAddPaymentModal = true;
                    this.addPaymentSubmitting = false;
                    const initialAmount = (item && Number(item.remaining_amount) > 0)
                        ? Number(item.remaining_amount)
                        : null;
                    this.addPaymentForm = {
                        amount: initialAmount,
                        payment_method: 'Cash',
                        payment_date: moment().format('YYYY-MM-DDTHH:mm'),
                        note: '',
                    };

                    this.$nextTick(() => {
                        if (window.feather) {
                            feather.replace();
                        }
                    });

                    if (item?.id) {
                        Axios.get(`{{ url('admin/order/payment-details') }}/${item.id}`)
                            .then((res) => {
                                if (res.data) {
                                    this.addPaymentOrder = res.data;
                                    if (!this.addPaymentForm.amount && res.data.remaining_amount) {
                                        this.addPaymentForm.amount = Number(res.data.remaining_amount);
                                    }
                                }
                            })
                            .catch((err) => {
                                console.error('Error fetching order details for Add Payment:', err);
                            });
                    }
                },
                closeAddPaymentModal() {
                    this.showAddPaymentModal = false;
                    this.addPaymentOrder = null;
                    this.addPaymentSubmitting = false;
                },
                async submitAddPayment() {
                    if (this.addPaymentSubmitting || !this.addPaymentOrder?.id) return;
                    if (!this.addPaymentForm.amount || Number(this.addPaymentForm.amount) <= 0) {
                        alert(@json(__('order.validation.payment_amount_required') ?: 'Please enter a valid payment amount.'));
                        return;
                    }
                    if (Number(this.addPaymentForm.amount) > Number(this.addPaymentOrder.remaining_amount || 0)) {
                        alert(@json(__('order.validation.payment_amount_exceeds') ?: 'Payment amount exceeds available balance.'));
                        return;
                    }

                    this.addPaymentSubmitting = true;
                    const url = `{{ url('admin/order/add-payment') }}/${this.addPaymentOrder.id}`;

                    try {
                        const response = await Axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            amount: this.addPaymentForm.amount,
                            payment_method: this.addPaymentForm.payment_method,
                            payment_date: this.addPaymentForm.payment_date,
                            note: this.addPaymentForm.note,
                        });

                        if (response.data && (response.data.message === 'success' || response.status === 200)) {
                            if (window.iziToast) {
                                iziToast.success({
                                    title: 'Success',
                                    message: @json(__('order.message.payment_status_success')),
                                    position: 'topRight'
                                });
                            } else if (window.toastr) {
                                toastr.success(@json(__('order.message.payment_status_success')));
                            }
                            this.closeAddPaymentModal();
                            reloadData(`{{ url()->full() }}`);
                        } else {
                            alert(response.data?.error || @json(__('order.message.payment_status_error') ?: 'Payment recording failed.'));
                            this.addPaymentSubmitting = false;
                        }
                    } catch (error) {
                        this.addPaymentSubmitting = false;
                        const errorMsg = error.response?.data?.error ||
                            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                            @json(__('order.message.payment_status_error') ?: 'Payment recording failed.');
                        alert(errorMsg);
                    }
                },
                formatCurrency(val) {
                    return '$' + (Number(val || 0)).toFixed(2);
                },
                async excel() {
                    this.exportLoading = true;
                    await Axios.get(`{{ route('admin-' . ($routeName ?? 'order') . '-report') }}`, {
                        params: this.formData,
                    }).then((response) => {
                        this.runExcelExport(response.data);
                    }).finally(() => {
                        this.exportLoading = false;
                    });
                },
                runExcelExport(rows) {
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet(@json(__('order.excel_report')));
                    worksheet.columns = [
                        { header: @json(__('order.table.order_id')), key: 'order_id', width: 16 },
                        { header: @json(__('order.table.order_date')), key: 'order_date', width: 18 },
                        { header: @json(__('order.table.shop')), key: 'shop', width: 22 },
                        { header: @json(__('order.table.barber')), key: 'barber', width: 22 },
                        { header: @json(__('order.table.customer_phone')), key: 'customer_phone', width: 18 },
                        { header: @json(__('order.table.item')), key: 'item', width: 28 },
                        { header: @json(__('order.table.type')), key: 'type', width: 12 },
                        { header: @json(__('order.table.qty')), key: 'qty', width: 8 },
                        { header: @json(__('order.table.price')), key: 'price', width: 14 },
                        { header: @json(__('order.table.discount')), key: 'discount', width: 14 },
                        { header: @json(__('order.table.commission')), key: 'commission', width: 14 },
                        { header: @json(__('order.table.pay_status')), key: 'payment_status', width: 14 },
                        { header: @json(__('order.table.paid')), key: 'paid_amount', width: 14 },
                        { header: @json(__('order.table.remaining')), key: 'remaining_amount', width: 18 },
                        { header: @json(__('order.table.pay_date')), key: 'payment_date', width: 18 },
                    ];

                    rows.forEach((item) => {
                        const order = item?.order || {};
                        worksheet.addRow({
                            order_id: order.invoice_number || item?.order_id || '',
                            order_date: this.dateFormat(order.order_date),
                            shop: order.shop?.name || '',
                            barber: order.barber?.name || '',
                            customer_phone: order.customer?.phone || '',
                            item: item?.product?.name || item?.service?.name || '',
                            type: item?.type || '',
                            qty: item?.qty || 1,
                            price: item?.price || 0,
                            discount: this.discountAmount(item),
                            commission: this.commissionAmount(item),
                            payment_status: this.orderStatusLabel(order.payment_status, Boolean(order.is_transferred)),
                            paid_amount: order.paid_amount || 0,
                            remaining_amount: order.remaining_amount || 0,
                            payment_date: this.dateFormat(order.payment_date),
                        });
                    });

                    worksheet.getRow(1).font = { bold: true };
                    workbook.xlsx.writeBuffer().then((data) => {
                        const blob = new Blob([data], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        saveAs(blob, 'Order_Report_' + moment().format('YYYY_MM_DD_HHmmss'));
                    });
                },
                dateFormat(date) {
                    return date ? moment(date).format('YYYY-MM-DD HH:mm') : '';
                },
                orderStatusLabel(status, isTransferred = false) {
                    if (isTransferred) return @json(__('order.status.paid_through_transfer'));
                    if (status === 'Paid') return @json(__('order.status.paid'));
                    if (status === 'Partial') return @json(__('order.status.partial'));
                    if (status === 'Cancel') return @json(__('order.status.rejected'));
                    return @json(__('order.status.pending'));
                },
                discountAmount(item) {
                    const discount = item?.type === 'product' ? item?.product_discount : item?.service_discount;
                    const type = item?.type === 'product' ? item?.product_discount_type : item?.service_discount_type;
                    return this.amountByType(item?.price || 0, discount || 0, type);
                },
                commissionAmount(item) {
                    const commission = item?.type === 'product' ? item?.product_commission : item?.service_commission;
                    const type = item?.type === 'product' ? item?.product_commission_type : item?.service_commission_type;
                    return this.amountByType(item?.price || 0, commission || 0, type);
                },
                amountByType(price, value, type) {
                    if (type === 'percent') {
                        return price * value / 100;
                    }

                    return value;
                },
            }));
        }

        if (window.Alpine) {
            registerOrderListingAlpine();
        } else {
            document.addEventListener('alpine:init', registerOrderListingAlpine);
        }
    </script>
@stop
