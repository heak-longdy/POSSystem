<?php

return [
    'title' => 'ការគ្រប់គ្រងការផ្ទេរស្តុក',
    'select_shop' => 'ជ្រើសរើសហាង',

    'table' => [
        'no' => 'ល.រ',
        'product' => 'ផលិតផល',
        'category' => 'ប្រភេទ',
        'uom' => 'ខ្នាត',
        'qty' => 'បរិមាណ',
        'date' => 'កាលបរិច្ឆេទ',
        'remark' => 'ចំណាំ',
        'from_shop' => 'ពីហាង',
        'to_shop' => 'ទៅហាង',
        'requested_by' => 'ស្នើសុំដោយ',
        'status' => 'ស្ថានភាព',
    ],

    'button' => [
        'create' => 'បង្កើតការផ្ទេរស្តុក',
        'save' => 'រក្សាទុក',
        'save_new' => 'រក្សាទុក & បង្កើតថ្មី',
        'submit' => 'រក្សាទុក',
        'cancel' => 'បោះបង់',
        'edit' => 'កែប្រែ',
    ],

    'form' => [
        'title' => [
            'create' => 'បង្កើតការផ្ទេរស្តុក',
            'update' => 'កែប្រែការផ្ទេរស្តុក',
            'view' => 'មើលការផ្ទេរស្តុក',
        ],
        'from_shop' => 'ពីហាង',
        'select_from_shop' => 'ជ្រើសរើសហាង',
        'to_shop' => 'ទៅហាង',
        'select_to_shop' => 'ជ្រើសរើសហាង',
        'product' => 'ផលិតផល',
        'select_product' => 'ជ្រើសរើសផលិតផល',
        'current_stock' => 'ស្តុកបច្ចុប្បន្ន',
        'qty' => 'បរិមាណ',
        'placeholder_qty' => 'បញ្ចូលបរិមាណ...',
        'status' => 'ស្ថានភាព',
        'remark' => 'ចំណាំ',
        'placeholder_remark' => 'បញ្ចូលចំណាំ...',
    ],

    'status' => [
        'confirmed' => 'បានបញ្ជាក់',
        'disabled' => 'អសកម្ម',
    ],

    'filter' => [
        'search_product' => 'ស្វែងរកផលិតផល...',
        'select_shop' => 'ជ្រើសរើសហាង',
        'date' => 'កាលបរិច្ឆេទ',
    ],

    'empty' => [
        'title' => 'មិនមានការផ្ទេរស្តុកទេ',
        'description' => 'អ្នកអាចបង្កើតការផ្ទេរស្តុកថ្មីដោយចុចប៊ូតុងខាងក្រោម។',
    ],

    'validation' => [
        'from_shop_required' => 'សូមជ្រើសរើសហាងដើម',
        'from_shop_invalid' => 'ហាងដើមមិនត្រឹមត្រូវ',
        'to_shop_required' => 'សូមជ្រើសរើសហាងគោលដៅ',
        'to_shop_invalid' => 'ហាងគោលដៅមិនត្រឹមត្រូវ',
        'to_shop_different' => 'ហាងគោលដៅត្រូវតែខុសពីហាងដើម',
        'product_required' => 'សូមជ្រើសរើសផលិតផល',
        'product_invalid' => 'ផលិតផលមិនត្រឹមត្រូវ',
        'qty_required' => 'សូមបញ្ចូលបរិមាណ',
        'qty_integer' => 'ទម្រង់បរិមាណមិនត្រឹមត្រូវ',
        'qty_min' => 'បរិមាណត្រូវមានយ៉ាងតិច ១',
        'qty_limited' => 'បរិមាណមានកំណត់ ឬអស់ពីស្តុកហើយ',
        'remark_max' => 'ចំណាំមិនត្រូវលើសពី ១០០០ តួអក្សរឡើយ',
    ],

    'message' => [
        'create_success' => 'បង្កើតបានជោគជ័យ!',
        'create_failed' => 'បង្កើតមិនជោគជ័យ!',
        'update_success' => 'កែប្រែបានជោគជ័យ!',
        'update_failed' => 'កែប្រែមិនជោគជ័យ!',
        'enable_success' => 'បើកដំណើរការបានជោគជ័យ!',
        'disable_success' => 'បិទដំណើរការបានជោគជ័យ!',
        'status_failed' => 'ប្តូរស្ថានភាពមិនជោគជ័យ!',
        'delete_success' => 'លុបបានជោគជ័យ!',
        'delete_failed' => 'លុបមិនជោគជ័យ!',
        'restore_success' => 'ស្តារឡើងវិញបានជោគជ័យ!',
        'restore_failed' => 'ស្តារឡើងវិញមិនជោគជ័យ!',
        'destroy_success' => 'លុបជាអចិន្ត្រៃយ៍បានជោគជ័យ!',
        'destroy_failed' => 'លុបជាអចិន្ត្រៃយ៍មិនជោគជ័យ!',
    ],
];
