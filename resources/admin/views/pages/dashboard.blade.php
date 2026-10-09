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

        .dash-grid-row-3 {
            grid-template-columns: 1fr 1.8fr;
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
            .dash-grid-row-3,
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
            .dash-grid-row-3,
            .dash-grid-row-4 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-grid-row-1,
            .dash-grid-row-secondary,
            .dash-grid-row-2,
            .dash-grid-row-3,
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
            <div class="dashboard-container">
                <!-- ================= ROW 1 ================= -->
                <div class="dash-grid-row dash-grid-row-1">
                    <!-- 4. Orders (Total Price) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">${{ number_format($totalOrderPrice ?? 0, 2) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.orders_total_price')</p>
                            </div>
                            <div class="grid-icon-box teal">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <span class="badge-subtle badge-teal-subtle">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                                <span>@lang('dashboard.total_sales')</span>
                            </span>
                        </div>
                    </div>

                    <!-- 5. Order Pending (Count) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">{{ number_format($pendingOrderCount ?? 0) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.orders_pending')</p>
                            </div>
                            <div class="grid-icon-box orange">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <span class="badge-subtle badge-orange-subtle">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span>@lang('dashboard.pending')</span>
                            </span>
                        </div>
                    </div>

                    <!-- 6. Order Remaining Amount Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">${{ number_format($totalOrderRemainingAmount ?? 0, 2) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.orders_remaining_amount')</p>
                            </div>
                            <div class="grid-icon-box coral">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                    <line x1="7" y1="15" x2="7.01" y2="15"></line>
                                    <line x1="11" y1="15" x2="13" y2="15"></line>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <a href="{{ Route::has('admin-remaining-amount-list') ? route('admin-remaining-amount-list', 'partial') : 'javascript:void(0);' }}" class="badge-subtle badge-coral-subtle" style="text-decoration: none;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span>{{ isset($remainingOrderCount) && $remainingOrderCount > 0 ? __('dashboard.remaining_orders', ['count' => $remainingOrderCount]) : __('dashboard.remaining') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- 7. Products (Count) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">{{ number_format($totalProductCount ?? ($totalProductsCount ?? 0)) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.products_count')</p>
                            </div>
                            <div class="grid-icon-box purple">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <a href="{{ Route::has('admin-product-list') ? route('admin-product-list', 1) : 'javascript:void(0);' }}" class="badge-subtle badge-purple-subtle" style="text-decoration: none;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>{{ isset($activeProductCount) && $activeProductCount > 0 ? __('dashboard.active_products', ['count' => $activeProductCount]) : __('dashboard.active') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ================= ROW 2: USER & STAFF COUNTS ================= -->
                <div class="dash-grid-row dash-grid-row-secondary">

                    <!-- 8. Customer (Count) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">{{ number_format($totalCustomerCount ?? ($totalCustomersCount ?? 0)) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.customers_count')</p>
                            </div>
                            <div class="grid-icon-box cyan">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <a href="{{ Route::has('admin-customer-list') ? route('admin-customer-list', 1) : 'javascript:void(0);' }}" class="badge-subtle badge-cyan-subtle" style="text-decoration: none;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>{{ isset($activeCustomerCount) && $activeCustomerCount > 0 ? __('dashboard.active_customers', ['count' => $activeCustomerCount]) : __('dashboard.active') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- 9. Staff (Count) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">{{ number_format($totalStaffCount ?? 0) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.staff_count')</p>
                            </div>
                            <div class="grid-icon-box emerald">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <polyline points="16 11 18 13 22 9"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <a href="{{ Route::has('admin-staff-list') ? route('admin-staff-list', 1) : 'javascript:void(0);' }}" class="badge-subtle badge-emerald-subtle" style="text-decoration: none;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>{{ isset($activeStaffCount) && $activeStaffCount > 0 ? __('dashboard.active_staff', ['count' => $activeStaffCount]) : __('dashboard.active') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- 10. User (Count) Card -->
                    <div class="dash-card top-mini-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3 class="top-mini-amount">{{ number_format($totalUserCount ?? 0) }}</h3>
                                <p class="top-mini-label">@lang('dashboard.user_count')</p>
                            </div>
                            <div class="grid-icon-box indigo">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                        </div>
                        <div style="margin-top: 14px;">
                            <a href="{{ Route::has('admin-user-list') ? route('admin-user-list', 1) : 'javascript:void(0);' }}" class="badge-subtle badge-indigo-subtle" style="text-decoration: none;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>{{ isset($activeUserCount) && $activeUserCount > 0 ? __('dashboard.active_users', ['count' => $activeUserCount]) : __('dashboard.active') }}</span>
                            </a>
                        </div>
                    </div>

                </div>


                <!-- ================= ROW 3 ================= -->
                <div class="dash-grid-row dash-grid-row-3">

                    <!-- 9. Weekly Stats -->
                    <div class="dash-card">
                        <h3 class="dash-card-title">@lang('dashboard.weekly_stats')</h3>
                        <p class="dash-card-subtitle">
                            @lang('dashboard.average_sales'): <strong style="color: var(--text-dark); font-weight: 700;">{{ $weeklyStats['average_sales_formatted'] ?? '$0.00' }}</strong>
                        </p>
                        
                        <div style="margin: 5px -10px 10px -10px;">
                            <div id="chart-weekly-stats" style="width: 100%; height: 130px;"></div>
                        </div>

                        <div class="weekly-items-list">
                            <!-- Item 1: Top Sales -->
                            <div class="weekly-item-row">
                                <div class="item-left-info" style="min-width: 0; flex: 1;">
                                    <div class="grid-icon-box blue" style="flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div style="min-width: 0; overflow: hidden;">
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.top_sales')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $weeklyStats['top_sales']['name'] ?? '---' }}">{{ $weeklyStats['top_sales']['name'] ?? '---' }}</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-blue" style="flex-shrink: 0; margin-left: 8px;">{{ $weeklyStats['top_sales']['badge'] ?? '+0' }}</span>
                            </div>

                            <!-- Item 2: Best Seller -->
                            <div class="weekly-item-row">
                                <div class="item-left-info" style="min-width: 0; flex: 1;">
                                    <div class="grid-icon-box teal" style="flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div style="min-width: 0; overflow: hidden;">
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.best_seller')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $weeklyStats['best_seller']['name'] ?? '---' }}">{{ $weeklyStats['best_seller']['name'] ?? '---' }}</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-teal" style="flex-shrink: 0; margin-left: 8px;">{{ $weeklyStats['best_seller']['badge'] ?? '+0' }}</span>
                            </div>

                            <!-- Item 3: Most Commented / Top Category -->
                            <div class="weekly-item-row">
                                <div class="item-left-info" style="min-width: 0; flex: 1;">
                                    <div class="grid-icon-box orange" style="flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="4" cy="4" r="2.5"/><circle cx="12" cy="4" r="2.5"/><circle cx="20" cy="4" r="2.5"/>
                                            <circle cx="4" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="20" cy="12" r="2.5"/>
                                            <circle cx="4" cy="20" r="2.5"/><circle cx="12" cy="20" r="2.5"/><circle cx="20" cy="20" r="2.5"/>
                                        </svg>
                                    </div>
                                    <div style="min-width: 0; overflow: hidden;">
                                        <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-dark);">@lang('dashboard.most_commented')</h4>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $weeklyStats['most_commented']['name'] ?? '---' }}">{{ $weeklyStats['most_commented']['name'] ?? '---' }}</p>
                                    </div>
                                </div>
                                <span class="item-badge-pill badge-orange" style="flex-shrink: 0; margin-left: 8px;">{{ $weeklyStats['most_commented']['badge'] ?? '+0' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 10. Yearly Sales -->
                    <div class="dash-card">
                        <div class="table-header-flex" style="margin-bottom: 4px;">
                            <div>
                                <h3 class="dash-card-title">@lang('dashboard.yearly_sales')</h3>
                                <p class="dash-card-subtitle" id="yearly-sales-subtitle">
                                    @lang('dashboard.total_sales'): <strong id="yearly-sales-total-val" style="color: var(--text-dark); font-weight: 700;">{{ $yearlySalesData['total_sales_formatted'] ?? '$0.00' }}</strong>
                                </p>
                            </div>
                            <select class="select-month-dropdown" id="yearly-sales-year-select" aria-label="@lang('dashboard.yearly_sales')">
                                @foreach ($yearlySalesAvailableYears ?? [] as $yr)
                                    <option value="{{ $yr }}" {{ (int)($selectedYearlySalesYear ?? 0) === (int)$yr ? 'selected' : '' }}>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                                    <h4 id="yearly-sales-salary-val">{{ $yearlySalesData['salary_formatted'] ?? '$0.00' }}</h4>
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
                                    <h4 id="yearly-sales-expense-val">{{ $yearlySalesData['expense_formatted'] ?? '$0.00' }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- ================= ROW 4 ================= -->
                <div class="dash-grid-row dash-grid-row-4">

                    <!-- 12. Recent Transactions -->
                    <div class="dash-card">
                        <div class="table-header-flex" style="margin-bottom: 8px;">
                            <h3 class="dash-card-title">@lang('dashboard.recent_transactions')</h3>
                            @if (Route::has('admin-order-list'))
                                <a href="{{ route('admin-order-list') }}" class="timeline-view-all-link" title="@lang('dashboard.view_all_transactions')">
                                    <span>@lang('dashboard.view_all_transactions')</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <div class="timeline-container">
                            @forelse ($recentTransactions ?? [] as $tx)
                                @php
                                    $createdCarbon = $tx->created_at ? \Carbon\Carbon::parse($tx->created_at) : null;
                                    $orderCarbon = $tx->order_date ? \Carbon\Carbon::parse($tx->order_date) : null;

                                    // Detect if created_at or order_date has real hour/minute
                                    $hasCreatedTime = $createdCarbon && $createdCarbon->format('H:i') !== '00:00';
                                    $hasOrderTime = $orderCarbon && $orderCarbon->format('H:i') !== '00:00';

                                    $timeObj = null;
                                    if ($hasCreatedTime) {
                                        $timeObj = $createdCarbon;
                                    } elseif ($hasOrderTime) {
                                        $timeObj = $orderCarbon;
                                    }

                                    $dateObj = $orderCarbon ?: $createdCarbon;
                                    $isToday = $dateObj ? $dateObj->isToday() : false;

                                    if ($timeObj) {
                                        $timeFormatted = $timeObj->format('h:i a');
                                        $dateFormatted = $isToday ? __('dashboard.today') : ($dateObj ? $dateObj->format('d M') : '');
                                    } else {
                                        // Avoid showing repetitive 12:00 am when only date was recorded
                                        $timeFormatted = $dateObj ? ($isToday ? __('dashboard.today') : $dateObj->format('d M')) : '--';
                                        $dateFormatted = $dateObj ? $dateObj->format('Y') : '';
                                    }

                                    $status = $tx->payment_status ?: 'Pending';
                                    $circleColor = match ($status) {
                                        'Paid' => 'circle-teal',
                                        'Partial' => 'circle-cyan',
                                        'Cancel' => 'circle-coral',
                                        default => 'circle-orange',
                                    };

                                    $statusBadgeClass = match ($status) {
                                        'Paid' => 'status-badge-paid',
                                        'Partial' => 'status-badge-partial',
                                        'Cancel' => 'status-badge-cancel',
                                        default => 'status-badge-pending',
                                    };

                                    $statusLabel = match ($status) {
                                        'Paid' => __('dashboard.paid'),
                                        'Partial' => __('dashboard.partial'),
                                        'Cancel' => __('dashboard.cancel'),
                                        default => __('dashboard.pending'),
                                    };

                                    $rawCustomer = $tx->customer?->name ?: ($tx->customer?->phone ?: null);
                                    $customerDisplay = $rawCustomer ?: __('dashboard.walk_in_customer');
                                    $invoiceNumber = $tx->invoice_number ? '#' . $tx->invoice_number : '#' . $tx->id;
                                    $orderUrl = Route::has('admin-order-detail') ? route('admin-order-detail', $tx->id) : (Route::has('admin-order-edit') ? route('admin-order-edit', $tx->id) : '#');
                                @endphp
                                <div class="timeline-row">
                                    <div class="timeline-time-col">
                                        <span class="timeline-time">{{ $timeFormatted }}</span>
                                        @if ($dateFormatted)
                                            <span class="timeline-date-sub">{{ $dateFormatted }}</span>
                                        @endif
                                    </div>
                                    <div class="timeline-indicator">
                                        <div class="timeline-circle {{ $circleColor }}"></div>
                                        <div class="timeline-line"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title-row">
                                            @if ($status === 'Paid')
                                                {!! __('dashboard.payment_received_from', [
                                                    'name' => '<span class="tx-name">' . e($customerDisplay) . '</span>',
                                                    'amount' => '<span class="tx-amount tx-amount-paid">$' . number_format($tx->total_price, 2) . '</span>'
                                                ]) !!}
                                            @elseif ($status === 'Partial')
                                                {!! __('dashboard.partial_payment_from', [
                                                    'name' => '<span class="tx-name">' . e($customerDisplay) . '</span>',
                                                    'amount' => '<span class="tx-amount tx-amount-partial">$' . number_format($tx->paid_amount, 2) . '</span>'
                                                ]) !!}
                                            @elseif ($status === 'Cancel')
                                                @lang('dashboard.order_cancelled') - <span class="tx-amount tx-amount-cancel">${{ number_format($tx->total_price, 2) }}</span>
                                            @else
                                                @lang('dashboard.new_sale_recorded')
                                                @if ($rawCustomer)
                                                    - <span class="tx-name">{{ $rawCustomer }}</span>
                                                @endif
                                                - <span class="tx-amount">${{ number_format($tx->total_price, 2) }}</span>
                                            @endif
                                        </div>
                                        <div class="timeline-meta-bar">
                                            <a href="{{ $orderUrl }}" class="timeline-order-pill">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                                </svg>
                                                {{ $invoiceNumber }}
                                            </a>
                                            <span class="timeline-status-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                            @if (!empty($tx->shop?->name))
                                                <span class="timeline-shop-tag">&bull; {{ $tx->shop->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="timeline-empty-state">
                                    <div class="empty-icon-circle">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                    </div>
                                    <p>@lang('dashboard.no_recent_transactions')</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- 13. Product Performance -->
                    <div class="dash-card">
                        <div class="table-header-flex">
                            <h3 class="dash-card-title">@lang('dashboard.product_performance')</h3>
                            <select class="select-month-dropdown" id="product-performance-month-select">
                                <option value="all" {{ ($selectedProductMonth ?? 'all') === 'all' ? 'selected' : '' }}>@lang('dashboard.all_time')</option>
                                @foreach ($productPerformanceMonths ?? [] as $m)
                                    <option value="{{ $m['value'] }}" {{ ($selectedProductMonth ?? '') === $m['value'] ? 'selected' : '' }}>
                                        {{ $m['label'] }}
                                    </option>
                                @endforeach
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
                                <tbody id="product-performance-tbody">
                                    @include('admin::pages.dashboard_product_performance_rows')
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
            var weeklyStatsData = {!! json_encode($weeklyStats['chart_data'] ?? [0, 0, 0, 0, 0, 0, 0]) !!};
            var weeklyStatsCategories = {!! json_encode($weeklyStats['chart_categories'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!};
            var weeklyStatsOptions = {
                series: [{
                    name: @json(__('dashboard.sales')),
                    data: weeklyStatsData
                }],
                labels: weeklyStatsCategories,
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
                yaxis: {
                    min: 0,
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        show: true
                    },
                    y: {
                        formatter: function (val) {
                            return '$' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            };
            if (document.querySelector("#chart-weekly-stats")) {
                var weeklyChart = new ApexCharts(document.querySelector("#chart-weekly-stats"), weeklyStatsOptions);
                weeklyChart.render();
            }

            // 6. Yearly Sales Column Chart
            var yearlySalesData = @json($yearlySalesData['chart_data'] ?? []);
            var yearlySalesOptions = {
                series: [{
                    name: @json(__('dashboard.sales')),
                    data: yearlySalesData
                }],
                chart: {
                    type: 'bar',
                    height: 190,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '38%',
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
                        style: { colors: '#7C8FAC', fontSize: '11px' }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return '$' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            };
            if (document.querySelector("#chart-yearly-sales")) {
                var yearlyChart = new ApexCharts(document.querySelector("#chart-yearly-sales"), yearlySalesOptions);
                yearlyChart.render();
            }

            // Yearly Sales Year Filter (AJAX)
            var yearlySalesYearSelect = document.getElementById('yearly-sales-year-select');
            var chartYearlyEl = document.querySelector("#chart-yearly-sales");
            if (yearlySalesYearSelect && yearlyChart) {
                yearlySalesYearSelect.addEventListener('change', function () {
                    var selectedYear = this.value;
                    if (chartYearlyEl) {
                        chartYearlyEl.style.opacity = '0.4';
                        chartYearlyEl.style.transition = 'opacity 0.2s ease';
                    }

                    fetch(`{{ route('admin-dashboard') }}?yearly_sales_year=${encodeURIComponent(selectedYear)}&ajax=yearly_sales`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(function (data) {
                        if (data && data.status === 'success') {
                            var totalValEl = document.getElementById('yearly-sales-total-val');
                            if (totalValEl && data.total_sales_formatted) {
                                totalValEl.textContent = data.total_sales_formatted;
                            }
                            var salaryValEl = document.getElementById('yearly-sales-salary-val');
                            if (salaryValEl && data.salary_formatted) {
                                salaryValEl.textContent = data.salary_formatted;
                            }
                            var expenseValEl = document.getElementById('yearly-sales-expense-val');
                            if (expenseValEl && data.expense_formatted) {
                                expenseValEl.textContent = data.expense_formatted;
                            }
                            yearlyChart.updateSeries([{
                                name: @json(__('dashboard.sales')),
                                data: data.chart_data || []
                            }]);
                        }
                        if (chartYearlyEl) {
                            chartYearlyEl.style.opacity = '1';
                        }
                    })
                    .catch(function (err) {
                        console.error('Error fetching yearly sales:', err);
                        if (chartYearlyEl) {
                            chartYearlyEl.style.opacity = '1';
                        }
                    });
                });
            }

            // 7. Product Performance Month Filter (AJAX)
            var productMonthSelect = document.getElementById('product-performance-month-select');
            var productTbody = document.getElementById('product-performance-tbody');
            if (productMonthSelect && productTbody) {
                productMonthSelect.addEventListener('change', function () {
                    var selectedMonth = this.value;
                    productTbody.style.opacity = '0.35';
                    productTbody.style.pointerEvents = 'none';
                    productTbody.style.transition = 'opacity 0.2s ease';

                    fetch(`{{ route('admin-dashboard') }}?product_month=${encodeURIComponent(selectedMonth)}&ajax=product_performance`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(function (data) {
                        if (data && typeof data.html === 'string') {
                            productTbody.innerHTML = data.html;
                        }
                        productTbody.style.opacity = '1';
                        productTbody.style.pointerEvents = '';
                    })
                    .catch(function (err) {
                        console.error('Error fetching product performance:', err);
                        productTbody.style.opacity = '1';
                        productTbody.style.pointerEvents = '';
                    });
                });
            }

        });
    </script>
@endsection
