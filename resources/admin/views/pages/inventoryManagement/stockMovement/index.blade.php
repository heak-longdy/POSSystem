@extends('admin::shared.layout')

@section('layout')
    <style>
        /* Scoped Select2 filter styles for Stock Movement */
        .select2-filter-wrap {
            min-width: 200px !important;
            max-width: 260px !important;
            width: 220px !important;
            margin-left: 5px !important;
            display: flex !important;
            align-items: center !important;
            position: relative !important;
        }

        .select2-filter-wrap .select2Group {
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
        }
    </style>
    @include('admin::shared.header', ['header_name' => __('stock_movement.title')])
    <div class="content-wrapper" id="app" x-data="xStockMovement">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => '',
            'filterStatus' => false,
            'filterView' => 'admin::pages.inventoryManagement.stockMovement.filter',
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
            'showCreate' => false,
            'exportAction' => 'excel()',
            'exportUrl' => $exportUrl ?? (\Illuminate\Support\Facades\Route::has('admin-stock-movement-export') ? route('admin-stock-movement-export', request()->query()) : url('admin/stock-movement/export' . (request()->query() ? '?' . http_build_query(request()->query()) : ''))),
            'exportLabel' => __('global.button.export'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'product_title', 'title' => __('stock_movement.table.product'), 'class' => 'text left', 'colVal' => 14],
                ['field' => 'category_title', 'title' => __('stock_movement.table.category'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'uom_title', 'title' => __('stock_movement.table.uom'), 'class' => '', 'colVal' => 7],
                ['field' => 'qty', 'title' => __('stock_movement.table.qty'), 'class' => '', 'colVal' => 8],
                ['field' => 'stock_status_title', 'title' => __('stock_movement.table.status'), 'class' => '', 'colVal' => 10],
                ['field' => 'from_title', 'title' => __('stock_movement.table.from'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'to_title', 'title' => __('stock_movement.table.to'), 'class' => 'text left', 'colVal' => 11],
                ['field' => 'created_date', 'title' => __('stock_movement.table.date'), 'class' => '', 'colVal' => 11],
                ['field' => 'request_by_title', 'title' => __('stock_movement.table.requested_by'), 'class' => 'text left', 'colVal' => 8],
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
                            ],
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
        function registerStockMovementIndexAlpine() {
            Alpine.data("xStockMovement", () => ({
                shopId: @json(request('shop_id') ?? ($shop?->id ?? '')),
                exportLoading: false,
                init() {
                    this.$nextTick(() => {
                        this.initSelectShop();
                    });
                },
                initSelectShop() {
                    const self = this;
                    const $select = $(this.$root).find('#stock_movement_filter_shop_id');
                    if (!$select.length || $select.data('select2')) return;

                    $select.select2({
                        placeholder: @json(__('stock_movement.filter.select_shop')),
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
                    const $select = $(this.$root).find('#stock_movement_filter_shop_id');
                    $select.val(null).trigger('change');
                    this.shopId = '';
                },
                async excel() {
                    this.exportLoading = true;
                    try {
                        const response = await Axios.get('{{ \Illuminate\Support\Facades\Route::has('admin-stock-movement-report') ? route('admin-stock-movement-report') : url('admin/stock-movement/report') }}', {
                            params: {
                                status_type: $(this.$root).find('select[name="status_type"]').val() || @json(request('status_type')),
                                search: $(this.$root).find('input[name="search"]').val() || @json(request('search')),
                                shop_id: $(this.$root).find('select[name="shop_id"]').val() || @json(request('shop_id')),
                            }
                        });
                        await this.RunExcelJSExport(response.data);
                    } catch (error) {
                        console.error('Export Excel failed:', error);
                    } finally {
                        this.exportLoading = false;
                    }
                },
                async RunExcelJSExport(datas) {
                    let workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet(@json(__('stock_movement.excel.sheet_name') ?? 'Stock Movement'));
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                    let lastColumn = dataColumn[dataColumn.length - 1];

                    const style_font = {
                        name: "Khmer OS System",
                        size: 10
                    };
                    const style_font_header = {
                        name: "Khmer OS Muol Light",
                        family: 4,
                        size: 16,
                        color: {
                            argb: "538DD5"
                        },
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

                    const titleTopRow = worksheet.addRow([@json(__('stock_movement.excel.title') ?? 'STOCK MOVEMENT REPORT')]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + "1");
                    worksheet.addRow([]);

                    const headers = [
                        @json(__('global.table.no')),
                        @json(__('stock_movement.table.product')),
                        @json(__('stock_movement.table.category')),
                        @json(__('stock_movement.table.uom')),
                        @json(__('stock_movement.table.qty')),
                        @json(__('stock_movement.table.status')),
                        @json(__('stock_movement.table.from')),
                        @json(__('stock_movement.table.to')),
                        @json(__('stock_movement.table.date')),
                        @json(__('stock_movement.table.requested_by')),
                    ];

                    const headerRow = worksheet.addRow(headers);
                    headerRow.eachCell((cell) => {
                        cell.fill = {
                            type: "pattern",
                            pattern: "solid",
                            fgColor: {
                                argb: "d4d4d4E5"
                            },
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
                            item?.category_title || item?.category?.name || item?.product?.category?.name || '---',
                            item?.uom_title || item?.uom?.name || item?.product?.uom?.name || '---',
                            item?.qty ?? 0,
                            item?.stock_status_title || '---',
                            item?.from_title || item?.shop?.name || '---',
                            item?.to_title || item?.data_to?.name || '---',
                            item?.created_date || (item?.created_at ? moment(item.created_at).format('YYYY-MM-DD HH:mm') : ''),
                            item?.request_by_title || (item?.request_by_type === 'admin' ? item?.user?.username : item?.barber?.name) || '---',
                        ]);

                        row.eachCell((cell, colNumber) => {
                            cell.border = style_border;
                            cell.font = style_font;
                            if ([2, 3, 7, 8, 10].includes(colNumber)) {
                                cell.alignment = align_left;
                            } else {
                                cell.alignment = align_center;
                            }
                        });
                    });

                    worksheet.columns = [
                        { width: 8 },
                        { width: 28 },
                        { width: 18 },
                        { width: 12 },
                        { width: 14 },
                        { width: 16 },
                        { width: 22 },
                        { width: 22 },
                        { width: 20 },
                        { width: 18 },
                    ];

                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    saveAs(blob, 'Stock_Movement_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx');
                }
            }));
        }

        if (window.Alpine) {
            registerStockMovementIndexAlpine();
        } else {
            document.addEventListener('alpine:init', registerStockMovementIndexAlpine);
        }
    </script>
@stop
