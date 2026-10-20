@extends('admin::shared.layout')

@section('layout')
    <style>
        /* Scoped Select2 filter styles for Stock Transfer */
        .select2-filter-wrap {
            min-width: 200px !important;
            max-width: 260px !important;
            width: 220px !important;
            margin-left: 10px !important;
            display: flex !important;
            align-items: center !important;
            position: relative !important;
        }

        /* .select2-filter-wrap .select2Group {
            width: 100% !important;
            position: relative !important;
        }

        .select2-filter-wrap .select2Group select,
        .select2-filter-wrap .select2Group .SelectField {
            width: 100% !important;
            height: 40px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 25px !important;
            padding: 0 10px !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            color: #334155 !important;
            background: #ffffff !important;
            outline: none !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
        }

        .select2-filter-wrap .select2Group .select2.select2-container {
            width: 100% !important;
        }

        .select2-filter-wrap .select2Group .select2-container .select2-selection--single {
            height: 40px !important;
            border-radius: 25px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 10px !important;
            box-shadow: none !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
        }

        .select2-filter-wrap .select2Group .select2-container--open .select2-selection--single,
        .select2-filter-wrap .select2Group .select2-container--focus .select2-selection--single,
        .select2-filter-wrap .select2Group .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        .select2-filter-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            line-height: normal !important;
            padding-left: 0 !important;
            padding-right: 28px !important;
        }

        .select2-filter-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }

        .select2-filter-wrap .select2Group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 8px !important;
        }

        .select2-filter-wrap .select2Group .select2Reset {
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

        .select2-filter-wrap .select2Group .select2Reset[style*="display: none"],
        .select2-filter-wrap .select2Group .select2Reset[style*="display:none"],
        .select2-filter-wrap .select2Group .select2Reset[x-cloak] {
            display: none !important;
        }

        .select2-filter-wrap .select2-container--default .select2-selection--single .select2-selection__clear {
            display: none !important;
        }

        .select2-filter-wrap .select2Group .select2Reset svg {
            fill: #94a3b8 !important;
            width: 18px !important;
            height: 18px !important;
            background: #ffffff !important;
            border-radius: 50% !important;
            transition: fill 0.15s ease !important;
        }

        .select2-filter-wrap .select2Group .select2Reset:hover svg {
            fill: #ef4444 !important;
        } */
    </style>
    @include('admin::shared.header', ['header_name' => __('stock_transfer.title')])
    <div class="content-wrapper" id="app" x-data="xStockTransferIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('stock_transfer.button.create'),
            'createPermission' => 'stock-transfer-create',
            'filterStatus' => false,
            'filterView' => 'admin::pages.inventoryManagement.stockTransfer.filter',
            'filterData' => ['shop' => $shop],
            'showTabs' => true,
                'tabs' => [
                    [
                        'label' => __('global.tab.all'),
                        'icon' => 'bx bx-data',
                        'url' => route('admin-' . $routeName . '-list', 1),
                        'active' => true,
                    ],
                ],
            'exportAction' => 'excel()',
            'exportUrl' => $exportUrl ?? (\Illuminate\Support\Facades\Route::has('admin-stock-transfer-export') ? route('admin-stock-transfer-export', request()->query()) : url('admin/stock-transfer/export' . (request()->query() ? '?' . http_build_query(request()->query()) : ''))),
            'exportLabel' => __('global.button.export'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'product_title', 'title' => __('stock_transfer.table.product'), 'class' => 'text left', 'colVal' => 19],
                ['field' => 'category_title', 'title' => __('stock_transfer.table.category'), 'class' => 'text left', 'colVal' => 9],
                ['field' => 'uom_title', 'title' => __('stock_transfer.table.uom'), 'class' => '', 'colVal' => 5],
                ['field' => 'qty', 'title' => __('stock_transfer.table.qty'), 'class' => '', 'colVal' => 7],
                ['field' => 'created_date', 'title' => __('stock_transfer.table.date'), 'class' => '', 'colVal' => 11],
                ['field' => 'remark', 'title' => __('stock_transfer.table.remark'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'shop_title', 'title' => __('stock_transfer.table.from_shop'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'destination_shop_title', 'title' => __('stock_transfer.table.to_shop'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'request_by_title', 'title' => __('stock_transfer.table.requested_by'), 'class' => 'text left', 'colVal' => 8],

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
                                
                            ],
                        ],
                        
                            
                            
                        
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => __('global.action.enable'), 'icon' => 'refresh']],
                        ],

                    ],
                ],
            ],
        ])
        @endcomponent

        <template x-if="exportLoading">
            <div style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 99999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
                <div style="background: #ffffff; padding: 24px 32px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; font-weight: 600; color: #1e293b;">
                    <svg style="animation: spin 1s linear infinite; width: 26px; height: 26px; color: #10b981;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ __('global.status.exporting') ?? 'Exporting Excel...' }}</span>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script src="{{ asset('admin-public/js/exceljs.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/FileSaver.min.js') }}"></script>
    <script>
        function registerStockTransferIndexAlpine() {
            Alpine.data('xStockTransferIndex', () => ({
                shopId: @json(request('shop_id') ?? ($shop?->id ?? '')),
                exportLoading: false,
                init() {
                    this.$nextTick(() => {
                        this.initSelectShop();
                    });
                },
                initSelectShop() {
                    const self = this;
                    const $select = $(this.$root).find('#stock_transfer_filter_shop_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_transfer.filter.select_shop')),
                        width: '100%',
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) {
                                return {
                                    search: param.term
                                };
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
                        this.shopId = selectedShop.id;
                    }
                },
                resetShopFilter() {
                    const $select = $(this.$root).find('#stock_transfer_filter_shop_id');
                    $select.val(null).trigger('change');
                    this.shopId = '';
                },
                verifyDialog(data, typeAction, btn) {
                    const confirmTemplate = `{{ __('global.dialog.confirm_action', ['action' => '__ACTION__']) }}`;
                    const confirmMsg = confirmTemplate.replace('__ACTION__', btn);
                    this.$store.confirmDialog.open({
                        data: {
                            message: confirmMsg,
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
                @include('admin::pages.inventoryManagement.partials.excel-request')
                async excel() {
                    this.exportLoading = true;
                    try {
                        const data = await this.requestInventoryExport({
                            status: @json($status),
                            search: $(this.$root).find('input[name="search"]').val() || @json(request('search')),
                            shop_id: $(this.$root).find('select[name="shop_id"]').val() || @json(request('shop_id')),
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
                    const worksheet = workbook.addWorksheet(@json(__('stock_transfer.excel.sheet_name') ?? 'Stock Transfer'));
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
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

                    const titleTopRow = worksheet.addRow([@json(__('stock_transfer.excel.title') ?? 'STOCK TRANSFER REPORT')]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + "1");
                    worksheet.addRow([]);

                    const headers = [
                        @json(__('global.table.no')),
                        @json(__('stock_transfer.table.product')),
                        @json(__('stock_transfer.table.category')),
                        @json(__('stock_transfer.table.uom')),
                        @json(__('stock_transfer.table.qty')),
                        @json(__('stock_transfer.table.date')),
                        @json(__('stock_transfer.table.remark')),
                        @json(__('stock_transfer.table.from_shop')),
                        @json(__('stock_transfer.table.to_shop')),
                        @json(__('stock_transfer.table.requested_by')),
                        @json(__('stock_transfer.table.status')),
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
                            item?.product_title || item?.product?.name || '---',
                            item?.category_title || item?.product?.category?.name || '---',
                            item?.uom_title || item?.product?.uom?.name || '---',
                            item?.qty || 0,
                            item?.created_date || (item?.created_at ? moment(item.created_at).format('YYYY-MM-DD HH:mm') : ''),
                            item?.remark || '',
                            item?.shop_title || item?.shop?.name || '---',
                            item?.destination_shop_title || item?.shop_to?.name || '---',
                            item?.request_by_title || item?.user?.username || item?.barber?.name || '---',
                            item?.stock_status_title || (Number(item?.status) === 1 ? @json(__('stock_transfer.status.confirmed')) : @json(__('stock_transfer.status.disabled'))),
                        ]);

                        row.eachCell((cell, colNumber) => {
                            cell.border = style_border;
                            cell.font = { name: "Khmer OS System", size: 10 };
                            if ([2, 3, 7, 8, 9, 10].includes(colNumber)) {
                                cell.alignment = align_left;
                            } else {
                                cell.alignment = align_center;
                            }
                        });
                    });

                    worksheet.columns = [
                        { width: 8 },
                        { width: 25 },
                        { width: 18 },
                        { width: 12 },
                        { width: 12 },
                        { width: 20 },
                        { width: 25 },
                        { width: 22 },
                        { width: 22 },
                        { width: 18 },
                        { width: 15 },
                    ];

                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    saveAs(blob, 'Stock_Transfer_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx');
                }
            }));
        }

        if (window.Alpine) {
            registerStockTransferIndexAlpine();
        } else {
            document.addEventListener('alpine:init', registerStockTransferIndexAlpine);
        }
    </script>
@stop
