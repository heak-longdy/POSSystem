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
        'active' => 'admin/customer/*',
        'path' => 'admin/customer/list/1',
        'permission' => ['customer-view'],
        'name' => [
            'en' => 'Customer',
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
    // Setting
    [
        'type'  => 'dropdown-multiple',
        'label' => 'Setting & Application',
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
                ],
                'icon' => 'bx-universal-access',
                'dropDown' => 'disable',
            ],
            [
                'type'  => 'single',
                'active' => 'admin/sector/*',
                'path' => 'admin/sector/list/1',
                'permission' => ['sector-view'],
                'name' => [
                    'en' => 'Sector',
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
                'active' => 'admin/contact*,admin/about/privacy*,admin/OurService*,admin/aboutUs*',
                'permission' => ['contact-view', 'about-view'],
                'name' => [
                    'en' => 'Setting',
                ],
                'icon' => 'bx-wrench',
                'children' => [
                    [
                        'path' => 'admin/OurService',
                        'active' => 'admin/OurService',
                        'permission' => 'our-service-view',
                        'name' => [
                            'en' => 'Our Service',
                        ],
                        'icon' => 'bx-server',
                    ],
                    [
                        'path' => 'admin/contact',
                        'active' => 'admin/contact',
                        'permission' => 'contact-view',
                        'name' => [
                            'en' => 'Contact',
                        ],
                        'icon' => 'bx-book',
                    ],
                    [
                        'path' => 'admin/aboutUs',
                        'active' => 'admin/aboutUs',
                        'permission' => 'about-view',
                        'name' => [
                            'en' => 'About',
                        ],
                        'icon' => 'bx-help-circle',
                    ],
                ],
            ],
        ]
    ],
    // endSetting
];
