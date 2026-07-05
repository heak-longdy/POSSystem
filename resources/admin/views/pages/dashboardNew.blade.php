@extends('admin::shared.layout')
@section('layout')
    {{-- <div class="header">
        @include('admin::shared.header', ['header_name' => 'Dashboard Management'])
    </div> --}}
    <div class="dashboard-admin" x-data="XDataDashboard">
        <style>
            .select2-dropdown.select2-dropdown--below {
                margin-top: 10px !important;
            }
        </style>

        {{-- <div class="dashboard-bg"></div> --}}
        <div class="dashboard-wrapper content-body" id="contentBody">
            <div class="dashboard-body">
                {{-- <div class="filter">
                    <h3></h3>
                    <form id="FilterForm" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row formRowSelect2">
                            <select name="shop_id" id="shop_id" class="SelectShop" x-init="fetchSelectShop('shop_id')">
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
                </div> --}}
                <div class="dashboard-list">
                    <div class="dashboard-row">
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>All Booking&nbsp;(Total Price)</span>
                                    <h3>{!! number_format($booking->totalBookingAll, 2) !!}&nbsp;KHR</h3>
                                    <p>{{ $booking?->totalCountBookingAll }}&nbsp;(Number of Booking)</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Products&nbsp;(Total Price)</span>
                                    <h3>{!! number_format($booking->totalProductBooking, 2) !!}&nbsp;KHR</h3>
                                    <p>{{ $booking?->totalCountProductBooking }}&nbsp;(Number of Booking)</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Services&nbsp;(Total Price)</span>
                                    <h3>{!! number_format($booking->totalServiceBooking, 2) !!}&nbsp;KHR</h3>
                                    <p>{{ $booking?->totalCountServiceBooking }}&nbsp;(Number of Booking)</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Top Up Request</span>
                                    <h3>{!! isset($total_top_up) && $total_top_up
                                        ? number_format($total_top_up / ($setting->rate ? $setting->rate : 0), 2)
                                        : 0 !!}&nbsp;USD</h3>
                                    <p>{!! number_format($total_top_up, 2) !!}&nbsp;KHR</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Pay Liabilities</span>
                                    <h3>{!! number_format($totalPayLiabilities, 2) !!}&nbsp;KHR</h3>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Customers</span>
                                    <h3>{{ isset($booking->totalCustomerCount) ? $booking->totalCustomerCount : 0 }}&nbsp;User
                                    </h3>
                                    {{-- <p>
                                        New
                                        customer&nbsp;<span>{{ isset($booking?->totalCustomerPhoneNumber) && $booking?->totalCustomerPhoneNumber ? number_format(($booking?->totalCustomerPhoneNumber / $booking->totalCustomerCount) * 100, 2) : 0 }}%</span>
                                        &nbsp;&&nbsp;Non-Phone
                                        Number&nbsp;<span>{{ isset($booking?->totalCustomerEmptyNumberCount) && $booking?->totalCustomerEmptyNumberCount ? number_format(($booking?->totalCustomerEmptyNumberCount / $booking->totalCustomerCount) * 100, 2) : 0 }}%</span>
                                    </p> --}}
                                    <p>Non-Phone
                                        Number&nbsp;<span>{{ isset($booking?->totalCustomerEmptyNumberCount) && $booking?->totalCustomerEmptyNumberCount ? number_format(($booking?->totalCustomerEmptyNumberCount / $booking->totalCustomerCount) * 100, 2) : 0 }}%</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Commission Expenses</span>
                                    <h3>{!! number_format($booking->totalCommissionExpenses, 2) !!}&nbsp;KHR</h3>
                                    <p>{!! number_format($booking->totalCommissionExpensesDiscount, 2) !!}&nbsp;% of total price</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Company’s Income</span>
                                    <h3>{!! number_format($booking->totalCompanyIncome, 2) !!}&nbsp;KHR</h3>
                                    <p>{!! number_format($booking->totalCompanyIncomeDiscount, 2) !!}&nbsp;% of total price</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Promotion Expenses</span>
                                    <h3>{!! number_format($booking->totalPromotionExpenses, 2) !!}&nbsp;KHR</h3>
                                    <p>{!! number_format($booking->totalPromotionExpensesDiscount, 2) !!}&nbsp;% of total price</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <label class="label">Dashboard&nbsp;2</label> --}}
                    <div class="dashboard-row">
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Pending Payment</span>
                                    <div class="div">
                                        (<span>{{ $pendingPayment->totalPendingPayment2DaysAgo->percent }}%&nbsp;more than 2
                                            day & {{ $pendingPayment->totalPendingPayment3DaysAgo->percent }}%&nbsp;more
                                            than 3days</span>)
                                    </div>
                                    <h3>{!! isset($pendingPayment->totalPendingPayment->total) && $pendingPayment->totalPendingPayment->total
                                        ? number_format($pendingPayment->totalPendingPayment->total / ($setting->rate ? $setting->rate : 0), 2)
                                        : 0 !!}&nbsp;USD</h3>
                                    <p>{!! number_format($pendingPayment->totalPendingPayment->total, 2) !!}&nbsp;KHR</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Highest Performance Rate&nbsp;(Total Price)</span>
                                    <div class="div">
                                        <span>{{ Carbon\Carbon::parse($highest_performance_rate->bookingDate)->isoFormat('Y/MM/DD') }}</span>
                                    </div>
                                    <h3>{!! isset($highest_performance_rate->totalPrice) && $highest_performance_rate->totalPrice
                                        ? number_format($highest_performance_rate->totalPrice / ($setting->rate ? $setting->rate : 0), 2)
                                        : 0 !!}&nbsp;USD</h3>
                                    <p>{!! number_format($highest_performance_rate->totalPrice, 2) !!}&nbsp;KHR</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Highest Performance Rate&nbsp;(Total Income)</span>
                                    <div class="div">
                                        <span>{{ Carbon\Carbon::parse($highest_performance_rate->bookingDate)->isoFormat('Y/MM/DD') }}</span>
                                    </div>
                                    <h3>{!! isset($highest_performance_rate->total_income) && $highest_performance_rate->total_income
                                        ? number_format($highest_performance_rate->total_income / ($setting->rate ? $setting->rate : 0), 2)
                                        : 0 !!}&nbsp;USD</h3>
                                    <p>{!! number_format($highest_performance_rate->total_income, 2) !!}&nbsp;KHR</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
                            <div class="item-body">
                                <div class="left">
                                    <span>Employee’s Balance</span>
                                    <h3>{!! isset($employee_balance) && $employee_balance
                                        ? number_format($employee_balance / ($setting->rate ? $setting->rate : 0), 2)
                                        : 0 !!}&nbsp;USD</h3>
                                    <p>{!! number_format($employee_balance, 2) !!}&nbsp;KHR</p>
                                </div>
                            </div>
                        </div>
                        <div class="item bg-success">
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

                {{-- <div class="filter">
                    <h3></h3>
                    <form id="FilterForm" action="{!! url()->current() !!}" method="GET" style="flex: 1;">
                        <div class="form-row formRowSelect2">
                            <select name="chart_shop_id" id="chart_shop_id" class="SelectChartShop" x-init="fetchSelectShop('chart_shop_id')">
                                <option value="">Select shop...</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <input type="text" id="chartFromDate" name="chart_from_date"
                                value="{!! $chartFromDate ? $chartFromDate : request('chart_from_date') !!}" placeholder="Select from date ..." autocomplete="off">
                            <i data-feather="calendar"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                        </button>
                        <button type="button" s-click-link="{!! url()->current() !!}">
                            <i data-feather="refresh-ccw"></i>
                        </button>
                    </form>
                </div> --}}


                {{-- <div class="charts">
                    <div class="charts-card">
                        <p class="chart-title">Number of customer by day vs. day</p>
                        <div id="bar-chart"></div>
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
                </div> --}}
                {{-- <div class="dashboard-footer"></div> --}}
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

                $("#chartFromDate").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#chartToDate").datepicker("option", "minDate", selected)
                    }
                });
                $("#chartToDate").datepicker({
                    minDate: `{{ isset($firstMonthDay) && $firstMonthDay ? $firstMonthDay : 0 }}`,
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    gotoCurrent: true,
                    yearRange: "-50:+0",
                    onSelect: function(selected) {
                        $("#chartFromDate").datepicker("option", "maxDate", selected)
                    }
                });

                // $("#chartFromDate").datepicker({
                //     dateFormat: 'yy-mm-dd',
                //     changeYear: true,
                //     changeMonth: true,
                //     gotoCurrent: true,
                //     yearRange: "-50:+0",
                // });

            });
        </script>
        {{-- <script>
            var dataBooking = @json($booking);
            var chartCustomerDayByDay = @json($chartCustomerDayByDay);
            var chartShopBooking = @json($chartShopBooking);
            var chartBookingByTime = @json($chartBookingByTime);

            //chartDay
            var titleDay = [];
            var totalDay = [];
            for (let day of Object.values(chartCustomerDayByDay)) {
                titleDay.push(day.name);
                totalDay.push(day.totalCustomer);
            }
            //endChartDay

            //chartShopBooking
            var ShopID = [];
            var totalPrice = [];
            var totalDiscount = [];
            var totalCommission = [];
            var totalIncome = [];
            for (let item of chartShopBooking) {
                let companyIncomePrice = item.totalPrice - (item.totalDiscount + item.totalCommission);
                ShopID.push(item.shop.name);
                totalPrice.push(item.totalPrice);
                totalDiscount.push(item.totalDiscount);
                totalCommission.push(item.totalCommission);
                totalIncome.push(companyIncomePrice);

            }
            //endChartShop

            //chartBookingTime
            // var titleTime = [];
            // var totalLastWeek = [];
            // var totalLastWeek2 = [];
            // var totalLastWeek3 = [];
            // for (let time of chartBookingByTime) {
            //     titleTime.push(time.name);
            //     totalLastWeek.push(time.totalLastWeek);
            //     totalLastWeek2.push(time.totalLastWeek2);
            //     totalLastWeek3.push(time.totalLastWeek3);
            // }

            // BAR CHART
            var barChartOptions = {
                series: [{
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
                    categories: titleDay
                }
            };

            var barChart = new ApexCharts(document.querySelector("#bar-chart"), barChartOptions);
            barChart.render();


            var areaChartoptions = {
                series: [{
                    name: 'Total Price',
                    data: totalPrice
                }, {
                    name: 'Total Commission',
                    data: totalCommission
                }, {
                    name: 'Total Promotion',
                    data: totalDiscount
                }, {
                    name: "Company 's Income Price",
                    data: totalIncome
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
                    categories: ShopID,
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "KHR " + (val ? val : 0).toFixed(2)
                        }
                    }
                }
            };

            var areachart = new ApexCharts(document.querySelector("#area-chart"), areaChartoptions);
            areachart.render();

            //bookingTitem
            // var options = {
            //     chart: {
            //         height: 350,
            //         type: "line",
            //         stacked: false,
            //         toolbar: {
            //             show: false
            //         }
            //     },
            //     dataLabels: {
            //         enabled: false
            //     },
            //     colors: ["#246dec", "#FF1654", "#f5b74f", "#367952"],
            //     series: [{
            //             name: 'Count of using serivce',
            //             data: []
            //         }, {
            //             name: 'Compare last week',
            //             data: totalLastWeek
            //         },
            //         {
            //             name: 'Compare last 2 week',
            //             data: totalLastWeek2
            //         },
            //         {
            //             name: 'Compare last 3 week',
            //             data: totalLastWeek3
            //         }
            //     ],
            //     stroke: {
            //         width: [0, 4, 4, 4]
            //     },
            //     plotOptions: {
            //         bar: {
            //             barHeight: '100%',
            //             distributed: true,
            //             horizontal: false,
            //             borderRadius: 4,
            //             columnWidth: '40%',
            //             dataLabels: {
            //                 position: 'top'
            //             },
            //         }
            //     },
            //     legend: {
            //         position: 'top',
            //         offsetX: 0,
            //         offsetY: 0,
            //         onItemClick: {
            //             toggleDataSeries: false
            //         }
            //     },
            //     xaxis: {
            //         categories: titleTime
            //     },
            //     tooltip: {
            //         shared: false,
            //         intersect: true,
            //         x: {
            //             show: false
            //         }
            //     },
            // };
            // var chart = new ApexCharts(document.querySelector("#chart"), options);
            // chart.render();
        </script> --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('XDataDashboard', () => ({
                    init() {
                        var shop = @json($shop);
                        var chartShop = @json($chartShop);
                        var option = "<option selected></option>";
                        var selectOptionHTML = $(option).val(shop?.id ? shop.id : null).text(shop?.name ?
                            shop.name : shop?.phone);
                        $('.SelectShop').append(selectOptionHTML).trigger('change');

                        var SelectChartShop = $(option).val(chartShop?.id ? chartShop.id : null).text(
                            chartShop?.name ?
                            chartShop.name : chartShop?.phone);
                        $('.SelectChartShop').append(SelectChartShop).trigger('change');
                    },
                    fetchSelectShop($shopId) {
                        $(`#${$shopId}`).select2({
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
                        });
                    },
                }));
            });
        </script>
    @endsection
