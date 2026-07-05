@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="Report">
        <div class="header">
            <div class="header-wrapper">
                <div class="btn-toggle-sidebar">
                    <span>Expense Detail ({{ $name }})</span>
                </div>
            </div>
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                            <div class="menu-item {!! Request::is('admin/customer/view_detail/income/*') ? 'active' : '' !!}" s-click-link="{!! route('admin-customer-income', $id) !!}">
                                Revenue
                            </div>
                            <div class="menu-item {!! Request::is('admin/customer/view_detail/expense/*') ? 'active' : '' !!}" s-click-link="{!! route('admin-customer-expense', $id) !!}">
                                Expense
                            </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <input type="hidden" name="check" x-model="check">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="from_date" id="fromDate"
                                placeholder="Start Date" autocomplete="off" value="{!! request('from_date') !!}">
                        </div>&nbsp;
                        <div class="mb-3">
                            <input type="text" class="form-control" name="to_date" id="toDate" placeholder="End Date"
                                autocomplete="off" value="{!! request('to_date') !!}">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success" @click="check = 'search'">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                        <button type="submit" class="btn-create bg-success" @click="check = 'excel_export'"
                            :disabled="checkData">
                            <i data-feather="arrow-down-circle"></i>
                            <span>Excel</span>
                        </button>

                        <button class="btn-create bg-primary" id="btn_pdf" @click="check = 'pdf_export'"
                            :disabled="checkData">
                            <i data-feather="arrow-down-circle"></i>
                            <span>PDF</span>
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
                @if ($data->count() > 0)
                    <div class="table-wrapper">
                        <div class="table-header">
                            <div class="row table-row-5">
                                <span>Nº</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Date</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Color</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Amount USD</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Amount KHR</span>
                            </div>
                            <div class="row table-row-15">
                                <span>Amount THB</span>
                            </div>
                            <div class="row table-row-20">
                                Description
                            </div>
                        </div>
                        <div class="table-body">
                            @foreach ($data as $index => $item)
                                <div class="column">
                                    <div class="row table-row-5">
                                        @if (request('check') == 'pdf_export')
                                            <span>{!! $index + 1 !!}</span>
                                        @else
                                            <span>{!! $data?->currentPage() * $data?->perPage() - $data?->perPage() + ($index + 1) !!}</span>
                                        @endif
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{{ Carbon::parse($item->expense_date)->format('d/M/Y') }}</span>
                                    </div>
                                    <div class="row table-row-15" style="display: flex;justify-content: center;">
                                        <span
                                            style="border-radius: 3px;width: 30px;height:30px;background:{{ $item->color }}"></span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-danger">{!! number_format($item->amount_usd) !!} USD</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-danger">{!! number_format($item->amount_khr) !!} KHR</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-danger">{!! number_format($item->amount_thb) !!} THB</span>
                                    </div>
                                    <div class="row table-row-20">
                                        <span>{!! $item->remark ? $item->remark : $item->des !!}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if (request('check') != 'pdf_export' || !request('check'))
                            <div class="table-footer">
                                @include('admin::components.pagination', ['paginate' => $data])
                            </div>
                        @endif
                    </div>
                @else
                    @component('admin::components.empty', [
                        'name' => __('customer.empty.title'),
                    ])
                    @endcomponent
                @endif
            </div>
        </div>
        @if (request('check') == 'pdf_export')
            @include('admin::pages.customer.view_detail.export_each_transaction.pdfExpense')
        @endif
    </div>
@section('script')

    <script>
        function generatePDF() {
            $("#modal-body").removeClass("export_table");
            const report = document.getElementById('export_table');
            const opt = {
                fontSize: 10,
                margin: 0.1,
                filename: 'report.pdf',
                image: {
                    type: 'jpeg',
                    quality: 100
                },
                html2canvas: {
                    scale: 5,
                    logging: true,
                    dpi: 192,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4',
                    orientation: 'landscape',
                    precision: '12'
                }
            };
            html2pdf().set(opt).from(report).save();

            setTimeout(function() {
                $("#export_table").addClass("export_table");
            }, 100);
        }
    </script>

    <script>
        Alpine.data('Report', () => ({
            check: '',
            incheck: '',
            checkData: true,
            init() {
                if (@json($data->count() > 0)) {
                    this.checkData = false;
                }
            },
        }));
    </script>

    <script>
        Alpine.data('Export', () => ({
            incheck: '',
            checkData: true,
            init() {
                if (@json($data->count() > 0)) {
                    this.checkData = false;
                }
            },
        }));
    </script>

    <script>
        $(document).ready(function() {
            $('#fromDate').datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: '-100:+100',
                dateFormat: "yy-mm-dd",
                onSelect: (select) => {
                    $('#toDate').datepicker('option', 'minDate', select)
                }
            });
            $('#toDate').datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: '-100:+100',
                dateFormat: "yy-mm-dd",
                onSelect: (select) => {
                    $('#fromDate').datepicker('option', 'maxDate', select)
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#staticBackdrop').modal('show')
            $(".btn_cancel").click(function() {
                $("#staticBackdrop").modal("hide");
                window.location.href = "{!! url()->current() !!}";
            });
        });
    </script>
@stop
<style>
    .ag-format-container {
        width: 742px;
        margin-left: 20px;
    }



    .ag-courses_box {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: start;
        -ms-flex-align: start;
        align-items: flex-start;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        /*
                    padding: 50px 0; */
    }

    .ag-courses_item {
        -ms-flex-preferred-size: calc(33.33333% - 30px);
        flex-basis: calc(33.33333% - 30px);
        overflow: hidden;
        border-radius: 9px;
        border: solid rgb(206, 205, 205) 1px;
    }

    .ag-courses-item_link {
        display: block;
        padding: 15px 13px;
        background-color: #50246f;
        overflow: hidden;
        position: relative;
    }

    .ag-courses-item_link:hover,
    .ag-courses-item_link:hover .ag-courses-item_date {
        text-decoration: none;
        color: #030303;
    }

    .ag-courses-item_link:hover .ag-courses-item_bg {
        -webkit-transform: scale(10);
        -ms-transform: scale(10);
        transform: scale(10);
    }

    .ag-courses-item_title {
        min-height: 0px;
        margin: 0 0 25px;
        overflow: hidden;
        font-weight: bold;
        font-size: 20px;
        color: #ffffff;
        z-index: 2;
        position: relative;
    }

    .ag-courses-item_date-box {
        font-size: 18px;
        color: #ffffff;
        z-index: 2;
        position: relative;
    }


    .ag-courses-item_bg {
        height: 128px;
        width: 128px;
        background-color: #50246f;

        z-index: 1;
        position: absolute;
        top: -75px;
        right: -75px;

        border-radius: 50%;

        -webkit-transition: all .5s ease;
        -o-transition: all .5s ease;
        transition: all .5s ease;
    }


    table {
        text-align: center;

    }

    .row_header {
        /* border-radius: 9px; */
    }

    table input {
        text-align: center;
        border: none;
    }

    .btn_currency {
        padding: 4px;
        height: 20px;
        background-color: rgb(172, 172, 172);
    }

    .btn_currency:hover {
        background-color: rgb(255, 255, 255);
    }

    .btn_currency.active {
        background-color: rgb(255, 255, 255);
    }


    .btn_currency {
        padding: 4px;
        height: 20px;
        background-color: rgb(172, 172, 172);
    }

    .btn_currency:hover {
        background-color: rgb(255, 255, 255);
    }

    .btn_currency.active {
        background-color: rgb(255, 255, 255);
    }
</style>
@stop
