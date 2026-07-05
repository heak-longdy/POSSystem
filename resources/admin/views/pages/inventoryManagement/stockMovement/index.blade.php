@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xStockMovement">
        <div class="header">
            @include('admin::shared.header', ['header_name' => __('Stock Movement Management')])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/stock-movement/list') ? 'active' : '' !!}" s-click-link="{!! route('admin-stock-movement-list') !!}">Data</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row form-row-inputCus">
                            <select name="status_type" id="status_type" style="width: 100%;">
                                <option value="">All Status</option>
                                <option value="stock_in" {!! request('status_type') == 'stock_in' ? 'selected' : '' !!}>Stock In</option>
                                <option value="stock_out" {!! request('status_type') == 'stock_out' ? 'selected' : '' !!}>Stock Out</option>
                                <option value="stock_transfer" {!! request('status_type') == 'stock_transfer' ? 'selected' : '' !!}>Stock Transfer</option>
                            </select>
                        </div>
                        <div class="form-row" style="min-width: 135px !important;">
                            <input type="text" name="search" placeholder="Enter product"
                                value="{!! request('search') !!}">
                            <i data-feather="filter"></i>
                        </div>&nbsp;
                        <div class="category-content-gp" style="width: 140px;">
                            <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
                                <option value=""> Select Shop</option>
                            </select>
                        </div>
                        <div class="form-row form-row-inputCus"
                            style="margin-left:0;min-width:100px !important;width:100px !important;">
                            <input type="text" name="from_date" placeholder="From date" value="{!! request('from_date') !!}"
                                id="fromDate" autocomplete="off">
                        </div>
                        <div class="form-row form-row-inputCus" style="min-width:100px !important;width:100px !important;">
                            <input type="text" name="to_date" placeholder="To Date" value="{!! request('to_date') !!}"
                                id="toDate" autocomplete="off">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success"
                            style="min-width: auto;cursor: pointer;">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    @can('stock-movement-excel')
                        <button type="button" @click="excel()" class="btnExcel">
                            <i class="material-symbols-outlined">upgrade</i>
                            <span>Excel</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('user.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.inventoryManagement.stockMovement.table')
        </div>
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>Export excel ...</label>
                </div>
            </div>
        </template>
    </div>
