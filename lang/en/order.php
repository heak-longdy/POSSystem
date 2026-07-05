<?php

return [
    'name' => 'Order',
    'title' => 'Order Management',
    'report_title' => 'Order Reports',
    'tab' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ],
    'breadcrumb' => [
        'all' => 'All',
    ],
    'filter' => [
        'search' => 'Search...',
        'all' => 'All',
    ],
    'button' => [
        'create' => 'Create New',
        'import' => 'Import Categories',
        'reload' => 'Refresh',
        'search' => 'Search',
        'export' => 'Export'
    ],
    'empty' => [
        'title' => ':name is empty',
        'description' => 'You can create a new :name by clicking the button below.',
    ],
    'order_notify' => [
        '2' => [
            'title' => 'Your order :number have confirmed',
            'description' => 'Please checking your order that have invoice number :number'
        ],
        '3' => [
            'title' => 'Your order :number have cancelled',
            'description' => 'Please checking your order that have invoice number :number'
        ],
        '4' => [
            'title' => 'Your order :number have completed',
            'description' => 'Please checking your order that have invoice number :number'
        ],
    ],
    'form' => [
        'title' => [
            'create' => 'Create Order',
            'update' => 'Update Order',
            'detail' => 'Order Detail'
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'disable' => 'Disable',
        ],
        'button' => [
            'update' => 'Update',
            'submit' => 'Submit',
            'cancel' => 'Cancel',
        ],
        'photo' => [
            'label' => 'Image',
            'placeholder' => 'Enter picture',
        ],
        'name' => [
            'en' => [
                'label' => 'Title English',
                'placeholder' => 'Enter title English'
            ],
            'km' => [
                'label' => 'Title Khmer',
                'placeholder' => 'Enter title Khmer'
            ],
            'zh' => [
                'label' => 'Title Chinese',
                'placeholder' => 'Enter title Chinese'
            ]
        ],
        'ordering' => [
            'label' => 'Ordering',
            'placeholder' => 'Enter ordering',
        ],
        'popular_ordering' => [
            'label' => 'Ordering Popular',
            'placeholder' => 'Enter ordering popular',
        ],
    ],
];
