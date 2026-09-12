@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('dashboard.dashboard')])

    <!-- Google Fonts & ApexCharts CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        :root {
            --primary-blue: #5D87FF;
            --primary-blue-light: #ECF2FF;
            --primary-cyan: #49BEFF;
            --primary-cyan-light: #E8F7FF;
            --primary-teal: #13DEB9;
            --primary-teal-light: #E6FFFA;
            --primary-orange: #FFAE1F;
            --primary-orange-light: #FEF5E5;
            --primary-coral: #FA896B;
            --primary-coral-light: #FDEDE8;
            --text-dark: #2A3547;
            --text-muted: #7C8FAC;
            --border-color: #EAEFF4;
            --card-bg: #FFFFFF;
            --page-bg: #F4F6FA;
        }

        .dashboard-container {
            font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--page-bg);
            padding: 24px;
            color: var(--text-dark);
            min-height: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        /* Common Card Styles */
        .dash-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02);
            padding: 24px;
            position: relative;
            box-sizing: border-box;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dash-card:hover {
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.04);
        }

        .dash-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            line-height: 1.3;
        }

        .dash-card-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin: 4px 0 0 0;
        }

        /* Grid Layouts */
        .dash-grid-row {
            display: grid;
            gap: 24px;
            margin-bottom: 24px;
        }

        .dash-grid-row-1 {
            grid-template-columns: 1.8fr 0.9fr 0.9fr;
        }

        .dash-grid-row-2 {
            grid-template-columns: 1.1fr 1.1fr 1fr;
        }

        .dash-grid-row-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .dash-grid-row-4 {
            grid-template-columns: 1fr 1.8fr;
        }

        @media (max-width: 1200px) {
            .dash-grid-row-1,
            .dash-grid-row-2,
            .dash-grid-row-3,
            .dash-grid-row-4 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-grid-row-1,
            .dash-grid-row-2,
            .dash-grid-row-3,
            .dash-grid-row-4 {
                grid-template-columns: 1fr;
            }
            .dashboard-container {
                padding: 14px;
            }
        }

        /* ---------------- Row 1 Cards ---------------- */
        /* Welcome Card */
        .welcome-card {
            background-color: var(--primary-blue-light);
            border-color: #dce7ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 24px 30px;
        }

        .welcome-left {
            z-index: 2;
            flex: 1;
        }

        .welcome-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .welcome-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            flex-shrink: 0;
        }

        .welcome-avatar svg {
            width: 100%;
            height: 100%;
        }

        .welcome-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .welcome-metrics {
            display: flex;
            gap: 40px;
        }

        .metric-item {
            display: flex;
            flex-direction: column;
        }

        .metric-value-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .metric-val {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .arrow-up-teal {
            display: inline-flex;
            align-items: center;
        }

        .metric-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .welcome-illustration {
            position: absolute;
            right: 15px;
            bottom: -5px;
            height: 165px;
            max-width: 210px;
            object-fit: contain;
            pointer-events: none;
            z-index: 1;
        }

        /* Top Small Cards (Expense & Sales) */
        .top-mini-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .top-mini-amount {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .top-mini-label {
            font-size: 13px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        .donut-chart-box {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 95px;
            margin-top: 5px;
        }

        .bar-chart-box {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 85px;
            padding: 0 10px;
            gap: 8px;
            margin-top: 15px;
        }

        .mini-pill-bar {
            width: 9px;
            background: linear-gradient(180deg, #93C5FD 0%, #DBEAFE 100%);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .mini-pill-bar:hover {
            background: #5D87FF;
        }

        /* ---------------- Row 2 Cards ---------------- */
        /* Revenue Updates */
        .revenue-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .chart-legend {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: var(--text-dark);
            margin: 8px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .dot-blue { background-color: var(--primary-blue); }
        .dot-cyan { background-color: var(--primary-cyan); }

        /* Sales Overview Card */
        .sales-radial-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 10px 0 20px 0;
        }

        .radial-center-text {
            position: absolute;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            text-align: center;
            top: 52%;
            transform: translateY(-50%);
        }

        .card-dual-stats {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #F1F4F9;
        }

        .dual-stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .grid-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .grid-icon-box.blue {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
        }

        .grid-icon-box.cyan {
            background-color: var(--primary-cyan-light);
            color: var(--primary-cyan);
        }

        .grid-icon-box.teal {
            background-color: var(--primary-teal-light);
            color: var(--primary-teal);
        }

        .grid-icon-box.orange {
            background-color: var(--primary-orange-light);
            color: var(--primary-orange);
        }

        .grid-icon-box.coral {
            background-color: var(--primary-coral-light);
            color: var(--primary-coral);
        }

        .stat-details h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .stat-details p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        /* Column 3 (Row 2): Mini Cards + Monthly Earnings */
        .right-column-group {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .mini-cards-pair {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .mini-stat-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .mini-stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .dots-row {
            display: flex;
            gap: 4px;
        }

        .dots-row span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--primary-blue);
        }

        .mini-stat-val {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .mini-stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        /* Monthly Earnings */
        .monthly-earnings-card {
            padding-bottom: 0 !important;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .monthly-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .capsule-toggle {
            width: 32px;
            height: 18px;
            border-radius: 12px;
            background-color: var(--primary-blue-light);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 2px 3px;
            box-sizing: border-box;
        }

        .capsule-toggle .toggle-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-blue);
        }

        .earnings-val-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 12px 0 0 0;
        }

        .earnings-val {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .earnings-growth {
            color: var(--primary-teal);
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .earnings-bottom-chart {
            margin: 0 -24px -5px -24px;
            height: 75px;
        }

        /* ---------------- Row 3 Cards ---------------- */
        /* Weekly Stats */
        .weekly-items-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 15px;
        }

        .weekly-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .item-left-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-badge-pill {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .badge-blue {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
        }

        .badge-teal {
            background-color: var(--primary-teal-light);
            color: var(--primary-teal);
        }

        .badge-orange {
            background-color: var(--primary-orange-light);
            color: var(--primary-orange);
        }

        .badge-coral {
            background-color: var(--primary-coral-light);
            color: var(--primary-coral);
        }

        /* Payment Gateways */
        .gateway-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin: 20px 0;
        }

        .gateway-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gateway-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gateway-amount {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .btn-view-all {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--primary-blue);
            background: transparent;
            color: var(--primary-blue);
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
        }

        .btn-view-all:hover {
            background-color: var(--primary-blue-light);
        }

        /* ---------------- Row 4 Cards ---------------- */
        /* Recent Transactions Timeline */
        .timeline-container {
            display: flex;
            flex-direction: column;
            gap: 0;
            margin-top: 20px;
            position: relative;
        }

        .timeline-row {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-bottom: 24px;
        }

        .timeline-row:last-child {
            padding-bottom: 0;
        }

        .timeline-time {
            font-size: 12px;
            color: var(--text-muted);
            min-width: 65px;
            padding-top: 1px;
        }

        .timeline-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 16px;
            position: relative;
        }

        .timeline-circle {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #fff;
            box-sizing: border-box;
            z-index: 2;
        }

        .circle-blue { border: 2px solid var(--primary-blue); }
        .circle-cyan { border: 2px solid var(--primary-cyan); }
        .circle-teal { border: 2px solid var(--primary-teal); }
        .circle-orange { border: 2px solid var(--primary-orange); }
        .circle-coral { border: 2px solid var(--primary-coral); }

        .timeline-line {
            width: 1px;
            background-color: #E2E8F0;
            position: absolute;
            top: 11px;
            bottom: -24px;
            left: 5px;
            z-index: 1;
        }

        .timeline-row:last-child .timeline-line {
            display: none;
        }

        .timeline-content {
            font-size: 13px;
            color: var(--text-dark);
            line-height: 1.4;
            padding-top: 0;
        }

        .timeline-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }

        /* Product Performance Table */
        .table-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .select-month-dropdown {
            border: 1px solid var(--border-color);
            background: #fff;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            color: var(--text-dark);
            font-weight: 500;
            outline: none;
            cursor: pointer;
            font-family: inherit;
        }

        .product-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .product-table th {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            padding: 12px 14px;
            border-bottom: 1px solid #F1F4F9;
        }

        .product-table td {
            font-size: 13px;
            color: var(--text-dark);
            padding: 14px;
            border-bottom: 1px solid #F6F9FC;
            vertical-align: middle;
        }

        .product-table tr:last-child td {
            border-bottom: none;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .product-thumb {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb.yellow { background-color: #FEF5E5; }
        .product-thumb.mint { background-color: #E6FFFA; }
        .product-thumb.gray { background-color: #F4F6FA; }
        .product-thumb.pink { background-color: #FDEDE8; }

        .product-info h5 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .product-info p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        .table-sparkline {
            width: 90px;
            height: 30px;
        }
    </style>

    <div class="content-wrapper" id="app">
        <div class="content-body" style="overflow-y: auto; height: 100%; width: 100%; padding: 0;">
            <div class="dashboard-container">

                <!-- ================= ROW 1 ================= -->
                <div class="dash-grid-row dash-grid-row-1">
                    
                    <!-- 1. Welcome Card -->
                    <div class="dash-card welcome-card">
                        <div class="welcome-left">
                            <div class="welcome-user">
                                <div class="welcome-avatar">
                                    <!-- Vector Illustrated User Avatar matching reference image -->
                                    <svg viewBox="0 0 64 64" width="44" height="44">
                                        <circle cx="32" cy="32" r="32" fill="#d2f4ea"/>
                                        <circle cx="32" cy="27" r="14" fill="#fcd34d"/>
                                        <path d="M19 56c0-8 6-14 13-14s13 6 13 14" fill="#ffffff"/>
                                        <path d="M22 20c2-7 18-7 20 0 2 0 4 3 2 7-2 0-3-3-5-3s-3 3-7 3-5-3-7-3c-2 0-3 3-5 3-2-4 0-7 2-7z" fill="#1e293b"/>
                                        <!-- Glasses -->
                                        <circle cx="27" cy="27" r="4.5" fill="none" stroke="#0f172a" stroke-width="1.5"/>
                                        <circle cx="37" cy="27" r="4.5" fill="none" stroke="#0f172a" stroke-width="1.5"/>
                                        <line x1="31.5" y1="27" x2="32.5" y2="27" stroke="#0f172a" stroke-width="1.5"/>
                                        <!-- Smile -->
                                        <path d="M29 34 Q 32 37 35 34" stroke="#e11d48" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                                        <!-- Tie -->
                                        <polygon points="32,42 29,56 35,56" fill="#f43f5e"/>
                                    </svg>
                                </div>
                                <h2 class="welcome-title">@lang('dashboard.welcome_back', ['name' => Auth::user()?->name ?? 'Mathew Anderson'])</h2>
                            </div>
                            <div class="welcome-metrics">
                                <div class="metric-item">
                                    <div class="metric-value-row">
                                        <span class="metric-val">$2,340</span>
                                        <span class="arrow-up-teal">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#13deb9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                <polyline points="7 7 17 7 17 17"></polyline>
                                            </svg>
                                        </span>
                                    </div>
                                    <span class="metric-label">@lang('dashboard.todays_sales')</span>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value-row">
                                        <span class="metric-val">35%</span>
                                        <span class="arrow-up-teal">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#13deb9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                <polyline points="7 7 17 7 17 17"></polyline>
                                            </svg>
                                        </span>
                                    </div>
                                    <span class="metric-label">@lang('dashboard.performance')</span>
                                </div>
                            </div>
                        </div>
                        <img src="{{ asset('admin-public/logo/welcome-bg2.webp') }}" class="welcome-illustration" alt="{{ __('dashboard.welcome_illustration') }}">
                    </div>

                    <!-- 2. Expense Card -->
                    <div class="dash-card top-mini-card">
                        <div>
                            <h3 class="top-mini-amount">$10,230</h3>
                            <p class="top-mini-label">@lang('dashboard.expense')</p>
                        </div>
                        <div class="donut-chart-box">
                            <div id="chart-expense-donut" style="width: 100%;"></div>
                        </div>
                    </div>

                    <!-- 3. Sales Card -->
                    <div class="dash-card top-mini-card">
                        <div>
                            <h3 class="top-mini-amount">$65,432</h3>
                            <p class="top-mini-label">@lang('dashboard.sales')</p>
                        </div>
                        <div class="bar-chart-box">
                            <div class="mini-pill-bar" style="height: 35%;"></div>
                            <div class="mini-pill-bar" style="height: 75%;"></div>
                            <div class="mini-pill-bar" style="height: 55%;"></div>
                            <div class="mini-pill-bar" style="height: 85%;"></div>
                            <div class="mini-pill-bar" style="height: 60%;"></div>
                            <div class="mini-pill-bar" style="height: 75%;"></div>
                        </div>
                    </div>

                </div>

                <!-- ================= ROW 2 ================= -->
                <div class="dash-grid-row dash-grid-row-2">

                    <!-- 4. Revenue Updates -->
                    <div class="dash-card">
                        <div class="revenue-header-row">
                            <div>
                                <h3 class="dash-card-title">@lang('dashboard.revenue_updates')</h3>
                                <p class="dash-card-subtitle">@lang('dashboard.overview_of_profit')</p>
                            </div>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-dot dot-blue"></span>
                                <span>@lang('dashboard.footware')</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot dot-cyan"></span>
                                <span>@lang('dashboard.fashionware')</span>
                            </div>
                        </div>
                        <div id="chart-revenue-updates" style="min-height: 250px;"></div>
                    </div>

                    <!-- 5. Sales Overview -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.sales_overview')</h3>
                        <p class="dash-card-subtitle">@lang('dashboard.every_month')</p>
                        
                        <div class="sales-radial-container">
                            <div id="chart-sales-radial" style="width: 100%;"></div>
                            <div class="radial-center-text">$500,458</div>
                        </div>

                        <div class="card-dual-stats">
                            <div class="dual-stat-item">
                                <div class="grid-icon-box blue">
                                    <!-- 3x3 Dots Grid SVG -->
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                        <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                        <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                    </svg>
                                </div>
                                <div class="stat-details">
                                    <h4>$23,450</h4>
                                    <p>@lang('dashboard.profit')</p>
                                </div>
                            </div>
                            <div class="dual-stat-item">
                                <div class="grid-icon-box cyan">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                        <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                        <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                    </svg>
                                </div>
                                <div class="stat-details">
                                    <h4>$23,450</h4>
                                    <p>@lang('dashboard.expense')</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6, 7, 8. Right Column Group (Mini Cards + Monthly Earnings) -->
                    <div class="right-column-group">
                        <!-- Mini cards pair -->
                        <div class="mini-cards-pair">
                            <!-- Mini Card 1: Sales -->
                            <div class="mini-stat-card">
                                <div class="mini-stat-top">
                                    <div class="grid-icon-box blue">
                                        <!-- Shopping Cart SVG -->
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="dots-row" style="margin-bottom: 8px;">
                                        <span></span><span></span><span></span><span></span>
                                    </div>
                                    <h4 class="mini-stat-val">
                                        $16.5k 
                                        <span class="arrow-up-teal" style="margin-left: 2px;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#13deb9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                <polyline points="7 7 17 7 17 17"></polyline>
                                            </svg>
                                        </span>
                                    </h4>
                                    <p class="mini-stat-label">@lang('dashboard.sales')</p>
                                </div>
                            </div>

                            <!-- Mini Card 2: Growth -->
                            <div class="mini-stat-card">
                                <div class="mini-stat-top">
                                    <div class="grid-icon-box cyan">
                                        <!-- Bar Chart Analytics SVG -->
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <div style="height: 16px; margin-bottom: 6px;">
                                        <svg viewBox="0 0 60 16" width="60" height="16" fill="none">
                                            <path d="M0 10 Q 15 4, 30 9 T 60 4" stroke="#49BEFF" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h4 class="mini-stat-val">
                                        24% 
                                        <span class="arrow-up-teal" style="margin-left: 2px;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#13deb9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                <polyline points="7 7 17 7 17 17"></polyline>
                                            </svg>
                                        </span>
                                    </h4>
                                    <p class="mini-stat-label">@lang('dashboard.growth')</p>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Earnings Card -->
                        <div class="dash-card monthly-earnings-card">
                            <div>
                                <div class="monthly-header-row">
                                    <h3 class="dash-card-title">@lang('dashboard.monthly_earnings')</h3>
                                    <div class="capsule-toggle">
                                        <div class="toggle-circle"></div>
                                    </div>
                                </div>
                                <div class="earnings-val-row">
                                    <span class="earnings-val">$6,820</span>
                                    <span class="earnings-growth">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#13deb9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="7" y1="17" x2="17" y2="7"></line>
                                            <polyline points="7 7 17 7 17 17"></polyline>
                                        </svg>
                                        +9%
                                    </span>
                                </div>
                            </div>
                            <div class="earnings-bottom-chart">
                                <div id="chart-monthly-earnings" style="width: 100%; height: 100%;"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= ROW 3 ================= -->
                <div class="dash-grid-row dash-grid-row-3">

                    <!-- 9. Weekly Stats -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.weekly_stats')</h3>
                        <p class="dash-card-subtitle">@lang('dashboard.average_sales')</p>
                        
                        <div style="margin: 5px -10px 10px -10px;">
                            <div id="chart-weekly-stats" style="width: 100%; height: 130px;"></div>
                        </div>

                        <div class="weekly-items-list">
                            <!-- Item 1 -->
                            <div class="weekly-item-row">
                                <div class="item-left-info">
                                    <div class="grid-icon-box blue">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.top_sales')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Johnathan Doe</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-blue">+68</span>
                            </div>

                            <!-- Item 2 -->
                            <div class="weekly-item-row">
                                <div class="item-left-info">
                                    <div class="grid-icon-box teal">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.best_seller')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.footware')</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-teal">+45</span>
                            </div>

                            <!-- Item 3 -->
                            <div class="weekly-item-row">
                                <div class="item-left-info">
                                    <div class="grid-icon-box orange">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.most_commented')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.fashionware')</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-orange">+14</span>
                            </div>
                        </div>
                    </div>

                    <!-- 10. Yearly Sales -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.yearly_sales')</h3>
                        <p class="dash-card-subtitle">@lang('dashboard.total_sales')</p>

                        <div id="chart-yearly-sales" style="min-height: 200px; margin: 10px 0;"></div>

                        <div class="card-dual-stats">
                            <div class="dual-stat-item">
                                <div class="grid-icon-box blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                        <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                        <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                    </svg>
                                </div>
                                <div class="stat-details">
                                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">@lang('dashboard.salary')</p>
                                    <h4>$36,358</h4>
                                </div>
                            </div>
                            <div class="dual-stat-item">
                                <div class="grid-icon-box cyan">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                        <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                        <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                    </svg>
                                </div>
                                <div class="stat-details">
                                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">@lang('dashboard.expense')</p>
                                    <h4>$5,296</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 11. Payment Gateways -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.payment_gateways')</h3>
                        <p class="dash-card-subtitle">@lang('dashboard.platform_for_income')</p>

                        <div class="gateway-list">
                            <!-- Paypal -->
                            <div class="gateway-item">
                                <div class="item-left-info">
                                    <div class="gateway-icon-box" style="background-color: var(--primary-blue-light);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--primary-blue)">
                                            <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.8 1.57 1.164.978 1.634 2.457 1.358 4.28-.466 3.09-2.585 5.253-5.753 5.918-.544.114-.99.516-1.077 1.066l-1.037 6.574c-.06.38-.388.657-.773.657h-4.9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.paypal')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.big_brands')</p>
                                    </div>
                                </div>
                                <span class="gateway-amount">+$6235</span>
                            </div>

                            <!-- Wallet -->
                            <div class="gateway-item">
                                <div class="item-left-info">
                                    <div class="gateway-icon-box" style="background-color: var(--primary-teal-light);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 10h20"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.wallet')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.bill_payment')</p>
                                    </div>
                                </div>
                                <span class="gateway-amount">-$345</span>
                            </div>

                            <!-- Credit Card -->
                            <div class="gateway-item">
                                <div class="item-left-info">
                                    <div class="gateway-icon-box" style="background-color: var(--primary-orange-light);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--primary-orange)">
                                            <circle cx="9" cy="12" r="5" fill-opacity="0.8"/>
                                            <circle cx="15" cy="12" r="5" fill-opacity="0.6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.credit_card')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.money_reversed')</p>
                                    </div>
                                </div>
                                <span class="gateway-amount">+$2235</span>
                            </div>

                            <!-- Refund -->
                            <div class="gateway-item">
                                <div class="item-left-info">
                                    <div class="gateway-icon-box" style="background-color: var(--primary-coral-light);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--primary-coral)">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8v8h8c0 4.41-3.59 8-8 8z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.refund')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">@lang('dashboard.bill_payment')</p>
                                    </div>
                                </div>
                                <span class="gateway-amount">-$32</span>
                            </div>
                        </div>

                        <button class="btn-view-all">@lang('dashboard.view_all_transactions')</button>
                    </div>

                </div>

                <!-- ================= ROW 4 ================= -->
                <div class="dash-grid-row dash-grid-row-4">

                    <!-- 12. Recent Transactions -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.recent_transactions')</h3>

                        <div class="timeline-container">
                            <!-- Item 1 -->
                            <div class="timeline-row">
                                <div class="timeline-time">09:30 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-blue"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    {{ __('dashboard.payment_received_from', ['name' => 'John Doe', 'amount' => '$385.90']) }}
                                </div>
                            </div>

                            <!-- Item 2 -->
                            <div class="timeline-row">
                                <div class="timeline-time">10:00 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-cyan"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    @lang('dashboard.new_sale_recorded') <a href="javascript:void(0)" class="timeline-link">#ML-3467</a>
                                </div>
                            </div>

                            <!-- Item 3 -->
                            <div class="timeline-row">
                                <div class="timeline-time">12:00 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-teal"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    {{ __('dashboard.payment_made_to', ['amount' => '$64.95', 'name' => 'Michael']) }}
                                </div>
                            </div>

                            <!-- Item 4 -->
                            <div class="timeline-row">
                                <div class="timeline-time">09:30 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-orange"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    @lang('dashboard.new_sale_recorded') <a href="javascript:void(0)" class="timeline-link">#ML-3467</a>
                                </div>
                            </div>

                            <!-- Item 5 -->
                            <div class="timeline-row">
                                <div class="timeline-time">09:30 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-coral"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    @lang('dashboard.new_arrival_recorded') <a href="javascript:void(0)" class="timeline-link">#ML-3467</a>
                                </div>
                            </div>

                            <!-- Item 6 -->
                            <div class="timeline-row">
                                <div class="timeline-time">12:00 am</div>
                                <div class="timeline-indicator">
                                    <div class="timeline-circle circle-teal"></div>
                                    <div class="timeline-line"></div>
                                </div>
                                <div class="timeline-content">
                                    @lang('dashboard.payment_done')
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 13. Product Performance -->
                    <div class="dash-card">
                        <div class="table-header-flex">
                            <h3 class="dash-card-title">@lang('dashboard.product_performance')</h3>
                            <select class="select-month-dropdown">
                                <option>{{ __('dashboard.months.march') }} 2025</option>
                                <option>{{ __('dashboard.months.february') }} 2025</option>
                                <option>{{ __('dashboard.months.january') }} 2025</option>
                            </select>
                        </div>

                        <div class="product-table-wrapper">
                            <table class="product-table">
                                <thead>
                                    <tr>
                                        <th>@lang('dashboard.table.product')</th>
                                        <th>@lang('dashboard.table.progress')</th>
                                        <th>@lang('dashboard.table.priority')</th>
                                        <th>@lang('dashboard.table.budget')</th>
                                        <th>@lang('dashboard.table.chart')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Product 1 -->
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb yellow">
                                                    <!-- Game Console Controller Vector -->
                                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="#1E293B">
                                                        <path d="M21 6H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-10 7H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm3-3c-.83 0-1.5-.67-1.5-1.5S17.67 9 18.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                                    </svg>
                                                </div>
                                                <div class="product-info">
                                                    <h5>@lang('dashboard.products.gaming_console')</h5>
                                                    <p>@lang('dashboard.products.electronics')</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>78.5%</td>
                                        <td>
                                            <span class="item-badge-pill badge-teal">@lang('dashboard.priority.low')</span>
                                        </td>
                                        <td>$3.9k</td>
                                        <td>
                                            <svg class="table-sparkline" viewBox="0 0 90 30" fill="none">
                                                <path d="M2 18 Q 20 2, 45 18 T 88 15" stroke="#5D87FF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </td>
                                    </tr>

                                    <!-- Product 2 -->
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb mint">
                                                    <!-- Leather Purse Vector -->
                                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="#F472B6">
                                                        <path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12z"/>
                                                    </svg>
                                                </div>
                                                <div class="product-info">
                                                    <h5>@lang('dashboard.products.leather_purse')</h5>
                                                    <p>@lang('dashboard.products.fashion')</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>58.6%</td>
                                        <td>
                                            <span class="item-badge-pill badge-orange">@lang('dashboard.priority.medium')</span>
                                        </td>
                                        <td>$3.5k</td>
                                        <td>
                                            <svg class="table-sparkline" viewBox="0 0 90 30" fill="none">
                                                <path d="M2 15 Q 22 24, 45 12 T 88 16" stroke="#CBD5E1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </td>
                                    </tr>

                                    <!-- Product 3 -->
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb gray">
                                                    <!-- Red Dress Vector -->
                                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="#EF4444">
                                                        <path d="M12 2l-2 3-3 1 2 6-2 10h10l-2-10 2-6-3-1z"/>
                                                    </svg>
                                                </div>
                                                <div class="product-info">
                                                    <h5>@lang('dashboard.products.red_velvate_dress')</h5>
                                                    <p>@lang('dashboard.products.womens_fashion')</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>25%</td>
                                        <td>
                                            <span class="item-badge-pill badge-blue">@lang('dashboard.priority.very_high')</span>
                                        </td>
                                        <td>$3.5k</td>
                                        <td>
                                            <svg class="table-sparkline" viewBox="0 0 90 30" fill="none">
                                                <path d="M2 18 Q 20 2, 45 18 T 88 15" stroke="#5D87FF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </td>
                                    </tr>

                                    <!-- Product 4 -->
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb pink">
                                                    <!-- Headphones Boat Vector -->
                                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="#FB7185">
                                                        <path d="M12 3a9 9 0 0 0-9 9v7c0 1.1.9 2 2 2h2a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5v-2a7 7 0 0 1 14 0v2h-2a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2c1.1 0 2-.9 2-2v-7a9 9 0 0 0-9-9z"/>
                                                    </svg>
                                                </div>
                                                <div class="product-info">
                                                    <h5>@lang('dashboard.products.headphone_boat')</h5>
                                                    <p>@lang('dashboard.products.electronics')</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>96.3%</td>
                                        <td>
                                            <span class="item-badge-pill badge-coral">@lang('dashboard.priority.high')</span>
                                        </td>
                                        <td>$3.5k</td>
                                        <td>
                                            <svg class="table-sparkline" viewBox="0 0 90 30" fill="none">
                                                <path d="M2 15 Q 22 24, 45 12 T 88 16" stroke="#CBD5E1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. Expense Donut Chart
            var expenseOptions = {
                series: [72, 28],
                chart: {
                    type: 'donut',
                    height: 100,
                    sparkline: { enabled: true }
                },
                colors: ['#5D87FF', '#FA896B'],
                stroke: { width: 0 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                }
            };
            if (document.querySelector("#chart-expense-donut")) {
                var expenseChart = new ApexCharts(document.querySelector("#chart-expense-donut"), expenseOptions);
                expenseChart.render();
            }

            // 2. Revenue Updates (Diverging Bar Chart)
            var revenueOptions = {
                series: [
                    {
                        name: @json(__('dashboard.footware')),
                        data: [2.5, 3.8, 3.2, 2.5, 2.2]
                    },
                    {
                        name: @json(__('dashboard.fashionware')),
                        data: [-2.8, 1.2, -3.4, -2.2, -1.8]
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 240,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                colors: ['#5D87FF', '#49BEFF'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '22%',
                        borderRadius: 4
                    },
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3,
                    yaxis: { lines: { show: true } }
                },
                yaxis: {
                    min: -5.0,
                    max: 5.0,
                    tickAmount: 4,
                    labels: {
                        style: { colors: '#7C8FAC', fontSize: '11px' },
                        formatter: function (val) {
                            return val.toFixed(1);
                        }
                    }
                },
                xaxis: {
                    categories: [
                        @json(__('dashboard.months_short.jan')),
                        @json(__('dashboard.months_short.feb')),
                        @json(__('dashboard.months_short.mar')),
                        @json(__('dashboard.months_short.apr')),
                        @json(__('dashboard.months_short.may'))
                    ],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#7C8FAC', fontSize: '12px' }
                    }
                },
                tooltip: {
                    theme: 'dark'
                }
            };
            if (document.querySelector("#chart-revenue-updates")) {
                var revenueChart = new ApexCharts(document.querySelector("#chart-revenue-updates"), revenueOptions);
                revenueChart.render();
            }

            // 3. Sales Overview Radial Gauge Chart
            var salesRadialOptions = {
                series: [78],
                chart: {
                    height: 230,
                    type: 'radialBar',
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            size: '68%',
                            background: 'transparent'
                        },
                        track: {
                            background: '#F1F4F9',
                            strokeWidth: '100%',
                        },
                        dataLabels: {
                            name: { show: false },
                            value: { show: false }
                        }
                    }
                },
                fill: {
                    colors: ['#49BEFF']
                },
                stroke: {
                    lineCap: 'round'
                }
            };
            if (document.querySelector("#chart-sales-radial")) {
                var salesRadialChart = new ApexCharts(document.querySelector("#chart-sales-radial"), salesRadialOptions);
                salesRadialChart.render();
            }

            // 4. Monthly Earnings Wave Chart
            var monthlyEarningsOptions = {
                series: [{
                    name: @json(__('dashboard.earnings')),
                    data: [18, 42, 16, 25, 12, 38, 15]
                }],
                chart: {
                    type: 'area',
                    height: 75,
                    sparkline: { enabled: true },
                    fontFamily: 'inherit'
                },
                stroke: {
                    curve: 'smooth',
                    width: 2.2,
                    colors: ['#5D87FF']
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0,
                        opacityFrom: 0.15,
                        opacityTo: 0.0,
                        stops: [0, 100]
                    }
                },
                colors: ['#5D87FF'],
                tooltip: {
                    theme: 'dark'
                }
            };
            if (document.querySelector("#chart-monthly-earnings")) {
                var monthlyChart = new ApexCharts(document.querySelector("#chart-monthly-earnings"), monthlyEarningsOptions);
                monthlyChart.render();
            }

            // 5. Weekly Stats Spline Chart
            var weeklyStatsOptions = {
                series: [{
                    name: @json(__('dashboard.sales')),
                    data: [10, 28, 48, 22, 18, 30, 35, 20]
                }],
                chart: {
                    type: 'area',
                    height: 125,
                    sparkline: { enabled: true },
                    fontFamily: 'inherit'
                },
                stroke: {
                    curve: 'smooth',
                    width: 2.2,
                    colors: ['#5D87FF']
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0,
                        opacityFrom: 0.12,
                        opacityTo: 0.0,
                        stops: [0, 100]
                    }
                },
                colors: ['#5D87FF'],
                tooltip: {
                    theme: 'dark'
                }
            };
            if (document.querySelector("#chart-weekly-stats")) {
                var weeklyChart = new ApexCharts(document.querySelector("#chart-weekly-stats"), weeklyStatsOptions);
                weeklyChart.render();
            }

            // 6. Yearly Sales Column Chart (June highlighted)
            var yearlySalesOptions = {
                series: [{
                    name: @json(__('dashboard.sales')),
                    data: [
                        { x: @json(__('dashboard.months_short.apr')), y: 40, fillColor: '#F1F4F9' },
                        { x: @json(__('dashboard.months_short.may')), y: 55, fillColor: '#F1F4F9' },
                        { x: @json(__('dashboard.months_short.june')), y: 100, fillColor: '#5D87FF' },
                        { x: @json(__('dashboard.months_short.july')), y: 35, fillColor: '#F1F4F9' },
                        { x: @json(__('dashboard.months_short.aug')), y: 45, fillColor: '#F1F4F9' },
                        { x: @json(__('dashboard.months_short.sept')), y: 60, fillColor: '#F1F4F9' }
                    ]
                }],
                chart: {
                    type: 'bar',
                    height: 190,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 5,
                        columnWidth: '24%',
                        distributed: false
                    }
                },
                dataLabels: { enabled: false },
                grid: { show: false },
                yaxis: { show: false },
                xaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#7C8FAC', fontSize: '12px' }
                    }
                },
                tooltip: {
                    theme: 'dark'
                }
            };
            if (document.querySelector("#chart-yearly-sales")) {
                var yearlyChart = new ApexCharts(document.querySelector("#chart-yearly-sales"), yearlySalesOptions);
                yearlyChart.render();
            }

        });
    </script>
@endsection
