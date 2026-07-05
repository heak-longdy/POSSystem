@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => ''])
    <style>
        .dashboard-admin {
            padding: 0 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .dashboard {
            color: rgb(42, 53, 71);
            transition-duration: 300ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-delay: 0ms;
            transition-behavior: normal;
            transition-property: box-shadow;
            border-radius: 7px;
            box-shadow: none;
            overflow-x: hidden;
            overflow-y: hidden;
            width: 70%;
            background-color: rgb(236, 242, 255);
            position: relative;
            padding: 0 30px;
            font-size: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 150px;
        }

        .dashboard>p {
            margin: 0;
            font-size: 24px;
        }

        .dashboard>img {
            width: 180px;
            height: 180px;
            object-fit: contain;
            position: absolute;
            right: 0;
            bottom: -50px;
        }

        .stats-container {
            display: flex;
            gap: 20px;
            width: 70%;
            justify-content: space-between;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid #e0e0e0;
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #666;
            font-weight: 500;
        }

        .stat-card .amount {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .stat-card.usd .amount {
            color: #2e7d32;
        }

        .stat-card.khr .amount {
            color: #1565c0;
        }
    </style>
    <div class="content-wrapper" id="app" x-data="xIndex">
        <div class="dashboard-admin">

            <div class="dashboard">
                <p>Welcome to dashboard</p>
                <img src="{{asset('admin-public/logo/welcome-bg2.webp')}}" />
            </div>

            <div class="stats-container">
                <div class="stat-card usd">
                    <h3>Total Customer Paid (USD)</h3>
                    <p class="amount">$ {{ number_format($totalCustomerPaidUsd ?? 0, 2) }}</p>
                </div>
                <div class="stat-card khr">
                    <h3>Total Customer Paid (KHR)</h3>
                    <p class="amount">៛ {{ number_format($totalCustomerPaidKhr ?? 0, 0) }}</p>
                </div>
            </div>

        </div>
    </div>
@stop
@section('script')
    {{-- <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> --}}
    <script>
        $(document).ready(function() {
            $("#fromDate,#toDate").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
            @if (!request('from_date') && !request('to_date'))
                $("#toDate").datepicker('setDate', 'today');
                $("#toDate").datepicker("option", "minDate", new Date());
            @endif
            $("#fromDate").change(function() {
                let str = $(this).val();
                $("#toDate").datepicker("option", "minDate", new Date(str));
            });
        });
    </script>
@endsection
