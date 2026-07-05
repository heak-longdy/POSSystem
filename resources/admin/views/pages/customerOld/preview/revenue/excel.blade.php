<div class="modal fade" id="staticBackdrop" data-mdb-backdrop="static" data-mdb-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl  modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                {{-- <img src="{{ asset('images/logobbn.png') }}" alt="" width="250px"/> --}}
                <span></span>
                <button type="button" class="btn-close btn_cancel" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="form-body" id="form-body">
                    <div class="container-fluid" id="body">
                        <div class="d-flex justify-content-center">
                            <div class="p-2">
                                <div class="title">
                                    <h2>Revenue Report</h2>
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <table>
                                <thead class="header_table">
                                    <tr>
                                        <th style="background-color:white"></th>
                                        <th style="background-color:white"></th>
                                        <th style="background-color:white"></th>
                                        <th style="background-color:white"></th>
                                        <th>Total</th>
                                        @if (Route::current()->getName() == 'admin-customer-income')
                                            <th>USD</th>
                                        @elseif (Route::current()->getName() == 'admin-customer-khr')
                                            <th>KHR</th>
                                        @else
                                            <th>BHT</th>
                                        @endif
                                        @if (Route::current()->getName() == 'admin-customer-income')
                                            <th style="border-right:1px solid #50246f;">
                                                {{ number_format($data->sum('amount_usd')) }} USD</th>
                                        @elseif (Route::current()->getName() == 'admin-customer-khr')
                                            <th style="border-right:1px solid #50246f;">
                                                {{ number_format($data->sum('amount_khr')) }} KHR</th>
                                        @else
                                            <th style="border-right:1px solid #50246f;">
                                                {{ number_format($data->sum('amount_thb')) }} THB</th>
                                        @endif

                                    </tr>
                                    <tr>
                                        <th style="border-left:1px solid #50246f;">លេខរៀង</th>
                                        <th>កាលបរិច្ឆេទ</th>
                                        <th>លេខរៀងចំណូល</th>
                                        <th>អ្នកប្រើប្រាស់</th>
                                        <th>រូបិយប័ណ្ណ</th>
                                        <th>ចំនួន</th>
                                        <th style="border-right:1px solid #50246f;">កត់ចំណាំ</th>
                                    </tr>
                                    <tr>
                                        <th style="border-left:1px solid #50246f;">No</th>
                                        <th>Date</th>
                                        <th>IDRevenue</th>
                                        <th>User Name</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th style="border-right:1px solid #50246f;">Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revenue as $index => $rev)
                                        @foreach ($data as $index => $item)
                                            @if ($rev->transaction_date == $item->revenue_date)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item->revenue_date }}</td>
                                                    <td>CASH{{ $index + 1 }}</td>
                                                    <td>{{ $name }}</td>
                                                    @if (Route::current()->getName() == 'admin-customer-income')
                                                        <td>USD</td>
                                                    @elseif (Route::current()->getName() == 'admin-customer-khr')
                                                        <td>KHR</td>
                                                    @else
                                                        <td>BHT</td>
                                                    @endif

                                                    @if (Route::current()->getName() == 'admin-customer-income')
                                                        <td>{{ $item->amount_usd }} USD</td>
                                                    @elseif (Route::current()->getName() == 'admin-customer-khr')
                                                        <td>{{ $item->amount_khr }} KHR</td>
                                                    @else
                                                        <td>{{ $item->amount_thb }} THB</td>
                                                    @endif
                                                    <td>{{ $item->remark }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <style>
                    @media all {
                        .modal-body {
                            color: #50246f;
                            /* display: none; */
                        }
                    }

                    @media print {
                        .page-break {
                            display: block;
                            page-break-inside: auto;
                        }
                    }
                </style>
            </div>
            <div class="modal_footer" style="padding-right:80px;padding-bottom:30px">
                <div class="d-flex flex-row-reverse bd-highlight">
                    <div class="p-2 bd-highlight">
                        <button type="button" id="table_to_excel" class="btn-create excel-btn"
                            onclick="htmlTableToExcel('xlsx')">
                            <i data-feather="download"></i>
                            <span>Save</span>
                        </button>



                        {{-- 
                    <button type="button" class="btn-create excel-btn"  s-click-link="{!!route('admin-customer-export_excel')!!}">
                        <i data-feather="download"></i>
                        <span>Save</span>
                    </button> --}}

                    </div>
                    <div class="p-2 bd-highlight">
                        <button id="btn_cancel" class="btn-create bg-danger btn_cancel">
                            <i data-feather="x-circle"></i>
                            <span>Cancel</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


<script>
    function htmlTableToExcel(type) {
        //   var data = document.getElementById('contents');
        //   var excelFile = XLSX.utils.table_to_book(data, {sheet: "sheet1"});
        //   XLSX.write(excelFile, { bookType: type, bookSST: true, type: 'base64' });
        //   XLSX.writeFile(excelFile, 'report.' + type);
        //   var preserveColors = (table.hasClass('form-body'));        
        const total = new Date();
        $("#form-body").table2excel({
            exclude: ".form-body",
            name: "Revenue",
            filename: "Revenue " + total.toDateString() + ".xls",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: true

        });
    }
</script>

<style>
    .excel-btn {
        background-color: #50246f;
    }

    .header_table {
        height: 20px;
    }

    #transaction {
        text-transform: uppercase;
        font-weight: bold
    }

    table {
        /* border: 1px #a39485 solid; */
        font-size: .9em;
        /* box-shadow: 0 2px 5px rgba(0,0,0,.25); */
        width: 100%;
        border-collapse: collapse;

        overflow: hidden;
    }

    table thead tr th {
        text-align: center
    }

    th {
        text-align: left;
    }

    thead {
        font-weight: bold;
        color: #fff;
        background-color: #50246f;
    }

    td,
    th {
        padding: 9px;
        vertical-align: middle;
    }

    td {
        border: 1px solid #50246f;
        background: #fff;
    }

    a {
        color: #73685d;
    }

    @media all and (max-width: 768px) {

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        th {
            text-align: center;
        }

        table {
            position: relative;
            padding-bottom: 0;
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, .2);
        }

        thead {
            float: left;
            white-space: nowrap;
        }

        tbody {
            overflow-x: auto;
            overflow-y: hidden;
            position: relative;
            white-space: nowrap;
        }

        tr {
            display: inline-block;
            vertical-align: top;
        }

        th {
            border-bottom: 1px solid #a39485;
        }

        td {
            border-bottom: 1px solid #e5e5e5;
        }


    }
</style>
