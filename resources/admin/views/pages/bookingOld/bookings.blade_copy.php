@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xBookingData" id="app">
        <div class="header">
            {{-- @include('admin::shared.header', ['header_name' => '#']) --}}
            <div class="header-wrapper marginBottom">
                <div class="btn-toggle-sidebar">
                    <span>Booking Management22</span>
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
                        {{-- <div class="menu-item {!! Request::is('admin/booking/list/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-booking-list', 1) !!}">
                            Data</div>
                        <div class="menu-item {!! Request::is('admin/booking/list/trash') ? 'active' : '' !!}" s-click-link="{!! route('admin-booking-list', 'trash') !!}">
                            @lang('adminGlobal.tab.trash')</div> --}}
                        <div class="tabs">
                            <a href="#" class="tabActive">
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
                        <div class="category-content-gp">
                            <select name="shop_id" class="SelectShop" id="shop_id" x-init="fetchSelectShop()">
                                <option value=""> Select Shop</option>
                            </select>
                        </div>
                        <div class="category-content-gp">
                            <select name="barber_id" class="SelectBarber" id="barber_id" x-init="fetchSelectBarber()">
                                <option value=""> Select Barber</option>
                            </select>
                        </div>
                        <div class="form-row w80">
                            <input type="text" name="from_date" placeholder="From Date" value="{!! isset($firstMonthDay) && $firstMonthDay ? $firstMonthDay : request('from_date') !!}"
                                id="from_date" autocomplete="off">
                            <i data-feather="calendar" id="from_date"></i>
                        </div>
                        <div class="form-row w80">
                            <input type="text" name="to_date" placeholder="To Date" value="{!! $lastMonthDay ? $lastMonthDay : request('to_date') !!}"
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
            
            @include('admin::pages.booking.table')
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
                input: 'checkbox',
                inputValue: 1,
                inputPlaceholder: 'Move to trash.',
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value == 1) {
                        $.ajax({
                            url: `/admin/booking/delete/${id}`,
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
                    } else {
                        $.ajax({
                            url: url,
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
    <script>
        $(document).ready(function() {
            $("#from_date").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#to_date").datepicker("option", "minDate", selected)
                }
            });
            $("#to_date").datepicker({
                minDate: `{{ isset($firstMonthDay) && $firstMonthDay ? $firstMonthDay : 0 }}`,
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: "-50:+0",
                onSelect: function(selected) {
                    $("#from_date").datepicker("option", "maxDate", selected)
                }
            });
        });
    </script>
    <script lang="ts">
        // $(document).ready(function() {
        //     $("#from_date,#to_date").datepicker({
        //         changeYear: true,
        //         gotoCurrent: true,
        //         yearRange: "-1:+1",
        //         dateFormat: "yy-mm-dd",
        //     });
        //     $("#from_date").change(function() {
        //         let str = $(this).val();
        //         $("#to_date").datepicker("option", "minDate", new Date(str));
        //     });
        //     const date = $('#from_date').val();
        //     if (date) {
        //         $("#to_date").datepicker("option", "minDate", new Date(date));
        //     }
        // });
        document.addEventListener('alpine:init', () => {
            Alpine.data('xBookingData', () => ({
                formData: {
                    status: @json(request('payment_status')),
                    shop_id: @json(request('shop_id')),
                    barber_id: @json(request('barber_id')),
                    from_date: @json(request('from_date')),
                    to_date: @json(request('to_date'))
                },
                exportLoading: false,
                init() {
                    var shop = @json($shop);
                    var barber = @json($barber);

                    var option = "<option selected></option>";
                    var selectOptionHTML = $(option).val(shop?.id ? shop.id : null).text(shop?.name ?
                        shop.name : shop?.phone);
                    $('.SelectShop').append(selectOptionHTML).trigger('change');

                    var selectOptionHTMLBarber = $(option).val(barber?.id ? barber.id : null).text(
                        barber?.name ? barber.name : barber?.phone);
                    $('.SelectBarber').append(selectOptionHTMLBarber).trigger('change');

                },
                async excel() {
                    this.exportLoading = true;
                    setTimeout(async () => {
                        await this.fetchData(
                            `/admin/booking/report`,
                            this.formData,
                            (res) => {
                                this.RunExcelJSExport(res);
                                this.exportLoading = false;

                            });
                    }, 500);
                },
                fetchSelectShop() {
                    $(`#shop_id`).select2({
                        placeholder: `Select Shop`,
                        ajax: {
                            url: '{{ route('admin-select-shop') }}',
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
                        this.select2FocusInputSearch();
                    });
                },
                fetchSelectBarber() {
                    $(`#barber_id`).select2({
                        placeholder: `Select barber`,
                        ajax: {
                            url: '{{ route('admin-select-barber') }}',
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
                        this.select2FocusInputSearch();
                    });
                },
                select2FocusInputSearch() {
                    var inputSearch = document.querySelectorAll('.select2-search__field');
                    inputSearch.forEach(val => {
                        val.focus();
                    });
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
                    const dataColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
                        'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'
                    ];
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
                    const worksheet = workbook.addWorksheet("Booking Report");

                    // Add Row Title and formatting
                    const titleTopRow = worksheet.addRow(["របាយការណ៍ការកក់"]);
                    titleTopRow.font = style_font_header;
                    titleTopRow.alignment = align_center;
                    worksheet.mergeCells("A1:" + lastColumn + 1);

                    worksheet.addRow([]);
                    // Add Header Row
                    const header = [
                        "Booking ID",
                        "Booking Date",
                        "Shop",
                        "Barber",
                        "Customer Phone Number",
                        "Point Receving",
                        "Product's name",
                        "Price of Product",
                        "Product Discount",
                        "Product Commision",
                        "Service's name",
                        "Price of Service",
                        "Service  Discount",
                        "Service Comission",
                        "Total Price",
                        "Total Discount",
                        "Total Commision",
                        "Amount Customer Pay To us",
                        "Net Income",
                        "Pay Satus",
                        "Pay Date"
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
                            item?.booking?.invoice_number,
                            this.dateFormatEn(item?.booking?.booking_date,
                                'YYYY-MM-DD H:mm'),
                            item?.booking?.shop?.name,
                            item?.booking?.barber?.name,
                            item?.booking?.customer?.phone,
                            (item?.point ?? 0),
                            this.getNameQty(item?.product, item?.qty),
                            (item?.product ? item?.price : 0),
                            this.productDiscount(item),
                            this.productCommission(item),
                            this.getNameQty(item?.service, item?.qty),
                            (item?.service ? item?.price : 0),
                            this.serviceDiscount(item),
                            this.serviceCommission(item),
                            ((item?.qty ?? 1) * item.price),
                            this.totalDiscount(item),
                            this.totalCommission(item),
                            this.totalDiscountAmountPay(item),
                            this.totalNetPay(item),
                            item?.booking?.payment_status,
                            this.dateFormatEn(item?.booking?.payment_date,
                                'YYYY-MM-DD H:mm'),
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
                        if (colI >= 13 && colI <= 17) {
                            worksheet.getColumn(colI).width = 20;
                        }
                        if (colI == 5 || colI == 18) {
                            worksheet.getColumn(colI).width = 30;
                        }
                        if (colI >= 19 && colI <= 20) {
                            worksheet.getColumn(colI).width = 16;
                        }
                        if (colI >= 8 && colI <= 10) {
                            worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        }
                        if (colI >= 12 && colI <= 19) {
                            worksheet.getColumn(colI).numFmt = '#,##0.00៛;[Red]-#,##0.00៛';
                        }
                    });
                    //endSetWithHeight

                    const footerRowTotal = worksheet.addRow([]);

                    // Generate Excel File with given name
                    const titleExportName = "Booking_Report_Date_" + this.dateFormatEn(moment(),
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
                getNameQty(item, qty) {
                    let name = "";
                    let getQty = qty ? qty : 1;
                    if (item?.name) {
                        name = item?.name + ' ' + '(' + getQty + ')';
                    }
                    return name;
                },
                serviceCommission(item) {
                    let price = 0;
                    if (item.type == "service" && item?.service_commission) {
                        if (item?.service_commission_type == "percent") {
                            price = (item?.price * item?.service_commission / 100);
                        } else if (item?.service_commission_type == "khr") {
                            price = item?.service_commission;
                        }
                    }
                    return price;
                },
                productCommission(item) {
                    let price = 0;
                    if (item.type == "product" && item?.product_commission) {
                        if (item?.product_commission_type == "percent") {
                            price = (item?.price * item?.product_commission / 100);
                        } else if (item?.product_commission_type == "khr") {
                            price = item?.product_commission;
                        }
                    }
                    return price;
                },
                serviceDiscount(item) {
                    let price = 0;
                    if (item.type == "service") {
                        if (item?.service_discount_type == "percent" && item?.service_discount) {
                            price = (item?.price * item?.service_discount / 100);
                        } else if (item?.service_discount_type == "khr" && item?.service_discount) {
                            price = item?.service_discount;
                        }
                    }
                    return price;
                },
                productDiscount(item) {
                    let price = 0;
                    if (item.type == "product") {
                        if (item?.product_discount_type == "percent" && item?.product_discount) {
                            price = (item?.price * item?.product_discount / 100);
                        } else if (item?.product_discount_type == "khr" && item?.product_discount) {
                            price = item?.product_discount;
                        }
                    }
                    return price;
                },
                totalDiscount(item) {
                    var price = 0;
                    let qty = item?.qty ? item.qty : 1;
                    if (item.type == "service" && item?.service_discount) {
                        price = qty * this.serviceDiscount(item);
                    } else if (item.type == "product" && item.product_discount) {
                        price = qty * this.productDiscount(item);
                    }
                    return price;
                },
                totalDiscountAmountPay(item) {
                    let price = item?.price ?? 0;
                    let total_discount = 0;
                    let qty = item?.qty ? item.qty : 1;
                    if (item.type == "service") {
                        if (item?.service_discount_type == "percent" && item?.service_discount) {
                            price = item?.price - (item?.price * item?.service_discount / 100);
                        } else if (item?.service_discount_type == "khr" && item?.service_discount) {
                            price = item?.service_discount > item?.price ? 0 : item?.price - item
                                ?.service_discount;
                        }
                    } else {
                        if (item?.product_discount_type == "percent" && item?.product_discount) {
                            price = item?.price - (item?.price * item?.product_discount / 100);
                        } else if (item?.product_discount_type == "khr" && item?.product_discount) {
                            price = item?.product_discount > item?.price ? 0 : item?.price - item
                                ?.product_discount;
                        }
                    }
                    total_discount = qty * price;
                    return total_discount;
                },
                totalCommission(item) {
                    let price = 0;
                    let qty = item?.qty ? item.qty : 1;
                    if (item.type == "service") {
                        price = qty * this.serviceCommission(item);
                    } else {
                        price = qty * this.productCommission(item);
                    }
                    return price;
                },
                totalNetPay(item) {
                    return this.totalDiscountAmountPay(item) - this.totalCommission(item);
                },
                discountFormat(type, discount) {
                    return discount;
                    if (type == "percent" && discount) {
                        return discount;
                    } else if (type == "percent" && discount) {
                        return 0;
                    }
                    return (type == "percent" && discount ? (discount ? discount : 0) : (discount ?
                        discount?.toFixed(2) : 0)) + ((type == "percent" && discount ? "%" : "") ||
                        (type == "khr" && discount ? "៛" : ""));
                }
            }));
        });
    </script>
@stop