@stop
@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $("#date").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
            $("#fromDate").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#toDate").datepicker("option", "minDate", selected)
                }
            });
            $("#toDate").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#fromDate").datepicker("option", "maxDate", selected)
                }
            });
        });
    </script>
    <script>
        var option = "<option selected></option>";
        //shop
        var shop = $(option).val(`{{ isset($shop->id) ? $shop->id : '' }}`).text(
            `{{ isset($shop->name) ? $shop->name : '' }}`);
        $('.SelectShop').append(shop).trigger('change');
    </script>
    <script>
        const header = {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded;charset=utf-8",
                Accept: "application/json",
            },
            responseType: "json",
        };
        document.addEventListener('alpine:init', () => {
            Alpine.data("xStockMovement", () => ({
                loading: false,
                loadingSubmit: false,
                memberCarData: [],
                baseImageUrl: "{{ asset('file_manager') }}",
                dataError: null,
                exportLoading: false,
                init() {},
                fetchSelectShop() {
                    $(`#shop_id`).select2({
                        placeholder: `Select Shop`,
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: "GET",
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
                                            text: item?.name ? item?.name : '',
                                            id: item.id
                                        }
                                    })
                                };
                            }
                        }
                    }).on('select2:open', (e) => {
                        document.querySelector('.select2-search__field').focus();
                    });
                },
                async excel() {
                    this.exportLoading = true;
                    setTimeout(async () => {
                        await this.fetchData(
                            `/admin/stock-movement/report`,
                            this.formData,
                            (res) => {
                                this.RunExcelJSExport(res);
                                this.exportLoading = false;
                            });
                    }, 500);
                },
                async fetchData(url, formData, callback) {
                    await Axios.get(url, {
                            params: formData,
                        })
                        .then((response) => {
                            callback(response.data);
                        })
                        .catch(function(error) {
                            this.exportLoading = false;
                        });
                },
                async RunExcelJSExport(dataItem) {
                    let datas = dataItem;
                    let workbook = new ExcelJS.Workbook();
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                    let lastColumn = dataColumn[dataColumn.length - 1];

                    const style_font = {
                        name: "Khmer OS System",
                        size: 11
                    };
                    const style_font_header = {
                        name: "Khmer OS Muol Light",
                        family: 4,
                        size: 18,
                        color: {
                            argb: "538DD5"
                        },
                    };
                    const style_border = {
                        top: {
                            style: "thin"
                        },
                        left: {
                            style: "thin"
                        },
                        bottom: {
                            style: "thin"
                        },
                        right: {
                            style: "thin"
                        },
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
                    const wrapText = {
                        wrapText: true
                    };
                    const colorRed = {
                        color: {
                            argb: "FF0000"
                        }
                    };

                    // Create workbook and worksheet
                    const worksheet = workbook.addWorksheet("Inventory Management Report");

                    // Add Row Title and formatting
                    const titleTopRow = worksheet.addRow(["Inventory Management Report"]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + 1);

                    worksheet.addRow([]);
                    // Add Header Row
                    const header = [
                        "ID",
                        "Date",
                        "Shop",
                        "Product",
                        "Categories",
                        "Quantities",
                        "Price",
                        "Requested By",
                        "Stock Type",
                        "Send To",
                    ];

                    const headerRow = worksheet.addRow(header);

                    // Cell Style : Fill and Border
                    headerRow.eachCell((cell, number) => {
                        cell.fill = {
                            type: "pattern",
                            pattern: "solid",
                            fgColor: {
                                argb: "d4d4d4E5"
                            },
                        };
                        cell.font = {
                            name: "Khmer Moul",
                            size: 12
                        };


                        cell.border = style_border;
                        cell.alignment = align_center;
                        // if (cell?._address == "G3" || cell?._address == "K3") {
                        //     cell.alignment = align_left;
                        // }
                    });
                    let i = 4;
                    datas.forEach((item, index) => {
                        worksheet.addRow([
                            item?.id,
                            this.dateFormatEn(item?.created_at, 'YYYY-MM-DD H:mm'),
                            // item?.shop?.name,
                            item.type == "shop" && item.status == "stock_in" ? item
                            ?.data_to?.name : item?.shop?.name,
                            item?.product?.name,
                            item?.category?.name,
                            item?.qty,
                            item?.product?.price ? item.product.price : 0,
                            item.request_by_type == "admin" ? item?.user?.username :
                            item?.barber?.name,
                            item?.status,
                            item.type == "shop" && item.status == "stock_in" ? item
                            ?.shop?.name : item?.data_to?.name,
                        ]);
                        //setStyle
                        dataColumn.forEach((column) => {
                            worksheet.getCell(column + i).font = style_font;
                            worksheet.getCell(column + i).alignment = align_center;
                            worksheet.getCell(column + i).border = style_border;

                            if (column == "I" || column == "J") {
                                worksheet.getCell(column + i).fill = {
                                    type: "pattern",
                                    pattern: "solid",
                                    fgColor: {
                                        argb: "d4d4d4E5"
                                    },
                                };
                            }

                        });
                        //endSteStyle
                        i++;

                    });

                    // add background for header
                    const backgroundCell = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: {
                            argb: "d4d4d4E5"
                        },
                    };

                    //setWidthHeight
                    worksheet.getRow(1).height = 50;
                    worksheet.getRow(3).height = 22;
                    dataColumn.forEach((column, colIndex = 1) => {
                        let colI = colIndex + 1;
                        worksheet.getColumn(colI).width = 25;
                        if (colI == 1) {
                            worksheet.getColumn(colI).width = 14;
                        }
                        if (colI == 2) {
                            worksheet.getColumn(colI).width = 18;
                        }
                        if (colI == 4) {
                            worksheet.getColumn(colI).width = 45;
                        }
                        if (colI == 3 || colI == 10) {
                            worksheet.getColumn(colI).width = 32;
                        }
                        if (colI >= 6 && colI <= 9) {
                            worksheet.getColumn(colI).width = 16;
                        }
                    });
                    //endSetWithHeight

                    const footerRowTotal = worksheet.addRow([]);

                    // Generate Excel File with given name
                    const titleExportName = "Inventory Management Report Date_" + this.dateFormatEn(
                        moment(),
                        'DD_MM_YYYY_H:mm:ss');
                    workbook.xlsx.writeBuffer().then(function(data) {
                        const blob = new Blob([data], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        });
                        saveAs(blob, titleExportName);
                    });
                },
                dateFormat(date, type) {
                    return moment(date).locale('km').format(type);
                },
                dateFormatEn(date, type) {
                    return date ? moment(date).format(type) : "";
                },
            }));
        });
    </script>
@stop
