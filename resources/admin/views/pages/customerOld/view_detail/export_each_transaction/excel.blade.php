<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table class="export_table" id="export_table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total</th>
                <th>{{ number_format($data->sum('amount_usd')) }}</th>
                <th>{{ number_format($data->sum('amount_khr')) }}</th>
                <th>{{ number_format($data->sum('amount_thb')) }}</th>
                <th>Remark</th>
            </tr>
            <tr>
                <th>លេខរៀង</th>
                <th>កាលបរិច្ឆេទ</th>
                <th>{{ Request::segment(4) == 'income' ? 'លេខរៀងចំណូល' : 'លេខរៀងចំណាយ' }}</th>
                <th>ឈ្មោះអ្នកប្រើ</th>
                <th>រូបិយប័ណ្ណ</th>
                <th>ចំនួន</th>
                <th>ចំនួន</th>
                <th>ចំនួន</th>
                <th>កត់ចំណាំ</th>
            </tr>
            <tr>
                <th>No</th>
                <th>Date</th>
                <th>{{ Request::segment(4) == 'income' ? 'IDRevenue' : 'IDExpense' }}</th>
                <th>User Name</th>
                <th>Currency</th>
                <th>Amount USD</th>
                <th>Amount KHR</th>
                <th>Amount THB</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->expense_date }}</td>
                    <td>CASH{{ Request::segment(4) == 'income' ? $item->revenue_id : $item->expense_id }}</td>
                    <td>{{ Request::segment(4) == 'income' ? ($item->revenue->user->name ?? '') : ($item->expense->user->name ?? '') }}</td>
                    <td>
                        <?php
                        $stringCurreny = [];
                        $item->amount_usd ? ($stringCurreny[] = 'USD') : '';
                        $item->amount_khr ? ($stringCurreny[] = 'KHR') : '';
                        $item->amount_thb ? ($stringCurreny[] = 'THB') : '';
                        ?>
                        {{ implode(', ', $stringCurreny) }}
                    </td>
                    <td>{!! number_format($item->amount_usd) !!}</td>
                    <td>{!! number_format($item->amount_khr) !!}</td>
                    <td>{!! number_format($item->amount_thb) !!}</td>
                    <td>{{ $item->remark ? $item->remark : $item->des }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
