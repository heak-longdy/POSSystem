<?php

return [
    'title'    => 'Other Income Report',
    'subtitle' => 'Comprehensive Other Income & Supplier Bonus Analytics',

    'tab' => [
        'daily'   => 'Daily Income Report',
        'monthly' => 'Monthly Income Report',
    ],

    'presets' => [
        'title'        => 'Quick Presets:',
        'today'        => 'Today',
        'yesterday'    => 'Yesterday',
        'last_7_days'  => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'this_month'   => 'This Month',
        'last_month'   => 'Last Month',
    ],

    'filter' => [
        'from_date'          => 'From Date',
        'to_date'            => 'To Date',
        'year'               => 'Year',
        'from_month'         => 'From Month',
        'to_month'           => 'To Month',
        'supplier'           => 'Supplier',
        'all_suppliers'      => 'All Suppliers',
        'category'           => 'Income Category',
        'all_categories'     => 'All Categories',
        'shop'               => 'Shop / Branch',
        'all_shops'          => 'All Shops / HQ',
        'payment_status'     => 'Payment Status',
        'all_statuses'       => 'All Payment Statuses',
        'keyword_search'     => 'Keyword Search',
        'placeholder_date'   => 'YYYY-MM-DD',
        'placeholder_search' => 'Reference, title, supplier...',
    ],

    'button' => [
        'filter' => 'Filter',
        'reset' => 'Reset',
    ],

    'toggle' => [
        'view_as'          => 'View As',
        'grouped'          => 'Grouped',
        'grouped_by_date'  => 'Grouped by Date',
        'grouped_by_month' => 'Grouped by Month',
        'ungrouped'        => 'Ungrouped (Itemized)',
        'grouped_tooltip'  => 'View income records grouped by date',
        'grouped_month_tooltip' => 'View income records grouped by month',
        'ungrouped_tooltip'=> 'View all individual income records',
    ],

    'summary' => [
        'total_transactions' => 'Total Transactions',
        'total_amount'       => 'Total Other Income',
        'total_received'     => 'Total Other Income',
        'supplier_bonus'     => 'Supplier Bonuses',
        'top_supplier'       => 'Top Supplier Contributor',
        'avg_transaction'    => 'Average Income / Txn',
    ],

    'table' => [
        'no'               => 'Nº',
        'date'             => 'Date',
        'month'            => 'Month',
        'active_days'      => 'Active Days',
        'transactions'     => 'Transactions',
        'amount'           => 'Total Amount ($)',
        'received'         => 'Amount ($)',
        'top_supplier'     => 'Top Supplier',
        'income_number'    => 'Income #',
        'title'            => 'Title / Description',
        'category'         => 'Category',
        'supplier'         => 'Supplier',
        'shop'             => 'Shop',
        'created_by'       => 'Recorded By',
        'action'           => 'Action',
        'na'               => 'N/A',
        'empty_grouped'    => 'No other income records found for the selected period.',
        'empty_ungrouped'  => 'No income records match the applied filters.',
    ],

    'modal' => [
        'details_title'    => 'Itemized Income Records for :period',
        'count'            => 'Total Records: :count',
        'total_amount'     => 'Total Amount: :amount',
        'expected_total'   => 'Expected: :amount',
        'received_total'   => 'Total Amount: :amount',
        'remaining_total'  => 'Balance: :amount',
        'close'            => 'Close',
        'loading'          => 'Loading records...',
    ],

    'excel' => [
        'exporting'        => 'Exporting Excel...',
        'button'           => 'Export to Excel',
        'sheet_name'       => 'Other Income Report',
    ],

    'days' => [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
        'sun' => 'Sunday',
    ],

    'months' => [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ],
];
