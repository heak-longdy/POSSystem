@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xBookingData">
        <style>
            .select2-container--default .select2-selection--single {
                border: none !important;
                height: 100% !important;
                padding: 0 !important;
            }

            #FilterForm {
                position: relative;
            }

            .select2-dropdown.select2-dropdown--below {
                margin-top: 10px !important;
                width: 200px !important;
            }
        </style>
        <div class="header">
            @include('admin::shared.header', ['header_name' => 'Summary Report Management'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/report-summary/list/shop') ? 'active' : '' !!}" s-click-link="{!! route('admin-report-summary-list', 'shop') !!}">
                            Shop</div>
                        <div class="menu-item {!! Request::is('admin/report-summary/list/barber') ? 'active' : '' !!}" s-click-link="{!! route('admin-report-summary-list', 'barber') !!}">
                            Barber</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form id="FilterForm" class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row" style="max-width: 300px;min-width: 90px;display:flex;">
                            <select style="width: 90px;" @input="selectChangeType($event)" name="product_select_type"
                                x-model="product_select_type">
                                <option value="all">Select all</option>
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                            <div style="flex:1;margin-left:5px;width: 200px;" x-show="product_select_type !='all'"
                                x-transition.duration.500ms>
                                <select name="product_or_service" id="product_or_service" class="SelectProductOrService"
                                    x-init="fetchSelectShop('product_or_service', product_select_type)" style="width:100%;">
                                    <option value="">Select shop...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row" style="width: 80px;min-width: 120px;">
                            <input type="text" name="from_date" placeholder="From Date" value="{!! isset($from_date) && $from_date ? $from_date : request('from_date') !!}"
                                id="from_date" autocomplete="off" style="width: 100%">
                        </div>
                        <div class="form-row" style="width: 80px;min-width: 120px;">
                            <input type="text" name="to_date" placeholder="To Date" value="{!! isset($to_date) && $to_date ? $to_date : request('to_date') !!}"
                                id="to_date" autocomplete="off" style="width: 100%;">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success"
                            style="min-width: auto;cursor: pointer;">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="table">
                @if (count($data->listing) > 0)
                    {{-- shop --}}
                    @if ($status == 'shop')
                        <div class="table-wrapper">
                            <div class="table-header">
                                <div class="row table-row-15 textLeft">
                                    <span>Shop</span>
                                </div>
                                <div class="row table-row-5">
                                    <span>N_ID</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Total Price</span>
                                </div>
                                <div class="row table-row-14">
                                    <span>Total Discount</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Total After Discount</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Total Commision</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total Income</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total Income (USD)</span>
                                </div>
                            </div>

                            <div class="table-body" style="max-height: calc(100% - 120px);height:auto;">
                                @foreach ($data->listing as $index => $item)
                                    <div class="column" style="height: auto;">
                                        <div class="row table-row-15 textLeft">
                                            <span
                                                class="wordBreak">{{ isset($item->shop?->name) && $item->shop?->name ? $item->shop?->name : '---' }}</span>
                                        </div>
                                        <div class="row table-row-5">
                                            <span class="wordBreak">{!! isset($item->nID) ? $item->nID : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span class="wordBreak">{!! number_format(isset($item->totalPrice) ? $item->totalPrice : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-14">
                                            <span class="wordBreak">{!! number_format(isset($item->totalDiscount) ? $item->totalDiscount : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span class="wordBreak">{!! number_format(isset($item->totalAfterDiscount) ? $item->totalAfterDiscount : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span class="wordBreak">{!! number_format(isset($item->totalCommission) ? $item->totalCommission : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalInCome) ? $item->totalInCome : 0, 0) !!}&nbsp;KHR</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalInCome) ? $item->totalInCome / ($setting->rate ? $setting->rate : 0) : 0, 2) !!}&nbsp;USD</span>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="table-footer table-header">
                                <div class="row table-row-15 textLeft">
                                    <span>Grand Total</span>
                                </div>
                                <div class="row table-row-5">
                                    <span>{{ $data?->total?->nID }}</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>{!! number_format(isset($data?->total->totalPrice) ? $data?->total->totalPrice : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-14">
                                    <span>{!! number_format(isset($data?->total->totalDiscount) ? $data?->total->totalDiscount : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>{!! number_format(isset($data?->total->totalAfterDiscount) ? $data?->total->totalAfterDiscount : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>{!! number_format(isset($data?->total->totalCommission) ? $data?->total->totalCommission : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(isset($data?->total->totalInCome) ? $data?->total->totalInCome : 0, 0) !!}&nbsp;KHR</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(
                                        isset($data?->total->totalInCome) ? $data?->total->totalInCome / ($setting->rate ? $setting->rate : 0) : 0,
                                        2,
                                    ) !!}&nbsp;USD </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- endShop --}}
                    {{-- barber --}}
                    @if ($status == 'barber')
                        <div class="table-wrapper">
                            <div class="table-header">
                                <div class="row table-row-11 textLeft">
                                    <span>Barber</span>
                                </div>
                                <div class="row table-row-10 textLeft">
                                    <span>Shop</span>
                                </div>
                                <div class="row table-row-5">
                                    <span>N_ID</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Total Price</span>
                                </div>
                                <div class="row table-row-14">
                                    <span>Total Discount</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total After Discount</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total Commision</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total Income</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>Total Income (USD)</span>
                                </div>
                            </div>

                            <div class="table-body" style="max-height: calc(100% - 120px);height:auto;">
                                @foreach ($data->listing as $index => $item)
                                    <div class="column" style="height: auto;">
                                        <div class="row table-row-11 textLeft">
                                            <span
                                                class="wordBreak">{{ isset($item->barber?->name) && $item->barber?->name ? $item->barber->name : '---' }}</span>
                                        </div>
                                        <div class="row table-row-10 textLeft">
                                            <span
                                                class="wordBreak">{{ isset($item->shop?->name) && $item->shop?->name ? $item->shop?->name : '---' }}</span>
                                        </div>
                                        <div class="row table-row-5">
                                            <span class="wordBreak">{!! isset($item->nID) ? $item->nID : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span class="wordBreak">{!! number_format(isset($item->totalPrice) ? $item->totalPrice : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-14">
                                            <span class="wordBreak">{!! number_format(isset($item->totalDiscount) ? $item->totalDiscount : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalAfterDiscount) ? $item->totalAfterDiscount : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalCommission) ? $item->totalCommission : 0, 0) !!}</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalInCome) ? $item->totalInCome : 0, 0) !!}&nbsp;KHR</span>
                                        </div>
                                        <div class="row table-row-13">
                                            <span class="wordBreak">{!! number_format(isset($item->totalInCome) ? $item->totalInCome / ($setting->rate ? $setting->rate : 0) : 0, 2) !!}&nbsp;USD</span>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="table-footer table-header">
                                <div class="row table-row-11 textLeft"></div>
                                <div class="row table-row-10 textLeft">
                                    <span>Grand Total</span>
                                </div>
                                <div class="row table-row-5">
                                    <span>{{ $data?->total?->nID }}</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>{!! number_format(isset($data?->total->totalPrice) ? $data?->total->totalPrice : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-14">
                                    <span>{!! number_format(isset($data?->total->totalDiscount) ? $data?->total->totalDiscount : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(isset($data?->total->totalAfterDiscount) ? $data?->total->totalAfterDiscount : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(isset($data?->total->totalCommission) ? $data?->total->totalCommission : 0, 0) !!}</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(isset($data?->total->totalInCome) ? $data?->total->totalInCome : 0, 0) !!}&nbsp;KHR</span>
                                </div>
                                <div class="row table-row-13">
                                    <span>{!! number_format(
                                        isset($data?->total->totalInCome) ? $data?->total->totalInCome / ($setting->rate ? $setting->rate : 0) : 0,
                                        2,
                                    ) !!}&nbsp;USD </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- endPayLiability --}}
                @else
                    @component('admin::components.empty', [
                        'name' => __('No data'),
                        'msg' => 'Summary reprot empty',
                    ])
                    @endcomponent
                @endif
            </div>
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
            $("#from_date,#to_date").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
            $("#from_date").change(function() {
                let str = $(this).val();
                $("#to_date").datepicker("option", "minDate", new Date(str));
            });
            const date = $('#from_date').val();
            if (date) {
                $("#to_date").datepicker("option", "minDate", new Date(date));
            }
        });
        document.addEventListener('alpine:init', () => {
            Alpine.data('xBookingData', () => ({
                formData: {
                    status: @json(request('payment_status')),
                    shop_id: @json(request('shop_id')),
                    barber_id: @json(request('barber_id')),
                    from_date: @json(request('from_date')),
                    to_date: @json(request('to_date')),
                    tran_type: @json(request('tran_type')),
                },
                tranType: [],
                exportLoading: false,
                product_select_type: 'all',
                init() {
                    var productSelectType = @json($productSelectType);
                    this.product_select_type = productSelectType ? productSelectType : 'all';
                    var dataProduct = @json($dataProduct);
                    var option = "<option selected></option>";
                    var selectOptionHTML = $(option).val(dataProduct?.id ? dataProduct.id : null).text(
                        dataProduct?.name ?
                        dataProduct.name : dataProduct?.phone);
                    $('.SelectProductOrService').append(selectOptionHTML).trigger('change');

                },
                selectChangeType($event) {
                    var selectType = $event.target.value;
                    this.product_select_type = selectType;
                    console.log(this.product_select_type,
                        'product_select_typeproduct_select_typeproduct_select_type');
                    this.resetSelect2(['product_or_service']);
                    this.fetchSelectShop('product_or_service', selectType);

                },
                fetchSelectShop($shopId, type) {
                    console.log('object', type);
                    var url = type == "product" ? '{{ route('admin-select-product') }}' :
                        '{{ route('admin-select-service') }}';
                    console.log('url', url);
                    $(`#${$shopId}`).select2({
                        placeholder: `Select ${type} ...`,
                        ajax: {
                            url: type != "all" ? url : "",
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
                                            text: item?.name ? item?.name : item
                                                ?.phone,
                                            id: item.id
                                        }
                                    })
                                };
                            }
                        }
                    }).on('select2:open', (e) => {
                        document.querySelector('.select2-search__field').focus();
                    }).on('select2:close', async (eventClose) => {
                        const _id = eventClose.target.value;
                    });
                },
                resetSelect2(Item) {
                    if (Item.length > 0) {
                        Item.map(val => {
                            $("#" + val).val('');
                            $("#" + val).select2();
                        });
                    }
                },
                async excel(status) {
                    this.exportLoading = true;
                    setTimeout(async () => {
                        await this.fetchData(
                            `/admin/report-transaction/report/${status}`,
                            this.formData,
                            (res) => {
                                if (status == "wallet") {
                                    this.RunExcelJSExport(res);
                                } else {
                                    this.RunPayLiabilityExport(res);
                                }

                            });
                    }, 500);
                },
                async fetchData(url, formData, callback) {
                    await Axios.get(url, {
                            params: formData,
                        })
                        .then((response) => {
                            this.exportLoading = false;
                            callback(response.data);

                        })
                        .catch(function(error) {
                            this.exportLoading = false;
                        });
                },
                async RunExcelJSExport(dataItem) {
                    let datas = dataItem;
                    let workbook = new ExcelJS.Workbook();
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
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
                    const worksheet = workbook.addWorksheet("Report Transaction");

                    // Add Row Title and formatting
                    const titleTopRow = worksheet.addRow(["របាយការណ៍ប្រតិបត្តិការ"]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + 1);

                    worksheet.addRow([]);
                    // Add Header Row
                    const header = [
                        "ID",
                        "Transaction ID",
                        "Shop",
                        "Barber",
                        "Barber's Phone Number",
                        "Amount USA",
                        "Amount KHR",
                        "Type",
                        "Status",
                        "Created At",
                        "Remark",
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
                            index + 1,
                            item?.tran_id,
                            item?.shop?.name,
                            item?.barber?.name,
                            item?.barber?.phone,
                            (item?.amount_dollar ? item.amount_dollar : 0).toFixed(
                                2),
                            (item?.amount ? item.amount : 0),
                            item?.tran_type,
                            item?.status == 2 ? "Approved" : "Pending",
                            this.dateFormatEn(item?.created_at, 'YYYY-MM-DD H:mm'),
                            item?.remark,
                        ]);
                        //setStyle
                        dataColumn.forEach((column) => {
                            worksheet.getCell(column + i).font = style_font;
                            worksheet.getCell(column + i).alignment = align_center;
                            worksheet.getCell(column + i).border = style_border;
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
                        if (colI == 1 || colI == 8) {
                            worksheet.getColumn(colI).width = 14;
                        }
                        if (colI == 4) {
                            worksheet.getColumn(colI).width = 27;
                        }
                        if (colI == 5) {
                            worksheet.getColumn(colI).width = 27;
                        }
                        if (colI == 3) {
                            worksheet.getColumn(colI).width = 32;
                        }
                        // if (colI == 8 || colI == 10 || colI == 12) {
                        //     worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        // }
                        if (colI == 7) {
                            worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        }
                    });
                    //endSetWithHeight

                    const footerRowTotal = worksheet.addRow([]);

                    // Generate Excel File with given name
                    const titleExportName = "Report Transaction Date_" + this.dateFormatEn(moment(),
                        'DD_MM_YYYY_H:mm:ss');
                    workbook.xlsx.writeBuffer().then(function(data) {
                        const blob = new Blob([data], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        });
                        saveAs(blob, titleExportName);
                    });
                },
                async RunPayLiabilityExport(dataItem) {
                    let datas = dataItem;
                    let workbook = new ExcelJS.Workbook();
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
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
                    const worksheet = workbook.addWorksheet("Report Transaction");

                    // Add Row Title and formatting
                    const titleTopRow = worksheet.addRow(["របាយការណ៍បំណុល"]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + 1);

                    worksheet.addRow([]);
                    // Add Header Row
                    const header = [
                        "Shop",
                        "Barber",
                        "Barber's Phone Number",
                        "Amount KHR",
                        "Type",
                        "Pay To Booking ID",
                        "Status",
                        "Created At"
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
                            item?.shop?.name,
                            item?.barber?.name,
                            item?.barber?.phone,
                            this.totalAmountPay(item),
                            "Pay Liabilties",
                            item?.invoice_number,
                            item?.payment_status,
                            this.dateFormatEn(item?.payment_date,
                                'YYYY-MM-DD H:mm'),
                        ]);
                        //setStyle
                        dataColumn.forEach((column) => {
                            worksheet.getCell(column + i).font = style_font;
                            worksheet.getCell(column + i).alignment = align_center;
                            worksheet.getCell(column + i).border = style_border;
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
                        // if (colI == 1 || colI == 7 || colI == 8) {
                        //     worksheet.getColumn(colI).width = 14;
                        // }
                        if (colI == 3) {
                            worksheet.getColumn(colI).width = 27;
                        }
                        if (colI == 4) {
                            worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        }
                        // if (colI == 5) {
                        //     worksheet.getColumn(colI).width = 27;
                        // }
                        // if (colI == 3) {
                        //     worksheet.getColumn(colI).width = 32;
                        // }
                        // if (colI == 8 || colI == 10 || colI == 12) {
                        //     worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        // }
                        // if (colI >= 14 && colI <= 19) {
                        //     worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        // }
                    });
                    //endSetWithHeight

                    const footerRowTotal = worksheet.addRow([]);

                    // Generate Excel File with given name
                    const titleExportName = "pay_liability_report_" + this.dateFormatEn(moment(),
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
                totalAmountPay(item) {
                    return item.total_price - (item.total_discount + item.total_commission);
                }
            }));
        });
    </script>
@stop
