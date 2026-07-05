@extends('admin::shared.layout')
@section('layout')
    <div class="dashboard-admin" x-data="XDataDashboard">
        <style>
            .select2-dropdown.select2-dropdown--below {
                margin-top: 10px !important;
            }
        </style>
        <div class="dashboard-bg"></div>
        <div class="dashboard-wrapper">
            <div class="dashboard-body">
                <div class="filter">
                    <h3>@lang('dashboard.dashboard')</h3>
                    <form id="FilterForm" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row formRowSelect2">
                            <select name="shop_id" id="shop_id" class="SelectShop" x-init="fetchSelectShop()">
                                <option value="">Select shop...</option>
                            </select>
                        </div>
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
                    {{-- <label class="label">Dashboard 1</label> --}}
                    <div class="dashboard-row">
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>All Booking</span>
                                    <h3>{!! number_format($booking->totalBookingAll, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Products</span>
                                    <h3>{!! number_format($booking->totalProductBooking, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Services</span>
                                    <h3>{!! number_format($booking->totalServiceBooking, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Top Up Request</span>
                                    <h3>{!! number_format($total_top_up, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Pay Liabilities</span>
                                    <h3>{!! number_format($booking->totalPayLiabilities, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Customers</span>
                                    <h3>{{ $customer }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Commission Expenses</span>
                                    <h3>{!! number_format($booking->totalCommissionExpenses, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Company’s Income</span>
                                    <h3>{!! number_format($booking->totalCompanyIncome, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Promotion Expenses</span>
                                    <h3>{!! number_format($booking->totalPromotionExpenses, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <label class="label">Dashboard 2</label> --}}
                    <div class="dashboard-row">
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Pending Payment</span>
                                    <h3>{!! number_format($booking->totalPendingPayment, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Highest Performance Rate</span>
                                    <h3>{!! '0' !!}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Employee’s Balance</span>
                                    <h3>{!! '0' !!}</h3>
                                </div>
                            </div>
                        </div> --}}
                        <div class="item bg-success" s-click-link="">
                            <div class="item-body">
                                <div class="left">
                                    <span>Shops & Brands</span>
                                    <div class="div">
                                        <span>Shop: {{ $shopData }} & Brands: {{ $brand }} & Employees:
                                            {{ $barber }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="charts">

                    <div class="charts-card">
                        <p class="chart-title">Number of customer by day vs. day</p>
                        <div id="bar-chart"></div>
                    </div>

                    <div class="charts-card">
                        <p class="chart-title">Total price, Total commission, Total Promotion and Company 's Income</p>
                        <div id="area-chart"></div>
                    </div>

                    <div class="charts-card" style="width: calc(100% - 25px) !important;margin-right: 0;">
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

            //chartDay
            var titleDay = [];
            var totalDay = [];
            for (let day of Object.values(dataBooking.days)) {
                titleDay.push(day.name);
                totalDay.push(day.total);
            }
            //endChartDay

            //chartTime
            var titleTime = [];
            var totalTime = [];
            var totalLastWeek = [];
            var totalLastWeek2 = [];
            var totalLastWeek3 = [];
            for (let time of dataBooking.times) {
                titleTime.push(time.name);
                totalTime.push(time.total);
                totalLastWeek.push(time.totalLastWeek);
                totalLastWeek2.push(time.totalLastWeek2);
                totalLastWeek3.push(time.totalLastWeek3);
            }
            console.log(totalLastWeek,'total22222');
            console.log(totalLastWeek2,'total22222');
            console.log(totalLastWeek3,'totalLast33333');
            // BAR CHART
            var barChartOptions = {
                series: [{
                    //data: [30, 140, 45, 80, 149, 100, 70, 91, 125]
                    data: totalDay
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
                        columnWidth: '80%',
                        dataLabels: {
                            enabled: false,
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
                    categories: titleDay
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
                    position: 'top',
                    onItemClick: {
                        toggleDataSeries: false
                    }
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
                // yaxis: {
                //     title: {
                //         text: '$ (thousands)'
                //     }
                // },
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
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ["#246dec", "#FF1654", "#f5b74f", "#367952"],
                series: [{
                        name: 'Count of using serivce',
                        data: []
                    }, {
                        name: 'Compare last week',
                        //data: [23, 24, 30, 26, 37, 48, 29, 50]
                        data : totalLastWeek
                    },
                    {
                        name: 'Compare last 2 week',
                        //data: [20, 29, 37, 36, 44, 45, 50, 58]
                        data : totalLastWeek2
                    },
                    {
                        name: 'Compare last 3 week',
                        //data: [20, 29, 45, 36, 64, 45, 70, 80]
                        data : totalLastWeek3
                    }
                ],
                stroke: {
                    width: [0, 4, 4, 4]
                },
                plotOptions: {
                    bar: {
                        barHeight: '100%',
                        distributed: true,
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '40%',
                        dataLabels: {
                            position: 'top'
                        },
                    }
                },
                legend: {
                    position: 'top',
                    offsetX: 0,
                    offsetY: 0,
                    onItemClick: {
                        toggleDataSeries: false
                    }
                },
                xaxis: {
                    categories: titleTime
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
                // legend: {
                //     horizontalAlign: "left",
                //     offsetX: 40
                // }
            };

            var chart = new ApexCharts(document.querySelector("#chart"), options);

            chart.render();
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('XDataDashboard', () => ({
                    init() {
                        var shop = @json($shop);
                        var option = "<option selected></option>";
                        var selectOptionHTML = $(option).val(shop?.id ? shop.id : null).text(shop?.name ?
                            shop.name : shop?.phone);
                        $('.SelectShop').append(selectOptionHTML).trigger('change');
                    },
                    fetchSelectShop() {
                        $('#shop_id').select2({
                            placeholder: `Select shop...`,
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
                                                text: item?.name ? item?.name : item
                                                    ?.phone,
                                                id: item.id
                                            }
                                        })
                                    };
                                }
                            }
                        }).on('select2:open', (e) => {
                            document.querySelector('.select2-search__field').focus();
                        }).on('select2:close', async (eventClose) => {
                            const _id = eventClose.target.value;
                            console.log(_id, 'idiiii');
                        });
                    },
                }));
            });
        </script>
    @endsection
