@extends('admin::shared.layout')
@section('layout')
    <div class="dashboard-admin">
        <div class="dashboard-bg"></div>
        <div class="dashboard-wrapper">
            <div class="dashboard-body">
                <div class="filter">
                    <h3>
                        @lang('dashboard.dashboard')
                    </h3>
                    <form id="FilterForm" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <input type="text" id="fromDate" name="from_date" value="{!! isset($firstMonthDay) && $firstMonthDay ? $firstMonthDay : request('from_date') !!}"
                                placeholder="@lang('dashboard.filter.from_date')" autocomplete="off">
                            <i data-feather="calendar"></i>
                        </div>
                        <div class="form-row">
                            <input type="text" id="toDate" name="to_date" value="{!! $lastMonthDay ? $lastMonthDay : request('to_date') !!}"
                                placeholder="@lang('dashboard.filter.to_date')" autocomplete="off">
                            <i data-feather="calendar" id="toDate"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                        </button>
                        <button type="button" s-click-link="{!! url()->current() !!}">
                            <i data-feather="refresh-ccw"></i>
                        </button>
                    </form>
                </div>
                <div class="dashboard-list">
                    <div class="dashboard-row">
                        <div class="item bg-all-booking" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Total Income</span>
                                    <h3>{!! $total_income !!}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-pending-booking" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Products</span>
                                    <h3>{!! $total_income_product !!}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                        <div class="item bg-completed-booking" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Services</span>
                                    <h3>{!! $total_income_service !!}</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>

                        <div class="item bg-cancel-booking" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Cancel Booking</span>
                                    <h3> 0</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="calendar"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>

                        <div class="item bg-customer" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Customers</span>
                                    <h3> 0</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>

                        <div class="item bg-shop" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Shops</span>
                                    <h3> 0</h3>
                                </div>
                                <div class="right">
                                    <i data-feather="square"></i>
                                </div>
                            </div>
                            <div class="item-footer">
                                <span>Detail</span>
                                <i data-feather="arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="charts">

                    <div class="charts-card">
                        <p class="chart-title">Number of customer by day vs. day</p>
                        <div id="bar-chart"></div>
                        {{-- <div id="chart"></div> --}}
                    </div>

                    <div class="charts-card">
                        <p class="chart-title">Total price, Total commission, Total Promotion and Company 's Income</p>
                        <div id="area-chart"></div>
                    </div>

                    <div class="charts-card">
                        <p class="chart-title">Booking by Time</p>
                        <div id="chart">
                        </div>
                    </div>

                </div>
                <div class="dashboard-footer"></div>
            </div>
        </div>
    @stop
    @section('script')
        {{-- <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
            $(document).ready(function() {
                $("#fromDate,#fromDate").datepicker({
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
        </script> --}}
        <script>
            $(document).ready(function() {
                $("#fromDate").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#toDate").datepicker("option", "minDate", selected)
                    }
                });
                $("#toDate").datepicker({
                    minDate: `{{ isset($firstMonthDay) && $firstMonthDay ? $firstMonthDay : 0 }}`,
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#fromDate").datepicker("option", "maxDate", selected)
                    }
                });
            });
        </script>
        <script>
            var dataBooking = @json($booking);
            console.log(dataBooking, 'dataBooking');

            // BAR CHART
            var barChartOptions = {
                series: [{
                    data: [30, 140, 45, 80, 149, 100, 70, 91, 125]
                }],
                chart: {
                    type: 'bar',
                    toolbar: {
                        show: false
                    },
                },
                colors: [
                    "#246dec",
                    // "#cc3c43",
                    // "#367952",
                    // "#f5b74f",
                    // "#4f35a1"
                ],
                plotOptions: {
                    bar: {
                        barHeight: '100%',
                        distributed: true,
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '40%',
                        dataLabels: {
                            position: 'bottom'
                        },
                    }
                },
                legend: {
                    show: false,
                    horizontalAlign: 'left'
                },
                xaxis: {
                    // categories: ["Laptop", "Phone", "Monitor", "Headphones", "Camera"],
                    categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999],
                }
            };

            var barChart = new ApexCharts(document.querySelector("#bar-chart"), barChartOptions);
            barChart.render();


            var areaChartoptions = {
                series: [{
                    name: 'Total Price',
                    data: dataBooking.totalPrice
                }, {
                    name: 'Total Commission',
                    data: dataBooking.totalCommission
                }, {
                    name: 'Total Promotion',
                    data: dataBooking.totalDiscount
                }, {
                    name: "Company 's Income Price",
                    data: dataBooking.totalIncome
                }],
                colors: [
                    "#246dec",
                    "#cc3c43",
                    "#f5b74f",
                    "#367952",
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4,
                        endingShape: 'rounded'
                    },
                },
                legend: {
                    position: 'top'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: dataBooking.invoiceData,
                },
                yaxis: {
                    title: {
                        text: '$ (thousands)'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "$ " + val + " thousands"
                        }
                    }
                }
            };

            var areachart = new ApexCharts(document.querySelector("#area-chart"), areaChartoptions);
            areachart.render();


            var options = {
                chart: {
                    height: 350,
                    type: "line",
                    stacked: false,
                    toolbar: {
                        show: false
                    },
                },
                dataLabels: {
                    enabled: false
                },
                colors: ["#FF1654", "#f5b74f", "#367952"],
                series: [{
                        name: 'Total Price',
                        data: [23, 24, 30, 26, 37, 48, 29, 50]
                    },
                    {
                        name: 'Total Price2',
                        data: [20, 29, 37, 36, 44, 45, 50, 58]
                    },
                    {
                        name: 'Total Price3',
                        data: [20, 29, 45, 36, 64, 45, 70, 80]
                    }
                ],
                stroke: {
                    width: [4, 4, 4]
                },
                plotOptions: {
                    bar: {
                        barHeight: '100%',
                        distributed: true,
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '40%',
                        dataLabels: {
                            position: 'bottom'
                        },
                    }
                },
                xaxis: {
                    categories: [2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016]
                },
                // yaxis: [{
                //         axisTicks: {
                //             show: true
                //         },
                //         axisBorder: {
                //             show: true,
                //             color: "#FF1654"
                //         },
                //         labels: {
                //             style: {
                //                 colors: "#FF1654"
                //             }
                //         },
                //         title: {
                //             text: "Series A",
                //             style: {
                //                 color: "#FF1654"
                //             }
                //         }
                //     },
                //     {
                //         opposite: true,
                //         axisTicks: {
                //             show: true
                //         },
                //         axisBorder: {
                //             show: true,
                //             color: "#247BA0"
                //         },
                //         labels: {
                //             style: {
                //                 colors: "#247BA0"
                //             }
                //         },
                //         title: {
                //             text: "Series B",
                //             style: {
                //                 color: "#247BA0"
                //             }
                //         }
                //     }
                // ],
                tooltip: {
                    shared: false,
                    intersect: true,
                    x: {
                        show: false
                    }
                },
                legend: {
                    horizontalAlign: "left",
                    offsetX: 40
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart"), options);

            chart.render();
        </script>
    @endsection
