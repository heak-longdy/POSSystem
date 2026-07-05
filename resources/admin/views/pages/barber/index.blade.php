@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xData">
        {{-- <div class="header">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/barber/list/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-barber-list', 1) !!}">
                            Active</div>
                        <div class="menu-item {!! Request::is('admin/barber/list/2') ? 'active' : '' !!}" s-click-link="{!! route('admin-barber-list', 2) !!}">
                            Disable</div>
                        <div class="menu-item {!! Request::is('admin/barber/list/trash') ? 'active' : '' !!}" s-click-link="{!! route('admin-barber-list', 'trash') !!}">
                            @lang('adminGlobal.tab.trash')</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <input type="text" name="search" placeholder="@lang('user.filter.search')"
                                value="{!! request('search') !!}">
                            <i data-feather="filter"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                        <button type="button" @click="excel()" class="btnExcel">
                            <i class="material-symbols-outlined">upgrade</i>
                            <span>Excel</span>
                        </button>
                    </form>
                    @can('barber-create')
                        <button class="btn-create" s-click-link="{!! route('admin-barber-create') !!}">
                            <i data-feather="plus-circle"></i>
                            <span>Create Barber</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('user.button.reload')</span>
                    </button>
                </div>
            </div>
        </div> --}}
        <div class="header">
            <div class="header-wrapper marginBottom">
                <div class="btn-toggle-sidebar">
                    <span>Barber Management</span>
                </div>
                <div class="navHeaderRight">
                    @can('barber-create')
                        <button class="btn btn-create" s-click-link="{!! route('admin-barber-create') !!}">
                            <i class='bx bx-plus-circle'></i>
                            <span>Create Barber</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}" class="refresh">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            <a href="{!! route('admin-barber-list', 1) !!}" class="{!! Request::is('admin/barber/list/1') ? 'tabActive' : '' !!}">
                                <i class='bx bx-data'></i>
                                Data
                            </a>
                            <a href="#">
                                <i class='bx bxs-color-fill'></i>
                                Pending
                            </a>
                            <a href="#">
                                <i class='bx bx-trash-alt'></i>
                                Trash
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row w80">
                            <select name="payment_status">
                                <option value="">All Status</option>
                                <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Pending</option>
                                <option value="Paid" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Paid</option>
                            </select>
                        </div>
                        <div class="form-row w80">
                            <input type="text" name="from_date" placeholder="From Date" value="{!! request('from_date') !!}"
                                id="from_date" autocomplete="off">
                            <i data-feather="calendar" id="from_date"></i>
                        </div>
                        <div class="form-row w80">
                            <input type="text" name="to_date" placeholder="To Date" value=" {!! request('to_date') !!}"
                                id="to_date" autocomplete="off">
                            <i data-feather="calendar"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success btnSearch">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    <button type="button" @click="excel()" class="btnExcel">
                        <i class="material-symbols-outlined">upgrade</i>
                        <span>Excel</span>
                    </button>
                    {{-- <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button> --}}
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.barber.table')
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
        $("body").on("click", ".trash-btn", function() {
            let url = $(this).data('url');
            let id = url.split('/').pop();
            let row = $(this).closest('.column');
            Swal.fire({
                customClass: "confirm-message",
                icon: "warning",
                html: `Are you sure to delete <b>${$(this).data('name')}</b>?`,
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value == 1) {
                        $.ajax({
                            url: `/admin/barber/delete/${id}`,
                            method: 'GET',
                            success: function(data) {
                                row.remove();
                                Toast({
                                    title: 'Success Message',
                                    message: 'Delete Successfully',
                                    status: 'success',
                                    duration: 5000,
                                });
                            }
                        });
                    }
                }
            });
        });
    </script>
    <script lang="ts">
        document.addEventListener('alpine:init', () => {
            Alpine.data('xData', () => ({
                formData: {
                    status: @json(request('status')),
                    search: @json(request('search')),
                },
                exportLoading: false,
                init() {},
                async excel() {
                    this.exportLoading = true;
                    setTimeout(async () => {
                        await this.fetchData(
                            `/admin/barber/report-excel`,
                            this.formData,
                            (res) => {
                                console.log(res, 'ressss');
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
                            callback(null);
                        });
                },
                async RunExcelJSExport(dataItem) {
                    let datas = dataItem;
                    let workbook = new ExcelJS.Workbook();
                    const dataColumn = ['A', 'B', 'C', 'D', 'E'];
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
                    const worksheet = workbook.addWorksheet("Barber Report");

                    // Add Row Title and formatting
                    const titleTopRow = worksheet.addRow(["របាយការណ៍ជាងកាត់សក់"]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + 1);

                    worksheet.addRow([]);
                    // Add Header Row
                    const header = [
                        "Barber's ID",
                        "Barber's Name",
                        "Barber's Phone Number",
                        "Balance (USD)",
                        "Balance (KHR)",
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
                        if (cell?._address == "G3" || cell?._address == "K3") {
                            cell.alignment = align_left;
                        }
                    });
                    let i = 4;
                    datas.forEach((item, index) => {
                        worksheet.addRow([
                            item?.id,
                            item?.name,
                            item?.phone,
                            item?.wallet_dollar,
                            item?.wallet,
                        ]);
                        //setStyle
                        dataColumn.forEach((column) => {
                            worksheet.getCell(column + i).font = style_font;
                            worksheet.getCell(column + i).alignment = align_center;
                            worksheet.getCell(column + i).border = style_border;

                            if (column == "G" || column == "K") {
                                worksheet.getCell(column + i)
                                    .alignment = align_left;
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
                        // if (colI >= 13 && colI <= 17) {
                        //     worksheet.getColumn(colI).width = 20;
                        // }
                        if (colI == 3) {
                            worksheet.getColumn(colI).width = 30;
                        }
                        // if (colI >= 19 && colI <= 20) {
                        //     worksheet.getColumn(colI).width = 16;
                        // }
                        if (colI >= 4 && colI <= 5) {
                            worksheet.getColumn(colI).numFmt = '#,##0.00;[Red]-#,##0.00';
                        }
                        // if (colI >= 12 && colI <= 19) {
                        //     worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        // }
                    });
                    //endSetWithHeight

                    const footerRowTotal = worksheet.addRow([]);

                    // Generate Excel File with given name
                    const titleExportName = "Barber_Report_Date_" + this.dateFormatEn(moment(),
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
