<?php

namespace App\Helper;

class Menu
{

    static $menu_list = [
        [
            'path' => 'admin/dashboard',
            'active' => 'admin/dashboard',
            'permission' => 'dashboard-view',
            'name' => [
                'en' => 'Dashboard',
                'km' => 'ផ្ទាំងគ្រប់គ្រង',
            ],
            'icon' => 'bxs-dashboard',
        ],
        // Booking
        [
            'path' => 'admin/booking/list/1',
            'active' => 'admin/booking*',
            'permission' => 'booking-view',
            'name' => [
                'en' => 'Bookings',
            ],
            'icon' => 'bxs-book',
        ],
        // Customer
        [
            'path' => 'admin/customer/list/1',
            'active' => 'admin/customer/*',
            'permission' => 'customer-view',
            'name' => [
                'en' => 'Customer',
            ],
            'icon' => 'bxs-user',
        ],
        // CustomerPoint
        [
            'path' => 'admin/customer-point/list',
            'active' => 'admin/customer-point/*',
            'permission' => 'customer-point-view',
            'name' => [
                'en' => 'Customer Point',
            ],
            'icon' => 'bxs-cube-alt',
        ],
        // Report Transaction
        [
            'path' => 'admin/report-transaction/list',
            'active' => 'admin/report-transaction/*',
            'permission' => 'report-transaction-view',
            'name' => [
                'en' => 'Transaction Report',
            ],
            'icon' => 'bx-transfer-alt',
        ],
        // Report Summary
        [
            'path' => 'admin/report-summary/list',
            'active' => 'admin/report-summary/*',
            'permission' => 'report-summary-view',
            'name' => [
                'en' => 'Summary Report',
            ],
            'icon' => 'bxs-report',
        ],
        // Shop
        [
            'path' => 'admin/shop/list/1',
            'active' => 'admin/shop*',
            'permission' => 'shop-view',
            'name' => [
                'en' => 'Shops',
            ],
            'icon' => 'bxs-store',
        ],
        // Barber
        [
            'path' => 'admin/barber/list/1',
            'active' => 'admin/barber*',
            'permission' => 'barber-view',
            'name' => [
                'en' => 'Barbers',
            ],
            'icon' => 'bxs-universal-access',
        ],
        // Wallet
        [
            'path' => 'admin/wallet/list/1',
            'active' => 'admin/wallet*',
            'permission' => 'wallet-view',
            'name' => [
                'en' => 'Wallet Request',
            ],
            'icon' => 'bxs-wallet',
        ],
        // Product
        [
            'path' => 'admin/product/list/1',
            'active' => 'admin/product*',
            'permission' => 'product-view',
            'name' => [
                'en' => 'Products',
            ],
            'icon' => 'bxl-product-hunt',
        ],
    
    
        // Service
        [
            'path' => 'admin/service/list/1',
            'active' => 'admin/service*',
            'permission' => 'service-view',
            'name' => [
                'en' => 'Services',
            ],
            'icon' => 'bxs-server',
        ],
        // Promotion
        [
            'path' => 'admin/promotion/list/1',
            'active' => 'admin/promotion*',
            'permission' => 'promotion-view',
            'name' => [
                'en' => 'Promotion',
            ],
            'icon' => 'bxs-info-square',
        ],
        // Slide
        [
            'path' => 'admin/slide/list/1',
            'active' => 'admin/slide*',
            'permission' => 'slide-view',
            'name' => [
                'en' => 'Banner',
            ],
            'icon' => 'bxs-image',
        ],
        // user
        [
            'type'  => 'single',
            'active' => 'admin/user/*,admin/member/*,admin/garage/*',
            'permission' => 'user-view',
            'permission' => ['user-view', 'garage-view', 'member-view'],
            'path' => 'admin/user/list/1',
            'name' => [
                'en' => 'Users',
            ],
            'icon' => 'bxs-user-plus',
        ],
    
        [
            'type'  => 'dropdown-multiple',
            'label' => 'Application',
            'list-menu' => [
                [
                    'active' => 'admin/stock-in/*,admin/stock-out/*,admin/stock-transfer/*,admin/stock-on-hand/*,admin/stock-movement/*',
                    'permission' => ['stock-in-view', 'stock-out-view', 'stock-transfer-view', 'stock-on-hand-view', 'stock-movement-view'],
                    'name' => [
                        'en' => 'Build',
                    ],
                    'icon' => 'archive',
                    'children' => [
                        [
                            'path' => 'admin/stock-in/list',
                            'active' => 'admin/stock-in/*',
                            'permission' => 'stock-in-view',
                            'name' => [
                                'en' => 'Authentication',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                        [
                            'path' => 'admin/stock-out/list',
                            'active' => 'admin/stock-out/*',
                            'permission' => 'stock-out-view',
                            'name' => [
                                'en' => 'App Check',
                            ],
                            'icon' => 'bxs-check-shield',
                        ],
                        [
                            'path' => 'admin/stock-transfer/list',
                            'active' => 'admin/stock-transfer/*',
                            'permission' => 'stock-transfer-view',
                            'name' => [
                                'en' => 'Storage',
                            ],
                            'icon' => 'bxs-folder',
                        ],
                        [
                            'path' => 'admin/stock-on-hand/list',
                            'active' => 'admin/stock-on-hand/*',
                            'permission' => 'stock-on-hand-view',
                            'name' => [
                                'en' => 'Extensions',
                            ],
                            'icon' => 'bxs-extension',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Hosting',
                            ],
                            'icon' => 'bx-world',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Remote Config',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                    ],
                ],
                [
                    'active' => 'admin/stock-in/*,admin/stock-out/*,admin/stock-transfer/*,admin/stock-on-hand/*,admin/stock-movement/*',
                    'permission' => ['stock-in-view', 'stock-out-view', 'stock-transfer-view', 'stock-on-hand-view', 'stock-movement-view'],
                    'name' => [
                        'en' => 'Release & Monitor',
                    ],
                    'icon' => 'archive',
                    'children' => [
                        [
                            'path' => 'admin/stock-in/list',
                            'active' => 'admin/stock-in/*',
                            'permission' => 'stock-in-view',
                            'name' => [
                                'en' => 'Authentication',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                        [
                            'path' => 'admin/stock-out/list',
                            'active' => 'admin/stock-out/*',
                            'permission' => 'stock-out-view',
                            'name' => [
                                'en' => 'App Check',
                            ],
                            'icon' => 'bxs-check-shield',
                        ],
                        [
                            'path' => 'admin/stock-transfer/list',
                            'active' => 'admin/stock-transfer/*',
                            'permission' => 'stock-transfer-view',
                            'name' => [
                                'en' => 'Storage',
                            ],
                            'icon' => 'bxs-folder',
                        ],
                        [
                            'path' => 'admin/stock-on-hand/list',
                            'active' => 'admin/stock-on-hand/*',
                            'permission' => 'stock-on-hand-view',
                            'name' => [
                                'en' => 'Extensions',
                            ],
                            'icon' => 'bxs-extension',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Hosting',
                            ],
                            'icon' => 'bx-world',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Remote Config',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                    ],
                ],
                [
                    'active' => 'admin/stock-in/*,admin/stock-out/*,admin/stock-transfer/*,admin/stock-on-hand/*,admin/stock-movement/*',
                    'permission' => ['stock-in-view', 'stock-out-view', 'stock-transfer-view', 'stock-on-hand-view', 'stock-movement-view'],
                    'name' => [
                        'en' => 'Analytics',
                    ],
                    'icon' => 'archive',
                    'children' => [
                        [
                            'path' => 'admin/stock-in/list',
                            'active' => 'admin/stock-in/*',
                            'permission' => 'stock-in-view',
                            'name' => [
                                'en' => 'Authentication',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                        [
                            'path' => 'admin/stock-out/list',
                            'active' => 'admin/stock-out/*',
                            'permission' => 'stock-out-view',
                            'name' => [
                                'en' => 'App Check',
                            ],
                            'icon' => 'bxs-check-shield',
                        ],
                        [
                            'path' => 'admin/stock-transfer/list',
                            'active' => 'admin/stock-transfer/*',
                            'permission' => 'stock-transfer-view',
                            'name' => [
                                'en' => 'Storage',
                            ],
                            'icon' => 'bxs-folder',
                        ],
                        [
                            'path' => 'admin/stock-on-hand/list',
                            'active' => 'admin/stock-on-hand/*',
                            'permission' => 'stock-on-hand-view',
                            'name' => [
                                'en' => 'Extensions',
                            ],
                            'icon' => 'bxs-extension',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Hosting',
                            ],
                            'icon' => 'bx-world',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Remote Config',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                    ],
                ],
                [
                    'active' => 'admin/stock-in/*,admin/stock-out/*,admin/stock-transfer/*,admin/stock-on-hand/*,admin/stock-movement/*',
                    'permission' => ['stock-in-view', 'stock-out-view', 'stock-transfer-view', 'stock-on-hand-view', 'stock-movement-view'],
                    'name' => [
                        'en' => ' Engage ',
                    ],
                    'icon' => 'archive',
                    'children' => [
                        [
                            'path' => 'admin/stock-in/list',
                            'active' => 'admin/stock-in/*',
                            'permission' => 'stock-in-view',
                            'name' => [
                                'en' => 'Authentication',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                        [
                            'path' => 'admin/stock-out/list',
                            'active' => 'admin/stock-out/*',
                            'permission' => 'stock-out-view',
                            'name' => [
                                'en' => 'App Check',
                            ],
                            'icon' => 'bxs-check-shield',
                        ],
                        [
                            'path' => 'admin/stock-transfer/list',
                            'active' => 'admin/stock-transfer/*',
                            'permission' => 'stock-transfer-view',
                            'name' => [
                                'en' => 'Storage',
                            ],
                            'icon' => 'bxs-folder',
                        ],
                        [
                            'path' => 'admin/stock-on-hand/list',
                            'active' => 'admin/stock-on-hand/*',
                            'permission' => 'stock-on-hand-view',
                            'name' => [
                                'en' => 'Extensions',
                            ],
                            'icon' => 'bxs-extension',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Hosting',
                            ],
                            'icon' => 'bx-world',
                        ],
                        [
                            'path' => 'admin/stock-movement/list',
                            'active' => 'admin/stock-movement/*',
                            'permission' => 'stock-movement-view',
                            'name' => [
                                'en' => 'Remote Config',
                            ],
                            'icon' => 'bxl-xing',
                        ],
                    ],
                ],
            ]
    
        ],
    
    
    
        [
            'type'  => 'dropdown-multiple',
            'label' => 'Setting',
            'list-menu' => [
                [
                    'type'  => 'dropdown-multiple',
                    'active' => 'admin/page/*,admin/contact/*',
                    'permission' => ['about-view', 'privacy-view', 'term-condition-view', 'contact-view'],
                    'name' => [
                        'en' => 'Pages',
                    ],
                    'icon' => 'book-open',
                    'children' => [
                        [
                            'path' => 'admin/page/about',
                            'active' => 'admin/page/about',
                            'permission' => 'about-view',
                            'name' => [
                                'en' => 'About',
                            ],
                        ],
                        [
                            'path' => 'admin/contact/privacy',
                            'active' => 'admin/contact/privacy',
                            'permission' => 'privacy-view',
                            'name' => [
                                'en' => 'Privacy',
                            ],
                        ],
                        [
                            'path' => 'admin/contact/contact',
                            'active' => 'admin/contact/contact',
                            'permission' => 'contact-view',
                            'name' => [
                                'en' => 'Contact',
                            ],
                        ],
                    ],
                ],
                // Setting children about and contact
                [
                    'type'  => 'dropdown-single',
                    'active' => 'admin/category/*,admin/supplier/*,admin/uom/*,admin/setting/data,admin/reward/*,admin/pointSetting/*,admin/brand/*,admin/brandSetting/*',
                    'permission' => ['category-view', 'supplier-view', 'uom-view', 'reward-view', 'data-view', 'pointSetting-view', 'brand-view', 'brandSetting-view'],
                    'name' => [
                        'en' => 'Settings',
                    ],
                    'icon' => 'settings',
                    'children' => [
                        [
                            'path' => 'admin/category/list/1',
                            'active' => 'admin/category/*',
                            'permission' => 'category-view',
                            'name' => [
                                'en' => 'Category',
                            ],
                        ],
                        [
                            'path' => 'admin/supplier/list/1',
                            'active' => 'admin/supplier/*',
                            'permission' => 'supplier-view',
                            'name' => [
                                'en' => 'Supplier',
                            ],
                        ],
                        [
                            'path' => 'admin/uom/list/1',
                            'active' => 'admin/uom/*',
                            'permission' => 'uom-view',
                            'name' => [
                                'en' => 'UOM',
                            ],
                        ],
                        [
                            'path' => 'admin/brand/list/1',
                            'active' => 'admin/brand/*',
                            'permission' => 'brand-view',
                            'name' => [
                                'en' => 'Brand',
                            ],
                        ],
                        [
                            'path' => 'admin/brandSetting/list/1',
                            'active' => 'admin/brandSetting/*',
                            'permission' => 'brand-setting-view',
                            'name' => [
                                'en' => 'Brand Setting',
                            ],
                        ],
                        [
                            'path' => 'admin/reward/list/1',
                            'active' => 'admin/reward/*',
                            'permission' => 'reward-view',
                            'name' => [
                                'en' => 'Point',
                            ],
                        ],
                        [
                            'path' => 'admin/setting/data',
                            'active' => 'admin/setting/data',
                            'permission' => 'top-up-rate-view',
                            'name' => [
                                'en' => 'Top Up Rate',
                            ],
                        ],
    
                    ],
                ],
            ]
    
        ],
        // page children about and contact
    
    
        // // Setting children about and contact
        // [
        //     'active' => 'admin/category/*,admin/uom/*,admin/setting/data',
        //     'permission' => ['category-view','uom-view','setting-view'],
        //     'name' => [
        //         'en' => 'Settings',
        //     ],
        //     'icon' => 'settings',
        //     'children' => [
        //         [
        //             'path' => 'admin/category/list/1',
        //             'active' => 'admin/category/*',
        //             'permission' => 'category-view',
        //             'name' => [
        //                 'en' => 'Category',
        //             ],
        //         ],
        //         [
        //             'path' => 'admin/uom/list/1',
        //             'active' => 'admin/uom/*',
        //             'permission' => 'uom-view',
        //             'name' => [
        //                 'en' => 'UOM',
        //             ],
        //         ],
        //         [
        //             'path' => 'admin/reward/list/1',
        //             'active' => 'admin/reward/*',
        //             'permission' => 'reward-view',
        //             'name' => [
        //                 'en' => 'Points',
        //             ],
        //         ],
        //     ],
        // ],
    
    ];

    public static function menuList()
    {
        return self::convertToCollection(self::$menu_list);
    }

    public static function convertToCollection($list)
    {
        return collect($list)->map(function ($voucher) {
            if (is_array($voucher)) {
                return self::convertToCollection($voucher);
            }
            return $voucher;
        });
    }
}
