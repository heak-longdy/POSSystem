<?php

return [
    'title'    => 'Purchase Report Management',
    'subtitle' => 'Supplier Purchases & Procurement Breakdown',

    'tab' => [
        'daily'   => 'Daily Purchases Report',
        'monthly' => 'Monthly Purchases Report',
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
        'shop'               => 'Shop / Branch',
        'all_shops'          => 'All Shops',
        'supplier'           => 'Supplier',
        'all_suppliers'      => 'All Suppliers',
        'status'             => 'Purchase Status',
        'status_all'         => 'All Statuses',
        'payment_status'     => 'Payment Status',
        'all_payment_statuses'=> 'All Payments',
        'keyword_search'     => 'Search',
        'placeholder_search' => 'PO #, Invoice #, Supplier...',
        'placeholder_date'   => 'YYYY-MM-DD',
    ],

    'kpi' => [
        'total_purchases'    => 'Total Purchases Count',
        'total_volume'       => 'Total Purchases ($)',
        'total_paid'         => 'Total Paid ($)',
        'total_outstanding'  => 'Total Outstanding / AP ($)',
    ],

    'table' => [
        'no'                  => 'Nº',
        'date'                => 'Date',
        'purchase_number'     => 'PO Number',
        'supplier_invoice_no' => 'Vendor Invoice #',
        'supplier'            => 'Supplier',
        'shop'                => 'Shop / Branch',
        'items_count'         => 'Items Count',
        'total_amount'        => 'Total Amount ($)',
        'paid_amount'         => 'Paid Amount ($)',
        'remaining_amount'    => 'Outstanding Balance ($)',
        'status'              => 'Status',
        'payment_status'      => 'Payment Status',
        'purchases_count'     => 'POs Count',
    ],

    'excel' => [
        'exporting' => 'Exporting Purchases...',
        'filename'  => 'Purchase_Report',
        'sheet'     => 'Purchases',
    ],
];
