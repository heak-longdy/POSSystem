<?php

return [
    [
        'type'  => 'single',
        'active' => 'admin/product/*',
        'path' => 'admin/product/list/1',
        'permission' => ['product-view'],
        'name' => [
            'en' => 'Product',
            'km' => 'ផលិតផល',
        ],
        'icon' => 'bx-package',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/order/*',
        'path' => 'admin/order/list/1',
        'permission' => ['order-view'],
        'name' => [
            'en' => 'Orders',
            'km' => 'ការបញ្ជាទិញ',
        ],
        'icon' => 'bx-calendar',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/remaining-amount/*',
        'path' => 'admin/remaining-amount/list/all',
        'permission' => 'order-view',
        'name' => [
            'en' => 'Remaining Amount',
            'km' => 'គ្រប់គ្រងទឹកប្រាក់នៅសល់',
        ],
        'icon' => 'bx-wallet',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/shop/*',
        'path' => 'admin/shop/list/1',
        'permission' => ['shop-view'],
        'name' => [
            'en' => 'Shop',
            'km' => 'ហាង',
        ],
        'icon' => 'bx-store-alt',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/customer/*',
        'path' => 'admin/customer/list/1',
        'permission' => ['customer-view'],
        'name' => [
            'en' => 'Customer',
            'km' => 'អតិថិជន',
        ],
        'icon' => 'bx-user',
    ],
    // Inventory Management
    [
        'type'  => 'dropdown-multiple',
        'label' => [
            'en' => 'Inventory Management',
            'km' => 'ការគ្រប់គ្រងស្តុក',
        ],
        'listMenu' => [
            [
                'type'  => 'single',
                'active' => 'admin/stock-in/*',
                'path' => 'admin/stock-in/list/1',
                'permission' => ['stock-in-view'],
                'name' => [
                    'en' => 'Stock In',
                    'km' => 'ការបញ្ចូលស្តុក',
                ],
                'icon' => 'bx-universal-access',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/stock-out/*',
                'path' => 'admin/stock-out/list/1',
                'permission' => ['stock-out-view'],
                'name' => [
                    'en' => 'Stock Out',
                    'km' => 'ការដកស្តុក',
                ],
                'icon' => 'bx-universal-access',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/stock-transfer/*',
                'path' => 'admin/stock-transfer/list/1',
                'permission' => ['stock-transfer-view'],
                'name' => [
                    'en' => 'Stock Transfer',
                    'km' => 'ការផ្ទេរស្តុក',
                ],
                'icon' => 'bxl-redux',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/stock-on-hand/*',
                'path' => 'admin/stock-on-hand/list/1',
                'permission' => ['stock-on-hand-view'],
                'name' => [
                    'en' => 'Stock On Hand',
                    'km' => 'ស្តុកដែលមាន',
                ],
                'icon' => 'bx-infinite',
                'dropDown' => 'disable',
            ],
            [
                'active' => 'admin/stock-movement/*',
                'path' => 'admin/stock-movement/list/1',
                'permission' => ['stock-movement-view'],
                'name' => [
                    'en' => 'Stock Movement',
                    'km' => 'ការផ្លាស់ប្ដូរស្តុក',
                ],
                'icon' => 'bx-user',
                'dropDown' => 'disable',
                'children' => [],
            ]
        ]
    ],

    // Report Management
    [
        'type'  => 'dropdown-multiple',
        'label' => [
            'en' => 'Report Management',
            'km' => 'ការគ្រប់គ្រងរបាយការណ៍',
        ],
        'listMenu' => [
            [
                'type'  => 'single',
                'active' => 'admin/report/order-transaction*',
                'path' => 'admin/report/order-transaction/daily',
                'permission' => ['report-transaction-view', 'report-sales-view', 'order-view'],
                'name' => [
                    'en' => 'Order Transaction Report',
                    'km' => 'របាយការណ៍ប្រតិបត្តិការបញ្ជាទិញ',
                ],
                'icon' => 'bx-receipt',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/report/sales*',
                'path' => 'admin/report/sales/daily',
                'permission' => ['report-sales-view', 'order-view'],
                'name' => [
                    'en' => 'Sales Report',
                    'km' => 'របាយការណ៍ការលក់',
                ],
                'icon' => 'bx-bar-chart-alt-2',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/report/inventory-movement*',
                'path' => 'admin/report/inventory-movement/daily',
                'permission' => ['report-inventory-view', 'stock-movement-view', 'report-sales-view'],
                'name' => [
                    'en' => 'Inventory Movement Report',
                    'km' => 'របាយការណ៍បម្រែបម្រួលស្តុក',
                ],
                'icon' => 'bx-transfer-alt',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/report/staff-expense*',
                'path' => 'admin/report/staff-expense/daily',
                'permission' => ['staff-expense-view', 'report-sales-view'],
                'name' => [
                    'en' => 'Staff Expense Report',
                    'km' => 'របាយការណ៍ចំណាយបុគ្គលិក',
                ],
                'icon' => 'bx-wallet-alt',
                'dropDown' => 'disable',
            ],
        ]
    ],

    // Setting
    [
        'type'  => 'dropdown-multiple',
        'label' => [
            'en' => 'Setting & Application',
            'km' => 'ការកំណត់ និងកម្មវិធី',
        ],
        'listMenu' => [
            // [
            //     'path' => 'admin/OurService',
            //     'active' => 'admin/OurService*',
            //     'permission' => ['currency-view', 'page-view'],
            //     'name' => [
            //         'en' => 'Setting',
            //     ],
            //     'icon' => 'bx-cog',
            //     'dropDown' => 'disable',
            //     'children' => [],
            // ],
            [
                'type'  => 'single',
                'active' => 'admin/position/*',
                'path' => 'admin/position/list/1',
                'permission' => ['position-view'],
                'name' => [
                    'en' => 'Position',
                    'km' => 'មុខតំណែង',
                ],
                'icon' => 'bx-universal-access',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/staff/*',
                'path' => 'admin/staff/list/1',
                'permission' => ['staff-view'],
                'name' => [
                    'en' => 'Staff Management',
                    'km' => 'ការគ្រប់គ្រងបុគ្គលិក',
                ],
                'icon' => 'bx-group',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/staff-expense*',
                'path' => 'admin/staff-expense/list/1',
                'permission' => ['staff-expense-view', 'staff-view'],
                'name' => [
                    'en' => 'Staff Expenses',
                    'km' => 'ចំណាយបុគ្គលិក',
                ],
                'icon' => 'bx-dollar-circle',
                'dropDown' => 'disable',
            ],
            [
                'active' => 'admin/user/*',
                'path' => 'admin/user/list/1',
                'permission' => 'user-view',
                'name' => [
                    'en' => 'User Management',
                    'km' => 'ការគ្រប់គ្រងអ្នកប្រើប្រាស់',
                ],
                'icon' => 'bx-user',
                'dropDown' => 'disable',
                'children' => [],
            ],
            [
                'active' => 'admin/about/privacy*,admin/aboutUs*,admin/uom/*,admin/category/*,admin/supplier/*,admin/setting/invoice*',
                'permission' => ['about-view', 'uom-view'],
                'name' => [
                    'en' => 'Setting',
                    'km' => 'ការកំណត់',
                ],
                'icon' => 'bx-wrench',
                'children' => [
                    [
                        'active' => 'admin/category/*',
                        'path' => 'admin/category/list/1',
                        'permission' => 'category-view',
                        'name' => [
                            'en' => 'Category',
                            'km' => 'ប្រភេទ',
                        ],
                        'icon' => 'bx-category',
                    ],
                    [
                        'active' => 'admin/supplier/*',
                        'path' => 'admin/supplier/list/1',
                        'permission' => 'supplier-view',
                        'name' => [
                            'en' => 'Supplier',
                            'km' => 'អ្នកផ្គត់ផ្គង់',
                        ],
                        'icon' => 'bx-ruler',
                    ],
                    [
                        'active' => 'admin/uom/*',
                        'path' => 'admin/uom/list/1',
                        'permission' => 'uom-view',
                        'name' => [
                            'en' => 'Unit of Measure',
                            'km' => 'ខ្នាត',
                        ],
                        'icon' => 'bx-ruler',
                    ],
                    [
                        'active' => 'admin/setting/invoice*',
                        'path' => 'admin/setting/invoice',
                        'name' => [
                            'en' => 'Invoice Setting',
                            'km' => 'ការកំណត់វិក្កយបត្រ',
                        ],
                        'icon' => 'bx-receipt',
                    ]
                ],
            ],
        ]
    ],
    // endSetting
];
