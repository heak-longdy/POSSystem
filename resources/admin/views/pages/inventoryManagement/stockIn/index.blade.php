@extends('admin::shared.layout')

@section('layout')
    <style>
        .select2-filter-wrap {
            min-width: 200px !important;
            max-width: 240px !important;
            width: 220px !important;
            margin-left: 10px !important;
            display: flex !important;
            align-items: center !important;
            position: relative !important;
        }

        .filter-select {
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 0 14px;
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
            background: #ffffff;
            outline: none;
            cursor: pointer;
        }

        .filter-date-input {
            position: relative;
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .filter-date-input input {
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 0 32px 0 14px;
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
            background: #ffffff;
            outline: none;
            width: 100%;
        }

        .filter-date-input i {
            position: absolute;
            right: 12px;
            font-size: 16px;
            color: #94a3b8;
            pointer-events: none;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
        }

        .badge-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-ordered { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-partial { background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-received { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-cancelled { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        
        .badge-unpaid { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-paid { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-default { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .po-number-link {
            color: #2563eb;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .po-number-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .table-subtext {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

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

        /* Scoped UI Datepicker Popup Elevation */
        .ui-datepicker.stock-in-datepicker-popup {
            z-index: 9999 !important;
        }
    </style>

    @include('admin::shared.header', ['header_name' => __('stock_in.title')])
    <div class="content-wrapper" id="app" x-data="xStockInIndex">
        @component('admin::components.listingData', [
            'routeName'        => $routeName,
            'createName'       => __('stock_in.button.create'),
            'createPermission' => 'stock-in-create',
            'filterStatus'     => false,
            'filterView'       => 'admin::pages.inventoryManagement.stockIn.filter',
            'filterData'       => ['shop' => $shop, 'supplier' => $supplier],
            'showTabs'         => true,
            'tabs'             => [
                [
                    'label'  => __('stock_in.tab.all') . ' (' . ($counts['all'] ?? 0) . ')',
                    'icon'   => 'bx bx-layer',
                    'url'    => route('admin-' . $routeName . '-list', 'all'),
                    'active' => $status === 'all' || $status === '1',
                ],
                [
                    'label'  => __('stock_in.tab.ordered') . ' (' . ($counts['ordered'] ?? 0) . ')',
                    'icon'   => 'bx bx-time-five',
                    'url'    => route('admin-' . $routeName . '-list', 'ordered'),
                    'active' => $status === 'ordered',
                ],
                [
                    'label'  => __('stock_in.tab.partial_received') . ' (' . ($counts['partial_received'] ?? 0) . ')',
                    'icon'   => 'bx bx-pie-chart-alt-2',
                    'url'    => route('admin-' . $routeName . '-list', 'partial_received'),
                    'active' => $status === 'partial_received',
                ],
                [
                    'label'  => __('stock_in.tab.received') . ' (' . ($counts['received'] ?? 0) . ')',
                    'icon'   => 'bx bx-check-circle',
                    'url'    => route('admin-' . $routeName . '-list', 'received'),
                    'active' => $status === 'received',
                ],
                [
                    'label'  => __('stock_in.tab.draft') . ' (' . ($counts['draft'] ?? 0) . ')',
                    'icon'   => 'bx bx-file-blank',
                    'url'    => route('admin-' . $routeName . '-list', 'draft'),
                    'active' => $status === 'draft',
                ],
                [
                    'label'  => __('stock_in.tab.trash') . ' (' . ($counts['trash'] ?? 0) . ')',
                    'icon'   => 'bx bx-trash',
                    'url'    => route('admin-' . $routeName . '-list', 'trash'),
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
                ['field' => 'purchase_number_link', 'title' => __('stock_in.table.po_number'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'supplier_title', 'title' => __('stock_in.table.supplier'), 'class' => 'text left', 'colVal' => 13],
                ['field' => 'shop_title', 'title' => __('stock_in.table.shop'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'created_date', 'title' => __('stock_in.table.date'), 'class' => 'text left', 'colVal' => 9],
                ['field' => 'formatted_total_amount', 'title' => __('stock_in.table.total_amount'), 'class' => 'text right font-bold', 'colVal' => 9],
                ['field' => 'formatted_paid_amount', 'title' => __('stock_in.table.paid_amount'), 'class' => 'text right font-semibold text-emerald-600', 'colVal' => 9],
                ['field' => 'formatted_remaining_amount_styled', 'title' => __('stock_in.table.remaining_amount'), 'class' => 'text right', 'colVal' => 9],
                ['field' => 'order_status_badge', 'title' => __('stock_in.table.order_status'), 'class' => 'text center', 'colVal' => 9],
                ['field' => 'payment_status_badge', 'title' => __('stock_in.table.payment_status'), 'class' => 'text center', 'colVal' => 8],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'view', 'title' => __('global.action.view'), 'icon' => 'visibility', 'type' => 'link'],
                                ['url' => 'edit', 'title' => __('global.action.edit'), 'icon' => 'edit', 'type' => 'link'],
                                ['url' => 'print', 'title' => __('stock_in.button.print') ?? 'Print PO', 'icon' => 'print', 'type' => 'link'],
                                ['url' => 'delete', 'title' => __('global.action.delete'), 'icon' => 'delete', 'class' => 'text-danger'],
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
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        self.shopId = $(this).val();
                        $(this).closest('form').submit();
                    });

                    const selectedShop = @json($shop);
                    if (selectedShop?.id) {
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
                        }
                    }).on('change select2:select select2:clear select2:unselect', function() {
                        self.supplierId = $(this).val();
                        $(this).closest('form').submit();
                    });

                    const selectedSupplier = @json($supplier);
                    if (selectedSupplier?.id) {
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

                    $from.datepicker({
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
                        onClose: function(selectedDate) {
                            $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                            if (selectedDate) {
                                $to.datepicker('option', 'minDate', selectedDate);
                            }
                        },
                        onSelect: function() {
                            $(this).closest('form').submit();
                        }
                    });

                    $to.datepicker({
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
                        onClose: function(selectedDate) {
                            $('#ui-datepicker-div').removeClass('stock-in-datepicker-popup');
                            if (selectedDate) {
                                $from.datepicker('option', 'maxDate', selectedDate);
                            }
                        },
                        onSelect: function() {
                            $(this).closest('form').submit();
                        }
                    });

                    if ($from.val()) {
                        $to.datepicker('option', 'minDate', $from.val());
                    }
                    if ($to.val()) {
                        $from.datepicker('option', 'maxDate', $to.val());
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
    </script>
@stop
