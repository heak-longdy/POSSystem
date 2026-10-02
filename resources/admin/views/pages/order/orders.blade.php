@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('order.title')])
    <style>
        .order-listing-wrapper .header .header-action-button .filter {
            display: flex;
            align-items: center;
            gap: 6px;
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
        div#ui-datepicker-div {
            z-index: 9999 !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row.date-input {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            padding: 0 34px 0 14px !important;
            cursor: pointer;
            box-sizing: border-box;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row.date-input input {
            cursor: pointer;
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .form-row.date-input i,
        .order-listing-wrapper .header .header-action-button .filter .form-row.date-input i.bx,
        .order-listing-wrapper .header .header-action-button .filter .form-row.date-input i.bx-calendar {
            position: absolute !important;
            right: 12px !important;
            top: 50% !important;
            bottom: auto !important;
            left: auto !important;
            transform: translateY(-50%) !important;
            font-size: 18px !important;
            color: #64748b !important;
            margin: 0 !important;
            pointer-events: none !important;
            line-height: 1 !important;
            display: block !important;
        }

        /* Seamless Select2 styling inside pill form-row */
        .order-listing-wrapper .header .header-action-button .filter .form-row.shop-select {
            padding: 0 8px 0 12px;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2.select2-container {
            width: 100% !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2-selection--single {
            border: none !important;
            background: transparent !important;
            height: 36px !important;
            display: flex !important;
            align-items: center !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 13px !important;
            line-height: 36px !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2-selection__placeholder {
            color: #8892a0 !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2-selection__arrow {
            height: 36px !important;
            top: 0 !important;
            right: 4px !important;
        }
        .order-listing-wrapper .header .header-action-button .filter .shop-select .select2-selection__arrow b {
            border-color: #64748b transparent transparent transparent !important;
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
            if ($("#from_date").length) {
                $("#from_date").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+10",
                    onSelect: function(selected) {
                        $("#to_date").datepicker("option", "minDate", selected);
                    }
                });
            }
            if ($("#to_date").length) {
                $("#to_date").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+10",
                    onSelect: function(selected) {
                        $("#from_date").datepicker("option", "maxDate", selected);
                    }
                });
            }
            if ($("#from_date").val()) {
                $("#to_date").datepicker("option", "minDate", $("#from_date").val());
            }
            if ($("#to_date").val()) {
                $("#from_date").datepicker("option", "maxDate", $("#to_date").val());
            }
        }

        $(document).ready(function() {
            initOrderDatepickers();

            $(document).on('click', '.form-row.date-input i', function(e) {
                e.stopPropagation();
                $(this).siblings('input').focus();
            });
            $(document).on('click', '.form-row.date-input', function() {
                $(this).find('input').focus();
            });
        });
    </script>
    <script lang="ts">
        document.addEventListener('alpine:init', () => {
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
                    }

                    this.$nextTick(() => {
                        initOrderDatepickers();
                    });
                },
                fetchSelectShop() {
                    $('#shop_id').select2({
                        placeholder: '{{ __('order.select_shop') }}',
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
                        document.querySelector('.select2-search__field').focus();
                    });
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
                            item: item,
                            url: `{{ url('admin/order/cancel') }}/${item.id}`,
                            typeAction: 'cancel',
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
        });
    </script>
@stop
