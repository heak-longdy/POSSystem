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
            --primary-purple: #7367F0;
            --primary-purple-light: #F2EFFF;
            --primary-emerald: #10B981;
            --primary-emerald-light: #E6FBF5;
            --primary-indigo: #6366F1;
            --primary-indigo-light: #EEF2FF;
            --text-dark: #2A3547;
            --text-muted: #7C8FAC;
            --border-color: #EAEFF4;
            --card-bg: #FFFFFF;
            --page-bg: #F4F6FA;
        }

        .dashboard-container {
            font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--page-bg);
            padding: 20px;
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
            gap: 20px;
            margin-bottom: 20px;
        }

        .dash-grid-row:last-child {
            margin-bottom: 0;
        }

        .dash-grid-row-1 {
            grid-template-columns: repeat(4, 1fr);
        }

        .dash-grid-row-secondary {
            grid-template-columns: repeat(3, 1fr);
        }

        .dash-grid-row-2 {
            grid-template-columns: 1.1fr 1.1fr 1fr;
        }

        .dash-grid-row-4 {
            grid-template-columns: 1fr 1.8fr;
        }

        .welcome-card {
            grid-column: span 3;
        }

        @media (max-width: 1200px) {
            .dash-grid-row-secondary {
                grid-template-columns: repeat(3, 1fr);
            }
            .dash-grid-row-2,
            .dash-grid-row-4 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 992px) {
            .dash-grid-row-1 {
                grid-template-columns: repeat(2, 1fr);
            }
            .dash-grid-row-secondary {
                grid-template-columns: repeat(2, 1fr);
            }
            .dash-grid-row-secondary > .dash-card:last-child {
                grid-column: span 2;
            }
            .welcome-card {
                grid-column: span 2;
            }
            .dash-card.top-mini-card:last-child {
                grid-column: span 2;
            }
            .dash-grid-row-4 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-grid-row-1,
            .dash-grid-row-secondary,
            .dash-grid-row-2,
            .dash-grid-row-4 {
                grid-template-columns: 1fr;
            }
            .dash-grid-row-secondary > .dash-card:last-child {
                grid-column: span 1;
            }
            .welcome-card {
                grid-column: span 1;
            }
            .dash-card.top-mini-card:last-child {
                grid-column: span 1;
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
            padding: 20px 18px;
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

        .badge-subtle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .badge-teal-subtle {
            background-color: var(--primary-teal-light);
            color: #0d9488;
        }

        .badge-orange-subtle {
            background-color: var(--primary-orange-light);
            color: #d97706;
        }

        .badge-blue-subtle {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
        }

        .badge-coral-subtle {
            background-color: var(--primary-coral-light);
            color: var(--primary-coral);
            transition: opacity 0.2s ease;
        }

        .badge-coral-subtle:hover {
            opacity: 0.85;
        }

        .badge-purple-subtle {
            background-color: var(--primary-purple-light);
            color: var(--primary-purple);
            transition: opacity 0.2s ease;
        }

        .badge-purple-subtle:hover {
            opacity: 0.85;
        }

        .badge-cyan-subtle {
            background-color: var(--primary-cyan-light);
            color: #0284c7;
            transition: opacity 0.2s ease;
        }

        .badge-cyan-subtle:hover {
            opacity: 0.85;
        }

        .badge-emerald-subtle {
            background-color: var(--primary-emerald-light);
            color: var(--primary-emerald);
            transition: opacity 0.2s ease;
        }

        .badge-emerald-subtle:hover {
            opacity: 0.85;
        }

        .badge-indigo-subtle {
            background-color: var(--primary-indigo-light);
            color: var(--primary-indigo);
            transition: opacity 0.2s ease;
        }

        .badge-indigo-subtle:hover {
            opacity: 0.85;
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

        .grid-icon-box.purple {
            background-color: var(--primary-purple-light);
            color: var(--primary-purple);
        }

        .grid-icon-box.emerald {
            background-color: var(--primary-emerald-light);
            color: var(--primary-emerald);
        }

        .grid-icon-box.indigo {
            background-color: var(--primary-indigo-light);
            color: var(--primary-indigo);
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
            margin-top: 12px;
            position: relative;
        }

        .timeline-view-all-link {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--primary-blue);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            background-color: var(--primary-blue-light);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .timeline-view-all-link:hover {
            background-color: var(--primary-blue);
            color: #ffffff;
            text-decoration: none;
        }

        .timeline-row {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding: 0 10px 0 8px;
            margin: 0 -8px;
            border-radius: 10px;
            transition: background-color 0.2s ease;
        }

        .timeline-row:hover {
            background-color: #F8FAFC;
        }

        .timeline-row:last-child {
            padding-bottom: 6px;
        }

        .timeline-time-col {
            min-width: 66px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-shrink: 0;
            padding-top: 1px;
        }

        .timeline-time {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            line-height: 1.25;
            letter-spacing: -0.2px;
            font-variant-numeric: tabular-nums;
        }

        .timeline-date-sub {
            display: inline-block;
            font-size: 11px;
            color: #94A3B8;
            margin-top: 3px;
            font-weight: 500;
            white-space: nowrap;
            line-height: 1.2;
        }

        .timeline-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 14px;
            position: relative;
            flex-shrink: 0;
            align-self: stretch;
        }

        .timeline-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ffffff;
            box-sizing: border-box;
            z-index: 2;
            margin-top: 2px;
            flex-shrink: 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .timeline-row:hover .timeline-circle {
            transform: scale(1.18);
        }

        .circle-teal {
            border: 2.5px solid var(--primary-teal);
            box-shadow: 0 0 0 3px rgba(19, 222, 185, 0.18);
        }
        .circle-orange {
            border: 2.5px solid var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 174, 31, 0.18);
        }
        .circle-cyan {
            border: 2.5px solid var(--primary-cyan);
            box-shadow: 0 0 0 3px rgba(73, 190, 255, 0.18);
        }
        .circle-coral {
            border: 2.5px solid var(--primary-coral);
            box-shadow: 0 0 0 3px rgba(250, 137, 107, 0.18);
        }
        .circle-blue {
            border: 2.5px solid var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.18);
        }

        .timeline-line {
            width: 2px;
            background-color: #EAEFF4;
            position: absolute;
            top: 8px;
            bottom: -2px;
            left: 5px;
            z-index: 1;
        }

        .timeline-row:last-child .timeline-line {
            display: none;
        }

        .timeline-content {
            flex: 1;
            min-width: 0;
            padding-top: 0;
            padding-bottom: 15px;
        }

        .timeline-title-row {
            font-size: 13px;
            color: var(--text-dark);
            line-height: 1.45;
            word-break: break-word;
        }

        .timeline-title-row strong,
        .timeline-title-row .tx-name {
            font-weight: 600;
            color: #1E293B;
        }

        .timeline-title-row .tx-amount {
            font-weight: 600;
            color: #1E293B;
            font-variant-numeric: tabular-nums;
        }

        .timeline-title-row .tx-amount-paid {
            color: #0D9488;
        }

        .timeline-title-row .tx-amount-partial {
            color: #0284C7;
        }

        .timeline-title-row .tx-amount-cancel {
            color: #E11D48;
        }

        .timeline-meta-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .timeline-order-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--primary-blue);
            background-color: var(--primary-blue-light);
            padding: 2px 8px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.2s ease;
            line-height: 1.35;
        }

        .timeline-order-pill:hover {
            background-color: var(--primary-blue);
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(93, 135, 255, 0.25);
        }

        .timeline-status-badge {
            font-size: 10.5px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            line-height: 1.35;
        }

        .status-badge-paid {
            background-color: var(--primary-teal-light);
            color: var(--primary-teal);
        }

        .status-badge-pending {
            background-color: var(--primary-orange-light);
            color: var(--primary-orange);
        }

        .status-badge-partial {
            background-color: var(--primary-cyan-light);
            color: var(--primary-cyan);
        }

        .status-badge-cancel {
            background-color: var(--primary-coral-light);
            color: var(--primary-coral);
        }

        .timeline-shop-tag {
            font-size: 11px;
            color: var(--text-muted);
            margin-left: 2px;
        }

        .timeline-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            text-align: center;
        }

        .timeline-empty-state .empty-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--text-muted);
        }

        .timeline-empty-state p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
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
        .product-thumb.blue { background-color: var(--primary-blue-light); }
        .product-thumb.cyan { background-color: var(--primary-cyan-light); }

        .product-name-link {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .product-name-link:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .product-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 36px 16px;
            text-align: center;
        }

        .product-empty-state .empty-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--text-muted);
        }

        .product-empty-state p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

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
            <div class="dashboard-container" id="dashboard-container" x-data="dashboardDateFilter"
                data-dashboard-url="{{ route('admin-dashboard', [], false) }}"
                data-from-date="{{ $dashboardFromDate ?? '' }}" data-to-date="{{ $dashboardToDate ?? '' }}"
                data-all-time="{{ ($dashboardAllTime ?? false) ? '1' : '0' }}"
                data-product-month="{{ $selectedProductMonth ?? 'all' }}">
                @include('admin::pages.dashboard_filter')
                <div id="dashboard-content-area" x-ref="dashboardContent" :aria-busy="loading" :inert="loading"
                    :style="{ opacity: loading ? 0.45 : 1 }"
                    @change="if ($event.target.matches('[data-product-month]')) changeProductMonth($event.target.value)">
                    @include('admin::pages.dashboard_content')
                </div>

                

            </div>
        </div>
    </div>
@stop

@section('script')
    @include('admin::pages.dashboard_filter_script')
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



        });
    </script>
@endsection
