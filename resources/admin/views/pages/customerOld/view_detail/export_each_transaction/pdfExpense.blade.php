<div class="form-body-pdf">
    <style>
        .form-body-pdf {
            display: none;
        }

        .pdfBGColor {
            background-color: #50246f;
            text-align: center;
            color: #ffffff;
        }

        .pdfBGColorFontText {
            background-color: #50246f;
            text-align: center;
            color: #ffffff;
            font-family: 'Khmer OS Battambang';
        }

        .pdfBorder {
            border-left: 1px solid #50246f;
            border-bottom: 1px solid #50246f;
            text-align: center;
        }
      	
      	thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
        }
      
    </style>
    <table class="table" id="tableCustomerViewDetailExpense">
        <thead class="header_table">
            <tr>
                <th style="width: 100px"></th>
                <th style="width: 150px"></th>
                <th style="width: 200px"></th>
                <th style="width: 150px"></th>
                <th style="width: 120px" class="pdfBGColor">Total</th>
                <th style="width: 140px" class="pdfBGColor">{{ number_format($data->sum('amount_usd')) }}</th>
                <th style="width: 250px" class="pdfBGColor">{{ number_format($data->sum('amount_khr')) }}</th>
                <th style="width: 250px" class="pdfBGColor">{{ number_format($data->sum('amount_thb')) }}</th>
                <th style="width: 250px" class="pdfBGColor">Remark</th>

            </tr>
            <tr>
                <th class="pdfBGColorFontText">លេខរៀង</th>
                <th class="pdfBGColorFontText">កាលបរិច្ឆេទ</th>
                <th class="pdfBGColorFontText">លេខរៀងចំណាយ</th>
                <th class="pdfBGColorFontText">ឈ្មោះអ្នកប្រើ</th>
                <th class="pdfBGColorFontText">រូបិយប័ណ្ណ</th>
                <th class="pdfBGColorFontText">ចំនួន</th>
                <th class="pdfBGColorFontText">ចំនួន</th>
                <th class="pdfBGColorFontText">ចំនួន</th>
                <th class="pdfBGColorFontText">កត់ចំណាំ</th>
            </tr>
            <tr>
                <th class="pdfBGColor">No</th>
                <th class="pdfBGColor">Date</th>
                <th class="pdfBGColorFontText">IDExpense</th>
                <th class="pdfBGColorFontText">User Name</th>
                <th class="pdfBGColorFontText">Currency</th>
                <th class="pdfBGColorFontText">Amount USD</th>
                <th class="pdfBGColorFontText">Amount KHR</th>
                <th class="pdfBGColorFontText">Amount THB</th>
                <th class="pdfBGColorFontText">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td class="pdfBorder">
                        {{ $index + 1 }}</td>
                    <td class="pdfBorder">
                        {{ $item->expense_date }}</td>
                    <td class="pdfBorder">
                        CASH{{ $item->expense_id }}</td>
                    <td class="pdfBorder">
                        {{ $item->expense->user->name ?? '' }}</td>
                    <td class="pdfBorder">
                        <?php
                        $stringCurreny = [];
                        $item->amount_usd ? $stringCurreny[] = "USD" : '';
                        $item->amount_khr ? $stringCurreny[] = "KHR" : '';
                        $item->amount_thb ? $stringCurreny[] = "THB" : '';
                        ?>
                        {{ implode(", ", $stringCurreny); }}
                    </td>
                    <td class="pdfBorder"> {!! number_format($item->amount_usd) !!}</td>
                    <td class="pdfBorder"> {!! number_format($item->amount_khr) !!}</td>
                    <td class="pdfBorder"> {!! number_format($item->amount_thb) !!}</td>
                    <td style="border-right:1px solid #50246f;" class="pdfBorder">
                        {{ $item->remark ? $item->remark : $item->des }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        const report = document.getElementById('tableCustomerViewDetailExpense');
        const opt = {
            fontSize: 10,
            margin: 0.1,
            filename: 'expense.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 5,
                logging: true,
                dpi: 192,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'landscape'
            }
        };
        html2pdf().set(opt).from(report).save().then((res) => {
            window.location.href = '{{ route('admin-customer-expense', request('id')) }}';
        });
    });
</script>
