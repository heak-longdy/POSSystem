@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="Report">
        <div class="header">
            <div class="header-wrapper">
                <div class="btn-toggle-sidebar">
                    <span>Revenue Detail ({{ $name }})</span>
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
                                    {{-- <div class="row table-row-15">
                                        <span>{!! colorName($item?->color) ?? null !!}</span>
                                    </div> --}}
                                    <div class="row table-row-15" style="display: flex;justify-content: center;">
                                        <span
                                            style="border-radius: 3px;width: 30px;height:30px;background:{{ $item->color }}"></span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-success">{!! number_format($item->amount_usd) !!} USD</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-success">{!! number_format($item->amount_khr) !!} KHR</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span class="text-success">{!! number_format($item->amount_thb) !!} THB</span>
                                    </div>
                                    <div class="row table-row-20">
                                        <span>{!! $item->remark ?? null !!}</span>
                                        <span>{!! $item->des ?? null !!}</span>
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
            @include('admin::pages.customer.view_detail.export_each_transaction.pdf')
        @endif
    </div>
@section('script')
    {{-- <script lang="ts">
        function getContentInPDF() {
            // access form elements one by one
            // var name = document.getElementById('name').value;
            // var comment = document.getElementById('comment').value;
            // create a single html element by adding form data
            var element = document.createElement('div');
            const opt = {
                fontSize: 10,
                margin: 0.1,
                filename: 'revenue.pdf',
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
            element.innerHTML = '<h1>Form Data</h1>' +
                '<p>Name: ' + 424 + '</p>' +
                '<p>Comment: ' + 24 + '</p>';
            element.innerHTML = `@include('admin::pages.customer.view_detail.export_each_transaction.pdf', ['data' => $data])`;
            // create a new pdf using the form element
            html2pdf().set(opt).from(element).save();
        }
        $(document).ready(function() {
            $('.btn_pdf').on('click', function() {
                getContentInPDF();
                return false;
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var id = {{ request('id') }};
                console.log(id, '4242424242424242444424');

                // ajax
                $.ajax({
                    type: "GET",
                    url: `{{ route('admin-customer-pdf') }}/${id}`,
                    data: {
                        id: id,

                    },
                    dataType: 'json',
                    success: function(res) {
                        console.log(res, 'resfsfsfsfs');

                        // console.log(res)
                        // $('#display').modal('show')
                        // $('#No').text('1');
                        // $('#Date').val(res.revenue_date);
                        // $('#IDRevenue').text('CASH1');
                        // $('#note').val(res.des);
                        // $('#Amount_USD').val(res.amount_usd);
                        // $('#Amount_KHR').val(res.amount_khr);
                        // $('#Amount_THB').val(res.amount_thb);

                        // $('#amount_usd').val(res.amount_usd);
                        // $('#amount_khr').val(res.amount_khr);
                        // $('#amount_thb').val(res.amount_thb);

                        // $("#table-tran").removeClass("table-tran");
                        // const report = document.getElementById('table-tran');
                        // const opt = {
                        //     fontSize: 10,
                        //     margin: 0.1,
                        //     filename: 'revenue.pdf',
                        //     image: {
                        //         type: 'jpeg',
                        //         quality: 100
                        //     },
                        //     html2canvas: {
                        //         scale: 5,
                        //         logging: true,
                        //         dpi: 192,
                        //         letterRendering: true
                        //     },
                        //     jsPDF: {
                        //         unit: 'in',
                        //         format: 'A4',
                        //         orientation: 'landscape',
                        //         precision: '12'
                        //     }
                        // };
                        // html2pdf().set(opt).from(report).save();
                        // setTimeout(function() {
                        //     $("#table-tran").addClass("table-tran");
                        // }, 100);
                    }
                });
            });

        });
    </script> --}}
    <script>
        Alpine.data('Report', () => ({
            check: '',
            checkData: true,
            init() {
                if (@json($data->count() > 0)) {
                    this.checkData = false;
                }
                console.log('hiiiiiii');
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
@stop
