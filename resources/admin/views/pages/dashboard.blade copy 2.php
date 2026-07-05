@extends('admin::shared.layout')
@section('layout')
    <style>
        /* body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
            margin: 0;
            flex-direction: column;
            grid-gap: 30px;
        } */

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
        }

        .header {
            margin-bottom: 20px;
        }

        .header h3 {
            margin: 0;
            color: #333;
        }

        .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .increase {
            color: #4CAF50;
            font-weight: bold;
        }

        .increase span {
            font-weight: normal;
            color: #aaa;
        }

        .chart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .chart {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: conic-gradient(#4285F4 0% 75%, #E0E0E0 75% 100%);
            margin-bottom: 10px;
        }

        .labels {
            display: flex;
            justify-content: space-around;
            width: 100%;
        }

        .dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .blue {
            background-color: #4285F4;
        }

        .light-blue {
            background-color: #E0E0E0;
        }

        /* 2 */
        .notification-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-content {
            display: flex;
            align-items: center;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            margin-right: 15px;
        }

        .text-content h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .text-content p {
            margin: 5px 0 0 0;
            color: #999;
            font-size: 12px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .avatars {
            display: flex;
            align-items: center;
        }

        .small-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .comment-icon img {
            width: 25px;
            height: 25px;
        }

        /* 3 */
        .welcome-card {
            background-color: #f1f7ff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
            width: 700px;
            max-width: 100%;
        }

        .left-section {
            flex: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-info p {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .stats {
            display: flex;
            justify-content: space-between;
        }

        .stat {
            text-align: left;
        }

        .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }

        .percentage {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }

        .stat p {
            margin: 5px 0 0 0;
            color: #999;
            font-size: 14px;
        }

        .right-section {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .illustration {
            width: 150px;
            height: auto;
        }
    </style>
    @include('admin::shared.header', ['header_name' => ''])
    <div class="dashboard-admin">
        {{-- <div class="dashboard-bg"></div> --}}
        <div class="dashboard-wrapper">
            <div class="dashboard-body">
                
                {{-- <div class="filter">
                    <h3>
                        Dashboard
                    </h3>
                </div> --}}
                <div class="dashboard-list">
                    <div class="dashboard-row">
                        <h1>Welcome to systme !</h1>
                        {{-- <div class="card">
                            <div class="header">
                                <h3>Yearly Breakup</h3>
                                <p class="amount">$36,358</p>
                                <p class="increase">+9% <span>last year</span></p>
                            </div>
                            <div class="chart-container">
                                <div class="chart"></div>
                                <div class="labels">
                                    <span class="dot blue"></span> 2023
                                    <span class="dot light-blue"></span> 2023
                                </div>
                            </div>
                        </div>
                        <div class="notification-card">
                            <div class="card-content">
                                <img src="avatar.png" alt="Avatar" class="avatar">
                                <div class="text-content">
                                    <h4>Super awesome, Vue coming soon!</h4>
                                    <p>22 March, 2023</p>
                                </div>
                            </div>
                            <div class="footer">
                                <div class="avatars">
                                    <img src="avatar1.png" alt="Avatar 1" class="small-avatar">
                                    <img src="avatar2.png" alt="Avatar 2" class="small-avatar">
                                    <img src="avatar3.png" alt="Avatar 3" class="small-avatar">
                                    <img src="avatar4.png" alt="Avatar 4" class="small-avatar">
                                </div>
                                <div class="comment-icon">
                                    <img src="comment.png" alt="Comment Icon">
                                </div>
                            </div>
                        </div>
                        <div class="notification-card">
                            <div class="card-content">
                                <img src="https://via.placeholder.com/50" alt="Avatar" class="avatar">
                                <div class="text-content">
                                    <h4>Super awesome, Vue coming soon!</h4>
                                    <p>22 March, 2023</p>
                                </div>
                            </div>
                            <div class="footer">
                                <div class="avatars">
                                    <img src="https://via.placeholder.com/30" alt="Avatar 1" class="small-avatar">
                                    <img src="https://via.placeholder.com/30" alt="Avatar 2" class="small-avatar">
                                    <img src="https://via.placeholder.com/30" alt="Avatar 3" class="small-avatar">
                                    <img src="https://via.placeholder.com/30" alt="Avatar 4" class="small-avatar">
                                </div>
                                <div class="comment-icon">
                                    <img src="https://via.placeholder.com/25" alt="Comment Icon">
                                </div>
                            </div>
                        </div> --}}
                        {{-- <div class="welcome-card">
                            <div class="left-section">
                                <div class="user-info">
                                    <img src="https://via.placeholder.com/40" alt="User Avatar" class="user-avatar">
                                    <p>Welcome back Mathew Anderson!</p>
                                </div>
                                <div class="stats">
                                    <div class="stat">
                                        <p class="amount">$2,340</p>
                                        <p>Today's Sales</p>
                                    </div>
                                    <div class="stat">
                                        <p class="percentage">35%</p>
                                        <p>Performance</p>
                                    </div>
                                </div>
                            </div>
                            <div class="right-section">
                                <img src="https://modernize-nextjs.adminmart.com/images/backgrounds/welcome-bg.svg " alt="Person working" class="illustration">
                            </div>
                        </div> --}}
                    </div>
                </div>
                <div class="dashboard-footer"></div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
