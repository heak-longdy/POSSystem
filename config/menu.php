<?php

return [
    // [
    //     'path' => 'admin/dashboard',
    //     'active' => 'admin/dashboard',
    //     'permission' => 'dashboard-view',
    //     'name' => [
    //         'en' => 'Dashboard',
    //         'km' => 'ផ្ទាំងគ្រប់គ្រង',
    //     ],
    //     'icon' => 'bxs-dashboard',
    // ],
    // Booking
    // [
    //     'path' => 'admin/booking/list/1',
    //     'active' => 'admin/booking*',
    //     'permission' => 'booking-view',
    //     'name' => [
    //         'en' => 'Bookings',
    //     ],
    //     'icon' => 'bx-bookmark',
    // ],
    // user
    // [
    //     'type'  => 'single',
    //     'active' => 'admin/customer/*',
    //     'path' => 'admin/customer/list/1',
    //     'permission' => ['user-view'],
    //     'name' => [
    //         'en' => 'Users',
    //     ],
    //     'icon' => 'bx-user',
    // ],
    
    // admin
    // [
    //     'type'  => 'single',
    //     'active' => 'admin/internships/*',
    //     'path' => 'admin/internships/list/1',
    //     'permission' => ['internships-view'],
    //     'name' => [
    //         'en' => 'Internship',
    //     ],
    //     'icon' => 'bxs-graduation',
    // ],
    // [
    //     'type'  => 'single',
    //     'active' => 'admin/blog/*',
    //     'path' => 'admin/blog/list/1',
    //     'permission' => ['blog-view'],
    //     'name' => [
    //         'en' => 'Blog',
    //     ],
    //     'icon' => 'bx-briefcase',
    // ],
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
        'active' => 'admin/booking/*',
        'path' => 'admin/booking/list/1',
        'permission' => 'booking-view',
        'name' => [
            'en' => 'Bookings',
            'km' => 'ការកក់',
        ],
        'icon' => 'bx-calendar',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/remaining-amount/*',
        'path' => 'admin/remaining-amount/list/all',
        'permission' => 'booking-view',
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
    [
        'type'  => 'single',
        'active' => 'admin/customer-paid/*',
        'path' => 'admin/customer-paid/list/1',
        'permission' => ['customer-paid-view'],
        'name' => [
            'en' => 'Pay Note',
            'km' => 'កំណត់ត្រាបង់ប្រាក់',
        ],
        'icon' => 'bx-dollar',
    ],
    [
        'type'  => 'single',
        'active' => 'admin/testimonial/*',
        'path' => 'admin/testimonial/list/1',
        'permission' => ['testimonial-view'],
        'name' => [
            'en' => 'Testimonial',
            'km' => 'មតិយោបល់',
        ],
        'icon' => 'bx-network-chart',
        'dropDown' => 'disable',
    ],
    // [
    //     'type'  => 'single',
    //     'active' => 'admin/position/*',
    //     'path' => 'admin/position/list/1',
    //     'permission' => ['position-view'],
    //     'name' => [
    //         'en' => 'Position',
    //     ],
    //     'icon' => 'bxl-ok-ru',
    // ],
    
    // [
    //     'type'  => 'single',
    //     'active' => 'admin/user/*',
    //     'path' => 'admin/user/list/1',
    //     'permission' => ['admin-view'],
    //     'name' => [
    //         'en' => 'User',
    //     ],
    //     'icon' => 'bx-user',
    // ],
    // Setting
    // [
    //     'type'  => 'dropdown-multiple',
    //     'label' => 'Administrator',
    //     'list-menu' => [
    //         [
    //             'active' => 'admin/user/*',
    //             'path' => 'admin/user/list/1',
    //             'permission' => 'user-view',
    //             'name' => [
    //                 'en' => 'User',
    //             ],
    //             'icon' => 'bx-user',
    //             'dropDown'=>'disable',
    //             'children' => [],
    //         ]
    //     ]
    // ],
    // [
    //     'type'  => 'dropdown-single',
    //     'label' => 'Administrator',
    //     'active' => 'admin/user/*',
    //     'permission' => ['user-view'],
    //     'name' => [
    //         'en' => 'Setting',
    //     ],
    //     'icon' => 'bx-wrench',
    //     'children' => [
    //         [
    //             'active' => 'admin/user/*',
    //             'path' => 'admin/user/list/1',
    //             'permission' => 'user-view',
    //             'name' => [
    //                 'en' => 'User',
    //             ],
    //             'icon' => 'bx-user',
    //         ],
    //     ],
    // ],
    // [
    //     'type'  => 'dropdown-single',
    //     'label' => 'Application',
    //     'active' => 'admin/contact/*,admin/about/privacy*,admin/OurService*,admin/aboutUs*',
    //     'permission' => ['contact-view', 'about-view'],
    //     'name' => [
    //         'en' => 'Setting',
    //     ],
    //     'icon' => 'bx-wrench',
    //     'children' => [
    //         [
    //             'path' => 'admin/OurService',
    //             'active' => 'admin/OurService',
    //             'permission' => 'our-service-view',
    //             'name' => [
    //                 'en' => 'Our Service',
    //             ],
    //             'icon' => 'bx-help-circle',
    //         ],
    //         [
    //             'path' => '#',
    //             'active' => '#',
    //             'permission' => 'contact-view',
    //             'name' => [
    //                 'en' => 'Contact',
    //             ],
    //             'icon' => 'bx-book',
    //         ],
    //         [
    //             'path' => 'admin/aboutUs',
    //             'active' => 'admin/aboutUs',
    //             'permission' => 'about-view',
    //             'name' => [
    //                 'en' => 'About',
    //             ],
    //             'icon' => 'bx-help-circle',
    //         ],
    //     ],
    // ],

    // Inventory Management
    [
        'type'  => 'dropdown-multiple',
        'label' => [
            'en' => 'Inventory Management',
            'km' => 'ការកំណត់ និងកម្មវិធី',
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
                    'km' => 'ការបញ្ចូលស្តុក',
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
                    'km' => 'ការផ្លាស់ប្ដូរស្តុក',
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
                'type'  => 'single',
                'active' => 'admin/sector/*',
                'path' => 'admin/sector/list/1',
                'permission' => ['sector-view'],
                'name' => [
                    'en' => 'Sector',
                    'km' => 'ផ្នែក',
                ],
                'icon' => 'bx-compass',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/partner/*',
                'path' => 'admin/partner/list/1',
                'permission' => ['partner-view'],
                'name' => [
                    'en' => 'Partner',
                    'km' => 'ដៃគូ',
                ],
                'icon' => 'bxl-redux',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/placement-type/*',
                'path' => 'admin/placement-type/list/1',
                'permission' => ['placement-type-view'],
                'name' => [
                    'en' => 'Placement Type',
                    'km' => 'ប្រភេទការដាក់ទីតាំង',
                ],
                'icon' => 'bx-badge-check',
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
                // 'active' => 'admin/report/revenue/*,admin/report/expense/*',
                // 'permission' => ['report-view', 'report-revenue', 'report-expense'],
                // 'name' => [
                //     'en' => 'Reports',
                // ],
                // 'icon' => 'bxs-report',
                'active' => 'admin/contact*,admin/about/privacy*,admin/OurService*,admin/aboutUs*,admin/uom/*,admin/category/*,admin/supplier/*',
                'permission' => ['contact-view', 'about-view', 'uom-view'],
                'name' => [
                    'en' => 'Setting',
                    'km' => 'ការកំណត់',
                ],
                'icon' => 'bx-wrench',
                'children' => [
                    [
                        'path' => 'admin/OurService',
                        'active' => 'admin/OurService',
                        'permission' => 'our-service-view',
                        'name' => [
                            'en' => 'Our Service',
                            'km' => 'សេវាកម្មរបស់យើង',
                        ],
                        'icon' => 'bx-server',
                    ],
                    [
                        'path' => 'admin/contact',
                        'active' => 'admin/contact',
                        'permission' => 'contact-view',
                        'name' => [
                            'en' => 'Contact',
                            'km' => 'ទំនាក់ទំនង',
                        ],
                        'icon' => 'bx-book',
                    ],
                    [
                        'path' => 'admin/aboutUs',
                        'active' => 'admin/aboutUs',
                        'permission' => 'about-view',
                        'name' => [
                            'en' => 'About',
                            'km' => 'អំពីយើង',
                        ],
                        'icon' => 'bx-help-circle',
                    ],
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
                            'km' => 'Unit of Measure',
                        ],
                        'icon' => 'bx-ruler',
                    ]
                ],
            ],
        ]
    ],
    // endSetting
];
