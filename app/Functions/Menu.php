<?php

namespace App\Helper;

class Menu
{

    static $menu_list = [
        [
            'path' => 'admin/dashboard',
            'active' => 'admin/dashboard',
            'permission' => 'dashboard',
            'name' => [
                'en' => 'Dashboard',
                'km' => 'ផ្ទាំងគ្រប់គ្រង',
            ],
            'icon' => 'grid',
        ],
        [
            'path' => 'admin/product/list/1',
            'active' => 'admin/product',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Product',
                'km' => 'ផលិតផល',
            ],
            'icon' => 'archive',
        ],
        [
            'path' => 'admin/category/list/1',
            'active' => 'admin/category',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Category',
                'km' => 'កញ្ចប់គម្រោងសមាជិក',
            ],
            'icon' => 'package',

        ],
        [
            'path' => 'admin/sub-category/list/1',
            'active' => 'admin/sub-category',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Sub Category',
                'km' => 'កញ្ចប់គម្រោងសមាជិក',
            ],
            'icon' => 'package',

        ],
        [
            'path' => 'admin/membership/list/1',
            'active' => 'admin/membership',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Membership',
                'km' => 'កញ្ចប់គម្រោងសមាជិក',
            ],
            'icon' => 'package',

        ],
        [
            'path' => 'admin/network/list',
            'active' => 'admin/network',
            'permission' => 'network_view',
            'name' => [
                'en' => 'Network',
                'km' => 'បណ្ដាញ',
            ],
            'icon' => 'share-2',
        ],
        [
            'path' => 'admin/order/list/1',
            'active' => 'admin/order',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Order',
                'km' => 'កញ្ចប់គម្រោងសមាជិក',
            ],
            'icon' => 'package',

        ],
        [
            'path' => 'admin/shop/list/1',
            'active' => 'admin/shop',
            // 'permission' => 'membership_view',
            'name' => [
                'en' => 'Shop',
                'km' => 'ហាងទំនិញ',
            ],
            'icon' => 'package',

        ],
        [
            'path' => 'admin/request/withdraw/1',
            'active' => 'admin/request/withdraw',
            'permission' => 'request_view',
            'name' => [
                'en' => 'Request Withdraw',
                'km' => 'សំណើរដកប្រាក់',
            ],
            'icon' => 'dollar-sign',
        ],
        [
            'path' => 'admin/payment-history/list',
            'active' => 'admin/payment-history',
            'permission' => 'payment_history_view',
            'name' => [
                'en' => 'Payment History',
                'km' => 'ប្រវត្តិការទូទាត់',
            ],
            'icon' => 'credit-card',
        ],
        [
            'path' => 'admin/report/list',
            'active' => 'admin/report',
            'permission' => 'report_view',
            'name' => [
                'en' => 'Report',
                'km' => 'របាយការណ៍',
            ],
            'icon' => 'file-text',
        ],
        [
            'path' => 'admin/transfer/list',
            'active' => 'admin/transfer',
            'permission' => 'transfer_view',
            'name' => [
                'en' => 'Transfer',
                'km' => 'ការផ្ទេរប្រាក់',
            ],
            'icon' => 'send',
        ],
        [
            'path' => 'admin/redeem/list/1',
            'active' => 'admin/redeem',
            'permission' => 'redeem_code_view',
            'name' => [
                'en' => 'Redeem Code',
                'km' => 'លេខកូដប្រតិបត្តិការ',
            ],
            'icon' => 'hash',
        ],
        [
            'path' => 'admin/slideshow/list/1',
            'active' => 'admin/slideshow',
            'permission' => 'slideshow_view',
            'name' => [
                'en' => 'Slideshow',
                'km' => 'ស្លាយ',
            ],
            'icon' => 'airplay',
        ],
        // [
        //     'path' => 'admin/student/list',
        //     'active' => 'admin/student',
        //     'permission' => 'student_view',
        //     'name' => [
        //         'en' => 'Student',
        //         'km' => 'សិស្ស',
        //     ],
        //     'icon' => 'user',
        // ],
        [
            'path' => 'admin/member/list/1',
            'active' => 'admin/member',
            'permission' => 'member_view',
            'name' => [
                'en' => 'Member',
                'km' => 'សមាជិក',
            ],
            'icon' => 'users',
        ],
        // [
        //     'path' => 'admin/author/list/1',
        //     'active' => 'admin/author',
        //     'permission' => 'author_view',
        //     'name' => [
        //         'en' => 'Author',
        //         'km' => 'អ្នកនិពន្ធ',
        //     ],
        //     'icon' => 'smile',
        // ],
        // [
        //     'path' => 'admin/agency/request/1',
        //     'active' => 'admin/agency/request',
        //     'permission' => 'agency_request_view',
        //     'name' => [
        //         'en' => 'Agency Request',
        //         'km' => 'សំណើរជាភ្នាក់ងារ',
        //     ],
        //     'icon' => 'trending-up',
        // ],
        // [
        //     'path' => 'admin/agency/list/1',
        //     'active' => 'admin/agency/list',
        //     'permission' => 'agency_view',
        //     'name' => [
        //         'en' => 'Agency',
        //         'km' => 'ភ្នាក់ងារ',
        //     ],
        //     'icon' => 'share-2',
        // ],
        [
            'path' => 'admin/user/list/1',
            'active' => 'admin/user',
            'permission' => 'user_view',
            'name' => [
                'en' => 'User Management',
                'km' => 'អ្នកប្រើប្រាស់',
            ],
            'icon' => 'star',
        ],
        [
            'path' => 'admin/info',
            'active' => 'admin/info',
            'permission' => 'info_view',
            'name' => [
                'en' => 'Information',
                'km' => 'ព័ត៌មាន',
            ],
            'icon' => 'phone-call',
        ],
        [
            'path' => 'admin/about',
            'active' => 'admin/about',
            'permission' => 'about_view',
            'name' => [
                'en' => 'About',
                'km' => 'ទំនាក់ទំនង',
            ],
            'icon' => 'info',
        ],
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
