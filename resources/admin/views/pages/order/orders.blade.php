@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('order.title')])
    <style>
        .order-listing-wrapper .header .header-action-button .filter {
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row {
            height: 38px;
            border-radius: 25px !important;
            border: 1px solid rgba(152, 152, 152, 0.35) !important;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            padding: 0 12px;
            box-sizing: border-box;
            margin-left: 0;
            min-width: unset;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row input {
            height: 100%;
            width: 100%;
            border: none !important;
            background: transparent !important;
            font-size: 13px;
            color: #1e293b;
            padding: 0 4px;
            box-sizing: border-box;
            outline: none;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row input::placeholder {
            color: #8892a0 !important;
        }

        /* Ensure headers and tabs do not clip the popover */
        .order-listing-wrapper .header {
            z-index: 100 !important;
            overflow: visible !important;
        }
        .order-listing-wrapper .header-tab,
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
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 370px;
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
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.15s ease;
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

        /* Search submit button */
        .order-listing-wrapper .header .header-action-button .filter .btnSearch {
            height: 38px !important;
            width: 38px !important;
            border-radius: 50% !important;
            border: 1px solid rgba(152, 152, 152, 0.35) !important;
            background: #ffffff !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin-left: 0 !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .order-listing-wrapper .header .header-action-button .filter .btnSearch:hover {
            background: #f1f5f9 !important;
            border-color: #3b82f6 !important;
            color: #2563eb !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .btnSearch svg {
            width: 18px;
            height: 18px;
            color: #475569;
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
                    'label' => __('order.tab.paid'),
                    'icon' => 'bx bx-check-circle',
                    'url' => $orderTabUrl('Paid'),
                    'active' => $status === 'Paid',
                ],
                [
                    'label' => __('order.tab.partial'),
                    'icon' => 'bx bx-credit-card',
                    'url' => $orderTabUrl('Partial'),
                    'active' => $status === 'Partial',
                ],
                [
                    'label' => __('order.tab.rejected'),
                    'icon' => 'bx bx-x-circle',
                    'url' => $orderTabUrl('Rejected'),
                    'active' => $status === 'Rejected',
                ],
                [
                    'label' => __('order.tab.trash'),
                    'icon' => 'bx bx-trash-alt',
                    'url' => $orderTabUrl('trash'),
                    'active' => $status === 'trash',
                ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.excel'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'invoice_title', 'title' => __('order.table.order_id'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'shop_title', 'title' => __('order.table.shop'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'customer_title', 'title' => __('order.table.customer'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'order_items_title', 'title' => __('order.table.products'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'payment_status_title', 'title' => __('order.table.pay_status'), 'class' => 'text left', 'colVal' => 8],
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
                                    'url' => 'edit',
                                    'title' => __('order.action.payment'),
                                    'icon' => 'payments',
                                    'type' => 'link',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'edit',
                                    'title' => __('order.action.payment'),
                                    'icon' => 'payments',
                                    'type' => 'link',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Paid'],
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
                                [
                                    'url' => 'delete',
                                    'title' => __('global.action.delete'),
                                    'icon' => 'Delete',
                                    'class' => 'text-danger',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
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
        const registerOrderListingAlpine = () => {
            if (typeof Alpine === 'undefined') return;
            Alpine.data('xIndex', () => ({
                exportLoading: false,
                formData: {
                    status: @json($status),
                    payment_status: null,
                    shop_id: @json(request('shop_id')),
                    from_date: @json(request('from_date')),
                    to_date: @json(request('to_date')),
                    search: @json(request('search')),
                },
                init() {
                    const shop = @json($shop);

                    if (shop?.id) {
                        $select2Data('#shop_id', shop.id, shop.name || shop.phone);
                        this.formData.shop_id = shop.id;
                    }

                    this.$nextTick(() => {
                        initOrderDatepickers();
                    });
                },
                fetchSelectShop() {
                    const self = this;
                    const $select = $('#shop_id');
                    if (!$select.length) return;

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
                },
                resetShopField() {
                    $('#shop_id').val(null).trigger('change');
                    this.formData.shop_id = null;
                },
                resetAdvancedFilters() {
                    this.resetShopField();
                    $('#from_date').val('').trigger('change');
                    $('#to_date').val('').trigger('change');
                    if ($("#from_date").length && $("#to_date").length) {
                        $("#to_date").datepicker("option", "minDate", null);
                        $("#from_date").datepicker("option", "maxDate", null);
                    }
                    this.formData.from_date = '';
                    this.formData.to_date = '';
                    const hasAppliedAdvanced = @json(request()->filled('shop_id') || request()->filled('from_date') || request()->filled('to_date'));
                    if (hasAppliedAdvanced) {
                        const form = document.querySelector('form.filter');
                        if (form) form.submit();
                    }
                },
                fetchSelectBarber() {
                    $('#barber_id').select2({
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
                        document.querySelector('.select2-search__field').focus();
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
                            payment_status: this.orderStatusLabel(order.payment_status),
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
                orderStatusLabel(status) {
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
        };

        if (window.Alpine) {
            registerOrderListingAlpine();
        } else {
            document.addEventListener('alpine:init', registerOrderListingAlpine);
        }
    </script>
@stop
