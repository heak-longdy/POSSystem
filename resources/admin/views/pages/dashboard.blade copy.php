@extends('admin::shared.layout')
@section('layout')
    <div class="dashboard-admin">
        <div class="dashboard-bg"></div>
        <div class="dashboard-wrapper">
            <div class="dashboard-body">
                <div class="filter">
                    <h3>
                        Dashboard
                    </h3>
                </div>
                <div class="dashboard-list">
                    <div class="dashboard-row">
                       <div class="item bg-revenue-usd" s-click-link="#">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Revenue USD</span>
                                    <h3> {{ number_format($totalRevenueUsd) }}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-revenue-khr" s-click-link="#">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Revenue KHR</span>
                                    <h3>{{ number_format($totalRevenueKhr) }}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-revenue-thb" s-click-link="#">

                            <div class="item-body">
                                <div class="left">
                                    <span>Total Revenue THB</span>
                                    <h3>{{ number_format($totalRevenueThb) }}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-expense-usd" s-click-link="#">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Expense USD</span>
                                    <h3>{{ number_format($totalExpenseUsd) }}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>

                        <div class="item bg-expense-khr" s-click-link="#">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Expense KHR</span>
                                    <h3>{{ number_format($totalExpenseKhr) }}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-expense-thb" s-click-link="#">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Expense THB</span>
                                    <h3> {{ number_format($totalExpenseThb) }} </h3>
                                </div>
                                <div class="right">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>View Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-footer"></div>
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
