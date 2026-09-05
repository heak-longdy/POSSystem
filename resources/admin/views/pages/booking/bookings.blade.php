@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('booking.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @php
            $bookingTabQuery = request()->except(['page', 'payment_status']);
            $bookingTabUrl = function ($tabStatus) use ($bookingTabQuery) {
                $url = route('admin-booking-list', $tabStatus);

                return empty($bookingTabQuery) ? $url : $url . '?' . http_build_query($bookingTabQuery);
            };
        @endphp
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('booking.button.create'),
            'createPermission' => 'booking-create',
            'filterStatus' => false,
            // 'filterView' => 'admin::pages.booking.filter',
            'filterData' => [
                'shop' => $shop,
                'barber' => $barber,
                'firstMonthDay' => $firstMonthDay,
                'lastMonthDay' => $lastMonthDay,
            ],
            'showSearch' => true,
            'showTabs' => true,
            'tabs' => [
                [
                    'label' => __('booking.tab.pending'),
                    'icon' => 'bx bx-time-five',
                    'url' => $bookingTabUrl('Pending'),
                    'active' => $status === 'Pending',
                ],
                [
                    'label' => __('booking.tab.paid'),
                    'icon' => 'bx bx-check-circle',
                    'url' => $bookingTabUrl('Paid'),
                    'active' => $status === 'Paid',
                ],
                [
                    'label' => __('booking.tab.partial'),
                    'icon' => 'bx bx-credit-card',
                    'url' => $bookingTabUrl('Partial'),
                    'active' => $status === 'Partial',
                ],
                [
                    'label' => __('booking.tab.rejected'),
                    'icon' => 'bx bx-x-circle',
                    'url' => $bookingTabUrl('Rejected'),
                    'active' => $status === 'Rejected',
                ],
                [
                    'label' => __('booking.tab.trash'),
                    'icon' => 'bx bx-trash-alt',
                    'url' => $bookingTabUrl('trash'),
                    'active' => $status === 'trash',
                ],
            ],
            'exportAction' => 'excel()',
            'exportLabel' => __('booking.button.excel'),
            'exportClass' => 'btnExcel',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'invoice_title', 'title' => __('booking.table.booking_id'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'shop_title', 'title' => __('booking.table.shop'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'customer_title', 'title' => __('booking.table.customer'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'booking_items_title', 'title' => __('booking.table.services_products'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'payment_status_title', 'title' => __('booking.table.pay_status'), 'class' => '', 'colVal' => 10],
                ['field' => 'total_price_title', 'title' => __('booking.table.total'), 'class' => '', 'colVal' => 10],
                ['field' => 'paid_amount_title', 'title' => __('booking.table.paid'), 'class' => '', 'colVal' => 8],
                ['field' => 'remaining_amount_title', 'title' => __('booking.table.remaining'), 'class' => '', 'colVal' => 10],
                ['field' => 'total_discount_title', 'title' => __('booking.table.discount'), 'class' => '', 'colVal' => 10],
                ['field' => 'booking_date_title', 'title' => __('booking.table.booking_date'), 'class' => '', 'colVal' => 12],
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
                                    'url' => 'edit',
                                    'title' => __('global.action.edit'),
                                    'icon' => 'edit',
                                    'type' => 'link',
                                    'visible' => ['payment_status' => 'Pending'],
                                ],
                                [
                                    'url' => 'edit',
                                    'title' => __('booking.action.payment'),
                                    'icon' => 'payments',
                                    'type' => 'link',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Partial'],
                                ],
                                [
                                    'url' => 'edit',
                                    'title' => __('booking.action.payment'),
                                    'icon' => 'payments',
                                    'type' => 'link',
                                    'class' => 'text-success',
                                    'visible' => ['payment_status' => 'Paid'],
                                ],
                                [
                                    'url' => 'cancel',
                                    'title' => __('booking.action.reject'),
                                    'icon' => 'cancel',
                                    'type' => 'click',
                                    'handler' => 'cancelBooking',
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
                    <label>{{ __('booking.export_excel') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#from_date").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#to_date").datepicker("option", "minDate", selected);
                }
            });
            $("#to_date").datepicker({
                minDate: `{{ $firstMonthDay }}`,
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#from_date").datepicker("option", "maxDate", selected);
                }
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
                    barber_id: @json(request('barber_id')),
                    from_date: @json($firstMonthDay),
                    to_date: @json($lastMonthDay),
                    search: @json(request('search')),
                },
                init() {
                    const shop = @json($shop);
                    const barber = @json($barber);

                    if (shop?.id) {
                        $select2Data('#shop_id', shop.id, shop.name || shop.phone);
                    }
                    if (barber?.id) {
                        $select2Data('#barber_id', barber.id, barber.name || barber.phone);
                    }
                },
                fetchSelectShop() {
                    $('#shop_id').select2({
                        placeholder: '{{ __('booking.select_shop') }}',
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
                        placeholder: '{{ __('booking.select_barber') }}',
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
                    const confirmTemplate = "{{ __('booking.confirm.action', ['action' => '__ACTION__']) }}";
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
                cancelBooking(item) {
                    const rejectTemplate = "{{ __('booking.confirm.reject', ['invoice' => '__INVOICE__']) }}";
                    this.$store.confirmDialog.open({
                        data: {
                            message: rejectTemplate.replace('__INVOICE__', '<b>' + (item?.invoice_title || '') + '</b>'),
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: '{{ __('booking.action.reject_booking') }}',
                            item: item,
                            typeAction: 'manual',
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "18rem"
                        },
                        afterClosed: (result) => {
                            if (!result) {
                                return;
                            }

                            const url = `{{ url('admin/booking/cancel') }}/${item.id}`;

                            Axios.post(url, {
                                _token: '{{ csrf_token() }}',
                            }).then((res) => {
                                if (res.data.message === 'success') {
                                    reloadData(`{{ url()->full() }}`);
                                }
                            }).catch((error) => {
                                const message = error.response?.data?.error ||
                                    Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                                    'Request failed.';
                                alert(message);
                            });
                        }
                    });
                },
                async excel() {
                    this.exportLoading = true;
                    await Axios.get(`{{ route('admin-booking-report') }}`, {
                        params: this.formData,
                    }).then((response) => {
                        this.runExcelExport(response.data);
                    }).finally(() => {
                        this.exportLoading = false;
                    });
                },
                runExcelExport(rows) {
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet(@json(__('booking.excel_report')));
                    worksheet.columns = [
                        { header: @json(__('booking.table.booking_id')), key: 'booking_id', width: 16 },
                        { header: @json(__('booking.table.booking_date')), key: 'booking_date', width: 18 },
                        { header: @json(__('booking.table.shop')), key: 'shop', width: 22 },
                        { header: @json(__('booking.table.barber')), key: 'barber', width: 22 },
                        { header: @json(__('booking.table.customer_phone')), key: 'customer_phone', width: 18 },
                        { header: @json(__('booking.table.item')), key: 'item', width: 28 },
                        { header: @json(__('booking.table.type')), key: 'type', width: 12 },
                        { header: @json(__('booking.table.qty')), key: 'qty', width: 8 },
                        { header: @json(__('booking.table.price')), key: 'price', width: 14 },
                        { header: @json(__('booking.table.discount')), key: 'discount', width: 14 },
                        { header: @json(__('booking.table.commission')), key: 'commission', width: 14 },
                        { header: @json(__('booking.table.pay_status')), key: 'payment_status', width: 14 },
                        { header: @json(__('booking.table.paid')), key: 'paid_amount', width: 14 },
                        { header: @json(__('booking.table.remaining')), key: 'remaining_amount', width: 18 },
                        { header: @json(__('booking.table.pay_date')), key: 'payment_date', width: 18 },
                    ];

                    rows.forEach((item) => {
                        worksheet.addRow({
                            booking_id: item?.booking?.invoice_number || '',
                            booking_date: this.dateFormat(item?.booking?.booking_date),
                            shop: item?.booking?.shop?.name || '',
                            barber: item?.booking?.barber?.name || '',
                            customer_phone: item?.booking?.customer?.phone || '',
                            item: item?.product?.name || item?.service?.name || '',
                            type: item?.type || '',
                            qty: item?.qty || 1,
                            price: item?.price || 0,
                            discount: this.discountAmount(item),
                            commission: this.commissionAmount(item),
                            payment_status: this.bookingStatusLabel(item?.booking?.payment_status),
                            paid_amount: item?.booking?.paid_amount || 0,
                            remaining_amount: item?.booking?.remaining_amount || 0,
                            payment_date: this.dateFormat(item?.booking?.payment_date),
                        });
                    });

                    worksheet.getRow(1).font = { bold: true };
                    workbook.xlsx.writeBuffer().then((data) => {
                        const blob = new Blob([data], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        saveAs(blob, 'Booking_Report_' + moment().format('YYYY_MM_DD_HHmmss'));
                    });
                },
                dateFormat(date) {
                    return date ? moment(date).format('YYYY-MM-DD HH:mm') : '';
                },
                bookingStatusLabel(status) {
                    if (status === 'Paid') return @json(__('booking.status.paid'));
                    if (status === 'Partial') return @json(__('booking.status.partial'));
                    if (status === 'Cancel') return @json(__('booking.status.rejected'));
                    return @json(__('booking.status.pending'));
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
