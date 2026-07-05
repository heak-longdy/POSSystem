@extends('admin::shared.layout')
@section('layout')
    <style>
        .textLeft {
            text-align: left !important;
        }
    </style>
    <div class="content-wrapper" x-data="xBookingData">
        <div class="header">
            @include('admin::shared.header', ['header_name' => 'Transaction Report Management'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/report-transaction/list/wallet') ? 'active' : '' !!}" s-click-link="{!! route('admin-report-transaction-list', 'wallet') !!}">
                            Wallet</div>
                        <div class="menu-item {!! Request::is('admin/report-transaction/list/pay_liability') ? 'active' : '' !!}" s-click-link="{!! route('admin-report-transaction-list', 'pay_liability') !!}">
                            Pay Liability</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        @if (request('status') == 'wallet')
                            <div class="form-row" style="width: 175px; padding: 0;" x-init="selectTranType()">
                                <select multiple="multiple" id="my-select" class="multipleSelect">
                                    <option value="Admin Top Up" @if (isset($type['Admin Top Up']) && $type['Admin Top Up']) selected @endif>Admin Top
                                        Up</option>
                                    <option value="ABA PAY" @if (isset($type['ABA PAY']) && $type['ABA PAY']) selected @endif>ABA PAY
                                    </option>
                                    <option value="KHQR" @if (isset($type['KHQR']) && $type['KHQR']) selected @endif>KHQR</option>
                                </select>
                                <input type="hidden" name="tran_type" :value="tranType" />
                            </div>
                            
                        @endif
                        <div class="form-row" style="width: 80px;min-width: 120px;">
                            <input type="text" name="from_date" placeholder="From Date" value="{!! request('from_date') !!}"
                                id="from_date" autocomplete="off" style="width: 100%">
                        </div>
                        <div class="form-row" style="width: 80px;min-width: 120px;">
                            <input type="text" name="to_date" placeholder="To Date" value="{!! request('to_date') !!}"
                                id="to_date" autocomplete="off" style="width: 100%;">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success"
                            style="min-width: auto;cursor: pointer;">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    @can('report-transaction-excel')
                        <button type="button" @click="excel(`{{ $status }}`)" class="btnExcel">
                            <i class="material-symbols-outlined">upgrade</i>
                            <span>Excel</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="table">
                @if ($data->count() > 0)
                    {{-- wallet --}}
                    @if ($status == 'wallet')
                        <div class="table-wrapper">
                            <div class="table-header">
                                <div class="row table-row-5">
                                    <span>ID</span>
                                </div>
                                <div class="row table-row-10 textLeft">
                                    <span>Transaction ID</span>
                                </div>
                                <div class="row table-row-10 textLeft">
                                    <span>Shop</span>
                                </div>
                                <div class="row table-row-10 textLeft">
                                    <span>Barber</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Barber's Phone</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Amount USA</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Amount KHR</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Type</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Status</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Created At</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Remark</span>
                                </div>
                            </div>
                            <div class="table-body">
                                @foreach ($data as $index => $item)
                                    <div class="column" style="height: auto;">
                                        <div class="row table-row-5">
                                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                                        </div>
                                        <div class="row table-row-10 textLeft">
                                            <span>{!! isset($item->tran_id) ? $item->tran_id : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10 textLeft">
                                            <span>{!! isset($item->shop->name) ? $item->shop->name : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10 textLeft">
                                            <span>{!! isset($item->barber->name) ? $item->barber->name : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! isset($item->barber->phone) ? $item->barber->phone : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>${!! number_format(isset($item->amount_dollar) ? $item->amount_dollar : 0, 2) !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! number_format(isset($item->amount) ? $item->amount : 0, 2) !!}KHR</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! isset($item->tran_type) ? $item->tran_type : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! isset($item->status) && $item->status == 2 ? 'Approved' : 'Pending' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! isset($item->created_at) ? $item->created_at : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span>{!! isset($item->remark) ? $item->remark : '---' !!}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="table-footer">
                                @include('admin::components.pagination', ['paginate' => $data])
                            </div>
                        </div>
                    @endif
                    {{-- endWallet --}}
                    {{-- pay_liability --}}
                    @if ($status == 'pay_liability')
                        <div class="table-wrapper">
                            <div class="table-header">
                                <div class="row table-row-15 textLeft">
                                    <span>Shop</span>
                                </div>
                                <div class="row table-row-15 textLeft">
                                    <span>Barber</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Barber's Phone Number</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Amount KHR</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Type</span>
                                </div>
                                <div class="row table-row-15">
                                    <span>Pay To Booking ID</span>
                                </div>
                                <div class="row table-row-5">
                                    <span>Status</span>
                                </div>
                                <div class="row table-row-10">
                                    <span>Created At</span>
                                </div>
                            </div>
                            <div class="table-body">
                                @foreach ($data as $index => $item)
                                    <div class="column" style="height: auto;">
                                        <div class="row table-row-15 textLeft">
                                            <span>{!! isset($item->shop->name) ? $item->shop->name : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-15 textLeft">
                                            <span>{!! isset($item->barber->name) ? $item->barber->name : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span>{!! isset($item->barber->phone) ? $item->barber->phone : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span>{!! number_format(
                                                isset($item->total_price) ? $item->total_price - ($item->total_discount + $item->total_commission) : 0,
                                                2,
                                            ) !!}៛</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! 'Pay Liabilties' !!}</span>
                                        </div>
                                        <div class="row table-row-15">
                                            <span>{!! isset($item) && $item->invoice_number ? $item->invoice_number : '--' !!}</span>
                                        </div>
                                        <div class="row table-row-5">
                                            <span>{!! isset($item->payment_status) ? $item->payment_status : '---' !!}</span>
                                        </div>
                                        <div class="row table-row-10">
                                            <span>{!! isset($item->payment_date) ? $item->payment_date : '---' !!}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="table-footer">
                                @include('admin::components.pagination', ['paginate' => $data])
                            </div>
                        </div>
                    @endif
                    {{-- endPayLiability --}}
                @else
                    @component('admin::components.empty', [
                        'name' => __('No data'),
                        'msg' => __('adminGlobal.empty.descriptionReportTransaction'),
                        'permission' => 'report-transaction-create',
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
    <script>
        $(document).ready(function() {

        });
    </script>
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
                tranType: @json($tranType),
                exportLoading: false,
                init() {
                    this.tranType.map((val, index) => {
                        this.tranType[val] = val;
                    });
                },
                selectTranType() {
                    $("#my-select").multipleSelect({
                        filter: true,
                        placeholder: 'All Type',
                        onClick: (arg1, arg2) => {
                            if (arg1.checked) {
                                this.tranType.push(arg1.value);
                            } else {
                                this.tranType = this.tranType.filter(val => val != arg1
                                    .value);
                            }
                            this.tranType.map((val, index) => {
                                this.tranType[val] = val;
                            });
                        },
                        onClose: (arg1, arg2) => {
                            console.log('tranTypethis', this.tranType);
                        },
                        onCheckAll: () => { 
                            this.tranType = ['Admin Top Up', 'ABA PAY', 'KHQR'];
                        },
                        onUncheckAll: () => {
                            this.tranType = [];
                        },
                    });
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
